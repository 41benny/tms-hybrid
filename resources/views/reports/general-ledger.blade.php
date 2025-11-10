@extends('layouts.app', ['title' => 'General Ledger'])

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">General Ledger</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Akun {{ $account->code }} - {{ $account->name }}</p>
        </div>
        <form method="get" class="flex items-center gap-2">
            <input type="hidden" name="account_id" value="{{ $account->id }}" />
            <input type="date" name="from" value="{{ $from }}" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" />
            <input type="date" name="to" value="{{ $to }}" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" />
            <button class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-800">Terapkan</button>
        </form>
    </div>

    <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
        <table class="min-w-full text-sm">
            <thead class="text-left border-b border-slate-200 dark:border-slate-800">
                <tr class="text-slate-500">
                    <th class="px-4 py-2">Tanggal</th>
                    <th class="px-4 py-2">No Jurnal</th>
                    <th class="px-4 py-2">Keterangan</th>
                    <th class="px-4 py-2 text-right">Debit</th>
                    <th class="px-4 py-2 text-right">Kredit</th>
                    <th class="px-4 py-2 text-right">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @php $run = $opening; @endphp
                <tr class="border-b border-slate-200 dark:border-slate-800">
                    <td class="px-4 py-2" colspan="5">Saldo Awal</td>
                    <td class="px-4 py-2 text-right">{{ number_format($run, 2, ',', '.') }}</td>
                </tr>
                @foreach($entries as $e)
                    @php $run += ($e->debit - $e->credit); @endphp
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <td class="px-4 py-2">{{ \Carbon\Carbon::parse($e->journal_date)->format('d M Y') }}</td>
                        <td class="px-4 py-2">{{ $e->journal_no }}</td>
                        <td class="px-4 py-2">{{ $e->memo ?: $e->description }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($e->debit, 2, ',', '.') }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($e->credit, 2, ',', '.') }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($run, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

