@extends('layouts.app', ['title' => 'Kas/Bank'])

@section('content')
    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
            <div class="flex items-center gap-2 text-green-800 dark:text-green-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <div class="flex items-center gap-2 text-red-800 dark:text-red-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <x-card :noPadding="true">
        <x-slot:header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="text-xl font-semibold">Kas/Bank</div>
                <div class="flex gap-2">
                    <x-button :href="route('cash-banks.create')" variant="primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Transaksi Baru
                    </x-button>
                </div>
            </div>
        </x-slot:header>

        <form method="get" id="filter-form">
        <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="text-left border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
                {{-- Row 1: Column Headers --}}
                <tr class="text-slate-600 dark:text-slate-400 text-xs uppercase">
                    <th class="px-3 py-3 text-center">No</th>
                    <th class="px-3 py-3" style="min-width: 100px">Tanggal</th>
                    <th class="px-3 py-3">No Voucher</th>
                    <th class="px-3 py-3">Nama</th>
                    <th class="px-3 py-3">Deskripsi</th>
                    <th class="px-3 py-3">Akun</th>
                    <th class="px-3 py-3 text-right">Debet</th>
                    <th class="px-3 py-3 text-right">Kredit</th>
                    <th class="px-3 py-3 text-right">Saldo</th>
                    <th class="px-3 py-3">Kategori</th>
                    <th class="px-3 py-3 text-center">Aksi</th>
                </tr>
                {{-- Row 2: Filters --}}
                <tr class="bg-slate-100 dark:bg-slate-900/50 text-xs">
                    <th class="px-1 py-1"></th>
                    <th class="px-1 py-1">
                        <div class="flex flex-col gap-1">
                            <input type="date" name="from" value="{{ request('from') }}" class="w-full px-1 py-1 text-[10px] rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500" onchange="this.form.submit()">
                            <input type="date" name="to" value="{{ request('to') }}" class="w-full px-1 py-1 text-[10px] rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500" onchange="this.form.submit()">
                        </div>
                    </th>
                    <th class="px-1 py-1">
                        <input type="text" name="voucher_number" value="{{ request('voucher_number') }}" placeholder="Cari Voucher..." class="w-full px-2 py-1 text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500" onkeypress="if(event.keyCode==13){this.form.submit()}">
                    </th>
                    <th class="px-1 py-1">
                        <input type="text" name="recipient" value="{{ request('recipient') }}" placeholder="Cari Nama..." class="w-full px-2 py-1 text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500" onkeypress="if(event.keyCode==13){this.form.submit()}">
                    </th>
                    <th class="px-1 py-1">
                        <input type="text" name="description" value="{{ request('description') }}" placeholder="Cari Deskripsi..." class="w-full px-2 py-1 text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500" onkeypress="if(event.keyCode==13){this.form.submit()}">
                    </th>
                    <th class="px-1 py-1">
                        <select name="cash_bank_account_id" class="w-full px-1 py-1 text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500" onchange="this.form.submit()">
                            <option value="">Semua</option>
                            @foreach($accounts as $a)
                                <option value="{{ $a->id }}" @selected(request('cash_bank_account_id')==$a->id)>{{ $a->name }}</option>
                            @endforeach
                        </select>
                    </th>
                    {{-- Empty filters for Amount columns --}}
                    <th></th>
                    <th></th>
                    <th></th>
                    <th class="px-1 py-1">
                        <select name="sumber" class="w-full px-1 py-1 text-xs rounded border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500" onchange="this.form.submit()">
                            <option value="">Semua</option>
                            @foreach(['customer_payment','vendor_payment','expense','other_in','other_out'] as $s)
                                <option value="{{ $s }}" @selected(request('sumber')==$s)>{{ ucwords(str_replace('_',' ', $s)) }}</option>
                            @endforeach
                        </select>
                    </th>
                    <th class="px-1 py-1 text-center">
                        <a href="{{ route('cash-banks.index') }}" class="text-xs text-red-600 hover:text-red-800" title="Reset Filter">Reset</a>
                    </th>
                </tr>
            </thead>
            <tbody>
            @php
                $runningBalance = $pageStartBalance;
                $startNumber = ($transactions->currentPage() - 1) * $transactions->perPage() + 1;
            @endphp
            @forelse($transactions as $index => $t)
                @php
                    // Calculate Net Amount
                    $netAmount = $t->amount - ($t->withholding_pph23 ?? 0) - ($t->admin_fee ?? 0);
                    
                    // Display checks
                    $isNetDiff = abs($netAmount - $t->amount) > 0.01;
                    
                    if ($t->jenis === 'cash_in') {
                        $debet = $netAmount;
                        $kredit = 0;
                        $signedNet = $netAmount;
                    } else {
                        $debet = 0;
                        $kredit = $netAmount;
                        $signedNet = -$netAmount;
                    }
                    
                    $currentBalance = $runningBalance;
                    $runningBalance -= $signedNet; 
                @endphp
                <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <td class="px-3 py-3 text-center text-slate-500">{{ $startNumber + $index }}</td>
                    <td class="px-3 py-3 whitespace-nowrap">
                        <div class="font-medium text-slate-900 dark:text-slate-100">{{ $t->tanggal->format('d/m/Y') }}</div>
                    </td>
                    <td class="px-3 py-3">
                        <div class="text-sm font-bold text-blue-600 dark:text-blue-400">{{ $t->voucher_number }}</div>
                    </td>
                    <td class="px-3 py-3">
                        @php
                            $displayName = $t->recipient_name ?: ($t->customer?->name ?? $t->vendor?->name ?? '-');
                        @endphp
                        <div class="font-medium text-slate-900 dark:text-slate-100 truncate max-w-[150px]" title="{{ $displayName }}">
                            {{ $displayName }}
                        </div>
                    </td>
                    <td class="px-3 py-3 max-w-[200px]">
                        <div class="text-slate-700 dark:text-slate-300 truncate" title="{{ $t->description }}">
                            {{ $t->description ?: '-' }}
                        </div>
                    </td>
                    <td class="px-3 py-3">
                        <div class="text-slate-900 dark:text-slate-100">{{ $t->account->name ?? '-' }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $t->account->account_number ?? '' }}</div>
                    </td>
                    <td class="px-3 py-3 text-right">
                        @if($debet > 0)
                            <div class="flex flex-col items-end">
                                <span class="text-green-600 dark:text-green-400 font-bold">Rp {{ number_format($debet, 0, ',', '.') }}</span>
                                @if($isNetDiff)
                                    <span class="text-[10px] text-slate-400 line-through" title="Gross: {{ number_format($t->amount, 0) }}">Rp {{ number_format($t->amount, 0, ',', '.') }}</span>
                                @endif
                            </div>
                        @else
                            <span class="text-slate-400">-</span>
                        @endif
                    </td>
                    <td class="px-3 py-3 text-right">
                        @if($kredit > 0)
                             <div class="flex flex-col items-end">
                                <span class="text-red-600 dark:text-red-400 font-bold">Rp {{ number_format($kredit, 0, ',', '.') }}</span>
                                @if($isNetDiff)
                                    <span class="text-[10px] text-slate-400 line-through" title="Gross: {{ number_format($t->amount, 0) }}">Rp {{ number_format($t->amount, 0, ',', '.') }}</span>
                                @endif
                            </div>
                        @else
                            <span class="text-slate-400">-</span>
                        @endif
                    </td>
                    <td class="px-3 py-3 text-right font-bold">
                        <span class="{{ $currentBalance >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400' }}">
                            Rp {{ number_format($currentBalance, 0, ',', '.') }}
                        </span>
                    </td>
                    <td class="px-3 py-3">
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                            {{ $t->sumber === 'customer_payment' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                            {{ $t->sumber === 'vendor_payment' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                            {{ $t->sumber === 'expense' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' : '' }}
                            {{ in_array($t->sumber, ['other_in', 'other_out']) ? 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200' : '' }}">
                            {{ ucwords(str_replace('_', ' ', $t->sumber)) }}
                        </span>
                    </td>
                    <td class="px-3 py-3">
                        <div class="flex items-center gap-2 justify-center">
                            <a href="{{ route('cash-banks.show', $t) }}" class="inline-flex items-center px-2 py-1 text-xs text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded" title="Detail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('cash-banks.print', $t) }}" target="_blank" class="inline-flex items-center px-2 py-1 text-xs text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 rounded" title="Print Voucher">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                            </a>
                            <form action="{{ route('cash-banks.cancel', $t) }}" method="POST" class="inline" onsubmit="return confirm('⚠️ VOID TRANSACTION?\n\nIni akan:\n✓ Hapus jurnal\n✓ Hapus payment records\n✓ Rollback vendor bill status\n✓ Mark transaction as VOID\n\nLanjutkan?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-2 py-1 text-xs text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded" title="Cancel/Void">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="px-3 py-8 text-center text-slate-500 dark:text-slate-400">
                        <div class="flex flex-col items-center gap-2">
                            <svg class="w-12 h-12 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p>Tidak ada transaksi</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
            <tfoot class="bg-slate-50 dark:bg-slate-800 border-t-2 border-slate-200 dark:border-slate-700 font-bold text-sm">
                <tr>
                    <td colspan="6" class="px-3 py-3 text-right uppercase text-slate-500 dark:text-slate-400">Total Ringkasan:</td>
                    <td class="px-3 py-3 text-right text-green-600 dark:text-green-400">
                        Rp {{ number_format($summary['in'] ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="px-3 py-3 text-right text-red-600 dark:text-red-400">
                        Rp {{ number_format($summary['out'] ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="px-3 py-3 text-right text-blue-600 dark:text-blue-400">
                        Rp {{ number_format($summary['net'] ?? 0, 0, ',', '.') }}
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
        </div>
        </form>
    </x-card>

    <div class="mt-4">{{ $transactions->links() }}</div>

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
            
            // Only target the first row headers for resizing
            const cols = table.querySelectorAll('thead tr:first-child th');
            
            cols.forEach((col, index) => {
                // Set initial width
                if (!col.style.width) {
                    col.style.width = (col.offsetWidth + 20) + 'px'; // Add buffer
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
