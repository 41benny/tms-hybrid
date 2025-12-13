@extends('layouts.app', ['title' => 'Detail Transaksi Kas/Bank'])

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">

        {{-- HEADER --}}
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <a href="{{ url()->previous() }}" class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-700 transition-colors" title="Kembali ke daftar Kas/Bank">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </a>
                <div>
                    <div class="text-xl font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span>Transaksi #{{ $trx->id }}</span>
                    </div>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $trx->tanggal->format('d M Y') }} - {{ $trx->account->name ?? '-' }}
                    </p>
                </div>
            </div>

            <div class="flex flex-col items-end gap-2">
                <span class="inline-flex items-center rounded-full
                    @if($trx->jenis === 'cash_in')
                        border-emerald-200 bg-emerald-50 text-emerald-700
                    @else
                        border-rose-200 bg-rose-50 text-rose-700
                    @endif
                    border px-3 py-1 text-xs font-semibold tracking-wide">
                    @if($trx->jenis === 'cash_in')
                        Kas/Bank Masuk
                    @else
                        Kas/Bank Keluar
                    @endif
                </span>
            </div>
        </div>

        @php
            $driverAdvances = $trx->driverAdvancePayments->map->driverAdvance->filter()->unique('id');
            $driverAdvanceDeductionTotal = $driverAdvances->sum(function($adv) {
                $mainCost = $adv->shipmentLeg->mainCost ?? null;
                $saving = $mainCost->driver_savings_deduction ?? $adv->deduction_savings ?? 0;
                $guarantee = $mainCost->driver_guarantee_deduction ?? $adv->deduction_guarantee ?? 0;
                return $saving + $guarantee;
            });
        @endphp

        {{-- GRID KONTEN --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- RINGKASAN --}}
            <x-card title="Ringkasan" class="md:col-span-1" headerClass="aurora-card-header">
                <div class="space-y-3 text-sm">

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500 dark:text-slate-400">Jenis</span>
                        <span class="font-semibold text-slate-900 dark:text-slate-50 capitalize">
                            {{ str_replace('_', ' ', $trx->jenis) }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500 dark:text-slate-400">Sumber</span>
                        <span class="font-medium text-slate-800 dark:text-slate-100">
                            {{ str_replace('_',' ', $trx->sumber) }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500 dark:text-slate-400">Nama</span>
                        <span class="font-medium text-slate-800 dark:text-slate-100">
                            {{ $trx->recipient_name ?? optional($trx->vendor)->name ?? optional($trx->customer)->name ?? '-' }}
                        </span>
                    </div>

                    <div class="pt-2 border-t border-slate-100 dark:border-slate-700/60">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-slate-500 dark:text-slate-400">Nominal</span>
                            <span class="text-base font-semibold text-slate-900 dark:text-slate-50">
                                {{ number_format($trx->amount, 2, ',', '.') }}
                            </span>
                        </div>

                        @if($trx->sumber === 'uang_jalan')
                            <div class="mt-2 flex items-center justify-between gap-4 text-xs">
                                <span class="text-slate-500 dark:text-slate-400">Total Potongan UJL</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-100">
                                    {{ number_format($driverAdvanceDeductionTotal, 2, ',', '.') }}
                                </span>
                            </div>
                        @endif

                        <div class="mt-2 flex items-center justify-between gap-4 text-xs">
                            <span class="text-slate-500 dark:text-slate-400">Potongan PPh 23</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-100">
                                {{ number_format($trx->withholding_pph23 ?? 0, 2, ',', '.') }}
                            </span>
                        </div>

                        {{-- Total Aktual --}}
                        <div class="mt-3 pt-2 border-t border-dashed border-slate-200 dark:border-slate-700/60 flex items-center justify-between gap-4">
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-200">
                                Total {{ $trx->jenis === 'cash_in' ? 'Diterima' : 'Dibayar' }}
                            </span>
                            <span class="text-base font-bold text-emerald-600 dark:text-emerald-400">
                                {{ number_format($trx->amount - ($trx->withholding_pph23 ?? 0) - ($trx->admin_fee ?? 0), 2, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <div class="pt-3 mt-1 space-y-2 border-t border-slate-100 dark:border-slate-700/60 text-xs">
                        <div class="flex items-start justify-between gap-4">
                            <span class="mt-0.5 text-slate-500 dark:text-slate-400">Ref</span>
                            <span class="text-right text-slate-800 dark:text-slate-100 break-all">
                                {{ $trx->reference_number ?: '-' }}
                            </span>
                        </div>

                        <div class="flex items-start justify-between gap-4">
                            <span class="mt-0.5 text-slate-500 dark:text-slate-400">Deskripsi</span>
                            <span class="text-right text-slate-800 dark:text-slate-100">
                                {{ $trx->description ?: '-' }}
                            </span>
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- RELASI --}}
            <x-card title="Relasi" class="md:col-span-2" headerClass="aurora-card-header">
                <div class="space-y-3 text-sm">
                    @php
                        // 1. Invoices (Direct or via Payments)
                        $invoices = $trx->invoicePayments->pluck('invoice')->filter();
                        if ($invoices->isEmpty() && $trx->invoice) {
                            $invoices = collect([$trx->invoice]);
                        }

                        // 2. Vendor Bills (Direct or via Payments)
                        $vendorBills = $trx->vendorBillPayments->pluck('vendorBill')->filter();
                        if ($vendorBills->isEmpty() && $trx->vendorBill) {
                            $vendorBills = collect([$trx->vendorBill]);
                        }
                        // Unique vendor bills
                        $vendorBills = $vendorBills->unique('id');

                        // 3. Collect Job Orders from ALL sources
                        $jobOrders = collect();

                        // From Invoices
                        foreach($invoices as $invoice) {
                            if($invoice->relationLoaded('items')) {
                                foreach($invoice->items as $item) {
                                    if($item->job_order_id) {
                                        $jobOrders->push($item->jobOrder);
                                    }
                                }
                            }
                        }

                        // From Vendor Bills
                        foreach($vendorBills as $bill) {
                            $items = $bill->items ?? collect();
                            foreach($items as $item) {
                                if($item->shipmentLeg && $item->shipmentLeg->jobOrder) {
                                    $jobOrders->push($item->shipmentLeg->jobOrder);
                                }
                            }
                        }

                        // From Driver Advances
                        foreach($trx->driverAdvancePayments as $payment) {
                            if($payment->driverAdvance && $payment->driverAdvance->shipmentLeg && $payment->driverAdvance->shipmentLeg->jobOrder) {
                                $jobOrders->push($payment->driverAdvance->shipmentLeg->jobOrder);
                            }
                        }

                        $jobOrders = $jobOrders->unique('id')->filter();
                    @endphp

                    {{-- Invoice --}}
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                            Invoice
                        </span>
                        <div class="text-sm">
                            @if($invoices->isNotEmpty())
                                @foreach($invoices as $invoice)
                                    <a href="{{ route('invoices.show', $invoice) }}"
                                       class="inline-flex items-center rounded-full bg-blue-50 dark:bg-blue-900/40 px-3 py-1 text-xs font-medium text-blue-700 dark:text-blue-200 hover:bg-blue-100 dark:hover:bg-blue-900/70 mr-1 mb-1">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                @endforeach
                            @else
                                <span class="text-slate-400 dark:text-slate-500">-</span>
                            @endif
                        </div>
                    </div>

                    {{-- Vendor Bill --}}
                    <div class="flex flex-col gap-1 pt-2 border-t border-slate-100 dark:border-slate-700/60">
                        <span class="text-xs font-medium tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                            Vendor Bill
                        </span>
                        <div class="text-sm">
                            @if($vendorBills->isNotEmpty())
                                @foreach($vendorBills as $bill)
                                    <a href="#" class="inline-flex items-center rounded-full bg-orange-50 dark:bg-orange-900/40 px-3 py-1 text-xs font-medium text-orange-700 dark:text-orange-200 mr-1 mb-1">
                                        {{ $bill->vendor_bill_number }}
                                    </a>
                                @endforeach
                            @else
                                <span class="text-slate-400 dark:text-slate-500">-</span>
                            @endif
                        </div>
                    </div>

                    {{-- Job Order --}}
                    <div class="flex flex-col gap-1 pt-2 border-t border-slate-100 dark:border-slate-700/60">
                        <span class="text-xs font-medium tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                            Job Order
                        </span>
                        <div class="text-sm">
                            @if($jobOrders->isNotEmpty())
                                @foreach($jobOrders as $jo)
                                    <a href="{{ route('job-orders.show', $jo) }}"
                                       class="inline-flex items-center rounded-full bg-indigo-50 dark:bg-indigo-900/40 px-3 py-1 text-xs font-medium text-indigo-700 dark:text-indigo-200 hover:bg-indigo-100 dark:hover:bg-indigo-900/70 mr-1 mb-1">
                                        {{ $jo->job_number }}
                                    </a>
                                @endforeach
                            @else
                                <span class="text-slate-400 dark:text-slate-500">-</span>
                            @endif
                        </div>
                    </div>

                    {{-- Customer & Vendor --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-slate-100 dark:border-slate-700/60">
                        <div class="flex flex-col gap-1">
                            <span class="text-xs font-medium tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                                Customer
                            </span>
                            <span class="text-sm font-medium text-slate-800 dark:text-slate-100">
                                {{ optional($trx->customer)->name ?: '-' }}
                            </span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-xs font-medium tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                                Vendor
                            </span>
                            <span class="text-sm font-medium text-slate-800 dark:text-slate-100">
                                {{ optional($trx->vendor)->name ?: '-' }}
                            </span>
                        </div>
                    </div>

                    {{-- Driver Advance --}}
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-700/60">
                        <span class="text-xs font-medium tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                            Driver Advance
                        </span>
                        <div class="mt-1 text-sm">
                            @if($driverAdvances->isNotEmpty())
                                @foreach($driverAdvances as $adv)
                                    <a href="{{ route('driver-advances.show', $adv) }}"
                                       class="inline-flex items-center rounded-full bg-amber-50 dark:bg-amber-900/30 px-3 py-1 text-xs font-medium text-amber-700 dark:text-amber-200 hover:bg-amber-100 dark:hover:bg-amber-900/60 mr-1 mb-1">
                                        {{ $adv->advance_number }}
                                    </a>
                                @endforeach
                            @else
                                <span class="text-slate-400 dark:text-slate-500">-</span>
                            @endif
                        </div>
                    </div>

                    {{-- COA --}}
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-700/60">
                        <span class="text-xs font-medium tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                            COA
                        </span>
                        <div class="mt-1 text-sm font-medium text-slate-800 dark:text-slate-100">
                            {{ optional($trx->accountCoa)->code }} {{ optional($trx->accountCoa)->name }}
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
    <style>
        [data-theme="aurora"] .aurora-card-header {
            position: relative;
        }
        [data-theme="aurora"] .aurora-card-header::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 2px;
            background: linear-gradient(90deg, #8b5cf6, #ec4899, #22d3ee);
        }
    </style>
@endsection
