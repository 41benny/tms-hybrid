@php($title = 'Sales Console')
@extends('layouts.app')

@section('content')
    <div class="space-y-4 md:space-y-6">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Sales Console</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Akses cepat untuk Sales: buat Job Order, kelola pengiriman, dan ajukan payment request dengan tampilan yang ramah mobile.
                </p>
            </div>
        </div>

        {{-- Grid cards - mobile first --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Job Orders --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-[#121826] p-4 shadow-sm">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Job Orders</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Buat dan pantau JO milik Anda.</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('job-orders.create') }}"
                       class="flex-1 min-w-[130px] inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-indigo-600 text-white text-xs font-semibold shadow hover:bg-indigo-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        JO Baru
                    </a>
                    <a href="{{ route('job-orders.index', ['view' => 'board']) }}"
                       class="flex-1 min-w-[130px] inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        JO Saya (Board)
                    </a>
                    <a href="{{ route('job-orders.index') }}"
                       class="flex-1 min-w-[130px] inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-900 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M9 6v12m6-12v12" />
                        </svg>
                        List JO
                    </a>
                </div>
            </div>

            {{-- Shipment / Pengiriman --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-[#121826] p-4 shadow-sm">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-cyan-100 dark:bg-cyan-900/40 text-cyan-600 dark:text-cyan-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2zm3 10h.01M7 14h4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Pengiriman (Shipment)</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Kelola leg pengiriman dari Job Order.</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('job-orders.index', ['status' => 'confirmed']) }}"
                       class="flex-1 min-w-[130px] inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-cyan-600 text-white text-xs font-semibold shadow hover:bg-cyan-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3" />
                        </svg>
                        JO Siap Jalan
                    </a>
                    <a href="{{ route('job-orders.index', ['status' => 'in_progress']) }}"
                       class="flex-1 min-w-[130px] inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13h2l1 6h13l1-6h2M5 10h14l-1.5-4.5A2 2 0 0015.61 4H8.39a2 2 0 00-1.89 1.5L5 10z" />
                        </svg>
                        Dalam Perjalanan
                    </a>
                    <a href="{{ route('job-orders.index', ['status' => 'completed']) }}"
                       class="flex-1 min-w-[130px] inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-900 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Selesai Dikirim
                    </a>
                </div>
            </div>

            {{-- Payment Requests --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-[#121826] p-4 shadow-sm">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Payment Requests</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Ajukan pembayaran vendor / driver.</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('payment-requests.create') }}"
                       class="flex-1 min-w-[130px] inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-emerald-600 text-white text-xs font-semibold shadow hover:bg-emerald-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        PR Baru
                    </a>
                    <a href="{{ route('payment-requests.index', ['status' => 'pending']) }}"
                       class="flex-1 min-w-[130px] inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Pending Approval
                    </a>
                    <a href="{{ route('payment-requests.index') }}"
                       class="flex-1 min-w-[130px] inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-900 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 8h10M5 6h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z" />
                        </svg>
                        Semua Pengajuan
                    </a>
                </div>
            </div>

            {{-- Invoices (opsional untuk follow up) --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-[#121826] p-4 shadow-sm">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Invoices</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Lihat invoice untuk follow up customer.</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('invoices.index', ['status' => 'sent']) }}"
                       class="flex-1 min-w-[130px] inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-amber-500 text-white text-xs font-semibold shadow hover:bg-amber-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Belum Lunas
                    </a>
                    <a href="{{ route('invoices.index') }}"
                       class="flex-1 min-w-[130px] inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-900 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M7 8h10M5 6h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z" />
                        </svg>
                        Semua Invoice
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

