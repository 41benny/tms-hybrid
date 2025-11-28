{{-- Modal: Pilih Job Order --}}
<x-modal id="jobOrderModal" title="Pilih Job Order">
    <div class="space-y-4" id="jobOrderFormContainer">
        <input type="hidden" id="modal_customer_id" value="{{ $selectedCustomer->id }}">
        
        <div class="flex items-center justify-between gap-3">
            <div class="text-sm font-medium text-slate-700 dark:text-slate-200">
                Job Order untuk {{ $selectedCustomer->name }}
            </div>
            <div class="flex items-center gap-2 text-xs">
                <span class="text-slate-500 dark:text-slate-400">Filter Status:</span>
                <select name="status_filter" id="status_filter"
                        class="rounded bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 px-2 py-1 text-xs"
                        onchange="updateJobOrderList()">
                    <option value="completed" @selected($statusFilter === 'completed')>Hanya Completed</option>
                    <option value="in_progress" @selected($statusFilter === 'in_progress')>In Progress (Untuk DP)</option>
                    <option value="all" @selected($statusFilter === 'all')>Semua Status</option>
                </select>
            </div>
        </div>

        <div id="job-order-list-container" class="border border-slate-200 dark:border-slate-700 rounded-lg max-h-60 overflow-y-auto divide-y divide-slate-200 dark:divide-slate-700">
            @include('invoices.partials.job-order-list')
        </div>

        {{-- DP Options --}}
        <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-lg border border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-2 mb-2">
                <input type="checkbox" name="is_dp" id="is_dp" value="1" class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500" onchange="toggleDpInput()">
                <label for="is_dp" class="text-sm font-medium text-slate-700 dark:text-slate-300">Buat sebagai Invoice Uang Muka (DP)</label>
            </div>
            <div id="dp_input_container" class="hidden pl-6">
                <label class="block text-xs text-slate-500 dark:text-slate-400 mb-1">Nominal DP (Opsional, default 50%)</label>
                <input type="number" name="dp_amount" id="dp_amount" class="w-full rounded-md border-slate-300 dark:border-slate-700 dark:bg-slate-900 text-sm" placeholder="Masukkan nominal DP...">
                <p class="text-xs text-slate-400 mt-1">Jika kosong, akan dihitung 50% dari nilai Job Order.</p>
            </div>
        </div>

        <div class="flex justify-between items-center mt-4">
            <button type="button"
                    onclick="closeJobOrderModal()"
                    class="text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">
                Batal
            </button>
            <x-button type="button" variant="primary" onclick="addSelectedJobOrders()">
                Gunakan Job Order Terpilih
            </x-button>
        </div>
    </div>
    {{-- All JavaScript functions have been moved to invoice-create.js for global availability --}}
</x-modal>
