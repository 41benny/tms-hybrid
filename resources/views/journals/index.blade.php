@extends('layouts.app', ['title' => 'Jurnal'])

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">Jurnal</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Daftar semua jurnal akuntansi</p>
        </div>
        <a href="{{ route('journals.create') }}" class="px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-500">+ Jurnal Adjustment</a>
    </div>

    <x-card>
        <form method="get" class="grid grid-cols-1 md:grid-cols-6 gap-3">
            <select name="source_type" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                <option value="">Semua Modul</option>
                <option value="invoice" @selected(request('source_type')==='invoice')>Penjualan</option>
                <option value="customer_payment" @selected(request('source_type')==='customer_payment')>Penjualan (Pembayaran)</option>
                <option value="vendor_bill" @selected(request('source_type')==='vendor_bill')>Pembelian</option>
                <option value="vendor_payment" @selected(request('source_type')==='vendor_payment')>Pembelian (Pembayaran)</option>
                <option value="expense" @selected(request('source_type')==='expense')>Kas/Bank</option>
                <option value="cash_in" @selected(request('source_type')==='cash_in')>Kas/Bank (Masuk)</option>
                <option value="cash_out" @selected(request('source_type')==='cash_out')>Kas/Bank (Keluar)</option>
                <option value="part_purchase" @selected(request('source_type')==='part_purchase')>Pembelian Part</option>
                <option value="part_usage" @selected(request('source_type')==='part_usage')>Pemakaian Part</option>
                <option value="adjustment" @selected(request('source_type')==='adjustment')>Adjustment</option>
            </select>
            <select name="status" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                <option value="">Status</option>
                <option value="draft" @selected(request('status')==='draft')>Draft</option>
                <option value="posted" @selected(request('status')==='posted')>Posted</option>
                <option value="void" @selected(request('status')==='void')>Void</option>
            </select>
            <input type="date" name="from" value="{{ request('from') }}" placeholder="Dari" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" />
            <input type="date" name="to" value="{{ request('to') }}" placeholder="Sampai" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" />
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no jurnal..." class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" />
            <button class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-800">Filter</button>
        </form>
    </x-card>

    <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/80">
        <table class="min-w-full text-sm">
            <thead class="text-left border-b border-slate-200 dark:border-slate-800">
                <tr class="text-slate-500">
                    <th class="px-4 py-2">No Jurnal</th>
                    <th class="px-4 py-2">Tanggal</th>
                    <th class="px-4 py-2">Sumber</th>
                    <th class="px-4 py-2">Referensi</th>
                    <th class="px-4 py-2">Memo</th>
                    <th class="px-4 py-2 text-right">Total Debit</th>
                    <th class="px-4 py-2 text-right">Total Kredit</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($journals as $journal)
                <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <td class="px-4 py-2 font-medium">{{ $journal->journal_no }}</td>
                    <td class="px-4 py-2">{{ $journal->journal_date->format('d M Y') }}</td>
                    <td class="px-4 py-2">
                        <x-badge>
                            {{ $sourceTypes[$journal->source_type] ?? ucfirst($journal->source_type) }}
                        </x-badge>
                    </td>
                    <td class="px-4 py-2">
                        @if($journal->source_reference)
                            <a href="{{ $journal->source_reference['url'] }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                {{ $journal->source_reference['type'] }}: {{ $journal->source_reference['number'] }}
                            </a>
                        @else
                            <span class="text-slate-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-2">{{ $journal->memo ? \Illuminate\Support\Str::limit($journal->memo, 40) : '-' }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($journal->total_debit, 2, ',', '.') }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($journal->total_credit, 2, ',', '.') }}</td>
                    <td class="px-4 py-2">
                        <x-badge :variant="$journal->status === 'posted' ? 'success' : ($journal->status === 'void' ? 'danger' : 'default')">
                            {{ ucfirst($journal->status) }}
                        </x-badge>
                    </td>
                    <td class="px-4 py-2 flex gap-2">
                        <a href="{{ route('journals.show', $journal) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline" title="Lihat">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </a>
                        @if($journal->source_type === 'adjustment' || $journal->status === 'draft')
                            <a href="{{ route('journals.edit', $journal) }}" class="text-emerald-600 dark:text-emerald-400 hover:underline" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                        Tidak ada jurnal ditemukan.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $journals->links() }}</div>
@endsection

