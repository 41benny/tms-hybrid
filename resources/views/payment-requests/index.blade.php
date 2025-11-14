@extends('layouts.app', ['title' => 'Pengajuan Pembayaran'])

@section('content')
    <x-card>
        <x-slot:header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Pengajuan Pembayaran</h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                        @if(Auth::check() && (Auth::user()->role ?? 'admin') === 'super_admin')
                            Semua pengajuan pembayaran vendor
                        @else
                            Pengajuan pembayaran Anda
                        @endif
                    </p>
                </div>
                <x-button :href="route('payment-requests.create')" variant="primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat Pengajuan Manual
                </x-button>
            </div>
        </x-slot:header>

        <form method="get" class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <select name="status" class="rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Status</option>
                @foreach(['pending' => 'Pending', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'paid' => 'Dibayar'] as $val => $label)
                    <option value="{{ $val }}" @selected(request('status')===$val)>{{ $label }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}" placeholder="Dari Tanggal" class="rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            <input type="date" name="to" value="{{ request('to') }}" placeholder="Sampai Tanggal" class="rounded-lg bg-white dark:bg-[#252525] border border-slate-300 dark:border-[#3d3d3d] px-3 py-2 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            <div></div>
            <x-button type="submit" variant="outline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter
            </x-button>
        </form>
    </x-card>

    {{-- Desktop Table View --}}
    <x-card :noPadding="true" class="mt-6 hidden md:block">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-[#2d2d2d]">
                <thead class="bg-slate-50 dark:bg-[#252525]">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Nomor</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Tipe</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Vendor Bill / Deskripsi</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Vendor</th>
                        @if(Auth::check() && (Auth::user()->role ?? 'admin') === 'super_admin')
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Diajukan Oleh</th>
                        @endif
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Tanggal</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Jumlah</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-[#1e1e1e] divide-y divide-slate-200 dark:divide-[#2d2d2d]">
                @forelse($requests as $r)
                    <tr class="hover:bg-slate-50 dark:hover:bg-[#252525] transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="font-medium text-slate-900 dark:text-slate-100 text-sm">{{ $r->request_number }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <x-badge :variant="$r->payment_type === 'manual' ? 'warning' : 'default'" class="text-xs">
                                {{ $r->payment_type === 'manual' ? 'MANUAL' : 'VENDOR BILL' }}
                            </x-badge>
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-400 text-sm">
                            @if($r->payment_type === 'vendor_bill' && $r->vendorBill)
                                <a href="{{ route('vendor-bills.show', $r->vendorBill) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    {{ $r->vendorBill->vendor_bill_number }}
                                </a>
                            @else
                                <span class="italic">{{ $r->description ?? 'Pembayaran Manual' }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400 text-sm">
                            {{ $r->vendorBill?->vendor->name ?? $r->vendor?->name ?? '-' }}
                        </td>
                        @if(Auth::check() && (Auth::user()->role ?? 'admin') === 'super_admin')
                        <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400 text-sm">
                            {{ $r->requestedBy->name ?? '-' }}
                        </td>
                        @endif
                        <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-400 text-sm">
                            {{ $r->request_date->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-slate-900 dark:text-slate-100 text-sm font-medium">
                            Rp {{ number_format($r->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <x-badge :variant="match($r->status) {
                                'pending' => 'default',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'paid' => 'success',
                                default => 'default'
                            }" class="text-xs">{{ strtoupper($r->status) }}</x-badge>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                <x-button :href="route('payment-requests.show', $r)" variant="ghost" size="sm" title="Lihat">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </x-button>
                                
                                @if($r->status === 'pending' && Auth::check() && (Auth::user()->role ?? 'admin') === 'super_admin')
                                <form method="POST" action="{{ route('payment-requests.approve', $r) }}" class="inline">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Setujui pengajuan ini?')" class="p-1.5 text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 rounded transition-colors" title="Setujui">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                </form>
                                @endif

                                @if($r->status === 'pending' && Auth::check() && (Auth::user()->id === $r->requested_by || (Auth::user()->role ?? 'admin') === 'super_admin'))
                                <form method="POST" action="{{ route('payment-requests.destroy', $r) }}" onsubmit="return confirm('Yakin ingin menghapus pengajuan ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 rounded transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ (Auth::check() && (Auth::user()->role ?? 'admin') === 'super_admin') ? 9 : 8 }}" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-12 h-12 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-sm">Belum ada pengajuan pembayaran</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    {{-- Mobile Card View --}}
    <div class="mt-6 space-y-4 md:hidden">
        @forelse($requests as $r)
        <x-card :noPadding="true">
            <div class="p-4">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex-1">
                        <div class="font-semibold text-slate-900 dark:text-slate-100">{{ $r->request_number }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $r->request_date->format('d M Y') }}</div>
                    </div>
                    <x-badge :variant="match($r->status) {
                        'pending' => 'default',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'paid' => 'success',
                        default => 'default'
                    }" class="text-xs">{{ strtoupper($r->status) }}</x-badge>
                </div>

                <div class="space-y-2 text-sm mb-4">
                    <div class="flex justify-between items-start">
                        <span class="text-slate-500 dark:text-slate-400">Tipe:</span>
                        <x-badge :variant="$r->payment_type === 'manual' ? 'warning' : 'default'" class="text-xs">
                            {{ $r->payment_type === 'manual' ? 'MANUAL' : 'VENDOR BILL' }}
                        </x-badge>
                    </div>
                    @if($r->payment_type === 'vendor_bill' && $r->vendorBill)
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Vendor Bill:</span>
                        <a href="{{ route('vendor-bills.show', $r->vendorBill) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                            {{ $r->vendorBill->vendor_bill_number }}
                        </a>
                    </div>
                    @else
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Deskripsi:</span>
                        <span class="text-slate-900 dark:text-slate-100 font-medium italic">{{ $r->description ?? '-' }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Vendor:</span>
                        <span class="text-slate-900 dark:text-slate-100 font-medium">{{ $r->vendorBill?->vendor->name ?? $r->vendor?->name ?? '-' }}</span>
                    </div>
                    @if(Auth::check() && (Auth::user()->role ?? 'admin') === 'super_admin')
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Diajukan:</span>
                        <span class="text-slate-900 dark:text-slate-100 font-medium">{{ $r->requestedBy->name ?? '-' }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between pt-2 border-t border-slate-200 dark:border-slate-700">
                        <span class="text-slate-500 dark:text-slate-400">Jumlah:</span>
                        <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($r->amount, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-3 border-t border-slate-200 dark:border-slate-700">
                    <x-button :href="route('payment-requests.show', $r)" variant="outline" size="sm" class="flex-1 justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Lihat
                    </x-button>

                    @if($r->status === 'pending' && Auth::check() && (Auth::user()->role ?? 'admin') === 'super_admin')
                    <form method="POST" action="{{ route('payment-requests.approve', $r) }}" class="flex-1">
                        @csrf
                        <button type="submit" onclick="return confirm('Setujui pengajuan ini?')" class="w-full px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition-colors flex items-center justify-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Setujui
                        </button>
                    </form>
                    @endif

                    @if($r->status === 'pending' && Auth::check() && (Auth::user()->id === $r->requested_by || (Auth::user()->role ?? 'admin') === 'super_admin'))
                    <form method="POST" action="{{ route('payment-requests.destroy', $r) }}" onsubmit="return confirm('Yakin ingin menghapus pengajuan ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 rounded transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </x-card>
        @empty
        <x-card>
            <div class="text-center py-8 text-slate-500 dark:text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-2 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-sm">Belum ada pengajuan pembayaran</p>
            </div>
        </x-card>
        @endforelse
    </div>

    <div class="mt-4">{{ $requests->links() }}</div>
@endsection

