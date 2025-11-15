<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Part;
use App\Models\Inventory\PartUsage;
use App\Models\Master\Truck;
use Illuminate\Http\Request;

class PartDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Stock summary
        $parts = Part::where('is_active', true)
            ->with('stocks')
            ->orderBy('code')
            ->get()
            ->map(function ($part) {
                $part->total_stock = $part->stocks->sum('quantity');
                $part->is_low_stock = $part->total_stock < $part->min_stock;

                return $part;
            });

        $lowStockParts = $parts->filter(fn ($p) => $p->is_low_stock);

        // Usage by truck
        $truckId = $request->get('truck_id');
        $usageQuery = PartUsage::query()
            ->with(['part', 'truck'])
            ->whereNotNull('truck_id');

        if ($truckId) {
            $usageQuery->where('truck_id', $truckId);
        }

        $usageByTruck = $usageQuery
            ->selectRaw('truck_id, SUM(total_cost) as total_cost, COUNT(*) as usage_count')
            ->groupBy('truck_id')
            ->orderByDesc('total_cost')
            ->limit(10)
            ->get();

        // Load truck relationship manually
        $truckIds = $usageByTruck->pluck('truck_id')->filter();
        if ($truckIds->isNotEmpty()) {
            $trucksMap = Truck::whereIn('id', $truckIds)->get()->keyBy('id');
            foreach ($usageByTruck as $usage) {
                if ($usage->truck_id && isset($trucksMap[$usage->truck_id])) {
                    $usage->setRelation('truck', $trucksMap[$usage->truck_id]);
                }
            }
        }

        $trucks = Truck::where('is_active', true)->orderBy('plate_number')->get();

        // Recent usage
        $recentUsages = PartUsage::with(['part', 'truck'])
            ->latest('usage_date')
            ->latest('id')
            ->limit(10)
            ->get();

        return view('inventory.dashboard', compact('parts', 'lowStockParts', 'usageByTruck', 'trucks', 'recentUsages', 'truckId'));
    }
}
