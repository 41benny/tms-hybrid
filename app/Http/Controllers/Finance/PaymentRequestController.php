<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\VendorBill;
use App\Models\Master\Vendor;
use App\Models\Operations\PaymentRequest;
use App\Models\Finance\PaymentRecipient;
use App\Models\User;
use App\Notifications\PaymentRequestCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentRequestController extends Controller
{
    /**
     * Payment Requests - Bisa diakses user dengan permission 'payment-requests'
     * Sales/Admin bisa ajukan pembayaran tanpa perlu akses dashboard hutang
     */
    public function index(Request $request)
    {
        $query = PaymentRequest::query()->with([
            'vendorBill.vendor',
            'vendor',
            'vendorBankAccount',
            'requestedBy',
            'driverAdvance.driver',
        ]);

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if ($user && $user->role === User::ROLE_SALES) {
            $salesProfile = $user->salesProfile;
            if ($salesProfile) {
                $query->forSales($salesProfile->id);
            } else {
                $query->whereRaw('1=0');
            }
        } elseif ($user && ($user->role ?? 'admin') !== User::ROLE_SUPER_ADMIN) {
            // Non-superadmin lain: default hanya yang dia ajukan
            $query->where('requested_by', $user->id);
        }

        // Filter status spesifik jika diminta
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Default: sembunyikan yang sudah dibayar kecuali user minta show=all atau filter status eksplisit
        $show = $request->get('show');
        if (! $status && $show !== 'all') {
            $query->where('status', '!=', 'paid');
        }

        // Filter date range
        if ($from = $request->get('from')) {
            $query->whereDate('request_date', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('request_date', '<=', $to);
        }

        $requests = $query->latest('request_date')->paginate(15)->withQueryString();

        // Vendor bills outstanding (gunakan scope + accessor)
        $unpaidBills = VendorBill::with(['vendor', 'items', 'paymentRequests'])
            ->outstanding()
            ->orderBy('bill_date', 'asc')
            ->get();

        // Driver advances outstanding (belum diajukan penuh)
        $outstandingAdvances = \App\Models\Operations\DriverAdvance::with(['driver', 'shipmentLeg.jobOrder', 'paymentRequests'])
            ->outstanding()
            ->orderBy('advance_date', 'asc')
            ->get();

        return view('payment-requests.index', compact('requests', 'unpaidBills', 'outstandingAdvances', 'show'));
    }

    public function create(Request $request)
    {
        try {
            $vendorBillId = $request->get('vendor_bill_id');
            $driverAdvanceId = $request->get('driver_advance_id');
            $vendorBill = null;
            $driverAdvance = null;
            $vendors = null;

            if ($vendorBillId) {
                // Payment Request dari Vendor Bill
                $vendorBill = VendorBill::with(['vendor.activeBankAccounts', 'items.shipmentLeg.jobOrder', 'paymentRequests'])->findOrFail($vendorBillId);
                $this->authorizeVendorBillForSales($vendorBill);

                // Calculate remaining yang BELUM DIAJUKAN (bukan yang belum dibayar)
                $totalRequested = $vendorBill->paymentRequests->sum('amount');
                $vendorBill->remaining_to_request = $vendorBill->total_amount - $totalRequested;
                $vendorBill->total_requested = $totalRequested;
            } elseif ($driverAdvanceId) {
                // Payment Request dari Driver Advance
                $driverAdvance = \App\Models\Operations\DriverAdvance::with(['driver', 'shipmentLeg.jobOrder'])->findOrFail($driverAdvanceId);
                $this->authorizeDriverAdvanceForSales($driverAdvance);
            } else {
                // Manual Payment Request - load all vendors
                $vendors = Vendor::with('activeBankAccounts')->where('is_active', true)->orderBy('name')->get();
            }

            return view('payment-requests.create', compact('vendorBill', 'driverAdvance', 'vendors'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat form: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'payment_type' => ['required', 'in:vendor_bill,manual,trucking'],
                'vendor_bill_id' => ['required_if:payment_type,vendor_bill', 'nullable', 'exists:vendor_bills,id'],
                'driver_advance_id' => ['required_if:payment_type,trucking', 'nullable', 'exists:driver_advances,id'],
                'vendor_id' => ['nullable', 'exists:vendors,id'],
                'vendor_bank_account_id' => ['nullable', 'exists:vendor_bank_accounts,id'],
                'manual_payee_name' => ['nullable', 'string', 'max:150'],
                'manual_bank_name' => ['nullable', 'string', 'max:100'],
                'manual_bank_account' => ['nullable', 'string', 'max:100'],
                'manual_bank_holder' => ['nullable', 'string', 'max:150'],
                'description' => ['nullable', 'string', 'max:255'],
                'amount' => ['required', 'numeric', 'min:1'],
                'notes' => ['nullable', 'string'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        // Generate request number
        $validated['request_number'] = $this->generateRequestNumber();
        $validated['request_date'] = now()->toDateString();
        $validated['requested_by'] = Auth::id() ?? 1;
        $validated['status'] = 'pending';

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $isSales = $user && $user->role === User::ROLE_SALES;
        $salesProfileId = $isSales ? optional($user->salesProfile)->id : null;

        // Batasi sales: tidak boleh payment_type manual tanpa referensi
        if ($isSales && $validated['payment_type'] === 'manual') {
            return back()->withErrors(['payment_type' => 'Sales tidak boleh membuat payment request manual.'])->withInput();
        }

        // Handle driver advance payment (uses 'trucking' type with driver_advance_id)
        if (!empty($validated['driver_advance_id'])) {
            $driverAdvance = \App\Models\Operations\DriverAdvance::with(['driver', 'paymentRequests', 'shipmentLeg.jobOrder'])->findOrFail($validated['driver_advance_id']);
            if ($isSales) {
                $this->authorizeDriverAdvanceForSales($driverAdvance, $salesProfileId);
            }

            // Calculate remaining amount that hasn't been requested yet
            $totalRequested = $driverAdvance->paymentRequests->sum('amount');
            $remaining = $driverAdvance->amount - $totalRequested;

            if ($validated['amount'] > $remaining) {
                return back()->withErrors(['amount' => 'Jumlah melebihi sisa yang belum diajukan. Total: Rp '.number_format($driverAdvance->amount, 0, ',', '.').', Sudah diajukan: Rp '.number_format($totalRequested, 0, ',', '.').', Maksimal: Rp '.number_format($remaining, 0, ',', '.')]);
            }

            // Set as trucking type with description
            $validated['payment_type'] = 'trucking';
            $validated['description'] = 'Pembayaran DP Uang Jalan - '.$driverAdvance->advance_number.' - '.$driverAdvance->driver->name;
            // Remove vendor_id if exists since this is driver payment
            unset($validated['vendor_id']);
        }

        // Validate amount for vendor_bill type - cek sisa yang BELUM DIAJUKAN
        if ($validated['payment_type'] === 'vendor_bill') {
            $vendorBill = VendorBill::with('paymentRequests', 'items.shipmentLeg.jobOrder')->findOrFail($validated['vendor_bill_id']);
            if ($isSales) {
                $this->authorizeVendorBillForSales($vendorBill, $salesProfileId);
            }
            $totalRequested = $vendorBill->paymentRequests->sum('amount');
            $remaining = $vendorBill->total_amount - $totalRequested;

            if ($validated['amount'] > $remaining) {
                return back()->withErrors(['amount' => 'Jumlah melebihi sisa yang belum diajukan. Total tagihan: Rp '.number_format($vendorBill->total_amount, 0, ',', '.').', Sudah diajukan: Rp '.number_format($totalRequested, 0, ',', '.').', Maksimal: Rp '.number_format($remaining, 0, ',', '.')]);
            }
        }

        // Validate manual payment requires vendor_id or driver_advance_id
        if (
            $validated['payment_type'] === 'manual'
            && empty($validated['vendor_id'])
            && empty($validated['manual_payee_name'])
            && empty($validated['driver_advance_id'])
        ) {
            return back()->withErrors(['vendor_id' => 'Isi nama penerima atau pilih vendor untuk pembayaran manual.'])->withInput();
        }

        // Store recipient master (if requested) before we unset helper fields
        if ($validated['payment_type'] === 'manual') {
            $this->storeManualRecipientIfNeeded($request, $validated);
        }

        try {
            // Append manual payee/bank info into notes so finance can see the free-text data
            if ($validated['payment_type'] === 'manual') {
                $manualDetails = [];
                if (!empty($validated['manual_payee_name'])) {
                    $manualDetails[] = 'Payee: '.$validated['manual_payee_name'];
                }
                if (!empty($validated['manual_bank_name'])) {
                    $manualDetails[] = 'Bank: '.$validated['manual_bank_name'];
                }
                if (!empty($validated['manual_bank_account'])) {
                    $manualDetails[] = 'No Rek: '.$validated['manual_bank_account'];
                }
                if (!empty($validated['manual_bank_holder'])) {
                    $manualDetails[] = 'a.n: '.$validated['manual_bank_holder'];
                }

                if (!empty($manualDetails)) {
                    $extra = 'Manual payee info — '.implode(' | ', $manualDetails);
                    $validated['notes'] = trim(($validated['notes'] ?? '')."\n".$extra);
                }
            }

              if ($validated['payment_type'] === 'manual') {
                  unset(
                      $validated['manual_payee_name'],
                      $validated['manual_bank_name'],
                      $validated['manual_bank_account'],
                      $validated['manual_bank_holder']
                  );
              }

              $paymentRequest = PaymentRequest::create($validated);

            // Load requestedBy relationship for notification
            $paymentRequest->load('requestedBy');

            // Send notification to finance team users
            $financeTeamUsers = User::getFinanceTeamUsers();
            foreach ($financeTeamUsers as $user) {
                $user->notify(new PaymentRequestCreated($paymentRequest));
            }

            // Kembali ke halaman detail payment request (behavior awal)
            return redirect()
                ->route('payment-requests.show', $paymentRequest)
                ->with('success', 'Payment request created successfully. Number: '.$paymentRequest->request_number);
        } catch (\Exception $e) {
            Log::error('Payment Request Store Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $validated
            ]);
            return back()->withErrors(['error' => 'Failed to create payment request: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(PaymentRequest $payment_request)
    {
        $this->authorizeForSales($payment_request);
        $payment_request->load([
            'vendorBill.vendor', 
            'vendor.activeBankAccounts', 
            'vendorBankAccount', 
            'driverAdvance.driver',
            'driverAdvance.shipmentLeg.jobOrder',
            'requestedBy', 
            'approvedBy', 
            'paidBy', 
            'cashBankTransaction'
        ]);

        return view('payment-requests.show', ['request' => $payment_request]);
    }

    public function approve(PaymentRequest $payment_request)
    {
        $this->authorizeForSales($payment_request);
        // Skip auth check for development
        // TODO: Implement proper authentication
        // $user = Auth::user();
        // if (! $user || ($user->role ?? 'admin') !== 'super_admin') {
        //     abort(403, 'Hanya super admin yang dapat menyetujui pengajuan.');
        // }

        if ($payment_request->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $payment_request->update([
            'status' => 'approved',
            'approved_by' => Auth::id() ?? 1,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Payment request approved.');
    }

    public function reject(Request $request, PaymentRequest $payment_request)
    {
        $this->authorizeForSales($payment_request);
        // Skip auth check for development
        // TODO: Implement proper authentication
        // $user = Auth::user();
        // if (! $user || ($user->role ?? 'admin') !== 'super_admin') {
        //     abort(403, 'Hanya super admin yang dapat menolak pengajuan.');
        // }

        if ($payment_request->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string'],
        ]);

        $payment_request->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'approved_by' => Auth::id() ?? 1,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Payment request rejected.');
    }

    public function destroy(PaymentRequest $payment_request)
    {
        // Skip auth check for development
        // TODO: Implement proper authentication
        // $user = Auth::user();
        // if (! $user || (($user->role ?? 'admin') !== 'super_admin' && $payment_request->requested_by !== $user->id)) {
        //     abort(403, 'Anda tidak memiliki akses untuk menghapus pengajuan ini.');
        // }

        if ($payment_request->status !== 'pending') {
            return back()->with('error', 'Hanya pengajuan dengan status pending yang dapat dihapus.');
        }

        $payment_request->delete();

        return redirect()->route('payment-requests.index')->with('success', 'Payment request deleted successfully.');
    }

    protected function generateRequestNumber(): string
    {
        $prefix = 'PR-'.now()->format('Ym').'-';
        $last = PaymentRequest::where('request_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('request_number');
        $seq = 1;
        if ($last && preg_match('/(\d{4})$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Optionally store/update master recipient for manual payment requests.
     */
    protected function storeManualRecipientIfNeeded(Request $request, array $validated): void
    {
        if (
            ! $request->boolean('save_recipient') ||
            empty($validated['manual_payee_name']) ||
            empty($validated['manual_bank_account'])
        ) {
            return;
        }

        PaymentRecipient::updateOrCreate(
            [
                'name' => $validated['manual_payee_name'],
                'account_number' => $validated['manual_bank_account'],
            ],
            [
                'bank_name' => $validated['manual_bank_name'] ?? null,
                'account_holder' => $validated['manual_bank_holder'] ?? null,
                'vendor_id' => $validated['vendor_id'] ?? null,
                'is_active' => true,
                'created_by' => Auth::id() ?? ($validated['requested_by'] ?? null),
            ]
        );
    }

    /**
     * Return related job order info for a payment request (vendor_bill type).
     */
    public function getJobInfo(PaymentRequest $payment_request)
    {
        $jobOrders = collect();

        // Handle vendor bill payment type
        if ($payment_request->payment_type === 'vendor_bill' && $payment_request->vendorBill) {
            $bill = $payment_request->vendorBill->load(['items.shipmentLeg.jobOrder.customer', 'items.shipmentLeg.jobOrder.items']);
            foreach ($bill->items as $item) {
                if ($item->shipmentLeg && $item->shipmentLeg->jobOrder) {
                    $jobOrders->push($item->shipmentLeg->jobOrder);
                }
            }
        }
        // Handle trucking payment type (driver advance)
        elseif ($payment_request->payment_type === 'trucking' && $payment_request->driverAdvance) {
            $driverAdvance = $payment_request->driverAdvance->load(['shipmentLeg.jobOrder.customer', 'shipmentLeg.jobOrder.items']);
            if ($driverAdvance->shipmentLeg && $driverAdvance->shipmentLeg->jobOrder) {
                $jobOrders->push($driverAdvance->shipmentLeg->jobOrder);
            }
        }

        // Return empty if no job orders found
        if ($jobOrders->isEmpty()) {
            return response()->json(['job_orders' => []]);
        }

        $jobOrders = $jobOrders->unique('id');

        $formatted = $jobOrders->map(function ($jo) {
            // Cargo summary from job order items
            $cargoSummary = $jo->items->map(function ($itm) {
                $qty = number_format($itm->quantity, 2, ',', '.');
                return ($itm->cargo_type ?: 'Cargo').": $qty";
            })->implode(' | ');

            return [
                'job_number' => $jo->job_number,
                'order_date' => optional($jo->order_date)->format('d M Y'),
                'service_type' => $jo->service_type,
                'customer' => $jo->customer?->name,
                'origin' => $jo->origin,
                'destination' => $jo->destination,
                'status' => $jo->status,
                'cargo_summary' => $cargoSummary,
            ];
        });

        return response()->json(['job_orders' => $formatted]);
    }

    /**
     * Search saved payment recipients for manual payment requests.
     */
    public function searchRecipients(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $vendorId = $request->get('vendor_id');

        $query = PaymentRecipient::query()->where('is_active', true);

        if ($vendorId) {
            $query->where('vendor_id', $vendorId);
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', '%'.$q.'%')
                    ->orWhere('account_number', 'like', '%'.$q.'%')
                    ->orWhere('bank_name', 'like', '%'.$q.'%');
            });
        }

        $recipients = $query
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(function (PaymentRecipient $r) {
                return [
                    'id' => $r->id,
                    'name' => $r->name,
                    'bank_name' => $r->bank_name,
                    'account_number' => $r->account_number,
                    'account_holder' => $r->account_holder,
                ];
            });

        return response()->json(['data' => $recipients]);
    }

    public function updateRecipient(Request $request, PaymentRecipient $payment_recipient)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'account_holder' => ['nullable', 'string', 'max:150'],
        ]);

        $payment_recipient->update($data);

        return response()->json(['success' => true]);
    }

    public function destroyRecipient(PaymentRecipient $payment_recipient)
    {
        $payment_recipient->update(['is_active' => false]);

        return response()->json(['success' => true]);
    }
}
