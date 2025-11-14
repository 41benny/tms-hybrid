@extends('layouts.app', ['title' => 'Job Orders'])

@section('content')
<div class="space-y-6">
    <x-card>
        <x-slot:header>
            <div class="flex flex-col gap-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Job Orders</h1>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Kelola order transportasi</p>
                    </div>
                    <x-button :href="route('job-orders.create')" variant="primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Buat Job Order Baru
                    </x-button>
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
                <span class="sr-only">Tabel</span>
            </a>
            <a href="{{ route('job-orders.index', array_merge(request()->except('view'), ['view' => 'board'])) }}"
               class="inline-flex items-center justify-center p-2 rounded-md transition-all duration-200 {{ $viewMode === 'board' ? 'bg-blue-600 text-white shadow' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}"
               aria-label="Tampilan papan">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                <span class="sr-only">Papan</span>
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
