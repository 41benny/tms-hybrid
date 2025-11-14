@extends('layouts.app', ['title' => 'Detail Vendor Bill'])

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $bill->vendor_bill_number }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $bill->vendor->name ?? '-' }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('hutang.dashboard') }}" class="px-4 py-2 rounded-lg bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-900 dark:text-slate-100 text-sm font-medium transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Dashboard Hutang
            </a>
            @if($bill->status !== 'paid' && $bill->status !== 'cancelled' && $bill->remaining > 0)
            <a href="{{ route('payment-requests.create', ['vendor_bill_id'=>$bill->id]) }}" class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Ajukan Pembayaran
            </a>
            @endif
            @if($bill->status === 'draft')
            <form method="post" action="{{ route('vendor-bills.mark-received', $bill) }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition-colors">
                    Mark Received
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Ringkasan Hutang --}}
    <x-card title="Ringkasan Hutang" class="mb-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Tanggal Tagihan</div>
                <div class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $bill->bill_date->format('d M Y') }}</div>
            </div>
            <div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Jatuh Tempo</div>
                <div class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ optional($bill->due_date)->format('d M Y') ?: '-' }}</div>
            </div>
            <div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Status</div>
                <div>
                    <x-badge :variant="match($bill->status) {
                        'draft' => 'default',
                        'received' => 'warning',
                        'partially_paid' => 'warning',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        default => 'default'
                    }">{{ strtoupper(str_replace('_',' ', $bill->status)) }}</x-badge>
                </div>
            </div>
            <div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Catatan</div>
                <div class="text-sm text-slate-900 dark:text-slate-100">{{ $bill->notes ?: '-' }}</div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-2 md:grid-cols-5 gap-4 pt-4 border-t border-slate-200 dark:border-slate-800">
            <div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">DPP</div>
                <div class="text-lg font-semibold text-slate-900 dark:text-slate-100">Rp {{ number_format($bill->dpp ?? 0, 0, ',', '.') }}</div>
            </div>
            <div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">PPN 11%</div>
                <div class="text-lg font-semibold text-slate-900 dark:text-slate-100">Rp {{ number_format($bill->ppn ?? 0, 0, ',', '.') }}</div>
            </div>
            <div>
                <div class="text-xs text-rose-500 dark:text-rose-400 mb-1">PPH 23 (Dipotong)</div>
                <div class="text-lg font-semibold text-rose-600 dark:text-rose-400">-Rp {{ number_format($bill->pph ?? 0, 0, ',', '.') }}</div>
            </div>
            <div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">Total Tagihan</div>
                <div class="text-lg font-bold text-slate-900 dark:text-slate-100">Rp {{ number_format($bill->total_amount, 0, ',', '.') }}</div>
            </div>
            <div>
                <div class="text-xs text-emerald-500 dark:text-emerald-400 mb-1">Total Dibayar</div>
                <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($bill->total_paid ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-800">
            <div class="flex items-center justify-between">
                <div class="text-sm text-slate-600 dark:text-slate-400">Sisa Hutang</div>
                <div class="text-2xl font-bold {{ $bill->remaining > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                    Rp {{ number_format($bill->remaining ?? $bill->total_amount, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </x-card>

    {{-- Payment Requests Section --}}
    @if($bill->paymentRequests->count() > 0)
    <x-card title="Pengajuan Pembayaran" class="mb-6">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-800">
                        <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Nomor</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Tanggal</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Diajukan Oleh</th>
                        <th class="px-3 py-2 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Jumlah</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Status</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bill->paymentRequests as $pr)
                        <tr class="border-b border-slate-100 dark:border-slate-800/50">
                            <td class="px-3 py-2 text-slate-900 dark:text-slate-100">
                                {{ $pr->request_number }}
                            </td>
                            <td class="px-3 py-2 text-slate-600 dark:text-slate-400">
                                {{ $pr->request_date->format('d M Y') }}
                            </td>
                            <td class="px-3 py-2 text-slate-600 dark:text-slate-400">
                                {{ $pr->requestedBy->name ?? '-' }}
                            </td>
                            <td class="px-3 py-2 text-right font-medium text-slate-900 dark:text-slate-100">
                                Rp {{ number_format($pr->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-2">
                                <x-badge :variant="match($pr->status) {
                                    'pending' => 'default',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'paid' => 'success',
                                    default => 'default'
                                }" class="text-xs">{{ strtoupper($pr->status) }}</x-badge>
                            </td>
                            <td class="px-3 py-2">
                                <a href="{{ route('payment-requests.show', $pr) }}" class="text-blue-600 dark:text-blue-400 hover:underline text-xs">
                                    Lihat →
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-300 dark:border-slate-700">
                        <td colspan="3" class="px-3 py-3 text-right font-semibold text-slate-900 dark:text-slate-100">Total Pengajuan</td>
                        <td class="px-3 py-3 text-right font-bold text-lg text-emerald-600 dark:text-emerald-400">
                            Rp {{ number_format($bill->paymentRequests->sum('amount'), 0, ',', '.') }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-card>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Items --}}
        <x-card title="Detail Items" class="h-fit">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800">
                            <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Deskripsi</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Qty</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Harga</th>
                            <th class="px-3 py-2 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bill->items as $it)
                            <tr class="border-b border-slate-100 dark:border-slate-800/50">
                                <td class="px-3 py-2 text-slate-900 dark:text-slate-100">
                                    <div class="font-medium">{{ $it->description }}</div>
                                    @if($it->shipmentLeg)
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                            Leg: {{ $it->shipmentLeg->leg_code }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right text-slate-600 dark:text-slate-400">{{ number_format($it->qty, 2, ',', '.') }}</td>
                                <td class="px-3 py-2 text-right text-slate-600 dark:text-slate-400">{{ number_format($it->unit_price, 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-right font-medium text-slate-900 dark:text-slate-100">
                                    {{ $it->subtotal < 0 ? '-' : '' }}{{ number_format(abs($it->subtotal), 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-slate-300 dark:border-slate-700">
                            <td colspan="3" class="px-3 py-3 text-right font-semibold text-slate-900 dark:text-slate-100">Total</td>
                            <td class="px-3 py-3 text-right font-bold text-lg text-slate-900 dark:text-slate-100">
                                Rp {{ number_format($bill->total_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-card>

        {{-- Mutasi Pembayaran --}}
        <x-card title="Mutasi Pembayaran" class="h-fit">
            @if($bill->payments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800">
                                <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Tanggal</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Akun</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Deskripsi</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bill->payments as $payment)
                                <tr class="border-b border-slate-100 dark:border-slate-800/50">
                                    <td class="px-3 py-2 text-slate-900 dark:text-slate-100">
                                        {{ $payment->tanggal->format('d M Y') }}
                                    </td>
                                    <td class="px-3 py-2 text-slate-600 dark:text-slate-400">
                                        {{ $payment->account->name ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-slate-600 dark:text-slate-400">
                                        <div>{{ $payment->description ?: '-' }}</div>
                                        @if($payment->reference_number)
                                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                                Ref: {{ $payment->reference_number }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-right font-medium text-emerald-600 dark:text-emerald-400">
                                        Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-slate-300 dark:border-slate-700">
                                <td colspan="3" class="px-3 py-3 text-right font-semibold text-slate-900 dark:text-slate-100">Total Dibayar</td>
                                <td class="px-3 py-3 text-right font-bold text-lg text-emerald-600 dark:text-emerald-400">
                                    Rp {{ number_format($bill->total_paid ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-slate-500 dark:text-slate-400">
                    <svg class="w-12 h-12 mx-auto mb-2 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-sm">Belum ada pembayaran</p>
                </div>
            @endif
        </x-card>
    </div>
@endsection
