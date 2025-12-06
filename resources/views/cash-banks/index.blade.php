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

    <x-card>
        <x-slot:header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-end gap-4">
                <x-button :href="route('cash-banks.create')" variant="primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Transaksi Baru
                </x-button>
            </div>
        </x-slot:header>

        <form method="get" class="grid grid-cols-1 md:grid-cols-6 gap-3">
            <select name="cash_bank_account_id" class="rounded-lg bg-[var(--bg-surface-secondary)] border border-[var(--border-color)] px-3 py-2 text-sm text-[var(--color-text-main)] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="">Semua Akun</option>
                @foreach($accounts as $a)
                    <option value="{{ $a->id }}" @selected(request('cash_bank_account_id')==$a->id)>{{ $a->name }}</option>
                @endforeach
            </select>
            <select name="sumber" class="rounded-lg bg-[var(--bg-surface-secondary)] border border-[var(--border-color)] px-3 py-2 text-sm text-[var(--color-text-main)] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="">Semua Sumber</option>
                @foreach(['customer_payment','vendor_payment','expense','other_in','other_out'] as $s)
                    <option value="{{ $s }}" @selected(request('sumber')==$s)>{{ ucwords(str_replace('_',' ', $s)) }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}" placeholder="Dari Tanggal" class="rounded-lg bg-[var(--bg-surface-secondary)] border border-[var(--border-color)] px-3 py-2 text-sm text-[var(--color-text-main)] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
            <input type="date" name="to" value="{{ request('to') }}" placeholder="Sampai Tanggal" class="rounded-lg bg-[var(--bg-surface-secondary)] border border-[var(--border-color)] px-3 py-2 text-sm text-[var(--color-text-main)] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
            <div></div>
            <x-button type="submit" variant="outline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter
            </x-button>
        </form>
    </x-card>

    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
        <x-card title="Ringkasan">
            <div class="text-sm space-y-1">
                <div class="flex justify-between">
                    <span>Total Masuk:</span>
                    <b class="text-green-600 dark:text-green-400">Rp {{ number_format($summary['in'] ?? 0, 0, ',', '.') }}</b>
                </div>
                <div class="flex justify-between">
                    <span>Total Keluar:</span>
                    <b class="text-red-600 dark:text-red-400">Rp {{ number_format($summary['out'] ?? 0, 0, ',', '.') }}</b>
                </div>
                <div class="flex justify-between pt-2 border-t border-slate-200 dark:border-slate-700">
                    <span>Net:</span>
                    <b class="text-blue-600 dark:text-blue-400">Rp {{ number_format($summary['net'] ?? 0, 0, ',', '.') }}</b>
                </div>
            </div>
        </x-card>
    </div>

    <x-card :noPadding="true" class="mt-4">
        <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="text-left border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
                <tr class="text-slate-600 dark:text-slate-400 text-xs uppercase">
                    <th class="px-3 py-3 text-center">No</th>
                    <th class="px-3 py-3">Tanggal</th>
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
                    
                    // Specific logic: Display Saldo refers to Balance AFTER this transaction.
                    // But our loop goes Backwards (desc).
                    // So $runningBalance (initially Top Balance) IS the balance after this transaction (Row 1).
                    // Then for Row 2, we subtract this row's contribution.
                    
                    $currentBalance = $runningBalance;
                    $runningBalance -= $signedNet; 
                @endphp
                <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <td class="px-3 py-3 text-center text-slate-500">{{ $startNumber + $index }}</td>
                    <td class="px-3 py-3 whitespace-nowrap">
                        <div class="font-medium text-slate-900 dark:text-slate-100">{{ $t->tanggal->format('d/m/Y') }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $t->tanggal->format('H:i') }}</div>
                    </td>
                    <td class="px-3 py-3">
                        <div class="font-mono text-sm font-bold text-blue-600 dark:text-blue-400">{{ $t->voucher_number }}</div>
                    </td>
                    <td class="px-3 py-3">
                        @php
                            $displayName = $t->recipient_name ?: ($t->customer?->name ?? $t->vendor?->name ?? '-');
                        @endphp
                        <div class="font-medium text-slate-900 dark:text-slate-100">{{ $displayName }}</div>
                    </td>
                    <td class="px-3 py-3 max-w-xs">
                        <div class="text-slate-700 dark:text-slate-300 truncate" title="{{ $t->description }}">
                            {{ Str::limit($t->description, 50) ?: '-' }}
                        </div>
                    </td>
                    <td class="px-3 py-3">
                        <div class="text-slate-900 dark:text-slate-100">{{ $t->account->name ?? '-' }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $t->account->account_number ?? '' }}</div>
                    </td>
                    <td class="px-3 py-3 text-right font-mono">
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
                    <td class="px-3 py-3 text-right font-mono">
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
                    <td class="px-3 py-3 text-right font-mono font-bold">
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
                        <div class="flex items-center gap-2">
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
        </table>
        </div>
    </x-card>

    <div class="mt-4">{{ $transactions->links() }}</div>
@endsection
