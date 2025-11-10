<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $q = Customer::query();
        if ($search = $request->get('q')) {
            $q->where('name', 'like', "%{$search}%");
        }
        $items = $q->orderBy('name')->paginate(15)->withQueryString();

        return view('master.customers.index', compact('items'));
    }

    public function create(): \Illuminate\View\View
    {
        return view('master.customers.create');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'npwp' => ['nullable', 'string', 'max:50'],
            'payment_term' => ['nullable', 'string', 'max:50'],
        ]);

        Customer::create($validated);

        return redirect()->route('customers.index')->with('success', 'Customer berhasil ditambahkan');
    }
}
