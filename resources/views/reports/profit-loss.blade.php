@extends('layouts.app', ['title' => 'Laba Rugi'])

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div>
            <div class="text-xl font-semibold">Laba Rugi</div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Periode {{ \Carbon\Carbon::parse($from)->format('d M Y') }} – {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>
        </div>
        <form method="get" class="flex items-center gap-2">
            <input type="date" name="from" value="{{ $from }}" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" />
            <input type="date" name="to" value="{{ $to }}" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" />
            <button class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-800">Terapkan</button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-card title="Pendapatan">
            <table class="min-w-full text-sm">
                <tbody>
                    @foreach($revenue as $r)
                        <tr class="border-b border-slate-200 dark:border-slate-800">
                            <td class="px-2 py-1">{{ $r->code }} - {{ $r->name }}</td>
                            <td class="px-2 py-1 text-right">{{ number_format($r->net, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-semibold">
                        <td class="px-2 py-2">Total Pendapatan</td>
                        <td class="px-2 py-2 text-right">{{ number_format($totalRevenue, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </x-card>

        <x-card title="Beban">
            <table class="min-w-full text-sm">
                <tbody>
                    @foreach($expense as $r)
                        <tr class="border-b border-slate-200 dark:border-slate-800">
                            <td class="px-2 py-1">{{ $r->code }} - {{ $r->name }}</td>
                            <td class="px-2 py-1 text-right">{{ number_format($r->net, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-semibold">
                        <td class="px-2 py-2">Total Beban</td>
                        <td class="px-2 py-2 text-right">{{ number_format($totalExpense, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </x-card>
    </div>

    <div class="mt-4">
        <x-card title="Laba/Rugi Bersih">
            <div class="text-2xl font-semibold">{{ number_format($profit, 2, ',', '.') }}</div>
        </x-card>
    </div>
@endsection

