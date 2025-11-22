@extends('layouts.app', ['title' => 'Vendor Bills'])

@section('content')
    <x-card>
        <x-slot:header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="text-2xl font-bold text-slate-900 dark:text-slate-100">Vendor Bills</div>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Vendor bills dibuat otomatis dari <a href="{{ route('hutang.dashboard') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">Dashboard Hutang</a></p>
                </div>
            </div>
        </x-slot:header>

        <form method="get" class="grid grid-cols-1 md:grid-cols-6 gap-3">
            <select name="status" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Status</option>
                @foreach(['draft','received','partially_paid','paid','cancelled'] as $st)
                    <option value="{{ $st }}" @selected(request('status')===$st)>{{ ucfirst(str_replace('_',' ', $st)) }}</option>
                @endforeach
            </select>
            <select name="vendor_id" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Vendor</option>
                @foreach($vendors as $v)
                    <option value="{{ $v->id }}" @selected(request('vendor_id')==$v->id)>{{ $v->name }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            <input type="date" name="to" value="{{ request('to') }}" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
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
                                <button type="button" onclick="showInfoPopup({{ $b->id }})" class="inline-flex items-center gap-1.5 px-2 py-1.5 text-xs font-medium text-sky-600 dark:text-sky-400 hover:bg-sky-50 dark:hover:bg-sky-950/30 rounded transition-colors" title="Info Muatan & Leg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                                <x-button :href="route('vendor-bills.show',$b)" variant="ghost" size="sm" title="Lihat Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </x-button>
                                @if($b->status !== 'paid' && $b->status !== 'cancelled')
                                    @php
                                        $remainingToRequest = $b->remaining_to_request ?? (max($b->total_amount - ($b->paymentRequests->sum('amount')),0));
                                    @endphp
                                    @if($remainingToRequest > 0)
                                        <x-button :href="route('payment-requests.create', ['vendor_bill_id'=>$b->id])" variant="ghost" size="sm" title="Ajukan Pembayaran (Sisa belum diajukan: Rp {{ number_format($remainingToRequest,0,',','.') }})">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </x-button>
                                    @else
                                        <button disabled class="inline-flex items-center gap-1.5 px-2 py-1.5 text-xs font-medium text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 rounded cursor-not-allowed" title="Semua nominal sudah diajukan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Diajukan Penuh
                                        </button>
                                    @endif
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

    {{-- Popup Info Muatan --}}
    <div id="infoPopup" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeInfoPopup()"></div>
        <div class="relative max-w-3xl mx-auto mt-20 bg-white dark:bg-slate-900 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200 dark:border-slate-700">
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Info Muatan & Leg</h2>
                <button type="button" onclick="closeInfoPopup()" class="p-1 rounded hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Tutup">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div id="popupContent" class="p-5 space-y-3 max-h-[65vh] overflow-y-auto">
                <div class="flex items-center justify-center py-8 text-slate-500 dark:text-slate-400">
                    <svg class="animate-spin h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showInfoPopup(billId) {
            document.getElementById('infoPopup').classList.remove('hidden');
            document.getElementById('popupContent').innerHTML = '<div class="flex items-center justify-center py-8 text-slate-500"><svg class="animate-spin h-6 w-6" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>';

            fetch(`/vendor-bills/${billId}/leg-info`)
                .then(r => r.json())
                .then(data => {
                    if(data.legs && data.legs.length > 0) {
                        let html = '';
                        data.legs.forEach(leg => {
                            html += `
                                <div class="border border-slate-200 dark:border-slate-700 rounded-lg p-3 bg-slate-50 dark:bg-slate-800/40">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="font-semibold text-xs text-slate-900 dark:text-slate-100">${leg.leg_code}</span>
                                        <span class="text-[10px] px-2 py-0.5 rounded ${leg.status_class}">${leg.status_label}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-600 dark:text-slate-400 space-y-0.5">
                                        <div>📅 Load: ${leg.load_date} → Unload: ${leg.unload_date}</div>
                                        <div>📦 Qty: ${leg.quantity} • ${leg.cost_category}</div>
                                        ${leg.truck ? `<div>🚚 ${leg.truck}${leg.driver ? ' • ' + leg.driver : ''}</div>` : ''}
                                        ${leg.vendor ? `<div>🏢 ${leg.vendor}</div>` : ''}
                                    </div>
                                </div>
                            `;
                        });
                        document.getElementById('popupContent').innerHTML = html;
                    } else {
                        document.getElementById('popupContent').innerHTML = '<div class="text-center py-8 text-slate-500 dark:text-slate-400 text-sm">Tidak ada leg terkait</div>';
                    }
                })
                .catch(() => {
                    document.getElementById('popupContent').innerHTML = '<div class="text-center py-8 text-rose-500 text-sm">Gagal memuat data</div>';
                });
        }

        function closeInfoPopup() {
            document.getElementById('infoPopup').classList.add('hidden');
        }
    </script>
@endsection
