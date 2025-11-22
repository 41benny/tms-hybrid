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
    <x-card>
        <x-slot:header>
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
        </x-slot:header>
    </x-card>

    @php
        $selectedCustomerId = (int) request('customer_id');
        $selectedCustomer = $selectedCustomerId
            ? $customers->firstWhere('id', $selectedCustomerId)
            : null;
        $invoiceDate   = old('invoice_date', request('invoice_date', date('Y-m-d')));
        $dueDate       = old('due_date', request('due_date', date('Y-m-d', strtotime('+30 days'))));
        $paymentTerms  = old('payment_terms', request('payment_terms'));
        $notes         = old('notes', request('notes'));
        $statusFilter  = request('status_filter', 'completed');
        $selectedJobOrderIds = (array) request('job_order_ids', []);
    @endphp

    {{-- Step 1: Informasi Customer --}}
    @include('invoices.partials.customer-section', ['customers' => $customers, 'selectedCustomer' => $selectedCustomer])

    {{-- Main Invoice Form --}}
    <form method="post" action="{{ route('invoices.store') }}" class="space-y-6 mt-4" id="invoiceForm">
        @csrf
        <input type="hidden" name="customer_id" value="{{ $selectedCustomer->id ?? '' }}">

        {{-- Step 2: Informasi Invoice --}}
        @include('invoices.partials.invoice-info-section', [
            'selectedCustomer' => $selectedCustomer,
            'nextInvoiceNumber' => $nextInvoiceNumber ?? null,
            'invoiceDate' => $invoiceDate,
            'dueDate' => $dueDate,
            'paymentTerms' => $paymentTerms,
            'notes' => $notes
        ])

        {{-- Step 3: Items & Total --}}
        <x-card title="3. Items & Total" collapsible="true">
            <div class="flex items-center justify-between gap-3 mb-3">
                <div class="text-sm text-slate-600 dark:text-slate-400">
                    @if(!empty($selectedJobOrderIds))
                        {{ count($selectedJobOrderIds) }} Job Order dipilih.
                    @else
                        Belum ada Job Order dipilih. Klik tombol di kanan untuk memilih Job Order.
                    @endif
                </div>
                @if($selectedCustomer)
                    <x-button type="button" variant="primary" size="sm" onclick="openJobOrderModal()">
                        Pilih Job Order
                    </x-button>
                @else
                    <button type="button"
                            class="px-4 py-2 rounded-lg bg-slate-200 text-slate-500 text-sm cursor-not-allowed"
                            disabled>
                        Pilih Job Order
                    </button>
                @endif
            </div>

            @include('invoices.partials.items-section', ['previewItems' => $previewItems ?? []])
        </x-card>
    </form>

    @if($selectedCustomer)
        {{-- Modal: Pilih Job Order --}}
        @include('invoices.partials.job-order-modal', [
            'selectedCustomer' => $selectedCustomer,
            'statusFilter' => $statusFilter
        ])
    @endif

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
