@extends('layouts.app', ['title' => 'Buat Permintaan Faktur Pajak'])

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div>
            <div class="text-xl font-semibold">Buat Permintaan Faktur Pajak</div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Pilih invoice yang akan diajukan faktur pajaknya</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('tax-invoices.index') }}" class="px-3 py-2 rounded bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                Kembali
            </a>
        </div>
    </div>

    <form action="{{ route('tax-invoices.store') }}" method="POST" id="requestForm">
        @csrf

        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/80">
            <table class="min-w-full text-sm">
                <thead class="text-left border-b border-slate-200 dark:border-slate-800">
                    <tr class="text-slate-500">
                        <th class="px-4 py-2 w-10">
                            <input type="checkbox" id="selectAll" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </th>
                        <th class="px-4 py-2">No. Invoice</th>
                        <th class="px-4 py-2">Tanggal</th>
                        <th class="px-4 py-2">Customer</th>
                        <th class="px-4 py-2">NPWP</th>
                        <th class="px-4 py-2">Tipe Transaksi</th>
                        <th class="px-4 py-2 text-right">DPP</th>
                        <th class="px-4 py-2 text-right">PPN</th>
                        <th class="px-4 py-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer" onclick="toggleRow('{{ $inv->id }}')">
                            <td class="px-4 py-2">
                                <input type="checkbox" name="invoice_ids[]" value="{{ $inv->id }}"
                                       class="invoice-check rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                       onclick="event.stopPropagation()">
                            </td>
                            <td class="px-4 py-2 font-medium">{{ $inv->invoice_number }}</td>
                            <td class="px-4 py-2">{{ $inv->invoice_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-2">
                                {{ $inv->customer->name }}
                            </td>
                            <td class="px-4 py-2">
                                {{ $inv->customer->npwp ?? '-' }}
                            </td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 rounded bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 text-xs">
                                    {{ $inv->transaction_type ?? '04' }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-right">{{ number_format($inv->subtotal, 0, ',', '.') }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format($inv->tax_amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-2 text-right font-semibold">{{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-8 text-slate-500">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <p>Tidak ada invoice yang perlu dibuatkan faktur pajak saat ini.</p>
                                    <p class="text-xs text-slate-400">Menampilkan semua invoice (kecuali Cancel) yang belum request faktur pajak</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex items-center justify-between bg-white dark:bg-slate-900 p-4 rounded-lg border border-slate-200 dark:border-slate-800 shadow-sm sticky bottom-4 z-10">
            <div class="text-sm text-slate-500">
                <span id="selectedCount" class="font-semibold">0</span> invoice dipilih
            </div>
            <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-500 disabled:opacity-50 disabled:cursor-not-allowed font-medium transition-colors shadow-sm" id="submitBtn" disabled>
                Ajukan Permintaan
            </button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Tax Invoice Create: Script loaded');

            const selectAll = document.getElementById('selectAll');
            const submitBtn = document.getElementById('submitBtn');
            const selectedCountSpan = document.getElementById('selectedCount');

            console.log('Elements found:', {
                selectAll: !!selectAll,
                submitBtn: !!submitBtn,
                selectedCountSpan: !!selectedCountSpan
            });

            function getCheckboxes() {
                return document.querySelectorAll('.invoice-check');
            }

            function updateState() {
                const checkboxes = getCheckboxes();
                const checkedCount = document.querySelectorAll('.invoice-check:checked').length;

                console.log('Update state:', {
                    totalCheckboxes: checkboxes.length,
                    checkedCount: checkedCount
                });

                if (selectedCountSpan) selectedCountSpan.textContent = checkedCount;
                if (submitBtn) submitBtn.disabled = checkedCount === 0;

                if (selectAll) {
                    const allChecked = checkedCount === checkboxes.length && checkboxes.length > 0;
                    selectAll.checked = allChecked;
                    selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
                }
            }

            // Expose toggleRow to global scope
            window.toggleRow = function(id) {
                console.log('Toggle row:', id);
                const checkbox = document.querySelector(`.invoice-check[value="${id}"]`);
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    updateState();
                } else {
                    console.error('Checkbox not found for id:', id);
                }
            };

            if (selectAll) {
                selectAll.addEventListener('change', function(e) {
                    console.log('Select all clicked, checked:', this.checked);
                    const checkboxes = getCheckboxes();
                    console.log('Found checkboxes:', checkboxes.length);
                    checkboxes.forEach(cb => {
                        cb.checked = this.checked;
                        console.log('Set checkbox', cb.value, 'to', this.checked);
                    });
                    updateState();
                });
                console.log('Select all listener attached');
            } else {
                console.error('Select all checkbox not found!');
            }

            // Add change listeners to all checkboxes
            const checkboxes = getCheckboxes();
            console.log('Attaching listeners to', checkboxes.length, 'checkboxes');
            checkboxes.forEach((cb, index) => {
                cb.addEventListener('change', function() {
                    console.log('Checkbox', index, 'changed to', this.checked);
                    updateState();
                });
                cb.addEventListener('click', e => e.stopPropagation());
            });

            // Initial update
            updateState();
        });
    </script>
@endsection
