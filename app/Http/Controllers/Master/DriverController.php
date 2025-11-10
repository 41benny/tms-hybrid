<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Driver;
use App\Models\Master\Vendor;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $q = Driver::query();
        if ($search = $request->get('q')) {
            $q->where('name', 'like', "%{$search}%");
        }
        $items = $q->orderBy('name')->paginate(15)->withQueryString();

        return view('master.drivers.index', compact('items'));
    }

    public function create(): \Illuminate\View\View
    {
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();

        return view('master.drivers.create', compact('vendors'));
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        Driver::create($validated);

        return redirect()->route('drivers.index')->with('success', 'Driver berhasil ditambahkan');
    }
}
