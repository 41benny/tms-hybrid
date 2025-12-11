@extends('layouts.app', ['title' => 'Jurnal'])

@section('content')
<div x-data="{ 
    activeTab: '{{ request('tab', 'all') }}',
    pendingInvoices: [],
    pendingVendorBills: [],
    pendingDriverAdvances: [],
    summary: { invoices: 0, vendor_bills: 0, driver_advances: 0 },
    loading: false,
    async loadSummary() {
        try {
            const res = await fetch('/api/pending-journals/summary');
            this.summary = await res.json();
        } catch (error) {
            console.error('Failed to load summary:', error);
        }
    },
    async loadPendingData() {
        if (this.activeTab !== 'pending' || this.loading) return;
        this.loading = true;
        try {
            const [invoicesRes, vendorBillsRes, driverAdvancesRes] = await Promise.all([
                fetch('/api/pending-journals/invoices'),
                fetch('/api/pending-journals/vendor-bills'),
                fetch('/api/pending-journals/driver-advances')
            ]);
            this.pendingInvoices = await invoicesRes.json();
            this.pendingVendorBills = await vendorBillsRes.json();
            this.pendingDriverAdvances = await driverAdvancesRes.json();
        } catch (error) {
            console.error('Failed to load pending journals:', error);
        } finally {
            this.loading = false;
        }
    }
}" x-init="loadSummary(); if (activeTab === 'pending') loadPendingData()">

    <!-- Tabs Header -->
    <x-card>
        <x-slot:header>
            <div class="flex items-center justify-between">
                <div class="flex gap-4 border-b border-[var(--border-color)] text-sm">
                    <button 
                        @click="activeTab = 'all'; window.history.pushState({}, '', '{{ route('journals.index') }}?tab=all')"
                        :class="activeTab === 'all' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-[var(--color-text-muted)]'"
                        class="px-4 py-2 font-medium transition-colors hover:text-indigo-600">
                        Semua Jurnal
                    </button>
                    <button 
                        @click="activeTab = 'pending'; loadPendingData(); window.history.pushState({}, '', '{{ route('journals.index') }}?tab=pending')"
                        :class="activeTab === 'pending' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-[var(--color-text-muted)]'"
                        class="px-4 py-2 font-medium transition-colors hover:text-indigo-600 flex items-center gap-2">
                        Pending
                        <span x-show="summary.invoices + summary.vendor_bills + summary.driver_advances > 0" 
                              class="bg-red-500 text-white text-xs rounded-full px-2 py-0.5"
                              x-text="summary.invoices + summary.vendor_bills + summary.driver_advances"></span>
                    </button>
                </div>
                <div class="flex gap-2">
                    <x-button 
                        :href="route('journals.traditional')" 
                        variant="outline" 
                        size="sm" 
                        class="normal-case border-emerald-300 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-900/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Excel view
                    </x-button>
                    <x-button :href="route('journals.create')" variant="primary" size="sm" class="normal-case">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Jurnal adjustment
                    </x-button>
                </div>
            </div>
        </x-slot:header>
    </x-card>

    <!-- All Journals Tab -->
    <div x-show="activeTab === 'all'" x-data="{ expandedJournals: [] }">
        <x-card class="mt-6">
            <form method="get" class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <input type="hidden" name="tab" value="all">
                <select name="source_type" class="rounded-lg bg-[var(--bg-surface-secondary)] border border-[var(--border-color)] px-3 py-2 text-sm text-[var(--color-text-main)] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">Semua Modul</option>
                    <option value="invoice" @selected(request('source_type')==='invoice')>Penjualan</option>
                    <option value="customer_payment" @selected(request('source_type')==='customer_payment')>Penjualan (Pembayaran)</option>
                    <option value="vendor_bill" @selected(request('source_type')==='vendor_bill')>Pembelian</option>
                    <option value="vendor_payment" @selected(request('source_type')==='vendor_payment')>Pembelian (Pembayaran)</option>
                    <option value="driver_advance" @selected(request('source_type')==='driver_advance')>Uang Jalan</option>
                    <option value="uang_jalan" @selected(request('source_type')==='uang_jalan')>Uang Jalan (Pembayaran)</option>
                    <option value="expense" @selected(request('source_type')==='expense')>Kas/Bank</option>
                    <option value="cash_in" @selected(request('source_type')==='cash_in')>Kas/Bank (Masuk)</option>
                    <option value="cash_out" @selected(request('source_type')==='cash_out')>Kas/Bank (Keluar)</option>
                    <option value="part_purchase" @selected(request('source_type')==='part_purchase')>Pembelian Part</option>
                    <option value="part_usage" @selected(request('source_type')==='part_usage')>Pemakaian Part</option>
                    <option value="adjustment" @selected(request('source_type')==='adjustment')>Adjustment</option>
                </select>
                <select name="status" class="rounded-lg bg-[var(--bg-surface-secondary)] border border-[var(--border-color)] px-3 py-2 text-sm text-[var(--color-text-main)] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">Status</option>
                    <option value="draft" @selected(request('status')==='draft')>Draft</option>
                    <option value="posted" @selected(request('status')==='posted')>Posted</option>
                    <option value="void" @selected(request('status')==='void')>Void</option>
                </select>
                <input type="date" name="from" value="{{ request('from') }}" placeholder="Dari" class="rounded-lg bg-[var(--bg-surface-secondary)] border border-[var(--border-color)] px-3 py-2 text-sm text-[var(--color-text-main)] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                <input type="date" name="to" value="{{ request('to') }}" placeholder="Sampai" class="rounded-lg bg-[var(--bg-surface-secondary)] border border-[var(--border-color)] px-3 py-2 text-sm text-[var(--color-text-main)] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no jurnal..." class="rounded-lg bg-[var(--bg-surface-secondary)] border border-[var(--border-color)] px-3 py-2 text-sm text-[var(--color-text-main)] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                <x-button type="submit" variant="outline">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter
                </x-button>
            </form>
        </x-card>

        <x-card :noPadding="true" class="mt-6">
            <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left border-b border-[var(--border-color)]">
                    <tr class="text-[var(--color-text-muted)]">
                        <th class="px-4 py-2 w-8"></th>
                        <th class="px-4 py-2">No Jurnal</th>
                        <th class="px-4 py-2">Tanggal</th>
                        <th class="px-4 py-2">Sumber</th>
                        <th class="px-4 py-2">Referensi</th>
                        <th class="px-4 py-2">Job Order</th>
                        <th class="px-4 py-2">Memo</th>
                        <th class="px-4 py-2 text-right">Total Debit</th>
                        <th class="px-4 py-2 text-right">Total Kredit</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($journals as $journal)
                    <!-- Main Journal Row -->
                    <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer"
                        @click="expandedJournals.includes({{ $journal->id }}) ? expandedJournals = expandedJournals.filter(id => id !== {{ $journal->id }}) : expandedJournals.push({{ $journal->id }})">
                        <td class="px-4 py-2">
                            <svg class="w-4 h-4 transition-transform" 
                                 :class="expandedJournals.includes({{ $journal->id }}) ? 'rotate-90' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </td>
                        <td class="px-4 py-2 font-medium">{{ $journal->journal_no }}</td>
                        <td class="px-4 py-2">{{ $journal->journal_date->format('d M Y') }}</td>
                        <td class="px-4 py-2">
                            <x-badge>
                                {{ $sourceTypes[$journal->source_type] ?? ucfirst($journal->source_type) }}
                            </x-badge>
                        </td>
                        <td class="px-4 py-2">
                            @if($journal->source_reference)
                                <a href="{{ $journal->source_reference['url'] }}" class="text-indigo-600 dark:text-indigo-400 hover:underline" @click.stop>
                                    {{ $journal->source_reference['type'] }}: {{ $journal->source_reference['number'] }}
                                </a>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            @if($journal->source_reference && isset($journal->source_reference['job_orders']) && !empty($journal->source_reference['job_orders']))
                                {{ implode(', ', $journal->source_reference['job_orders']) }}
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
                            @if($journal->is_revision)
                                <x-badge variant="warning" class="ml-1">Revisi</x-badge>
                            @endif
                        </td>
                        <td class="px-4 py-2" @click.stop>
                            <div class="flex gap-2">
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
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Expanded Detail Row -->
                    <tr x-show="expandedJournals.includes({{ $journal->id }})" 
                        x-collapse
                        class="bg-slate-50 dark:bg-slate-900/50">
                        <td colspan="11" class="px-4 py-3">
                            <div class="ml-8">
                                <div class="text-xs font-semibold text-[var(--color-text-muted)] mb-2">DETAIL JURNAL ENTRY:</div>
                                <table class="w-full text-xs">
                                    <thead>
                                        <tr class="border-b border-slate-200 dark:border-slate-700">
                                            <th class="text-left py-2 px-3 font-medium text-[var(--color-text-muted)]">Kode Akun</th>
                                            <th class="text-left py-2 px-3 font-medium text-[var(--color-text-muted)]">Nama Akun</th>
                                            <th class="text-left py-2 px-3 font-medium text-[var(--color-text-muted)]">Keterangan</th>
                                            <th class="text-right py-2 px-3 font-medium text-[var(--color-text-muted)]">Debit</th>
                                            <th class="text-right py-2 px-3 font-medium text-[var(--color-text-muted)]">Kredit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($journal->lines as $line)
                                        <tr class="border-b border-slate-100 dark:border-slate-800">
                                            <td class="py-2 px-3 font-mono">{{ $line->account->code }}</td>
                                            <td class="py-2 px-3">{{ $line->account->name }}</td>
                                            <td class="py-2 px-3 text-[var(--color-text-muted)]">{{ $line->description ?? '-' }}</td>
                                            <td class="py-2 px-3 text-right font-medium {{ $line->debit > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                                                {{ $line->debit > 0 ? number_format($line->debit, 2, ',', '.') : '-' }}
                                            </td>
                                            <td class="py-2 px-3 text-right font-medium {{ $line->credit > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400' }}">
                                                {{ $line->credit > 0 ? number_format($line->credit, 2, ',', '.') : '-' }}
                                            </td>
                                        </tr>
                                        @endforeach
                                        <!-- Total Row -->
                                        <tr class="font-semibold bg-slate-100 dark:bg-slate-800">
                                            <td colspan="3" class="py-2 px-3 text-right">TOTAL:</td>
                                            <td class="py-2 px-3 text-right text-emerald-600 dark:text-emerald-400">
                                                {{ number_format($journal->total_debit, 2, ',', '.') }}
                                            </td>
                                            <td class="py-2 px-3 text-right text-blue-600 dark:text-blue-400">
                                                {{ number_format($journal->total_credit, 2, ',', '.') }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                            Tidak ada jurnal ditemukan.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
            </div>
        </x-card>

        <div class="mt-4">{{ $journals->links() }}</div>
    </div>

    <!-- Pending Journals Tab -->
    <div x-show="activeTab === 'pending'" x-cloak>
        <div x-show="loading" class="mt-6 text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <p class="mt-2 text-[var(--color-text-muted)]">Loading...</p>
        </div>

        <div x-show="!loading" class="mt-6 space-y-6">
            <!-- Pending Invoices -->
            <x-card>
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold">Invoice Belum Di-jurnal</h3>
                        <span class="bg-red-500 text-white text-xs rounded-full px-2 py-0.5" x-text="summary.invoices"></span>
                    </div>
                </x-slot:header>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left border-b border-[var(--border-color)]">
                            <tr class="text-[var(--color-text-muted)]">
                                <th class="px-4 py-2">No Invoice</th>
                                <th class="px-4 py-2">Tanggal</th>
                                <th class="px-4 py-2">Customer</th>
                                <th class="px-4 py-2 text-right">Total</th>
                                <th class="px-4 py-2">Status</th>
                                <th class="px-4 py-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="invoice in pendingInvoices" :key="invoice.id">
                                <tr class="border-b border-slate-100 dark:border-slate-800">
                                    <td class="px-4 py-2 font-medium" x-text="invoice.invoice_number"></td>
                                    <td class="px-4 py-2" x-text="new Date(invoice.invoice_date).toLocaleDateString('id-ID')"></td>
                                    <td class="px-4 py-2" x-text="invoice.customer?.name || '-'"></td>
                                    <td class="px-4 py-2 text-right" x-text="'Rp ' + parseFloat(invoice.total_amount).toLocaleString('id-ID')"></td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800" x-text="invoice.status"></span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <a :href="`/invoices/${invoice.id}`" target="_blank" class="text-indigo-600 hover:underline text-sm">Lihat Detail →</a>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="pendingInvoices.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-slate-500">Tidak ada invoice pending</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-card>

            <!-- Pending Vendor Bills -->
            <x-card>
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold">Vendor Bill Belum Di-jurnal</h3>
                        <span class="bg-red-500 text-white text-xs rounded-full px-2 py-0.5" x-text="summary.vendor_bills"></span>
                    </div>
                </x-slot:header>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left border-b border-[var(--border-color)]">
                            <tr class="text-[var(--color-text-muted)]">
                                <th class="px-4 py-2">No Vendor Bill</th>
                                <th class="px-4 py-2">Tanggal</th>
                                <th class="px-4 py-2">Vendor</th>
                                <th class="px-4 py-2 text-right">Total</th>
                                <th class="px-4 py-2">Status</th>
                                <th class="px-4 py-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="bill in pendingVendorBills" :key="bill.id">
                                <tr class="border-b border-slate-100 dark:border-slate-800">
                                    <td class="px-4 py-2 font-medium" x-text="bill.vendor_bill_number"></td>
                                    <td class="px-4 py-2" x-text="new Date(bill.bill_date).toLocaleDateString('id-ID')"></td>
                                    <td class="px-4 py-2" x-text="bill.vendor?.name || '-'"></td>
                                    <td class="px-4 py-2 text-right" x-text="'Rp ' + parseFloat(bill.total_amount).toLocaleString('id-ID')"></td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800" x-text="bill.status"></span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <a :href="`/vendor-bills/${bill.id}`" target="_blank" class="text-indigo-600 hover:underline text-sm">Lihat Detail →</a>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="pendingVendorBills.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-slate-500">Tidak ada vendor bill pending</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-card>

            <!-- Pending Driver Advances -->
            <x-card>
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold">Driver Advance Belum Di-jurnal</h3>
                        <span class="bg-red-500 text-white text-xs rounded-full px-2 py-0.5" x-text="summary.driver_advances"></span>
                    </div>
                </x-slot:header>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left border-b border-[var(--border-color)]">
                            <tr class="text-[var(--color-text-muted)]">
                                <th class="px-4 py-2">No Advance</th>
                                <th class="px-4 py-2">Tanggal</th>
                                <th class="px-4 py-2">Driver</th>
                                <th class="px-4 py-2">Job Order</th>
                                <th class="px-4 py-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="advance in pendingDriverAdvances" :key="advance.id">
                                <tr class="border-b border-slate-100 dark:border-slate-800">
                                    <td class="px-4 py-2 font-medium" x-text="advance.advance_number"></td>
                                    <td class="px-4 py-2" x-text="new Date(advance.advance_date).toLocaleDateString('id-ID')"></td>
                                    <td class="px-4 py-2" x-text="advance.driver?.name || '-'"></td>
                                    <td class="px-4 py-2" x-text="advance.shipment_leg?.job_order?.job_number || '-'"></td>
                                    <td class="px-4 py-2">
                                        <a :href="`/driver-advances/${advance.id}`" target="_blank" class="text-indigo-600 hover:underline text-sm">Lihat Detail →</a>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="pendingDriverAdvances.length === 0">
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">Tidak ada driver advance pending</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection
