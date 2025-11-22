@extends('layouts.app', ['title' => 'Detail Permintaan Faktur Pajak'])

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-2">
                <a href="{{ route('tax-invoices.index') }}" class="hover:text-blue-600">Permintaan Faktur Pajak</a>
                <span>/</span>
                <span>{{ $taxInvoiceRequest->request_number }}</span>
            </div>
            <div class="text-2xl font-bold text-slate-800 dark:text-slate-100">Detail Permintaan Faktur Pajak</div>
        </div>
        <div class="flex gap-2">
            @if($taxInvoiceRequest->status === 'requested')
                <a href="{{ route('tax-invoices.complete', $taxInvoiceRequest) }}" class="btn btn-primary flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Input Faktur Pajak
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Request Info -->
        <div class="lg:col-span-2 space-y-6">
            <x-card title="Informasi Permintaan">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-sm text-slate-500 block mb-1">No. Request</label>
                        <div class="font-medium text-lg">{{ $taxInvoiceRequest->request_number }}</div>
                    </div>
                    <div>
                        <label class="text-sm text-slate-500 block mb-1">Status</label>
                        <div>
                            @if($taxInvoiceRequest->status === 'requested')
                                <span class="badge bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                    Requested
                                </span>
                            @else
                                <span class="badge bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Completed
                                </span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <label class="text-sm text-slate-500 block mb-1">Tanggal Request</label>
                        <div class="font-medium">{{ $taxInvoiceRequest->requested_at->format('d F Y H:i') }}</div>
                    </div>
                    <div>
                        <label class="text-sm text-slate-500 block mb-1">Diajukan Oleh</label>
                        <div class="font-medium">{{ $taxInvoiceRequest->requester->name }}</div>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-700">
                    <h4 class="font-semibold mb-4">Detail Faktur Pajak</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-sm text-slate-500 block mb-1">Nomor Faktur Pajak</label>
                            <div class="font-mono text-lg font-medium">
                                {{ $taxInvoiceRequest->tax_invoice_number ?? '-' }}
                            </div>
                        </div>
                        <div>
                            <label class="text-sm text-slate-500 block mb-1">Tanggal Faktur Pajak</label>
                            <div class="font-medium">
                                {{ $taxInvoiceRequest->tax_invoice_date ? $taxInvoiceRequest->tax_invoice_date->format('d F Y') : '-' }}
                            </div>
                        </div>
                        @if($taxInvoiceRequest->status === 'completed')
                            <div>
                                <label class="text-sm text-slate-500 block mb-1">Diinput Oleh</label>
                                <div class="font-medium">{{ $taxInvoiceRequest->completer->name ?? '-' }}</div>
                            </div>
                            <div>
                                <label class="text-sm text-slate-500 block mb-1">Waktu Input</label>
                                <div class="font-medium">{{ $taxInvoiceRequest->completed_at ? $taxInvoiceRequest->completed_at->format('d F Y H:i') : '-' }}</div>
                            </div>
                        @endif
                    </div>
                    @if($taxInvoiceRequest->notes)
                        <div class="mt-4">
                            <label class="text-sm text-slate-500 block mb-1">Catatan</label>
                            <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded text-sm">
                                {{ $taxInvoiceRequest->notes }}
                            </div>
                        </div>
                    @endif

                    @if($taxInvoiceRequest->tax_invoice_file_path && $taxInvoiceRequest->status === 'completed')
                        <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                            <a href="{{ route('tax-invoices.download', $taxInvoiceRequest) }}"
                               class="btn btn-secondary btn-sm flex items-center gap-2 w-fit"
                               target="_blank">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Download Faktur Pajak
                            </a>
                        </div>
                    @endif
                </div>
            </x-card>

            <x-card title="Informasi Invoice">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="text-sm text-slate-500 block mb-1">No. Invoice</label>
                        <a href="{{ route('invoices.show', $taxInvoiceRequest->invoice_id) }}" class="text-blue-600 hover:underline font-medium flex items-center gap-1" target="_blank">
                            {{ $taxInvoiceRequest->invoice->invoice_number }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>
                    </div>
                    <div>
                        <label class="text-sm text-slate-500 block mb-1">Tanggal Invoice</label>
                        <div class="font-medium">{{ $taxInvoiceRequest->invoice->invoice_date->format('d F Y') }}</div>
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-slate-800 rounded-lg p-4">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-slate-600 dark:text-slate-400">DPP (Dasar Pengenaan Pajak)</span>
                            <span class="font-mono">{{ number_format($taxInvoiceRequest->dpp, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600 dark:text-slate-400">PPN (11%)</span>
                            <span class="font-mono">{{ number_format($taxInvoiceRequest->ppn, 2, ',', '.') }}</span>
                        </div>
                        <div class="border-t border-slate-200 dark:border-slate-700 my-2"></div>
                        <div class="flex justify-between font-bold text-lg">
                            <span>Total</span>
                            <span class="font-mono">{{ number_format($taxInvoiceRequest->total_amount, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Right Column: Customer Info -->
        <div class="space-y-6">
            <x-card title="Informasi Customer">
                <div class="space-y-4">
                    <div>
                        <label class="text-sm text-slate-500 block mb-1">Nama Customer</label>
                        <div class="font-medium text-lg">{{ $taxInvoiceRequest->customer_name }}</div>
                    </div>
                    <div>
                        <label class="text-sm text-slate-500 block mb-1">NPWP</label>
                        <div class="font-mono bg-slate-100 dark:bg-slate-800 px-3 py-2 rounded">
                            {{ $taxInvoiceRequest->customer_npwp ?? 'Tidak ada data NPWP' }}
                        </div>
                    </div>
                    <div>
                        <label class="text-sm text-slate-500 block mb-1">Tipe Transaksi</label>
                        <div class="flex items-center gap-2">
                            <span class="badge bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                Kode {{ $taxInvoiceRequest->transaction_type }}
                            </span>
                            <span class="text-sm text-slate-600 dark:text-slate-400">
                                @if($taxInvoiceRequest->transaction_type == '04')
                                    DPP Nilai Lain
                                @elseif($taxInvoiceRequest->transaction_type == '05')
                                    Besaran Tertentu
                                @elseif($taxInvoiceRequest->transaction_type == '08')
                                    Dibebaskan
                                @else
                                    Lainnya
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
@endsection
