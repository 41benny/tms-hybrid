@extends('layouts.app', ['title' => 'General Ledger'])

@section('content')
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <div class="text-2xl font-semibold text-slate-900 dark:text-white">General Ledger</div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Detail transaksi per akun dengan running balance</p>
            </div>
        </div>

        {{-- Filter Section --}}
        <div class="bg-white dark:bg-[#252525] rounded-lg border border-slate-200 dark:border-[#2d2d2d] p-4 mb-6">
            <form method="GET" action="{{ route('reports.general-ledger') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Akun</label>
                    <select name="account_id" required class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Pilih Akun --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>
                                {{ $acc->code }} - {{ $acc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Dari Tanggal</label>
                    <input type="date" name="from" value="{{ $from }}" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Sampai Tanggal</label>
                    <input type="date" name="to" value="{{ $to }}" class="w-full rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
                        Tampilkan
                    </button>
                    @if(request('account_id'))
                    <a href="{{ route('reports.general-ledger') }}" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg">
                        Reset
                    </a>
                    @endif
                </div>
            </form>
        </div>

        @if(isset($account))
            {{-- Account Info & Export --}}
            <div class="bg-white dark:bg-[#252525] rounded-lg border border-slate-200 dark:border-[#2d2d2d] p-4 mb-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $account->code }} - {{ $account->name }}</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Periode: {{ \Carbon\Carbon::parse($from)->format('d M Y') }} - {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>
                    </div>
                </div>
            </div>

            {{-- Ledger Table --}}
            <div class="bg-white dark:bg-[#252525] rounded-lg border border-slate-200 dark:border-[#2d2d2d] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-[#2d2d2d]">
                        <thead class="bg-slate-50 dark:bg-[#1e1e1e]">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">No Jurnal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Keterangan</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Debit</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Kredit</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-400 uppercase">Saldo</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-[#252525] divide-y divide-slate-200 dark:divide-[#2d2d2d]">
                            @php $runningBalance = $opening; @endphp

                            {{-- Opening Balance --}}
                            <tr class="bg-slate-50 dark:bg-[#1e1e1e]">
                                <td colspan="5" class="px-4 py-3 text-sm font-semibold text-slate-900 dark:text-white">Saldo Awal</td>
                                <td class="px-4 py-3 text-sm text-right font-semibold {{ $runningBalance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format(abs($runningBalance), 2, ',', '.') }}
                                </td>
                            </tr>

                            @forelse($entries as $entry)
                                @php $runningBalance += ($entry->debit - $entry->credit); @endphp
                                <tr class="hover:bg-slate-50 dark:hover:bg-[#1e1e1e]">
                                    <td class="px-4 py-3 text-sm text-slate-900 dark:text-white">
                                        {{ \Carbon\Carbon::parse($entry->journal_date)->format('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <a href="{{ route('journals.show', $entry->journal_id) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                            {{ $entry->journal_no }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                                        {{ $entry->memo ?: $entry->description }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        {{ $entry->debit > 0 ? number_format($entry->debit, 2, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        {{ $entry->credit > 0 ? number_format($entry->credit, 2, ',', '.') : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right font-medium {{ $runningBalance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format(abs($runningBalance), 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">Tidak ada transaksi</td>
                                </tr>
                            @endforelse

                            {{-- Closing Balance --}}
                            @if($entries->count() > 0)
                            <tr class="bg-slate-50 dark:bg-[#1e1e1e] font-semibold">
                                <td colspan="3" class="px-4 py-3 text-sm text-slate-900 dark:text-white">Saldo Akhir</td>
                                <td class="px-4 py-3 text-sm text-right">{{ number_format($entries->sum('debit'), 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-right">{{ number_format($entries->sum('credit'), 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-right {{ $runningBalance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format(abs($runningBalance), 2, ',', '.') }}
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            {{-- Empty State --}}
            <div class="bg-white dark:bg-[#252525] rounded-lg border border-slate-200 dark:border-[#2d2d2d] p-12">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-slate-900 dark:text-white">Pilih Akun</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Silakan pilih akun dari dropdown untuk menampilkan general ledger</p>
                </div>
            </div>
        @endif
    </div>
@endsection

