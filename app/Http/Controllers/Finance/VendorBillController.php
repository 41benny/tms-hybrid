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
        $query = VendorBill::query()->with('vendor');
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

        $vendors = Vendor::orderBy('name')->get();

        return view('vendor-bills.index', compact('bills', 'vendors'));
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
        $vendor_bill->load(['vendor', 'items']);

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
        $vendor_bill->update(['status' => 'received']);
        if (class_exists(JournalService::class)) {
            app(JournalService::class)->postVendorBill($vendor_bill);
        }

        return back()->with('success', 'Vendor bill ditandai diterima.');
    }

    public function markAsPaid(VendorBill $vendor_bill)
    {
        $vendor_bill->update(['status' => 'paid']);

        return back()->with('success', 'Vendor bill ditandai lunas.');
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
}
