<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Truck;
use App\Models\Master\Vendor;
use Illuminate\Http\Request;

class TruckController extends Controller
{
    public function index(Request $request)
    {
        $q = Truck::query();
        if ($search = $request->get('q')) {
            $q->where('plate_number', 'like', "%{$search}%");
        }
        $items = $q->orderBy('plate_number')->paginate(15)->withQueryString();

        return view('master.trucks.index', compact('items'));
    }

    public function create(): \Illuminate\View\View
    {
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();

        return view('master.trucks.create', compact('vendors'));
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'plate_number' => ['required', 'string', 'max:50', 'unique:trucks,plate_number'],
            'vehicle_type' => ['required', 'string', 'max:50'],
            'capacity_tonase' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_own_fleet' => ['nullable', 'boolean'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['is_own_fleet'] = $request->has('is_own_fleet');

        Truck::create($validated);

        return redirect()->route('trucks.index')->with('success', 'Truck berhasil ditambahkan');
    }
}
