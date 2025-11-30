@extends('layouts.app', ['title' => 'Buku Besar (Traditional)'])

@section('content')
<div x-data="{
    showSearch: false,
    searchClass: '',
    searchDate: '',
    searchJournalNo: '',
    searchAccountCode: '',
    searchAccountName: '',
    searchDescription: '',
    searchNama: '',
    searchDebit: '',
    searchCredit: '',
    
    get filteredEntries() {
        let entries = Array.from(document.querySelectorAll('tbody tr[data-entry]'));
        
        return entries.filter(row => {
            const classText = row.dataset.class?.toLowerCase() || '';
            const dateText = row.dataset.date?.toLowerCase() || '';
            const journalNo = row.dataset.journalNo?.toLowerCase() || '';
            const accountCode = row.dataset.accountCode?.toLowerCase() || '';
            const accountName = row.dataset.accountName?.toLowerCase() || '';
            const description = row.dataset.description?.toLowerCase() || '';
            const nama = row.dataset.nama?.toLowerCase() || '';
            const debit = row.dataset.debit?.toLowerCase() || '';
            const credit = row.dataset.credit?.toLowerCase() || '';
            
            return classText.includes(this.searchClass.toLowerCase()) &&
                   dateText.includes(this.searchDate.toLowerCase()) &&
                   journalNo.includes(this.searchJournalNo.toLowerCase()) &&
                   accountCode.includes(this.searchAccountCode.toLowerCase()) &&
                   accountName.includes(this.searchAccountName.toLowerCase()) &&
                   description.includes(this.searchDescription.toLowerCase()) &&
                   nama.includes(this.searchNama.toLowerCase()) &&
                   debit.includes(this.searchDebit.toLowerCase()) &&
                   credit.includes(this.searchCredit.toLowerCase());
        });
    },
    
    get totalDebit() {
        return this.filteredEntries.reduce((sum, row) => {
            return sum + parseFloat(row.dataset.debitRaw || 0);
        }, 0);
    },
    
    get totalCredit() {
        return this.filteredEntries.reduce((sum, row) => {
            return sum + parseFloat(row.dataset.creditRaw || 0);
        }, 0);
    },
    
    filterRows() {
        const entries = Array.from(document.querySelectorAll('tbody tr[data-entry]'));
        const filtered = this.filteredEntries;
        
        entries.forEach(row => {
            if (filtered.includes(row)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
        
        // Update totals
        document.getElementById('total-debit').textContent = this.totalDebit.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('total-credit').textContent = this.totalCredit.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
}" x-init="$watch('searchClass', () => filterRows()); $watch('searchDate', () => filterRows()); $watch('searchJournalNo', () => filterRows()); $watch('searchAccountCode', () => filterRows()); $watch('searchAccountName', () => filterRows()); $watch('searchDescription', () => filterRows()); $watch('searchNama', () => filterRows()); $watch('searchDebit', () => filterRows()); $watch('searchCredit', () => filterRows());">

    <x-card>
        <x-slot:header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold">Buku Besar (Traditional View)</h2>
                    <p class="text-sm text-[var(--color-text-muted)] mt-1">Semua journal entries ditampilkan dalam satu tabel</p>
                </div>
                <div class="flex gap-2">
                    <button @click="showSearch = !showSearch" class="px-4 py-2 text-sm bg-slate-600 hover:bg-slate-700 text-white rounded-lg transition-colors">
                        <span x-text="showSearch ? 'Sembunyikan Filter' : 'Tampilkan Filter'"></span>
                    </button>
                    <a href="{{ route('journals.index') }}" class="px-4 py-2 text-sm bg-[var(--bg-surface-secondary)] hover:bg-[var(--bg-surface-tertiary)] rounded-lg transition-colors">
                        ← Kembali
                    </a>
                    <button onclick="window.print()" class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
                        Print
                    </button>
                </div>
        </x-slot:header>

        <!-- Filter Form -->
        <form method="get" class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
            <div>
                <label class="block text-sm font-medium mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ request('from') }}" class="w-full rounded-lg bg-[var(--bg-surface-secondary)] border border-[var(--border-color)] px-3 py-2 text-sm text-[var(--color-text-main)] focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ request('to') }}" class="w-full rounded-lg bg-[var(--bg-surface-secondary)] border border-[var(--border-color)] px-3 py-2 text-sm text-[var(--color-text-main)] focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>
            <div class="flex items-end">
                <x-button type="submit" variant="outline" class="w-full">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter
                </x-button>
            </div>
        </form>
    </x-card>

    <!-- Traditional Ledger Table -->
    <x-card :noPadding="true" class="mt-6 text-xs">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="sticky top-0 bg-[var(--bg-surface)] border-b-2 border-[var(--border-color)]">
                    <!-- Header Row -->
                    <tr class="text-[var(--color-text-muted)]">
                        <th class="px-3 py-2 text-left font-semibold">Class</th>
                        <th class="px-3 py-2 text-left font-semibold">Tanggal</th>
                        <th class="px-3 py-2 text-left font-semibold">No Jurnal</th>
                        <th class="px-3 py-2 text-left font-semibold">Kode Akun</th>
                        <th class="px-3 py-2 text-left font-semibold">Nama Akun</th>
                        <th class="px-3 py-2 text-left font-semibold">Keterangan</th>
                        <th class="px-3 py-2 text-left font-semibold">Nama</th>
                        <th class="px-3 py-2 text-right font-semibold">Debit</th>
                        <th class="px-3 py-2 text-right font-semibold">Kredit</th>
                    </tr>
                    <!-- Search Row -->
                    <tr x-show="showSearch" x-collapse class="bg-slate-50 dark:bg-slate-900">
                        <th class="px-2 py-1">
                            <input type="text" x-model="searchClass" placeholder="Filter..." class="w-full px-2 py-1 text-xs rounded border border-[var(--border-color)] bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        </th>
                        <th class="px-2 py-1">
                            <input type="text" x-model="searchDate" placeholder="Filter..." class="w-full px-2 py-1 text-xs rounded border border-[var(--border-color)] bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        </th>
                        <th class="px-2 py-1">
                            <input type="text" x-model="searchJournalNo" placeholder="Filter..." class="w-full px-2 py-1 text-xs rounded border border-[var(--border-color)] bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        </th>
                        <th class="px-2 py-1">
                            <input type="text" x-model="searchAccountCode" placeholder="Filter..." class="w-full px-2 py-1 text-xs rounded border border-[var(--border-color)] bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        </th>
                        <th class="px-2 py-1">
                            <input type="text" x-model="searchAccountName" placeholder="Filter..." class="w-full px-2 py-1 text-xs rounded border border-[var(--border-color)] bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        </th>
                        <th class="px-2 py-1">
                            <input type="text" x-model="searchDescription" placeholder="Filter..." class="w-full px-2 py-1 text-xs rounded border border-[var(--border-color)] bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        </th>
                        <th class="px-2 py-1">
                            <input type="text" x-model="searchNama" placeholder="Filter..." class="w-full px-2 py-1 text-xs rounded border border-[var(--border-color)] bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        </th>
                        <th class="px-2 py-1">
                            <input type="text" x-model="searchDebit" placeholder="Filter..." class="w-full px-2 py-1 text-xs rounded border border-[var(--border-color)] bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        </th>
                        <th class="px-2 py-1">
                            <input type="text" x-model="searchCredit" placeholder="Filter..." class="w-full px-2 py-1 text-xs rounded border border-[var(--border-color)] bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $entry)
                    <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-900/50"
                        data-entry
                        data-class="{{ $classLabels[$entry->journal->source_type] ?? $entry->journal->source_type }}"
                        data-date="{{ $entry->journal->journal_date->format('d M Y') }}"
                        data-journal-no="{{ $entry->journal->journal_no }}"
                        data-account-code="{{ $entry->account->code }}"
                        data-account-name="{{ $entry->account->name }}"
                        data-description="{{ $entry->journal->memo ?: $entry->description }}"
                        data-nama="{{ $entry->customer?->name ?? $entry->vendor?->name ?? $entry->transport?->driver?->name ?? $entry->transport?->plate_number ?? ($entry->journal->source_type === 'fixed_asset_depreciation' ? 'Sistem' : '-') }}"
                        data-debit="{{ $entry->debit > 0 ? number_format($entry->debit, 2, ',', '.') : '-' }}"
                        data-credit="{{ $entry->credit > 0 ? number_format($entry->credit, 2, ',', '.') : '-' }}"
                        data-debit-raw="{{ $entry->debit }}"
                        data-credit-raw="{{ $entry->credit }}">
                        <td class="px-3 py-2">
                            <span class="px-2 py-1 text-xs rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200">
                                {{ $classLabels[$entry->journal->source_type] ?? $entry->journal->source_type }}
                            </span>
                        </td>
                        <td class="px-3 py-2">{{ $entry->journal->journal_date->format('d M Y') }}</td>
                        <td class="px-3 py-2 line-clamp-2 font-medium">{{ $entry->journal->journal_no }}</td>
                        <td class="px-3 py-2 font-mono text-xs">{{ $entry->account->code }}</td>
                        <td class="px-3 py-2 line-clamp-2">{{ $entry->account->name }}</td>
                        <td class="px-3 py-2 text-[var(--color-text-muted)]">
                            {{ $entry->journal->memo ?: $entry->description }}
                            @if($entry->jobOrder)
                                <span class="text-indigo-600 font-medium"> - JO: {{ $entry->jobOrder->order_no }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-xs text-[var(--color-text-muted)] line-clamp-2">
                            @php
                                $nama = $entry->customer?->name 
                                     ?? $entry->vendor?->name 
                                     ?? $entry->transport?->driver?->name 
                                     ?? $entry->transport?->plate_number
                                     ?? ($entry->journal->source_type === 'fixed_asset_depreciation' ? 'Sistem' : '-');
                            @endphp
                            {{ $nama }}
                        </td>
                        <td class="px-3 py-2 text-right font-medium {{ $entry->debit > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                            {{ $entry->debit > 0 ? number_format($entry->debit, 2, ',', '.') : '-' }}
                        </td>
                        <td class="px-3 py-2 text-right font-medium {{ $entry->credit > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400' }}">
                            {{ $entry->credit > 0 ? number_format($entry->credit, 2, ',', '.') : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                            Tidak ada journal entries ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-100 dark:bg-slate-800 border-t-2 border-[var(--border-color)] font-bold">
                    <tr>
                        <td colspan="7" class="px-3 py-3 text-right">GRAND TOTAL (Filtered):</td>
                        <td class="px-3 py-3 text-right text-emerald-600 dark:text-emerald-400" id="total-debit">
                            {{ number_format($totalDebit, 2, ',', '.') }}
                        </td>
                        <td class="px-3 py-3 text-right text-blue-600 dark:text-blue-400" id="total-credit">
                            {{ number_format($totalCredit, 2, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-card>

    <div class="mt-4 text-sm text-[var(--color-text-muted)]">
        <p>💡 <strong>Tip:</strong> Gunakan search box di setiap kolom untuk filter data. Grand total akan otomatis menyesuaikan dengan hasil filter.</p>
    </div>
</div>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    word-break: break-word;
}

table {
    table-layout: fixed;
    width: 100%;
}

table td {
    white-space: normal;
}

/* Resizable columns */
table th {
    position: relative;
    user-select: none;
    overflow: visible;
}

table th .resizer {
    position: absolute;
    top: 0;
    right: -3px;
    width: 6px;
    cursor: col-resize;
    user-select: none;
    height: 100%;
    z-index: 1;
}

table th .resizer:hover {
    background-color: rgba(99, 102, 241, 0.5);
}

table th .resizer.resizing {
    background-color: rgba(99, 102, 241, 0.7);
}

@media print {
    .no-print, button, form, nav, aside {
        display: none !important;
    }
    
    table {
        font-size: 10px;
    }
    
    thead {
        position: static !important;
    }
    
    .resizer {
        display: none !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        const table = document.querySelector('table');
        if (!table) return;
        
        const cols = table.querySelectorAll('thead th');
        
        cols.forEach((col, index) => {
            // Set initial width
            if (!col.style.width) {
                col.style.width = col.offsetWidth + 'px';
            }
            
            const resizer = document.createElement('div');
            resizer.classList.add('resizer');
            col.appendChild(resizer);
            
            let startX, startWidth;
            
            resizer.addEventListener('mousedown', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                startX = e.pageX;
                startWidth = col.offsetWidth;
                
                resizer.classList.add('resizing');
                document.body.style.cursor = 'col-resize';
                document.body.style.userSelect = 'none';
                
                const mouseMoveHandler = function(e) {
                    e.preventDefault();
                    const width = startWidth + (e.pageX - startX);
                    if (width > 50) { // minimum width
                        col.style.width = width + 'px';
                    }
                };
                
                const mouseUpHandler = function() {
                    resizer.classList.remove('resizing');
                    document.body.style.cursor = '';
                    document.body.style.userSelect = '';
                    document.removeEventListener('mousemove', mouseMoveHandler);
                    document.removeEventListener('mouseup', mouseUpHandler);
                };
                
                document.addEventListener('mousemove', mouseMoveHandler);
                document.addEventListener('mouseup', mouseUpHandler);
            });
        });
    }, 100);
});
</script>
@endsection
