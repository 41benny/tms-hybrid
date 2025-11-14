@extends('layouts.app', ['title' => 'Vendor Bills'])

@section('content')
    <x-card>
        <x-slot:header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Vendor Bills</h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Vendor bills dibuat otomatis dari <a href="{{ route('hutang.dashboard') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">Dashboard Hutang</a></p>
                </div>
            </div>
        </x-slot:header>

        <form method="get" class="grid grid-cols-1 md:grid-cols-6 gap-3">
            <select name="status" class="rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Status</option>
                @foreach(['draft','received','partially_paid','paid','cancelled'] as $st)
                    <option value="{{ $st }}" @selected(request('status')===$st)>{{ ucfirst(str_replace('_',' ', $st)) }}</option>
                @endforeach
            </select>
            <select name="vendor_id" class="rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Vendor</option>
                @foreach($vendors as $v)
                    <option value="{{ $v->id }}" @selected(request('vendor_id')==$v->id)>{{ $v->name }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            <div></div>
            <x-button type="submit" variant="outline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter
            </x-button>
        </form>
    </x-card>

    <x-card :noPadding="true" class="mt-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-[#2d2d2d]">
                <thead class="bg-slate-50 dark:bg-[#252525]">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Nomor</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Vendor</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Tanggal</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">DPP</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">PPN</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">PPH</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Total</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Dibayar</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Tgl Bayar</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-[#1e1e1e] divide-y divide-slate-200 dark:divide-[#2d2d2d]">
                @forelse($bills as $b)
                    <tr class="hover:bg-slate-50 dark:hover:bg-[#252525] transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="font-medium text-slate-900 dark:text-slate-100 text-sm">{{ $b->vendor_bill_number }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400 text-sm">
                            {{ $b->vendor->name ?? '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400 text-sm">
                            {{ $b->bill_date->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-slate-900 dark:text-slate-100 text-sm font-medium">
                            {{ number_format($b->dpp ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-slate-900 dark:text-slate-100 text-sm">
                            {{ number_format($b->ppn ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-rose-600 dark:text-rose-400 text-sm">
                            {{ $b->pph > 0 ? '-' : '' }}{{ number_format($b->pph ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-slate-900 dark:text-slate-100 text-sm font-semibold">
                            {{ number_format($b->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-emerald-600 dark:text-emerald-400 text-sm font-medium">
                            {{ number_format($b->total_paid ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400 text-sm">
                            {{ $b->last_payment_date ? $b->last_payment_date->format('d M Y') : '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <x-badge :variant="match($b->status) {
                                'draft' => 'default',
                                'received' => 'warning',
                                'partially_paid' => 'warning',
                                'paid' => 'success',
                                'cancelled' => 'danger',
                                default => 'default'
                            }" class="text-xs">{{ strtoupper(str_replace('_',' ', $b->status)) }}</x-badge>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                <x-button :href="route('vendor-bills.show',$b)" variant="ghost" size="sm" title="Lihat Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </x-button>
                                @if($b->status !== 'paid' && $b->status !== 'cancelled')
                                <x-button :href="route('payment-requests.create', ['vendor_bill_id'=>$b->id])" variant="ghost" size="sm" title="Ajukan Pembayaran">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </x-button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-12 h-12 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-sm">Belum ada vendor bills</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    <div class="mt-4">{{ $bills->links() }}</div>
@endsection
