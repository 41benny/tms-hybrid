<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\VendorBill;
use App\Models\Master\Vendor;
use App\Models\Operations\Transport;
use App\Services\Accounting\JournalService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VendorBillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = VendorBill::query()->with(['vendor', 'items', 'payments', 'paymentRequests']);
        $scope = $request->get('scope');
        // Default tampilkan hanya outstanding kecuali user pilih 'all'
        if ($scope !== 'all') {
            $query->outstanding();
            $scope = 'outstanding';
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($vendor = $request->get('vendor_id')) {
            $query->where('vendor_id', $vendor);
        }
        if ($from = $request->get('from')) {
            $query->whereDate('bill_date', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('bill_date', '<=', $to);
        }
        $bills = $query->latest()->paginate(15)->withQueryString();

        // Calculate DPP, PPN, PPH for each bill
        $bills->getCollection()->transform(function ($bill) {
            $dpp = 0;
            $ppn = 0;
            $pph = 0;

            foreach ($bill->items as $item) {
                $desc = strtolower($item->description);
                if (str_contains($desc, 'ppn')) {
                    $ppn += abs($item->subtotal);
                } elseif (str_contains($desc, 'pph') || str_contains($desc, 'pph23')) {
                    $pph += abs($item->subtotal);
                } else {
                    // Exclude PPN and PPH from DPP
                    if (! str_contains($desc, 'ppn') && ! str_contains($desc, 'pph')) {
                        $dpp += $item->subtotal;
                    }
                }
            }

            $bill->dpp = $dpp;
            $bill->ppn = $ppn;
            $bill->pph = $pph;
            $bill->total_paid = $bill->payments->sum('amount_paid');
            $bill->last_payment_date = $bill->payments->first()?->payment_date;
            // Tracking pengajuan (bukan pembayaran) menggunakan accessor agar tersedia di view
            $bill->total_requested = $bill->total_requested; // accessor value
            $bill->remaining_to_request = $bill->remaining_to_request; // accessor value

            return $bill;
        });

        $vendors = Vendor::orderBy('name')->get();

        return view('vendor-bills.index', compact('bills', 'vendors', 'scope'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $vendors = Vendor::orderBy('name')->get();
        $transports = Transport::select('id', 'job_order_id')->latest()->get();

        return view('vendor-bills.create', compact('vendors', 'transports'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
            'bill_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:bill_date'],
            'status' => ['nullable', Rule::in(['draft', 'received', 'partially_paid', 'paid', 'cancelled'])],
            'notes' => ['nullable', 'string'],
            'items' => ['array'],
            'items.*.description' => ['required', 'string'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.transport_id' => ['nullable', 'exists:transports,id'],
        ]);

        $bill = new VendorBill;
        $bill->fill($data);
        $bill->vendor_bill_number = $this->generateBillNo($data['bill_date']);
        $bill->status = $data['status'] ?? 'draft';
        $bill->save();

        $total = 0;
        foreach ($data['items'] ?? [] as $row) {
            $row['subtotal'] = (float) $row['qty'] * (float) $row['unit_price'];
            $total += $row['subtotal'];
            $bill->items()->create($row);
        }
        $bill->update(['total_amount' => $total]);

        return redirect()->route('vendor-bills.show', $bill)->with('success', 'Vendor bill dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(VendorBill $vendor_bill)
    {
        $vendor_bill->load(['vendor', 'items.shipmentLeg', 'payments.account', 'paymentRequests.requestedBy']);

        // Calculate DPP, PPN, PPH
        $dpp = 0;
        $ppn = 0;
        $pph = 0;

        foreach ($vendor_bill->items as $item) {
            $desc = strtolower($item->description);
            if (str_contains($desc, 'ppn')) {
                $ppn += abs($item->subtotal);
            } elseif (str_contains($desc, 'pph') || str_contains($desc, 'pph23')) {
                $pph += abs($item->subtotal);
            } else {
                if (! str_contains($desc, 'ppn') && ! str_contains($desc, 'pph')) {
                    $dpp += $item->subtotal;
                }
            }
        }

        $vendor_bill->dpp = $dpp;
        $vendor_bill->ppn = $ppn;
        $vendor_bill->pph = $pph;
        $vendor_bill->total_paid = $vendor_bill->payments->sum('amount_paid');
        // Accessor-based tracking of pengajuan
        $vendor_bill->total_requested = $vendor_bill->total_requested;
        $vendor_bill->remaining_to_request = $vendor_bill->remaining_to_request;
        // Payment-based remaining (dipakai di bagian lain bila diperlukan)
        $vendor_bill->remaining = $vendor_bill->total_amount - $vendor_bill->total_paid;

        return view('vendor-bills.show', ['bill' => $vendor_bill]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VendorBill $vendor_bill)
    {
        $vendors = Vendor::orderBy('name')->get();
        $vendor_bill->load('items');

        return view('vendor-bills.edit', ['bill' => $vendor_bill, 'vendors' => $vendors]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VendorBill $vendor_bill)
    {
        $data = $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
            'bill_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:bill_date'],
            'status' => ['required', Rule::in(['draft', 'received', 'partially_paid', 'paid', 'cancelled'])],
            'notes' => ['nullable', 'string'],
            'items' => ['array'],
            'items.*.description' => ['required', 'string'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.transport_id' => ['nullable', 'exists:transports,id'],
        ]);

        $vendor_bill->update($data);

        $vendor_bill->items()->delete();
        $total = 0;
        foreach ($data['items'] ?? [] as $row) {
            $row['subtotal'] = (float) $row['qty'] * (float) $row['unit_price'];
            $total += $row['subtotal'];
            $vendor_bill->items()->create($row);
        }
        $vendor_bill->update(['total_amount' => $total]);

        return redirect()->route('vendor-bills.show', $vendor_bill)->with('success', 'Vendor bill diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VendorBill $vendor_bill)
    {
        $vendor_bill->delete();

        return redirect()->route('vendor-bills.index')->with('success', 'Vendor bill dihapus.');
    }

    public function markAsReceived(VendorBill $vendor_bill)
    {
        // Validasi total amount
        if ($vendor_bill->total_amount <= 0) {
            return back()->with('error', 'Vendor bill tidak dapat diposting karena total amount = 0 atau negatif.');
        }

        // Validasi ada items
        if ($vendor_bill->items()->count() === 0) {
            return back()->with('error', 'Vendor bill tidak dapat diposting karena tidak ada item.');
        }

        $vendor_bill->update(['status' => 'received']);

        if (class_exists(JournalService::class)) {
            try {
                app(JournalService::class)->postVendorBill($vendor_bill);
            } catch (\Exception $e) {
                // Rollback status jika posting gagal
                $vendor_bill->update(['status' => 'draft']);
                return back()->with('error', 'Gagal membuat jurnal: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Vendor bill ditandai diterima dan jurnal berhasil dibuat.');
    }

    public function markAsPaid(VendorBill $vendor_bill)
    {
        $vendor_bill->update(['status' => 'paid']);

        return back()->with('success', 'Vendor bill ditandai lunas.');
    }

    /**
     * Print Vendor Bill as Purchase Order / SPK Vendor.
     */
    public function print(VendorBill $vendor_bill)
    {
        $vendor_bill->load(['vendor', 'items.shipmentLeg.jobOrder.customer']);

        return view('vendor-bills.print', ['bill' => $vendor_bill]);
    }

    protected function generateBillNo(string $date): string
    {
        $d = new \DateTimeImmutable($date);
        $prefix = 'VBL-'.$d->format('Ym').'-';
        $last = VendorBill::where('vendor_bill_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('vendor_bill_number');
        $seq = 1;
        if ($last && preg_match('/(\d{4})$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Return leg/shipment info as JSON for popup on index.
     */
    public function getLegInfo(VendorBill $vendor_bill)
    {
        $vendor_bill->load(['items.shipmentLeg.truck', 'items.shipmentLeg.driver', 'items.shipmentLeg.vendor']);
        $legs = collect();
        foreach ($vendor_bill->items as $item) {
            if ($item->shipmentLeg) {
                $legs->push($item->shipmentLeg);
            }
        }
        $legs = $legs->unique('id');

        $formatted = $legs->map(function ($leg) {
            $statusClass = match ($leg->status) {
                'pending' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300',
                'in_transit' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                'delivered' => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400',
                'cancelled' => 'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400',
                default => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300',
            };

            return [
                'leg_code' => $leg->leg_code,
                'status' => $leg->status,
                'status_label' => strtoupper(str_replace('_', ' ', $leg->status)),
                'status_class' => $statusClass,
                'load_date' => optional($leg->load_date)->format('d M Y'),
                'unload_date' => optional($leg->unload_date)->format('d M Y'),
                'quantity' => number_format($leg->quantity, 2, ',', '.'),
                'cost_category' => ucfirst($leg->cost_category),
                'truck' => $leg->truck?->plate_number,
                'driver' => $leg->driver?->name,
                'vendor' => $leg->vendor?->name,
            ];
        });

        return response()->json(['legs' => $formatted]);
    }

    /**
     * Return related job order info (aggregated from legs) for popup display.
     */
    public function getJobInfo(VendorBill $vendor_bill)
    {
        $vendor_bill->load(['items.shipmentLeg.jobOrder.customer', 'items.shipmentLeg.jobOrder.sales', 'items.shipmentLeg.jobOrder.items', 'items.shipmentLeg']);
        $jobOrders = collect();
        foreach ($vendor_bill->items as $item) {
            if ($item->shipmentLeg && $item->shipmentLeg->jobOrder) {
                $jobOrders->push($item->shipmentLeg->jobOrder);
            }
        }
        $jobOrders = $jobOrders->unique('id');

        $formatted = $jobOrders->map(function ($jo) {
            // Cargo summary from job order items
            $cargoSummary = $jo->items->map(function ($itm) {
                $qty = number_format($itm->quantity, 2, ',', '.');
                $sn = $itm->serial_number ? ' (S/N: '.$itm->serial_number.')' : '';
                return ($itm->cargo_type ?: 'Cargo')." $qty units$sn";
            })->implode(' — ');

            return [
                'job_number' => $jo->job_number,
                'order_date' => optional($jo->order_date)->format('d M Y'),
                'status' => $jo->status,
                'customer' => $jo->customer?->name,
                'sales' => $jo->sales?->name,
                'origin' => $jo->origin,
                'destination' => $jo->destination,
                'cargo_summary' => $cargoSummary,
                'leg_count' => $jo->shipmentLegs()->count(),
            ];
        });

        return response()->json(['job_orders' => $formatted]);
    }
}
