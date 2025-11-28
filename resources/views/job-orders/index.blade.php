@extends('layouts.app', ['title' => 'Job Orders'])

@section('content')
<div class="space-y-6">
    <x-card>
        <x-slot:header>
            {{-- Filter Section with Create Button --}}
            <form method="get" class="flex flex-nowrap gap-2 items-center overflow-x-auto pb-2">
                <input type="hidden" name="view" value="{{ request('view', 'table') }}">

                <select name="status" class="rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-2 py-1.5 text-xs text-slate-900 dark:text-slate-100 shrink-0">
                    <option value="">Semua Status</option>
                    @foreach(['draft' => 'Draft', 'confirmed' => 'Confirmed', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $val => $label)
                        <option value="{{ $val }}" @selected(request('status')===$val)>{{ $label }}</option>
                    @endforeach
                </select>

                <select name="invoice_status" class="rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-2 py-1.5 text-xs text-slate-900 dark:text-slate-100 shrink-0">
                    <option value="">Semua Invoice Status</option>
                    <option value="not_invoiced" @selected(request('invoice_status')==='not_invoiced')>⚠️ Belum Diinvoice</option>
                    <option value="invoiced" @selected(request('invoice_status')==='invoiced')>✓ Sudah Diinvoice</option>
                </select>

                <input type="date" name="from" value="{{ request('from') }}" placeholder="Dari Tanggal" class="rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-2 py-1.5 text-xs text-slate-900 dark:text-slate-100 shrink-0">

                <input type="date" name="to" value="{{ request('to') }}" placeholder="Sampai Tanggal" class="rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-2 py-1.5 text-xs text-slate-900 dark:text-slate-100 shrink-0">

                <x-button type="submit" variant="primary" size="sm" class="shrink-0">
                    Filter
                </x-button>

                @if(request()->hasAny(['status', 'invoice_status', 'from', 'to']))
                <x-button :href="route('job-orders.index', ['view' => request('view', 'table')])" variant="ghost" size="sm">
                    Reset
                </x-button>
                @endif

                <div class="ml-auto flex items-center gap-2">
                    <div class="inline-flex rounded-lg border theme-border p-0.5 shadow-sm" style="background: rgba(0,0,0,0.2);">
                        <a href="{{ route('job-orders.index', array_merge(request()->except('view'), ['view' => 'table'])) }}"
                           class="inline-flex items-center justify-center p-1.5 rounded-md transition-all duration-200 {{ $viewMode === 'table' ? 'text-white shadow' : 'theme-text-muted hover:bg-white/10' }}"
                           style="{{ $viewMode === 'table' ? 'background: var(--color-primary);' : '' }}"
                           aria-label="Tampilan tabel">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </a>
                        <a href="{{ route('job-orders.index', array_merge(request()->except('view'), ['view' => 'board'])) }}"
                           class="inline-flex items-center justify-center p-1.5 rounded-md transition-all duration-200 {{ $viewMode === 'board' ? 'text-white shadow' : 'theme-text-muted hover:bg-white/10' }}"
                           style="{{ $viewMode === 'board' ? 'background: var(--color-primary);' : '' }}"
                           aria-label="Tampilan papan">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </a>
                    </div>

                    <x-button :href="route('job-orders.create')" variant="primary" size="sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Job Order
                    </x-button>
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
        </x-slot:header>
    </x-card>

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
