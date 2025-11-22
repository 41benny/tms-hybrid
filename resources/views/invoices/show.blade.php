@extends('layouts.app', ['title' => 'Detail Invoice'])

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('invoices.index') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-700 transition-colors" title="Kembali ke List Invoice">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <div class="text-xl font-semibold">{{ $invoice->invoice_number }}</div>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $invoice->customer->name ?? '-' }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('invoices.pdf', $invoice) }}" target="_blank" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-800 text-white hover:bg-slate-700 dark:bg-slate-200 dark:text-slate-900 dark:hover:bg-slate-300 transition-colors" title="Print / PDF">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
            </a>

            @if($invoice->status !== 'cancelled')
                <a href="{{ route('cash-banks.create', ['sumber'=>'customer_payment','invoice_id'=>$invoice->id,'amount'=>$invoice->total_amount]) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition-colors" title="Terima Pembayaran">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </a>
            @endif

            @if($invoice->status === 'draft')
                <form method="post" action="{{ route('invoices.mark-as-sent', $invoice) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="cursor-pointer inline-flex items-center justify-center w-9 h-9 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition-colors" title="Mark as Sent">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                    </button>
                </form>
            @endif

            @if($invoice->status === 'sent' && $invoice->paid_amount == 0)
                <form method="post" action="{{ route('invoices.revert-to-draft', $invoice) }}" onsubmit="return confirm('Apakah Anda yakin ingin mengembalikan invoice ke Draft? Jurnal akuntansi akan DIHAPUS.');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="cursor-pointer inline-flex items-center justify-center w-9 h-9 rounded-lg bg-yellow-500 text-white hover:bg-yellow-600 transition-colors" title="Revert to Draft">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    </button>
                </form>
            @endif

            @if($invoice->canBeCancelled())
                <form method="post" action="{{ route('invoices.cancel', $invoice) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan invoice ini?');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="cursor-pointer inline-flex items-center justify-center w-9 h-9 rounded-lg bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50 transition-colors" title="Cancel Invoice">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </form>
            @endif

            @if($invoice->canBeEdited())
                <a href="{{ route('invoices.edit', $invoice) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-700 transition-colors" title="Edit Invoice">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-card title="Ringkasan">
            <div class="space-y-1 text-sm">
                <div>Tanggal: {{ $invoice->invoice_date->format('d M Y') }}</div>
                <div>Jatuh Tempo: {{ optional($invoice->due_date)->format('d M Y') ?: '-' }}</div>
                <div>Status: <x-badge>{{ ucfirst(str_replace('_',' ', $invoice->status)) }}</x-badge></div>
                <div class="pt-2 border-t border-slate-200 dark:border-slate-700">
                    <div>Subtotal: <b>{{ number_format($invoice->subtotal, 2, ',', '.') }}</b></div>
                    @if($invoice->tax_amount > 0)
                        <div>PPN: <b>{{ number_format($invoice->tax_amount, 2, ',', '.') }}</b></div>
                    @endif
                    @if($invoice->discount_amount > 0)
                        <div>Discount: <b class="text-red-600">-{{ number_format($invoice->discount_amount, 2, ',', '.') }}</b></div>
                    @endif
                    <div class="font-bold text-indigo-600 dark:text-indigo-400">Total: {{ number_format($invoice->total_amount, 2, ',', '.') }}</div>
                    @if($invoice->show_pph23)
                        <div class="text-amber-600 dark:text-amber-400 text-xs mt-1">PPh 23: -{{ number_format($invoice->pph23_amount, 2, ',', '.') }}</div>
                        <div class="font-semibold text-emerald-600 dark:text-emerald-400">Net Payable: {{ number_format($invoice->total_amount - $invoice->pph23_amount, 2, ',', '.') }}</div>
                    @endif
                </div>
                <div class="pt-2 border-t border-slate-200 dark:border-slate-700">Catatan: {{ $invoice->notes ?: '-' }}</div>

                @if($invoice->tax_amount > 0)
                    <div class="pt-2 border-t border-slate-200 dark:border-slate-700">
                        <div class="font-semibold mb-1">Faktur Pajak</div>
                        @if($invoice->tax_invoice_status === 'none')
                            <div class="text-slate-500 italic">Belum direquest</div>
                            @if($invoice->status === 'sent')
                                <form action="{{ route('tax-invoices.store') }}" method="POST" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="invoice_ids[]" value="{{ $invoice->id }}">
                                    <button type="submit" class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded hover:bg-blue-200 transition-colors">
                                        Request Faktur Pajak
                                    </button>
                                </form>
                            @endif
                        @elseif($invoice->tax_invoice_status === 'requested')
                            <div class="flex items-center gap-2">
                                <span class="badge bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Requested</span>
                                <a href="{{ route('tax-invoices.show', $invoice->taxInvoiceRequest) }}" class="text-xs text-blue-600 hover:underline">Lihat Request</a>
                            </div>
                            <div class="text-xs text-slate-500 mt-1">Diajukan pada: {{ $invoice->tax_requested_at->format('d M Y H:i') }}</div>
                        @elseif($invoice->tax_invoice_status === 'completed')
                            <div class="flex items-center gap-2">
                                <span class="badge bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Completed</span>
                                <a href="{{ route('tax-invoices.show', $invoice->taxInvoiceRequest) }}" class="text-xs text-blue-600 hover:underline">Lihat Detail</a>
                            </div>
                            <div class="font-mono font-medium mt-1">{{ $invoice->tax_invoice_number }}</div>
                            <div class="text-xs text-slate-500">Tanggal: {{ $invoice->tax_invoice_date->format('d M Y') }}</div>
                        @endif
                    </div>
                @endif
            </div>
        </x-card>
        <x-card title="Items">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-slate-500">
                            <th class="px-2 py-1">Deskripsi</th>
                            <th class="px-2 py-1">Qty</th>
                            <th class="px-2 py-1">Harga</th>
                            <th class="px-2 py-1">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $it)
                            <tr class="border-t border-slate-200 dark:border-slate-800">
                                <td class="px-2 py-1">{{ $it->description }}</td>
                                <td class="px-2 py-1">{{ $it->quantity }}</td>
                                <td class="px-2 py-1">{{ number_format($it->unit_price, 2, ',', '.') }}</td>
                                <td class="px-2 py-1">{{ number_format($it->amount, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
@endsection
