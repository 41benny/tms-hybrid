@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
    <div class="md:hidden space-y-4">
        <div class="relative rounded-[24px] overflow-hidden bg-gradient-to-br from-indigo-500 via-purple-600 to-pink-500 p-5 shadow-xl">
            <div class="absolute -top-24 -right-16 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-10 w-40 h-40 bg-cyan-400/20 rounded-full blur-3xl"></div>
            <div class="relative space-y-4">
                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <p class="text-[11px] uppercase tracking-[0.2em] text-white/70">Welcome back</p>
                        <p class="text-xl font-semibold text-white">{{ auth()->user()->name ?? 'User' }}</p>
                    </div>
                    <div class="px-3 py-1 rounded-full bg-black/30 border border-white/20 text-[11px] text-white flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse"></span>
                        <span>System Optimal</span>
                    </div>
                </div>
                <div class="rounded-2xl bg-black/25 border border-white/15 px-4 py-3 space-y-1">
                    <p class="text-[11px] text-white/70">Jobs In Progress</p>
                    <p class="text-2xl font-bold text-white">5</p>
                    <p class="text-[11px] text-white/60">Total 12 selesai bulan ini</p>
                </div>
                <div class="grid grid-cols-2 gap-3 text-[11px]">
                    <div class="rounded-xl bg-black/25 border border-white/10 px-3 py-3 space-y-1">
                        <p class="text-white/70">Pendapatan Bulan Ini</p>
                        <p class="text-sm font-semibold text-emerald-200">Rp 125.000.000</p>
                    </div>
                    <div class="rounded-xl bg-black/25 border border-white/10 px-3 py-3 space-y-1">
                        <p class="text-white/70">Biaya Vendor Bulan Ini</p>
                        <p class="text-sm font-semibold text-rose-200">Rp 75.000.000</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-3">
            <div class="rounded-2xl bg-slate-900/80 border border-slate-700 px-4 py-3 flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-400">Job Orders</p>
                    <p class="text-sm font-semibold text-slate-100">5 berjalan · 12 selesai</p>
                </div>
                <a href="{{ route('job-orders.index') }}" class="text-[11px] font-medium text-cyan-300 hover:text-cyan-200">Lihat</a>
            </div>
            <div class="rounded-2xl bg-slate-900/80 border border-slate-700 px-4 py-3 flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-400">Invoices</p>
                    <p class="text-sm font-semibold text-slate-100">3 belum dibayar</p>
                </div>
                <a href="{{ route('invoices.index') }}" class="text-[11px] font-medium text-cyan-300 hover:text-cyan-200">Lihat</a>
            </div>
            <div class="rounded-2xl bg-slate-900/80 border border-slate-700 px-4 py-3 flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-400">Vendor Bills</p>
                    <p class="text-sm font-semibold text-slate-100">2 jatuh tempo</p>
                </div>
                <a href="{{ route('vendor-bills.index') }}" class="text-[11px] font-medium text-cyan-300 hover:text-cyan-200">Lihat</a>
            </div>
        </div>
    </div>

    <div class="hidden md:block">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-card title="Ringkasan">
                <div class="flex items-center gap-2">
                    <x-badge variant="success">Jobs Selesai: 12</x-badge>
                    <x-badge variant="warning">Jobs Berjalan: 5</x-badge>
                    <x-badge variant="danger">Issue: 1</x-badge>
                </div>
            </x-card>
            <x-card title="Pendapatan Bulan Ini">
                <div class="text-2xl font-semibold">Rp 125.000.000</div>
            </x-card>
            <x-card title="Biaya Vendor Bulan Ini">
                <div class="text-2xl font-semibold">Rp 75.000.000</div>
            </x-card>
        </div>

        <div class="mt-4">
            <x-card title="Aktivitas Terbaru">
                <ul class="list-disc ml-5 space-y-1">
                    <li>Invoice INV-001 diposting</li>
                    <li>Vendor bill VBL-002 dibuat</li>
                    <li>Transport TR-010 delivered</li>
                </ul>
            </x-card>
        </div>
    </div>
@endsection
