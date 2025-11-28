@extends('layouts.app')

@php
    /** @var \Illuminate\Http\Request $request */
    $title = '403 - Akses Ditolak';
    $role = auth()->user()->role ?? null;

    // Tentukan URL kembali yang aman
    $previous = url()->previous();
    $current  = request()->fullUrl();

    if (! $previous || $previous === $current) {
        if ($role === 'sales') {
            $backUrl = route('sales.console');
        } elseif (auth()->check()) {
            $backUrl = route('dashboard');
        } else {
            $backUrl = route('login');
        }
    } else {
        $backUrl = $previous;
    }
@endphp

@section('content')
    <div class="min-h-[60vh] flex flex-col items-center justify-center text-center">
        <div class="inline-flex items-center justify-center rounded-full border border-slate-600/70 px-4 py-1 mb-4 bg-slate-900/60">
            <span class="text-xs font-mono text-slate-400 mr-2">Error</span>
            <span class="text-sm font-semibold text-slate-100">403 • Akses Ditolak</span>
        </div>

        <h1 class="text-2xl md:text-3xl font-bold text-slate-100 mb-3">
            Anda tidak berhak mengakses halaman ini.
        </h1>

        @if(isset($exception) && $exception->getMessage())
            <p class="max-w-xl text-sm md:text-base text-slate-400 mb-8 px-4">
                {{ $exception->getMessage() }}
            </p>
        @else
            <p class="max-w-xl text-sm md:text-base text-slate-400 mb-8 px-4">
                Silakan kembali ke halaman sebelumnya atau ke menu utama.
            </p>
        @endif

        <div class="flex flex-wrap items-center justify-center gap-3 px-4">
            <a href="{{ $backUrl }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-100 text-sm font-medium shadow-md shadow-slate-900/40 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Kembali</span>
            </a>

            @if($role === 'sales')
                <a href="{{ route('sales.console') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-cyan-600 hover:bg-cyan-500 text-slate-50 text-sm font-medium shadow-md shadow-cyan-900/50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3" />
                    </svg>
                    <span>Ke Sales Home</span>
                </a>
            @endif
        </div>
    </div>
@endsection

