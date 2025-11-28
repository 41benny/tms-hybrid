@extends('layouts.app', ['title' => 'Buat Invoice'])

@section('content')
<div class="space-y-6">
    {{-- Global Alerts: success / error / validation --}}
    @if(session('success'))
        <div class="rounded-lg border border-green-300 bg-green-50 dark:bg-green-900/30 px-4 py-3 text-sm text-green-700 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-lg border border-red-300 bg-red-50 dark:bg-red-900/30 px-4 py-3 text-sm text-red-700 dark:text-red-300">
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="rounded-lg border border-amber-300 bg-amber-50 dark:bg-amber-900/30 px-4 py-3 text-sm text-amber-800 dark:text-amber-200">
            <div class="font-semibold mb-1">Terjadi kesalahan validasi:</div>
            <ul class="list-disc ml-5 space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="text-2xl font-bold text-slate-900 dark:text-slate-100">Buat Invoice</div>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Pilih customer, lengkapi detail invoice, lalu ambil Job Order terkait.
            </p>
        </div>
        <x-button :href="route('invoices.index')" variant="ghost" size="sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Close
        </x-button>
    </div>

    @php
        $selectedCustomerId = (int) request('customer_id');
        $selectedCustomer = $selectedCustomerId
            ? $customers->firstWhere('id', $selectedCustomerId)
            : null;
        $invoiceDate   = old('invoice_date', request('invoice_date', date('Y-m-d')));
        $dueDate       = old('due_date', request('due_date', date('Y-m-d', strtotime('+30 days'))));
        $paymentTerms  = old('payment_terms', request('payment_terms', 30)); // Default 30 days
        $notes         = old('notes', request('notes'));
        $statusFilter  = request('status_filter', 'completed');
        $selectedJobOrderIds = (array) request('job_order_ids', []);
    @endphp

    {{-- Main Invoice Form --}}
    <form method="post" action="{{ route('invoices.store') }}" class="space-y-6 mt-4" id="invoiceForm">
        @csrf
        
        <x-card class="p-6">
            {{-- Header Section --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                {{-- Kiri: Customer Info --}}
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-slate-700 dark:text-slate-300 mb-1">
                            Customer <span class="text-red-500">*</span>
                        </label>
                        {{-- Customer Selection --}}
                        <div class="relative">
                            <input type="text"
                                   id="customer_search"
                                   class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                   placeholder="Ketik nama customer..."
                                   autocomplete="off"
                                   value="{{ $selectedCustomer->name ?? '' }}">
                            <input type="hidden" name="customer_id" id="customer_id_input" value="{{ $selectedCustomer->id ?? '' }}">

                            {{-- Suggestions Dropdown --}}
                            <div id="customer_suggestions" class="absolute z-10 w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto hidden">
                                {{-- Populated by JS --}}
                            </div>
                        </div>
                        @error('customer_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm text-slate-700 dark:text-slate-300 mb-1">
                            Alamat
                        </label>
                        <textarea name="customer_address" rows="3" readonly
                                  class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm text-slate-500 dark:text-slate-400"
                        >{{ $selectedCustomer->address ?? '' }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-slate-700 dark:text-slate-300 mb-1">
                                No. Telp
                            </label>
                            <input type="text" name="customer_phone" readonly
                                   class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm text-slate-500 dark:text-slate-400"
                                   value="{{ $selectedCustomer->phone ?? '' }}">
                        </div>
                        <div>
                            <label class="block text-sm text-slate-700 dark:text-slate-300 mb-1">
                                NPWP
                            </label>
                            <input type="text" name="customer_npwp" readonly
                                   class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm text-slate-500 dark:text-slate-400"
                                   value="{{ $selectedCustomer->npwp ?? '' }}">
                        </div>
                    </div>
                </div>

                {{-- Kanan: Invoice Details --}}
                <div class="space-y-4">
                    <input type="hidden" name="reference" id="reference_hidden" value="{{ old('reference', request('reference')) }}">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-slate-700 dark:text-slate-300 mb-1">
                                No. Invoice
                            </label>
                            <input type="text" name="invoice_number"
                                   class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm text-slate-700 dark:text-slate-300"
                                   value="{{ old('invoice_number', $nextInvoiceNumber ?? 'Akan digenerate otomatis') }}"
                                   placeholder="Auto-generated if empty">
                            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                                Kosongkan untuk auto-generate.
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm text-slate-700 dark:text-slate-300 mb-1">
                                Tanggal Invoice <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="invoice_date"
                                   class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                   value="{{ $invoiceDate }}" required>
                            @error('invoice_date')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-slate-700 dark:text-slate-300 mb-1">
                                Payment Terms (Hari)
                            </label>
                            <input type="number" id="payment_terms" name="payment_terms"
                                   class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                   value="{{ $paymentTerms }}" placeholder="e.g. 30">
                            @error('payment_terms')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm text-slate-700 dark:text-slate-300 mb-1">
                                Jatuh Tempo <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="due_date"
                                   class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                   value="{{ $dueDate }}" required>
                            @error('due_date')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm text-slate-700 dark:text-slate-300 mb-1">
                            Referensi / PO Number
                        </label>
                        <input type="text" id="reference_header" name="reference"
                               class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                               value="{{ old('reference', request('reference')) }}">
                    </div>

                    <div>
                        <label class="block text-sm text-slate-700 dark:text-slate-300 mb-1">
                            Tipe Transaksi (PPN)
                        </label>
                        <select id="transaction_type_select" name="transaction_type" class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            <option value="04" {{ old('transaction_type', '04') == '04' ? 'selected' : '' }}>04 - DPP Nilai Lain (11%)</option>
                            <option value="05" {{ old('transaction_type') == '05' ? 'selected' : '' }}>05 - Besaran Tertentu (1.1%)</option>
                            <option value="08" {{ old('transaction_type') == '08' ? 'selected' : '' }}>08 - Dibebaskan (0%)</option>
                            <option value="01" {{ old('transaction_type') == '01' ? 'selected' : '' }}>01 - Kepada Pihak Lain Bukan Pemungut PPN</option>
                            <option value="02" {{ old('transaction_type') == '02' ? 'selected' : '' }}>02 - Kepada Pemungut Bendaharawan</option>
                            <option value="03" {{ old('transaction_type') == '03' ? 'selected' : '' }}>03 - Kepada Pemungut Selain Bendaharawan</option>
                            <option value="06" {{ old('transaction_type') == '06' ? 'selected' : '' }}>06 - Penyerahan Lainnya</option>
                            <option value="07" {{ old('transaction_type') == '07' ? 'selected' : '' }}>07 - Tidak Dipungut</option>
                            <option value="09" {{ old('transaction_type') == '09' ? 'selected' : '' }}>09 - Aktiva Pasal 16D</option>
                        </select>
                        <p class="text-xs text-slate-500 mt-1">Pilih tipe PPN untuk hitung otomatis.</p>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm text-slate-700 dark:text-slate-300 mb-1">
                        Catatan (Tampil di Invoice)
                    </label>
                    <textarea name="notes" rows="2"
                              class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                    >{{ $notes }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm text-slate-700 dark:text-slate-300 mb-1">
                        Catatan Internal (Tidak Tampil)
                    </label>
                    <textarea name="internal_notes" rows="2"
                              class="w-full rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                    >{{ old('internal_notes') }}</textarea>
                </div>
            </div>

            {{-- Items Section --}}
            <div class="border-t border-slate-200 dark:border-slate-700 pt-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-slate-100">
                        Item Invoice
                    </h3>

                    {{-- Tombol Pilih Job Order --}}
                    <button type="button"
                            id="btn_pilih_job_order"
                            onclick="openJobOrderModal()"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md transition-all duration-200 {{ $selectedCustomer ? 'text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500' : 'bg-slate-200 text-slate-500 cursor-not-allowed' }}"
                            {{ $selectedCustomer ? '' : 'disabled' }}>
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Pilih Job Order
                    </button>
                </div>

                @include('invoices.partials.items-section', ['previewItems' => $previewItems ?? []])
            </div>
        </x-card>
    </form>


    {{-- Modal: Pilih Job Order (will be loaded dynamically) --}}
    <div id="jobOrderModalContainer">
        @if($selectedCustomer)
            @include('invoices.partials.job-order-modal', [
                'selectedCustomer' => $selectedCustomer,
                'statusFilter' => $statusFilter
            ])
        @endif
    </div>


    {{-- Preview Invoice Modal --}}
    @include('invoices.partials.preview-modal')

    @php
        $customerLookup = $customers->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'address' => $c->address,
                'phone' => $c->phone,
                'npwp' => $c->npwp,
            ];
        })->values();
    @endphp

    <script>
        // Pass data to JavaScript
        window.CUSTOMER_LOOKUP = @json($customerLookup);
        window.INVOICE_CREATE_ROUTE = @json(route('invoices.create'));
    </script>
    <script src="{{ asset('js/invoice-create.js') }}"></script>
</div>
@endsection
