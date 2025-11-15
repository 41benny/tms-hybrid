<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\VendorBill;
use App\Models\Master\Vendor;
use App\Models\Operations\PaymentRequest;
use App\Models\User;
use App\Notifications\PaymentRequestCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = PaymentRequest::query()->with(['vendorBill.vendor', 'vendor', 'requestedBy']);

        // Skip role filter for development
        // TODO: Implement proper authentication and role-based filtering
        $user = Auth::user();
        if ($user && ($user->role ?? 'admin') !== 'super_admin') {
            // Admin hanya bisa lihat pengajuannya sendiri
            $query->where('requested_by', $user->id);
        }

        // Filter status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Filter date range
        if ($from = $request->get('from')) {
            $query->whereDate('request_date', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('request_date', '<=', $to);
        }

        $requests = $query->latest('request_date')->paginate(15)->withQueryString();

        return view('payment-requests.index', compact('requests'));
    }

    public function create(Request $request)
    {
        $vendorBillId = $request->get('vendor_bill_id');
        $vendorBill = null;
        $vendors = null;

        if ($vendorBillId) {
            // Payment Request dari Vendor Bill
            $vendorBill = VendorBill::with(['vendor.activeBankAccounts', 'items', 'payments'])->findOrFail($vendorBillId);

            // Calculate remaining
            $totalPaid = $vendorBill->payments->sum('amount');
            $vendorBill->remaining = $vendorBill->total_amount - $totalPaid;
        } else {
            // Manual Payment Request - load all vendors
            $vendors = Vendor::with('activeBankAccounts')->where('is_active', true)->orderBy('name')->get();
        }

        return view('payment-requests.create', compact('vendorBill', 'vendors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_type' => ['required', 'in:vendor_bill,manual'],
            'vendor_bill_id' => ['required_if:payment_type,vendor_bill', 'nullable', 'exists:vendor_bills,id'],
            'vendor_id' => ['required_if:payment_type,manual', 'nullable', 'exists:vendors,id'],
            'vendor_bank_account_id' => ['nullable', 'exists:vendor_bank_accounts,id'],
            'description' => ['required_if:payment_type,manual', 'nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        // Generate request number
        $validated['request_number'] = $this->generateRequestNumber();
        $validated['request_date'] = now()->toDateString();
        // Use default user (ID 1) if not authenticated (for development)
        $validated['requested_by'] = Auth::id() ?? 1;
        $validated['status'] = 'pending';

        // Validate amount for vendor_bill type
        if ($validated['payment_type'] === 'vendor_bill') {
            $vendorBill = VendorBill::with('payments')->findOrFail($validated['vendor_bill_id']);
            $totalPaid = $vendorBill->payments->sum('amount');
            $remaining = $vendorBill->total_amount - $totalPaid;

            if ($validated['amount'] > $remaining) {
                return back()->withErrors(['amount' => 'Jumlah melebihi sisa tagihan. Maksimal: Rp '.number_format($remaining, 0, ',', '.')]);
            }
        }

        $paymentRequest = PaymentRequest::create($validated);

        // Load requestedBy relationship for notification
        $paymentRequest->load('requestedBy');

        // Send notification to finance team users
        $financeTeamUsers = User::getFinanceTeamUsers();
        foreach ($financeTeamUsers as $user) {
            $user->notify(new PaymentRequestCreated($paymentRequest));
        }

        return redirect()->route('payment-requests.show', $paymentRequest)
            ->with('success', 'Pengajuan pembayaran berhasil dibuat. Nomor: '.$paymentRequest->request_number);
    }

    public function show(PaymentRequest $payment_request)
    {
        $payment_request->load(['vendorBill.vendor', 'vendor.activeBankAccounts', 'vendorBankAccount', 'requestedBy', 'approvedBy', 'paidBy', 'cashBankTransaction']);

        // Skip permission check for development
        // TODO: Implement proper authentication
        // $user = Auth::user();
        // if ($user && ($user->role ?? 'admin') !== 'super_admin' && $payment_request->requested_by !== $user->id) {
        //     abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
        // }

        return view('payment-requests.show', ['request' => $payment_request]);
    }

    public function approve(PaymentRequest $payment_request)
    {
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

        return back()->with('success', 'Pengajuan pembayaran disetujui.');
    }

    public function reject(Request $request, PaymentRequest $payment_request)
    {
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

        return back()->with('success', 'Pengajuan pembayaran ditolak.');
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

        return redirect()->route('payment-requests.index')->with('success', 'Pengajuan pembayaran berhasil dihapus.');
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
}
