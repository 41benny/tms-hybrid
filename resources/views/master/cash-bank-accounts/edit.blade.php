@extends('layouts.app', ['title' => 'Edit Cash/Bank Account'])

@section('content')
<form method="post" action="{{ route('master.cash-bank-accounts.update', $account) }}" class="space-y-6">
    @csrf
    @method('PUT')

    {{-- Header --}}
    <x-card>
        <x-slot:header>
            <div class="flex items-center gap-3">
                <a href="{{ route('master.cash-bank-accounts.index') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <div class="text-xl font-semibold">Edit Cash/Bank Account</div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ $account->name }}</p>
                </div>
            </div>
        </x-slot:header>
    </x-card>

    {{-- Basic Information --}}
    <x-card title="Basic Information">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Account Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $account->name) }}" required class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2">
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Code</label>
                <input type="text" name="code" value="{{ old('code', $account->code) }}" class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2">
                @error('code')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Type <span class="text-red-500">*</span></label>
                <select name="type" id="account_type" required class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2" onchange="toggleBankFields()">
                    <option value="cash" @selected(old('type', $account->type) === 'cash')>Cash</option>
                    <option value="bank" @selected(old('type', $account->type) === 'bank')>Bank</option>
                </select>
                @error('type')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <div class="flex items-center gap-2 mt-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $account->is_active)) class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                    <label for="is_active" class="text-sm">Active</label>
                </div>
            </div>
        </div>
    </x-card>

    {{-- Bank Information (conditional) --}}
    <x-card title="Bank Information" id="bank_info_section">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Bank Name</label>
                <input type="text" name="bank_name" value="{{ old('bank_name', $account->bank_name) }}" class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2">
                @error('bank_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Account Number</label>
                <input type="text" name="account_number" value="{{ old('account_number', $account->account_number) }}" class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2">
                @error('account_number')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Branch</label>
                <input type="text" name="branch" value="{{ old('branch', $account->branch) }}" class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2">
                @error('branch')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Account Holder</label>
                <input type="text" name="account_holder" value="{{ old('account_holder', $account->account_holder) }}" class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2">
                @error('account_holder')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </x-card>

    {{-- Accounting Information --}}
    <x-card title="Accounting Information">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Chart of Account</label>
                <select name="coa_id" class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2">
                    <option value="">- Select COA -</option>
                    @foreach($coas as $coa)
                        <option value="{{ $coa->id }}" @selected(old('coa_id', $account->coa_id) == $coa->id)>
                            {{ $coa->code }} - {{ $coa->name }}
                        </option>
                    @endforeach
                </select>
                @error('coa_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Opening Balance</label>
                <input type="number" step="0.01" name="opening_balance" value="{{ old('opening_balance', $account->opening_balance) }}" class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2">
                @error('opening_balance')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">Current Balance</label>
                <div class="text-2xl font-bold {{ $account->current_balance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ number_format($account->current_balance, 2, ',', '.') }}
                </div>
                <p class="text-xs text-slate-500 mt-1">Current balance is calculated from transactions. Cannot be edited manually.</p>
            </div>
        </div>

        <div class="mt-4">
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea name="description" rows="3" class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2">{{ old('description', $account->description) }}</textarea>
            @error('description')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    </x-card>

    {{-- Actions --}}
    <div class="flex justify-end gap-2">
        <a href="{{ route('master.cash-bank-accounts.index') }}" class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded hover:bg-slate-50 dark:hover:bg-slate-800">
            Cancel
        </a>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Update Account
        </button>
    </div>
</form>

<script>
function toggleBankFields() {
    const type = document.getElementById('account_type').value;
    const bankSection = document.getElementById('bank_info_section');
    
    if (type === 'bank') {
        bankSection.style.display = 'block';
    } else {
        bankSection.style.display = 'none';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleBankFields();
});
</script>
@endsection
