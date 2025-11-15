<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Finance\VendorBill;
use App\Models\Inventory\Part;
use App\Models\Inventory\PartPurchase;
use App\Models\Inventory\PartPurchaseItem;
use App\Models\Inventory\PartStock;
use App\Models\Inventory\PartUsage;
use App\Models\Master\Vendor;
use App\Services\Accounting\JournalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartPurchaseController extends Controller
{
    public function __construct(protected JournalService $journalService) {}

    public function index(Request $request)
    {
        $query = PartPurchase::query()->with(['vendor', 'items.part']);

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('purchase_number', 'like', "%{$search}%")
                    ->orWhere('invoice_number', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $purchases = $query->latest('purchase_date')->latest('id')->paginate(20)->withQueryString();

        return view('inventory.part-purchases.index', compact('purchases'));
    }

    public function create()
    {
        $parts = Part::where('is_active', true)->orderBy('code')->get();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();

        return view('inventory.part-purchases.create', compact('parts', 'vendors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_date' => ['required', 'date'],
            'vendor_id' => ['required', 'exists:vendors,id'],
            'invoice_number' => ['nullable', 'string', 'max:255'],
            'is_direct_usage' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.part_id' => ['required', 'exists:parts,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                $purchase = new PartPurchase;
                $purchase->purchase_number = $this->generatePurchaseNumber($validated['purchase_date']);
                $purchase->purchase_date = $validated['purchase_date'];
                $purchase->vendor_id = $validated['vendor_id'];
                $purchase->invoice_number = $validated['invoice_number'] ?? null;
                $purchase->is_direct_usage = $request->has('is_direct_usage');
                $purchase->status = 'received';
                $purchase->notes = $validated['notes'] ?? null;
                $purchase->created_by = auth()->id();
                $purchase->save();

                $totalAmount = 0;
                foreach ($validated['items'] as $itemData) {
                    $subtotal = (float) $itemData['quantity'] * (float) $itemData['unit_price'];
                    $totalAmount += $subtotal;

                    $item = new PartPurchaseItem;
                    $item->part_purchase_id = $purchase->id;
                    $item->part_id = $itemData['part_id'];
                    $item->quantity = $itemData['quantity'];
                    $item->unit_price = $itemData['unit_price'];
                    $item->subtotal = $subtotal;
                    $item->notes = $itemData['notes'] ?? null;
                    $item->save();

                    if ($purchase->is_direct_usage) {
                        // Langsung pakai - buat usage record
                        $usage = new PartUsage;
                        $usage->usage_number = $this->generateUsageNumber($validated['purchase_date']);
                        $usage->usage_date = $validated['purchase_date'];
                        $usage->part_id = $itemData['part_id'];
                        $usage->quantity = $itemData['quantity'];
                        $usage->unit_cost = $itemData['unit_price'];
                        $usage->total_cost = $subtotal;
                        $usage->usage_type = 'purchase_direct';
                        $usage->description = 'Pembelian langsung pakai - '.$purchase->purchase_number;
                        $usage->part_purchase_id = $purchase->id;
                        $usage->created_by = auth()->id();
                        $usage->save();
                    } else {
                        // Masuk stok
                        $stock = PartStock::firstOrNew([
                            'part_id' => $itemData['part_id'],
                            'location' => 'main',
                        ]);

                        if ($stock->exists) {
                            // Average costing
                            $oldTotal = $stock->quantity * $stock->unit_cost;
                            $newTotal = (float) $itemData['quantity'] * (float) $itemData['unit_price'];
                            $totalQty = $stock->quantity + (float) $itemData['quantity'];
                            $stock->unit_cost = $totalQty > 0 ? ($oldTotal + $newTotal) / $totalQty : (float) $itemData['unit_price'];
                            $stock->quantity += (float) $itemData['quantity'];
                        } else {
                            $stock->quantity = (float) $itemData['quantity'];
                            $stock->unit_cost = (float) $itemData['unit_price'];
                        }

                        $stock->save();
                    }
                }

                $purchase->total_amount = $totalAmount;
                $purchase->save();

                // Buat VendorBill otomatis untuk tracking hutang
                $vendorBill = new VendorBill;
                $vendorBill->vendor_id = $purchase->vendor_id;
                $vendorBill->bill_date = $purchase->purchase_date;
                $vendorBill->due_date = $purchase->purchase_date->copy()->addDays(30); // default 30 hari
                $vendorBill->vendor_bill_number = $this->generateVendorBillNumber($purchase->purchase_date);
                $vendorBill->status = 'received';
                $vendorBill->total_amount = $totalAmount;
                $vendorBill->notes = 'Pembelian part: '.$purchase->purchase_number.($purchase->invoice_number ? ' | Invoice: '.$purchase->invoice_number : '');
                $vendorBill->save();

                // Buat vendor bill items
                foreach ($validated['items'] as $itemData) {
                    $part = Part::find($itemData['part_id']);
                    $subtotal = (float) $itemData['quantity'] * (float) $itemData['unit_price'];
                    $vendorBill->items()->create([
                        'description' => $part->name.' ('.$itemData['quantity'].' x '.number_format($itemData['unit_price'], 2).')',
                        'qty' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'subtotal' => $subtotal,
                    ]);
                }

                // Link vendor bill ke purchase
                $purchase->vendor_bill_id = $vendorBill->id;
                $purchase->save();

                // Generate journal untuk pembelian part
                // Jika masuk stok: Dr Inventory, Cr AP
                // Jika langsung pakai: Dr Expense, Cr AP (akan dihandle oleh VendorBill atau manual)
                if (class_exists(JournalService::class)) {
                    try {
                        if (! $purchase->is_direct_usage) {
                            // Masuk stok: gunakan Inventory
                            $this->journalService->postPartPurchase($purchase);
                        } else {
                            // Langsung pakai: gunakan VendorBill (expense_vendor)
                            $this->journalService->postVendorBill($vendorBill);
                        }
                    } catch (\Exception $e) {
                        // Log error but don't fail the transaction
                        \Log::warning('Failed to generate journal for part purchase: '.$e->getMessage());
                    }
                }

                return redirect()->route('part-purchases.show', $purchase)
                    ->with('success', 'Pembelian part berhasil disimpan dan Vendor Bill telah dibuat.');
            });
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Gagal menyimpan pembelian: '.$e->getMessage());
        }
    }

    public function show(PartPurchase $partPurchase)
    {
        $partPurchase->load(['vendor', 'vendorBill', 'items.part', 'usages.truck']);

        return view('inventory.part-purchases.show', compact('partPurchase'));
    }

    protected function generatePurchaseNumber(string $date): string
    {
        $d = new \DateTimeImmutable($date);
        $prefix = 'PCH-'.$d->format('Ym').'-';
        $last = PartPurchase::where('purchase_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('purchase_number');
        $seq = 1;
        if ($last && preg_match('/(\d{4})$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    protected function generateUsageNumber(string $date): string
    {
        $d = new \DateTimeImmutable($date);
        $prefix = 'USG-'.$d->format('Ym').'-';
        $last = PartUsage::where('usage_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('usage_number');
        $seq = 1;
        if ($last && preg_match('/(\d{4})$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    protected function generateVendorBillNumber(string $date): string
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
