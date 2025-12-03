@extends('layouts.app', ['title' => 'Accounting Periods'])

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <div class="text-2xl font-semibold text-slate-900 dark:text-white">Accounting Periods</div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Periode berikut otomatis dibuat (OPEN) saat Anda menutup periode sebelumnya. Jurnal bulan baru ditahan sampai bulan sebelumnya CLOSED.</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="get" class="flex items-center gap-2">
                <select name="year" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-3 py-2 text-sm">
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <button class="px-4 py-2 rounded bg-indigo-600 hover:bg-indigo-700 text-white text-sm">Tampilkan</button>
            </form>
            @php
                $noPeriods = $years->isEmpty();
            @endphp
            @if($noPeriods && auth()->user()?->isSuperAdmin())
                <form method="post" action="{{ route('accounting.periods.create-current') }}" onsubmit="return confirm('Inisialisasi periode pertama ({{ date('F Y') }})?')">
                    @csrf
                    <button class="px-4 py-2 rounded bg-green-600 hover:bg-green-700 text-white text-sm whitespace-nowrap">
                        <i class="fas fa-play mr-1"></i>Inisialisasi Awal
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success') || session('error') || session('info') || $errors->any())
        <div class="space-y-3 mb-6">
            @if(session('success'))
                <x-alert variant="success">{{ session('success') }}</x-alert>
            @endif
            @if(session('error'))
                <x-alert variant="danger">{!! nl2br(e(session('error'))) !!}</x-alert>
            @endif
            @if(session('info'))
                <x-alert variant="info">{{ session('info') }}</x-alert>
            @endif
            @if($errors->any())
                <x-alert variant="danger">
                    <div class="font-semibold mb-1">Perbaiki isian berikut:</div>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-alert>
            @endif
        </div>
    @endif

    @php
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('m');
    @endphp

    @if($periods->where('year', $currentYear)->where('month', $currentMonth)->isEmpty() && $year == $currentYear && auth()->user()?->isSuperAdmin())
        <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
            <div class="flex items-start gap-3">
                <i class="fas fa-exclamation-triangle text-amber-600 dark:text-amber-400 mt-0.5"></i>
                <div>
                    <h3 class="font-semibold text-amber-900 dark:text-amber-200">Periode Bulan Ini Belum Terbentuk</h3>
                    <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">
                        Periode fiskal <strong>{{ date('F Y') }}</strong> belum ada karena periode <strong>bulan sebelumnya</strong> belum di-close. Tutup periode bulan sebelumnya untuk membuka bulan ini otomatis.
                    </p>
                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-2">Jika ini pertama kali (belum ada periode sama sekali), tekan tombol Inisialisasi Awal di kanan atas.</p>
                </div>
            </div>
        </div>
    @endif

    @if(auth()->user()?->isSuperAdmin())
        <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <div class="flex items-start gap-3">
                <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 mt-0.5"></i>
                <div>
                    <h3 class="font-semibold text-blue-900 dark:text-blue-200">Aturan & Alur Periode</h3>
                    <ul class="text-sm text-blue-700 dark:text-blue-300 mt-2 space-y-1">
                        <li>1. Semua jurnal bulan baru ditahan sampai periode bulan sebelumnya di-close.</li>
                        <li>2. Saat periode bulan lalu di-close, periode bulan baru otomatis dibuat status OPEN.</li>
                        <li>3. Periode bulan berjalan tidak bisa di-close sampai berganti bulan.</li>
                        <li>4. Periode masa depan tidak bisa di-close.</li>
                        <li>5. CLOSED bisa di-REOPEN untuk koreksi; LOCKED permanen.</li>
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="overflow-x-auto bg-white dark:bg-[#252525] rounded-lg border border-slate-200 dark:border-[#2d2d2d]">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-[#2d2d2d] text-sm">
            <thead class="bg-slate-50 dark:bg-[#1e1e1e]">
                <tr class="text-slate-600 dark:text-slate-300">
                    <th class="px-4 py-3 text-left">Bulan</th>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-[#2d2d2d]">
                @foreach($periods as $p)
                    <tr class="hover:bg-slate-50 dark:hover:bg-[#1e1e1e]">
                        <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">{{ str_pad($p->month,2,'0',STR_PAD_LEFT) }}/{{ $p->year }}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $p->start_date }} - {{ $p->end_date }}</td>
                        <td class="px-4 py-3">
                            @php
                                $color = match ($p->status) {
                                    'open' => 'green',
                                    'closed' => 'yellow',
                                    'locked' => 'red',
                                    default => 'gray',
                                };
                                $nextYearCalc = $p->month === 12 ? $p->year + 1 : $p->year;
                                $nextMonthCalc = $p->month === 12 ? 1 : $p->month + 1;
                                $hasNext = $periods->first(fn($q) => $q->year == $nextYearCalc && $q->month == $nextMonthCalc);
                            @endphp
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-{{ $color }}-100 text-{{ $color }}-700 dark:bg-{{ $color }}-700/20 dark:text-{{ $color }}-300">{{ strtoupper($p->status) }}</span>
                            @if($p->status === 'open' && ! $hasNext)
                                <span class="inline-flex items-center ml-2 px-2 py-1 rounded text-xs bg-indigo-100 text-indigo-700 dark:bg-indigo-700/30 dark:text-indigo-300" title="Tutup periode ini untuk membuka periode bulan berikut dan mengizinkan jurnal baru">
                                    Menahan Jurnal Bulan Berikut
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                @if(!auth()->user()?->isSuperAdmin())
                                    <span class="text-xs text-slate-400">Restricted</span>
                                @else
                                    @php
                                        $isCurrentMonth = $p->year == date('Y') && $p->month == date('m');
                                        $isPastMonth = mktime(0, 0, 0, $p->month, 1, $p->year) < mktime(0, 0, 0, date('m'), 1, date('Y'));
                                    @endphp
                                    @if($p->status === 'open' && $isPastMonth)
                                        <form method="post" action="{{ route('accounting.periods.close', $p) }}" onsubmit="return confirm('PERINGATAN: Anda akan CLOSE periode {{ $p->month }}/{{ $p->year }}. Setelah di-close tidak bisa posting transaksi baru dan tidak bisa edit transaksi existing. Lanjutkan?')">
                                            @csrf
                                            <button class="px-3 py-1.5 rounded bg-yellow-600 hover:bg-yellow-700 text-white text-xs">Close</button>
                                        </form>
                                    @elseif($p->status === 'open' && $isCurrentMonth)
                                        <button disabled class="px-3 py-1.5 rounded bg-slate-300 dark:bg-slate-700 text-slate-500 dark:text-slate-400 text-xs cursor-not-allowed" title="Tidak bisa close periode bulan berjalan">
                                            Close (Bulan Ini)
                                        </button>
                                    @elseif($p->status === 'closed')
                                        <form method="post" action="{{ route('accounting.periods.reopen', $p) }}" onsubmit="return confirm('Reopen periode ini?')" class="inline">
                                            @csrf
                                            <button class="px-3 py-1.5 rounded bg-green-600 hover:bg-green-700 text-white text-xs">Reopen</button>
                                        </form>
                                        <form method="post" action="{{ route('accounting.periods.lock', $p) }}" onsubmit="return confirm('Lock periode ini? Tidak bisa diubah lagi.')" class="inline">
                                            @csrf
                                            <button class="px-3 py-1.5 rounded bg-red-600 hover:bg-red-700 text-white text-xs">Lock</button>
                                        </form>
                                    @elseif($p->status === 'locked')
                                        <span class="text-xs text-slate-400">No actions</span>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                @if($periods->isEmpty())
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                            <p class="mb-3">Tidak ada periode untuk tahun ini.</p>
                            @if(auth()->user()?->isSuperAdmin())
                                <p class="text-sm text-slate-400 dark:text-slate-500">
                                    Gunakan tombol "Buat Bulan Ini" di atas untuk membuat periode bulan berjalan.
                                </p>
                            @endif
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
@endsection
