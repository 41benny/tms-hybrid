@extends('layouts.app', ['title' => 'Trial Balance'])

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div>
            <div class="text-xl font-semibold">Trial Balance</div>
            <p class="text-sm text-slate-500 dark:text-slate-400">Periode {{ \Carbon\Carbon::parse($from)->format('d M Y') }} – {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>
        </div>
        <form method="get" class="flex items-center gap-2">
            <input type="date" name="from" value="{{ $from }}" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" />
            <input type="date" name="to" value="{{ $to }}" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" />
            <button class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-800">Terapkan</button>
        </form>
    </div>

    <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
        <table class="min-w-full text-sm">
            <thead class="text-left border-b border-slate-200 dark:border-slate-800">
                <tr class="text-slate-500">
                    <th class="px-4 py-2">Kode</th>
                    <th class="px-4 py-2">Nama Akun</th>
                    <th class="px-4 py-2 text-right">Saldo Awal</th>
                    <th class="px-4 py-2 text-right">Debit</th>
                    <th class="px-4 py-2 text-right">Kredit</th>
                    <th class="px-4 py-2 text-right">Saldo Akhir</th>
                </tr>
            </thead>
            <tbody>
            @foreach($rows as $r)
                <tr class="border-b border-slate-100 dark:border-slate-800">
                    <td class="px-4 py-2">
                        <a class="underline" href="{{ route('reports.general-ledger', ['account_id'=>$r['acc']->id, 'from'=>$from, 'to'=>$to]) }}">{{ $r['acc']->code }}</a>
                    </td>
                    <td class="px-4 py-2">{{ $r['acc']->name }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($r['opening'], 2, ',', '.') }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($r['debit'], 2, ',', '.') }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($r['credit'], 2, ',', '.') }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($r['closing'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr class="font-semibold">
                    <td class="px-4 py-2" colspan="2">Total</td>
                    <td class="px-4 py-2 text-right">{{ number_format($tot['opening'], 2, ',', '.') }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($tot['debit'], 2, ',', '.') }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($tot['credit'], 2, ',', '.') }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($tot['closing'], 2, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
@endsection
