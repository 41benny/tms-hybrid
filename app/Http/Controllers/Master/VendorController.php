<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Vendor;
use App\Models\Master\VendorBankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $q = Vendor::query()->with('bankAccounts');
        if ($search = $request->get('q')) {
            $q->where('name', 'like', "%{$search}%");
        }
        $items = $q->orderBy('name')->paginate(15)->withQueryString();

        return view('master.vendors.index', compact('items'));
    }

    public function create(): \Illuminate\View\View
    {
        return view('master.vendors.create');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'vendor_type' => ['required', 'string', 'max:50'],
            'pic_name' => ['nullable', 'string', 'max:255'],
            'pic_phone' => ['nullable', 'string', 'max:50'],
            'pic_email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            // Bank accounts
            'bank_accounts' => ['nullable', 'array'],
            'bank_accounts.*.bank_name' => ['required', 'string', 'max:255'],
            'bank_accounts.*.account_number' => ['required', 'string', 'max:255'],
            'bank_accounts.*.account_holder_name' => ['required', 'string', 'max:255'],
            'bank_accounts.*.branch' => ['nullable', 'string', 'max:255'],
            'bank_accounts.*.is_primary' => ['nullable', 'boolean'],
            'bank_accounts.*.is_active' => ['nullable', 'boolean'],
            'bank_accounts.*.notes' => ['nullable', 'string'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        DB::transaction(function () use ($validated, $request) {
            $vendor = Vendor::create($validated);

            // Save bank accounts
            if ($request->has('bank_accounts')) {
                foreach ($request->bank_accounts as $index => $accountData) {
                    $vendor->bankAccounts()->create([
                        'bank_name' => $accountData['bank_name'],
                        'account_number' => $accountData['account_number'],
                        'account_holder_name' => $accountData['account_holder_name'],
                        'branch' => $accountData['branch'] ?? null,
                        'is_primary' => isset($accountData['is_primary']) && $accountData['is_primary'],
                        'is_active' => isset($accountData['is_active']) ? $accountData['is_active'] : true,
                        'notes' => $accountData['notes'] ?? null,
                    ]);
                }
            }
        });

        return redirect()->route('vendors.index')->with('success', 'Vendor berhasil ditambahkan');
    }

    public function edit(Vendor $vendor)
    {
        $vendor->load('bankAccounts');

        return view('master.vendors.create', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'vendor_type' => ['required', 'string', 'max:50'],
            'pic_name' => ['nullable', 'string', 'max:255'],
            'pic_phone' => ['nullable', 'string', 'max:50'],
            'pic_email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            // Bank accounts
            'bank_accounts' => ['nullable', 'array'],
            'bank_accounts.*.id' => ['nullable', 'exists:vendor_bank_accounts,id'],
            'bank_accounts.*.bank_name' => ['required', 'string', 'max:255'],
            'bank_accounts.*.account_number' => ['required', 'string', 'max:255'],
            'bank_accounts.*.account_holder_name' => ['required', 'string', 'max:255'],
            'bank_accounts.*.branch' => ['nullable', 'string', 'max:255'],
            'bank_accounts.*.is_primary' => ['nullable', 'boolean'],
            'bank_accounts.*.is_active' => ['nullable', 'boolean'],
            'bank_accounts.*.notes' => ['nullable', 'string'],
            'bank_accounts.*._destroy' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        DB::transaction(function () use ($validated, $request, $vendor) {
            $vendor->update($validated);

            // Update bank accounts
            if ($request->has('bank_accounts')) {
                $existingIds = [];

                foreach ($request->bank_accounts as $accountData) {
                    // Skip if marked for deletion
                    if (isset($accountData['_destroy']) && $accountData['_destroy']) {
                        if (isset($accountData['id'])) {
                            VendorBankAccount::find($accountData['id'])?->delete();
                        }

                        continue;
                    }

                    if (isset($accountData['id']) && $accountData['id']) {
                        // Update existing
                        $account = VendorBankAccount::find($accountData['id']);
                        if ($account && $account->vendor_id == $vendor->id) {
                            $account->update([
                                'bank_name' => $accountData['bank_name'],
                                'account_number' => $accountData['account_number'],
                                'account_holder_name' => $accountData['account_holder_name'],
                                'branch' => $accountData['branch'] ?? null,
                                'is_primary' => isset($accountData['is_primary']) && $accountData['is_primary'],
                                'is_active' => isset($accountData['is_active']) ? $accountData['is_active'] : true,
                                'notes' => $accountData['notes'] ?? null,
                            ]);
                            $existingIds[] = $account->id;
                        }
                    } else {
                        // Create new
                        $newAccount = $vendor->bankAccounts()->create([
                            'bank_name' => $accountData['bank_name'],
                            'account_number' => $accountData['account_number'],
                            'account_holder_name' => $accountData['account_holder_name'],
                            'branch' => $accountData['branch'] ?? null,
                            'is_primary' => isset($accountData['is_primary']) && $accountData['is_primary'],
                            'is_active' => isset($accountData['is_active']) ? $accountData['is_active'] : true,
                            'notes' => $accountData['notes'] ?? null,
                        ]);
                        $existingIds[] = $newAccount->id;
                    }
                }

                // Delete accounts that were not in the request
                if (! empty($existingIds)) {
                    $vendor->bankAccounts()->whereNotIn('id', $existingIds)->delete();
                }
            }
        });

        return redirect()->route('vendors.index')->with('success', 'Vendor berhasil diupdate');
    }
}
