<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Finance\CashBankAccount;
use Illuminate\Http\Request;

class CashBankAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CashBankAccount::query()->with('chartOfAccount');

        // Filter by type
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        // Filter by status
        if ($request->has('status')) {
            $status = $request->get('status');
            $query->where('is_active', $status === 'active');
        }

        // Search
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('account_number', 'like', "%{$search}%")
                  ->orWhere('bank_name', 'like', "%{$search}%");
            });
        }

        $accounts = $query->orderBy('type')->orderBy('name')->paginate(20)->withQueryString();

        return view('master.cash-bank-accounts.index', compact('accounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $coas = ChartOfAccount::whereIn('code', ['1110', '1120', '1130']) // Cash & Bank accounts
            ->orWhere('code', 'like', '11%')
            ->orderBy('code')
            ->get();

        return view('master.cash-bank-accounts.create', compact('coas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'type' => ['required', 'in:cash,bank'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'branch' => ['nullable', 'string', 'max:255'],
            'account_holder' => ['nullable', 'string', 'max:255'],
            'coa_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'opening_balance' => ['nullable', 'numeric'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Set current_balance = opening_balance untuk account baru
        $data['current_balance'] = $data['opening_balance'] ?? 0;
        $data['is_active'] = $data['is_active'] ?? true;

        $account = CashBankAccount::create($data);

        return redirect()->route('master.cash-bank-accounts.index')
            ->with('success', 'Cash/Bank account berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CashBankAccount $cash_bank_account)
    {
        $cash_bank_account->load(['chartOfAccount', 'transactions' => function($q) {
            $q->latest('tanggal')->limit(10);
        }]);

        return view('master.cash-bank-accounts.show', ['account' => $cash_bank_account]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CashBankAccount $cash_bank_account)
    {
        $coas = ChartOfAccount::whereIn('code', ['1110', '1120', '1130'])
            ->orWhere('code', 'like', '11%')
            ->orderBy('code')
            ->get();

        return view('master.cash-bank-accounts.edit', [
            'account' => $cash_bank_account,
            'coas' => $coas
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CashBankAccount $cash_bank_account)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'type' => ['required', 'in:cash,bank'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'branch' => ['nullable', 'string', 'max:255'],
            'account_holder' => ['nullable', 'string', 'max:255'],
            'coa_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'opening_balance' => ['nullable', 'numeric'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $data['is_active'] ?? false;

        $cash_bank_account->update($data);

        return redirect()->route('master.cash-bank-accounts.index')
            ->with('success', 'Cash/Bank account berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CashBankAccount $cash_bank_account)
    {
        // Soft delete: set is_active = false
        $cash_bank_account->update(['is_active' => false]);

        return redirect()->route('master.cash-bank-accounts.index')
            ->with('success', 'Cash/Bank account berhasil dinonaktifkan.');
    }

    /**
     * Activate account
     */
    public function activate(CashBankAccount $cash_bank_account)
    {
        $cash_bank_account->update(['is_active' => true]);

        return redirect()->route('master.cash-bank-accounts.index')
            ->with('success', 'Cash/Bank account berhasil diaktifkan.');
    }
}
