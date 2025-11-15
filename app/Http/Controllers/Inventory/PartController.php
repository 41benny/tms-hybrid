<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Part;
use Illuminate\Http\Request;

class PartController extends Controller
{
    public function index(Request $request)
    {
        $query = Part::query();

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        $parts = $query->with('stocks')->orderBy('code')->paginate(20)->withQueryString();

        // Calculate total stock for each part
        $parts->getCollection()->transform(function ($part) {
            $part->total_stock = $part->stocks->sum('quantity');

            return $part;
        });

        $categories = Part::distinct()->pluck('category')->filter();

        return view('inventory.parts.index', compact('parts', 'categories'));
    }

    public function create()
    {
        return view('inventory.parts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:parts,code'],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:20'],
            'category' => ['nullable', 'string', 'max:100'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        Part::create($validated);

        return redirect()->route('parts.index')->with('success', 'Part berhasil ditambahkan.');
    }

    public function show(Part $part)
    {
        $part->load(['stocks', 'purchaseItems.purchase', 'usages.truck']);
        $part->total_stock = $part->stocks->sum('quantity');

        return view('inventory.parts.show', compact('part'));
    }

    public function edit(Part $part)
    {
        return view('inventory.parts.edit', compact('part'));
    }

    public function update(Request $request, Part $part)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:parts,code,'.$part->id],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:20'],
            'category' => ['nullable', 'string', 'max:100'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        $part->update($validated);

        return redirect()->route('parts.index')->with('success', 'Part berhasil diperbarui.');
    }

    public function destroy(Part $part)
    {
        $part->delete();

        return redirect()->route('parts.index')->with('success', 'Part berhasil dihapus.');
    }
}
