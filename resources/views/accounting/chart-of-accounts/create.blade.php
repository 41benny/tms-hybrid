@extends('layouts.app', ['title' => 'Tambah Akun COA'])

@section('content')
<div class="space-y-6">
    <x-card>
        <x-slot:header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="text-2xl font-bold text-slate-900 dark:text-slate-100">Tambah Akun COA</div>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Definisikan akun baru untuk dipakai di seluruh modul</p>
                </div>
                <x-button :href="route('chart-of-accounts.index')" variant="ghost">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Tutup
                </x-button>
            </div>
        </x-slot:header>
    </x-card>

    @include('accounting.chart-of-accounts._form', [
        'account' => $account,
        'types' => $types,
        'statuses' => $statuses,
        'parentOptions' => $parentOptions,
        'action' => route('chart-of-accounts.store'),
        'method' => 'POST',
        'submitLabel' => 'Simpan Akun',
    ])
</div>
@endsection

