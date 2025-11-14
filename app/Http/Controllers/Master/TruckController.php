<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Driver;
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
        $items = $q->with(['vendor', 'driver'])->orderBy('plate_number')->paginate(15)->withQueryString();

        return view('master.trucks.index', compact('items'));
    }

    public function create(): \Illuminate\View\View
    {
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $drivers = Driver::where('is_active', true)->orderBy('name')->get();

        return view('master.trucks.create', compact('vendors', 'drivers'));
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
            'driver_id' => ['nullable', 'exists:drivers,id'],
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['is_own_fleet'] = $request->has('is_own_fleet');

        Truck::create($validated);

        return redirect()->route('trucks.index')->with('success', 'Truck berhasil ditambahkan');
    }

    public function edit(Truck $truck): \Illuminate\View\View
    {
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $drivers = Driver::where('is_active', true)->orderBy('name')->get();

        return view('master.trucks.edit', compact('truck', 'vendors', 'drivers'));
    }

    public function update(Request $request, Truck $truck): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'plate_number' => ['required', 'string', 'max:50', 'unique:trucks,plate_number,'.$truck->id],
            'vehicle_type' => ['required', 'string', 'max:50'],
            'capacity_tonase' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_own_fleet' => ['nullable', 'boolean'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'driver_id' => ['nullable', 'exists:drivers,id'],
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['is_own_fleet'] = $request->has('is_own_fleet');

        $truck->update($validated);

        return redirect()->route('trucks.index')->with('success', 'Truck berhasil diupdate');
    }

    public function destroy(Truck $truck): \Illuminate\Http\RedirectResponse
    {
        $truck->delete();

        return redirect()->route('trucks.index')->with('success', 'Truck berhasil dihapus');
    }
}
