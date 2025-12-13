@extends('layouts.app', ['title' => 'Detail Jurnal'])

@section('content')
<div class="space-y-6">
    {{-- Header Card --}}
    <x-card>
        <x-slot:header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="text-2xl font-bold text-slate-900 dark:text-slate-100">Detail Jurnal</div>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $journal->journal_no }}</p>
                </div>
                <div class="flex gap-2">
                    @if($journal->source_type === 'adjustment' || $journal->status === 'draft')
                        <x-button :href="route('journals.edit', $journal)" variant="outline" size="sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit
                        </x-button>
                    @endif
                    <x-button :href="url()->previous()" variant="ghost" size="sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Kembali
                    </x-button>
                </div>
            </div>
        </x-slot:header>
    </x-card>

    <x-card title="Informasi Jurnal">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm text-slate-500 dark:text-slate-400">No Jurnal</label>
                <p class="font-semibold">{{ $journal->journal_no }}</p>
            </div>
            <div>
                <label class="text-sm text-slate-500 dark:text-slate-400">Tanggal</label>
                <p class="font-semibold">{{ $journal->journal_date->format('d M Y') }}</p>
            </div>
            <div>
                <label class="text-sm text-slate-500 dark:text-slate-400">Status</label>
                <p>
                    <x-badge :variant="$journal->status === 'posted' ? 'success' : ($journal->status === 'void' ? 'danger' : 'default')">
                        {{ ucfirst($journal->status) }}
                    </x-badge>
                </p>
            </div>
            <div>
                <label class="text-sm text-slate-500 dark:text-slate-400">Mata Uang</label>
                <p class="font-semibold">{{ $journal->currency }}</p>
            </div>
            @if($sourceReference)
                <div>
                    <label class="text-sm text-slate-500 dark:text-slate-400">Sumber Transaksi</label>
                    <p>
                        <a href="{{ $sourceReference['url'] }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                            {{ $sourceReference['type'] }}: {{ $sourceReference['number'] }}
                        </a>
                    </p>
                </div>
            @endif
            @if($journal->memo)
                <div class="md:col-span-2">
                    <label class="text-sm text-slate-500 dark:text-slate-400">Memo/Keterangan</label>
                    <p>{{ $journal->memo }}</p>
                </div>
            @endif
        </div>
    </x-card>

    <x-card title="Detail Jurnal">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border border-slate-200 dark:border-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800">
                    <tr>
                        <th class="px-4 py-2 text-left border-b">Kode Akun</th>
                        <th class="px-4 py-2 text-left border-b">Nama Akun</th>
                        <th class="px-4 py-2 text-right border-b">Debit</th>
                        <th class="px-4 py-2 text-right border-b">Kredit</th>
                        <th class="px-4 py-2 text-left border-b">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($journal->lines as $line)
                        <tr class="border-b border-slate-100 dark:border-slate-800">
                            <td class="px-4 py-2 font-medium">{{ $line->account->code }}</td>
                            <td class="px-4 py-2">{{ $line->account->name }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format($line->debit, 2, ',', '.') }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format($line->credit, 2, ',', '.') }}</td>
                            <td class="px-4 py-2">{{ $line->description }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 dark:bg-slate-800 font-semibold">
                    <tr>
                        <td colspan="2" class="px-4 py-2 text-right">Total</td>
                        <td class="px-4 py-2 text-right">{{ number_format($journal->total_debit, 2, ',', '.') }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($journal->total_credit, 2, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-card>
</div>
@endsection

