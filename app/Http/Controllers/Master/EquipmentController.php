<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Equipment;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        $q = Equipment::query();
        if ($search = $request->get('q')) {
            $q->where('name', 'like', "%{$search}%");
        }
        $items = $q->orderBy('name')->paginate(15)->withQueryString();

        return view('master.equipment.index', compact('items'));
    }

    public function create(): \Illuminate\View\View
    {
        return view('master.equipment.create');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $equipment = Equipment::create($validated);

        // If AJAX request (from modal)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'equipment' => $equipment,
                'message' => 'Cargo type berhasil ditambahkan',
            ]);
        }

        return redirect()->route('equipment.index')->with('success', 'Equipment berhasil ditambahkan');
    }

    public function edit(Equipment $equipment): \Illuminate\View\View
    {
        return view('master.equipment.edit', compact('equipment'));
    }

    public function update(Request $request, Equipment $equipment): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $equipment->update($validated);

        return redirect()->route('equipment.index')->with('success', 'Equipment berhasil diupdate');
    }

    public function destroy(Equipment $equipment): \Illuminate\Http\RedirectResponse
    {
        $equipment->delete();

        return redirect()->route('equipment.index')->with('success', 'Equipment berhasil dihapus');
    }
}
