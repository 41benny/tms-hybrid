{{-- Step 1: Informasi Customer --}}
<x-card title="1. Informasi Customer" collapsible="true">
    @php
        $selectedCustomerId = (int) request('customer_id');
        $selectedCustomer = $selectedCustomerId
            ? $customers->firstWhere('id', $selectedCustomerId)
            : null;
    @endphp
    <form method="get" action="{{ route('invoices.create') }}" class="space-y-4 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Customer</label>
                <div class="relative">
                    <input type="hidden" name="customer_id" id="customer_id_input" value="{{ $selectedCustomerId ?: '' }}">
                    <input type="text"
                           id="customer_search"
                           autocomplete="off"
                           placeholder="Ketik nama customer..."
                           value="{{ $selectedCustomer?->name }}"
                           class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                    <div id="customer_suggestions"
                         class="absolute z-30 mt-1 w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                        {{-- suggestions diisi via JS --}}
                    </div>
                </div>
                <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                    Ketik minimal 2 huruf, lalu pilih customer dari daftar.
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                    Reff / No PO Customer
                </label>
                <input type="text"
                       name="reference"
                       id="reference_header"
                       value="{{ old('reference', request('reference')) }}"
                       placeholder="No PO dari customer"
                       class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-3 py-2 text-sm">
            </div>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                Alamat Customer
            </label>
            <textarea rows="3"
                      class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-3 py-2 text-sm text-slate-700 dark:text-slate-200"
                      name="customer_address"
                      placeholder="Alamat customer akan terisi setelah memilih customer">{{ old('customer_address', $selectedCustomer?->address) }}</textarea>
            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                Terisi otomatis dari master customer, tetapi boleh diedit untuk keperluan invoice ini.
            </p>

            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        Telepon Customer
                    </label>
                    <input type="text"
                           name="customer_phone"
                           value="{{ old('customer_phone', $selectedCustomer?->phone) }}"
                           class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                        NPWP Customer
                    </label>
                    <input type="text"
                           name="customer_npwp"
                           value="{{ old('customer_npwp', $selectedCustomer?->npwp) }}"
                           class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-3 py-2 text-sm">
                </div>
            </div>
        </div>
    </form>
</x-card>
