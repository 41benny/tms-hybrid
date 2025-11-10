@extends('layouts.app', ['title' => 'Kas/Bank'])

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">Kas/Bank</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Transaksi kas masuk/keluar</p>
        </div>
        <a href="{{ route('cash-banks.create') }}" class="px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-500">+ Transaksi</a>
    </div>

    <x-card>
        <form method="get" class="grid grid-cols-1 md:grid-cols-6 gap-3">
            <select name="cash_bank_account_id" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                <option value="">Akun</option>
                @foreach($accounts as $a)
                    <option value="{{ $a->id }}" @selected(request('cash_bank_account_id')==$a->id)>{{ $a->name }}</option>
                @endforeach
            </select>
            <select name="sumber" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                <option value="">Sumber</option>
                @foreach(['customer_payment','vendor_payment','expense','other_in','other_out'] as $s)
                    <option value="{{ $s }}" @selected(request('sumber')==$s)>{{ str_replace('_',' ', $s) }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" />
            <input type="date" name="to" value="{{ request('to') }}" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" />
            <div></div>
            <button class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-800">Filter</button>
        </form>
    </x-card>

    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
        <x-card title="Ringkasan">
            <div class="text-sm space-y-1">
                <div>Total Masuk: <b>{{ number_format($summary['in'] ?? 0, 2, ',', '.') }}</b></div>
                <div>Total Keluar: <b>{{ number_format($summary['out'] ?? 0, 2, ',', '.') }}</b></div>
                <div>Net: <b>{{ number_format($summary['net'] ?? 0, 2, ',', '.') }}</b></div>
            </div>
        </x-card>
    </div>

    <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/80">
        <table class="min-w-full text-sm">
            <thead class="text-left border-b border-slate-200 dark:border-slate-800">
                <tr class="text-slate-500">
                    <th class="px-4 py-2">Tanggal</th>
                    <th class="px-4 py-2">Akun</th>
                    <th class="px-4 py-2">Jenis</th>
                    <th class="px-4 py-2">Sumber</th>
                    <th class="px-4 py-2">Nominal</th>
                    <th class="px-4 py-2">Ref</th>
                    <th class="px-4 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @foreach($transactions as $t)
                <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                    <td class="px-4 py-2">{{ $t->tanggal->format('d M Y') }}</td>
                    <td class="px-4 py-2">{{ $t->account->name ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $t->jenis }}</td>
                    <td class="px-4 py-2">{{ str_replace('_',' ', $t->sumber) }}</td>
                    <td class="px-4 py-2">{{ number_format($t->amount, 2, ',', '.') }}</td>
                    <td class="px-4 py-2">{{ $t->reference_number }}</td>
                    <td class="px-4 py-2"><a class="underline" href="{{ route('cash-banks.show',$t) }}">Lihat</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $transactions->links() }}</div>
@endsection
