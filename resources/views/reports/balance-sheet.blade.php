@extends('layouts.app', ['title' => 'Neraca'])

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">Neraca</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Per {{ \Carbon\Carbon::parse($asOf)->format('d M Y') }}</p>
        </div>
        <form method="get" class="flex items-center gap-2">
            <input type="date" name="as_of" value="{{ $asOf }}" class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2" />
            <button class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-800">Terapkan</button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-card title="Aset">
            <table class="min-w-full text-sm">
                <tbody>
                    @foreach($sections['asset'] as $r)
                        <tr class="border-b border-slate-200 dark:border-slate-800">
                            <td class="px-2 py-1">{{ $r['acc']->code }} - {{ $r['acc']->name }}</td>
                            <td class="px-2 py-1 text-right">{{ number_format($r['balance'], 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-semibold">
                        <td class="px-2 py-2">Total Aset</td>
                        <td class="px-2 py-2 text-right">{{ number_format($totals['asset'], 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </x-card>

        <x-card title="Kewajiban">
            <table class="min-w-full text-sm">
                <tbody>
                    @foreach($sections['liability'] as $r)
                        <tr class="border-b border-slate-200 dark:border-slate-800">
                            <td class="px-2 py-1">{{ $r['acc']->code }} - {{ $r['acc']->name }}</td>
                            <td class="px-2 py-1 text-right">{{ number_format($r['balance'], 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-semibold">
                        <td class="px-2 py-2">Total Kewajiban</td>
                        <td class="px-2 py-2 text-right">{{ number_format($totals['liability'], 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </x-card>

        <x-card title="Ekuitas">
            <table class="min-w-full text-sm">
                <tbody>
                    @foreach($sections['equity'] as $r)
                        <tr class="border-b border-slate-200 dark:border-slate-800">
                            <td class="px-2 py-1">{{ $r['acc']->code }} - {{ $r['acc']->name }}</td>
                            <td class="px-2 py-1 text-right">{{ number_format($r['balance'], 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-semibold">
                        <td class="px-2 py-2">Total Ekuitas</td>
                        <td class="px-2 py-2 text-right">{{ number_format($totals['equity'], 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </x-card>
    </div>
@endsection

