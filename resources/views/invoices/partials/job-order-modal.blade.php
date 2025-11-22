{{-- Modal: Pilih Job Order --}}
<x-modal id="jobOrderModal" title="Pilih Job Order">
    <form method="get" action="{{ route('invoices.create') }}" class="space-y-4" id="jobOrderForm">
        <input type="hidden" name="customer_id" value="{{ $selectedCustomer->id }}">
        {{-- Simpan state header invoice agar tidak hilang saat reload --}}
        <input type="hidden" name="invoice_date" id="modal_invoice_date">
        <input type="hidden" name="due_date" id="modal_due_date">
        <input type="hidden" name="payment_terms" id="modal_payment_terms">
        <input type="hidden" name="notes" id="modal_notes">
        <input type="hidden" name="tax_code" id="modal_tax_code">
        <input type="hidden" name="reference" id="modal_reference">

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
            <x-button type="submit" variant="primary">
                Gunakan Job Order Terpilih
            </x-button>
        </div>
    </form>

    <script>
        function toggleDpInput() {
            const isDp = document.getElementById('is_dp').checked;
            const container = document.getElementById('dp_input_container');
            if (isDp) {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
                document.getElementById('dp_amount').value = '';
            }
        }

        function updateJobOrderList() {
            const status = document.getElementById('status_filter').value;
            const customerId = document.querySelector('input[name="customer_id"]').value;
            const container = document.getElementById('job-order-list-container');
            
            // Show loading state
            container.innerHTML = '<div class="p-4 text-center text-sm text-slate-500">Memuat...</div>';
            
            // Fetch updated list via AJAX
            fetch(`{{ route('invoices.create') }}?load_job_orders=1&customer_id=${customerId}&status_filter=${status}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                container.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                container.innerHTML = '<div class="p-4 text-center text-sm text-red-500">Gagal memuat data.</div>';
            });
        }
    </script>
</x-modal>
