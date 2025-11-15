@extends('layouts.app', ['title' => 'Pembelian Part'])

@section('content')
<div class="space-y-4 md:space-y-6">
    <x-card>
        <x-slot:header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-slate-900 dark:text-slate-100">Pembelian Part</h1>
                    <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 mt-1">Daftar pembelian sparepart</p>
                </div>
                <x-button :href="route('part-purchases.create')" variant="primary" class="w-full sm:w-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="hidden sm:inline">Tambah</span>
                    <span class="sm:hidden">Tambah Pembelian</span>
                </x-button>
            </div>
        </x-slot:header>
    </x-card>

    <x-card>
        <form method="get" class="flex flex-col sm:flex-row gap-2">
            <input 
                type="text" 
                name="q" 
                value="{{ request('q') }}" 
                placeholder="Cari no pembelian/invoice..." 
                class="flex-1 rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 md:px-4 py-2 text-sm"
            >
            <select name="status" class="rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 md:px-4 py-2 text-sm">
                <option value="">Semua Status</option>
                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                <option value="received" @selected(request('status') === 'received')>Received</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
            </select>
            <x-button variant="outline" type="submit" class="w-full sm:w-auto">Cari</x-button>
        </form>
    </x-card>

    {{-- Mobile Card View --}}
    <div class="block md:hidden space-y-3">
        @forelse($purchases as $purchase)
            <x-card>
                <div class="space-y-2">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="font-semibold text-slate-900 dark:text-slate-100">{{ $purchase->purchase_number }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $purchase->purchase_date->format('d M Y') }}</p>
                        </div>
                        <x-badge :variant="$purchase->status === 'received' ? 'success' : ($purchase->status === 'cancelled' ? 'danger' : 'default')">
                            {{ ucfirst($purchase->status) }}
                        </x-badge>
                    </div>
                    <div class="text-sm">
                        <p class="text-slate-600 dark:text-slate-400">Supplier: {{ $purchase->vendor->name ?? $purchase->supplier_name ?? '-' }}</p>
                        <p class="text-slate-600 dark:text-slate-400">Total: {{ number_format($purchase->total_amount, 2, ',', '.') }}</p>
                        @if($purchase->is_direct_usage)
                            <x-badge variant="warning" class="mt-1">Langsung Pakai</x-badge>
                        @endif
                    </div>
                    <a href="{{ route('part-purchases.show', $purchase) }}" class="text-indigo-600 text-sm hover:underline">Lihat Detail →</a>
                </div>
            </x-card>
        @empty
            <x-card>
                <div class="text-center py-8 text-slate-500 dark:text-slate-400">
                    <span class="text-4xl">📦</span>
                    <p class="mt-2 text-sm">Belum ada pembelian part</p>
                </div>
            </x-card>
        @endforelse
    </div>

    {{-- Desktop Table View --}}
    <x-card :noPadding="true" class="hidden md:block">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-[#2d2d2d]">
                <thead class="bg-slate-50 dark:bg-[#252525]">
                    <tr>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">No Pembelian</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Tanggal</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Supplier</th>
                        <th class="px-4 md:px-6 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Total</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Status</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-[#1e1e1e] divide-y divide-slate-200 dark:divide-[#2d2d2d]">
                    @forelse($purchases as $purchase)
                        <tr class="hover:bg-slate-50 dark:hover:bg-[#252525] transition-colors">
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-slate-900 dark:text-slate-100">{{ $purchase->purchase_number }}</div>
                                @if($purchase->is_direct_usage)
                                    <x-badge variant="warning" class="mt-1 text-xs">Langsung Pakai</x-badge>
                                @endif
                            </td>
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-400">
                                {{ $purchase->purchase_date->format('d M Y') }}
                            </td>
                            <td class="px-4 md:px-6 py-4">
                                <div class="text-slate-900 dark:text-slate-100">{{ $purchase->vendor->name ?? $purchase->supplier_name ?? '-' }}</div>
                                @if($purchase->invoice_number)
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Inv: {{ $purchase->invoice_number }}</div>
                                @endif
                            </td>
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap text-right font-semibold text-slate-900 dark:text-slate-100">
                                {{ number_format($purchase->total_amount, 2, ',', '.') }}
                            </td>
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                <x-badge :variant="$purchase->status === 'received' ? 'success' : ($purchase->status === 'cancelled' ? 'danger' : 'default')">
                                    {{ ucfirst($purchase->status) }}
                                </x-badge>
                            </td>
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('part-purchases.show', $purchase) }}" class="text-indigo-600 hover:text-indigo-800">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="text-4xl">📦</span>
                                    <p class="text-sm">Belum ada pembelian part</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($purchases->hasPages())
            <div class="px-4 md:px-6 py-4 border-t border-slate-200 dark:border-[#2d2d2d]">
                {{ $purchases->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection

