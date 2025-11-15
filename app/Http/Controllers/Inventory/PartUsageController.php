<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Part;
use App\Models\Inventory\PartStock;
use App\Models\Inventory\PartUsage;
use App\Models\Master\Truck;
use App\Services\Accounting\JournalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartUsageController extends Controller
{
    public function __construct(protected JournalService $journalService) {}

    public function index(Request $request)
    {
        $query = PartUsage::query()->with(['part', 'truck']);

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('usage_number', 'like', "%{$search}%")
                    ->orWhereHas('part', function ($q) use ($search) {
                        $q->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($truckId = $request->get('truck_id')) {
            $query->where('truck_id', $truckId);
        }

        if ($usageType = $request->get('usage_type')) {
            $query->where('usage_type', $usageType);
        }

        if ($from = $request->get('from')) {
            $query->whereDate('usage_date', '>=', $from);
        }

        if ($to = $request->get('to')) {
            $query->whereDate('usage_date', '<=', $to);
        }

        $usages = $query->latest('usage_date')->latest('id')->paginate(20)->withQueryString();
        $trucks = Truck::where('is_active', true)->orderBy('plate_number')->get();

        return view('inventory.part-usages.index', compact('usages', 'trucks'));
    }

    public function create()
    {
        $parts = Part::where('is_active', true)->with('stocks')->orderBy('code')->get();
        $trucks = Truck::where('is_active', true)->orderBy('plate_number')->get();

        return view('inventory.part-usages.create', compact('parts', 'trucks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'usage_date' => ['required', 'date'],
            'part_id' => ['required', 'exists:parts,id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'truck_id' => ['nullable', 'exists:trucks,id'],
            'usage_type' => ['required', 'string', 'in:maintenance,repair,replacement,other'],
            'description' => ['nullable', 'string'],
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                $part = Part::findOrFail($validated['part_id']);

                // Get stock and calculate cost
                $stock = PartStock::where('part_id', $part->id)
                    ->where('location', 'main')
                    ->first();

                if (! $stock || $stock->quantity < $validated['quantity']) {
                    return back()->withInput()
                        ->with('error', 'Stok tidak mencukupi. Stok tersedia: '.($stock ? number_format($stock->quantity, 2) : 0));
                }

                $unitCost = $stock->unit_cost;
                $totalCost = (float) $validated['quantity'] * $unitCost;

                // Create usage record
                $usage = new PartUsage;
                $usage->usage_number = $this->generateUsageNumber($validated['usage_date']);
                $usage->usage_date = $validated['usage_date'];
                $usage->part_id = $validated['part_id'];
                $usage->quantity = $validated['quantity'];
                $usage->unit_cost = $unitCost;
                $usage->total_cost = $totalCost;
                $usage->truck_id = $validated['truck_id'] ?? null;
                $usage->usage_type = $validated['usage_type'];
                $usage->description = $validated['description'] ?? null;
                $usage->created_by = auth()->id();
                $usage->save();

                // Reduce stock
                $stock->quantity -= (float) $validated['quantity'];
                $stock->save();

                // Generate journal
                if (class_exists(JournalService::class)) {
                    try {
                        $this->journalService->postPartUsage($usage);
                    } catch (\Exception $e) {
                        // Log error but don't fail the transaction
                        \Log::warning('Failed to generate journal for part usage: '.$e->getMessage());
                    }
                }

                return redirect()->route('part-usages.show', $usage)
                    ->with('success', 'Pemakaian part berhasil dicatat.');
            });
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Gagal menyimpan pemakaian: '.$e->getMessage());
        }
    }

    public function show(PartUsage $partUsage)
    {
        $partUsage->load(['part', 'truck', 'purchase', 'createdBy']);

        return view('inventory.part-usages.show', compact('partUsage'));
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
}
