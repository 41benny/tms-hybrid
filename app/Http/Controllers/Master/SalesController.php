<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Sales;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $q = Sales::query();
        if ($search = $request->get('q')) {
            $q->where('name', 'like', "%{$search}%");
        }
        $items = $q->orderBy('name')->paginate(15)->withQueryString();

        return view('master.sales.index', compact('items'));
    }

    public function create(): \Illuminate\View\View
    {
        return view('master.sales.create');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        Sales::create($validated);

        return redirect()->route('sales.index')->with('success', 'Sales berhasil ditambahkan');
    }

    public function edit(Sales $sale): \Illuminate\View\View
    {
        return view('master.sales.edit', compact('sale'));
    }

    public function update(Request $request, Sales $sale): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        $sale->update($validated);

        return redirect()->route('sales.index')->with('success', 'Sales berhasil diupdate');
    }

    public function destroy(Sales $sale): \Illuminate\Http\RedirectResponse
    {
        $sale->delete();

        return redirect()->route('sales.index')->with('success', 'Sales berhasil dihapus');
    }
}
