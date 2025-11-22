@extends('layouts.app', ['title' => 'Job Orders'])

@section('content')
<div class="space-y-6">
    <x-card>
        <x-slot:header>
            <div class="flex flex-col gap-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <div class="text-2xl font-bold text-slate-900 dark:text-slate-100">Job Orders</div>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Kelola order transportasi</p>
                    </div>
                    <x-button :href="route('job-orders.create')" variant="primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Buat Job Order Baru
                    </x-button>
                </div>

                {{-- Filter Section --}}
                <div class="pt-4 border-t border-slate-200 dark:border-slate-700">
                    <form method="get" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <input type="hidden" name="view" value="{{ request('view', 'table') }}">

                        <select name="status" class="rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100">
                            <option value="">Semua Status</option>
                            @foreach(['draft' => 'Draft', 'confirmed' => 'Confirmed', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $val => $label)
                                <option value="{{ $val }}" @selected(request('status')===$val)>{{ $label }}</option>
                            @endforeach
                        </select>

                        <select name="invoice_status" class="rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100">
                            <option value="">Semua Invoice Status</option>
                            <option value="not_invoiced" @selected(request('invoice_status')==='not_invoiced')>⚠️ Belum Diinvoice</option>
                            <option value="invoiced" @selected(request('invoice_status')==='invoiced')>✓ Sudah Diinvoice</option>
                        </select>

                        <input type="date" name="from" value="{{ request('from') }}" placeholder="Dari Tanggal" class="rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100">

                        <div class="flex gap-2">
                            <input type="date" name="to" value="{{ request('to') }}" placeholder="Sampai Tanggal" class="flex-1 rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 dark:bg-indigo-500 text-white rounded-lg hover:bg-indigo-700 dark:hover:bg-indigo-600 transition-colors text-sm font-medium">
                                Filter
                            </button>
                            @if(request()->hasAny(['status', 'invoice_status', 'from', 'to']))
                            <a href="{{ route('job-orders.index', ['view' => request('view', 'table')]) }}" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors text-sm font-medium">
                                Reset
                            </a>
                            @endif
                        </div>
                    </form>

                    @if(request('invoice_status') === 'not_invoiced')
                    <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800 rounded-lg flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm text-amber-700 dark:text-amber-400">
                            Menampilkan job orders yang <strong>belum diinvoice</strong>. Klik "Buat Invoice" untuk membuat invoice.
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </x-slot:header>
    </x-card>

    <div class="flex justify-end">
        <div class="inline-flex rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 p-1 shadow-sm">
            <a href="{{ route('job-orders.index', array_merge(request()->except('view'), ['view' => 'table'])) }}"
               class="inline-flex items-center justify-center p-2 rounded-md transition-all duration-200 {{ $viewMode === 'table' ? 'bg-blue-600 text-white shadow' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}"
               aria-label="Tampilan tabel">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
            </a>
            <a href="{{ route('job-orders.index', array_merge(request()->except('view'), ['view' => 'board'])) }}"
               class="inline-flex items-center justify-center p-2 rounded-md transition-all duration-200 {{ $viewMode === 'board' ? 'bg-blue-600 text-white shadow' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}"
               aria-label="Tampilan papan">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
            </a>
        </div>
    </div>

    @if($viewMode === 'board')
        <div>
            @include('job-orders.partials.board-view')

            @if($orders->hasPages())
                <div class="mt-4">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    @else
        <x-card :noPadding="true">
            @include('job-orders.partials.table-view')

            @if($orders->hasPages())
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800">
                    {{ $orders->links() }}
                </div>
            @endif
        </x-card>
    @endif
</div>
@endsection
