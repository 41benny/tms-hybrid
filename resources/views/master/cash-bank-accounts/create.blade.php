@extends('layouts.app', ['title' => 'Create Cash/Bank Account'])

@section('content')
<form method="post" action="{{ route('master.cash-bank-accounts.store') }}" class="space-y-6">
    @csrf

    {{-- Header --}}
    <x-card>
        <x-slot:header>
            <div class="flex items-center gap-3">
                <a href="{{ route('master.cash-bank-accounts.index') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <div class="text-xl font-semibold">Create Cash/Bank Account</div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Add new cash or bank account</p>
                </div>
            </div>
        </x-slot:header>
    </x-card>

    {{-- Basic Information --}}
    <x-card title="Basic Information">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Account Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2" placeholder="e.g., Kas Besar, BCA Operasional">
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Code</label>
                <input type="text" name="code" value="{{ old('code') }}" class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2" placeholder="e.g., KAS-01">
                @error('code')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Type <span class="text-red-500">*</span></label>
                <select name="type" id="account_type" required class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2" onchange="toggleBankFields()">
                    <option value="cash" @selected(old('type') === 'cash')>Cash</option>
                    <option value="bank" @selected(old('type') === 'bank')>Bank</option>
                </select>
                @error('type')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <div class="flex items-center gap-2 mt-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500">
                    <label for="is_active" class="text-sm">Active</label>
                </div>
            </div>
        </div>
    </x-card>

    {{-- Bank Information (conditional) --}}
    <x-card title="Bank Information" id="bank_info_section" style="display: none;">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Bank Name</label>
                <input type="text" name="bank_name" value="{{ old('bank_name') }}" class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2" placeholder="e.g., Bank Central Asia">
                @error('bank_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Account Number</label>
                <input type="text" name="account_number" value="{{ old('account_number') }}" class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2" placeholder="e.g., 1234567890">
                @error('account_number')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Branch</label>
                <input type="text" name="branch" value="{{ old('branch') }}" class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2" placeholder="e.g., KCP Jakarta Pusat">
                @error('branch')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Account Holder</label>
                <input type="text" name="account_holder" value="{{ old('account_holder') }}" class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2" placeholder="e.g., PT. Company Name">
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
                        <option value="{{ $coa->id }}" @selected(old('coa_id') == $coa->id)>
                            {{ $coa->code }} - {{ $coa->name }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-500 mt-1">Link to chart of account for journal entries</p>
                @error('coa_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Opening Balance</label>
                <input type="number" step="0.01" name="opening_balance" value="{{ old('opening_balance', 0) }}" class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2">
                <p class="text-xs text-slate-500 mt-1">Initial balance when account is created</p>
                @error('opening_balance')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-4">
            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea name="description" rows="3" class="w-full rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2" placeholder="Additional notes...">{{ old('description') }}</textarea>
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
            Create Account
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
