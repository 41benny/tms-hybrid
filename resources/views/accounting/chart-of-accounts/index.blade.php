@extends('layouts.app', ['title' => 'Chart of Accounts'])

@section('content')
<div class="space-y-6">
    <x-card>
        <x-slot:header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Chart of Accounts</h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Kelola daftar akun dan struktur COA</p>
                </div>
                <x-button :href="route('chart-of-accounts.create')" variant="primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Akun
                </x-button>
            </div>
        </x-slot:header>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div class="rounded-xl border border-slate-200 dark:border-[#2d2d2d] bg-gradient-to-br from-indigo-50 to-white dark:from-indigo-900/20 dark:to-transparent p-4">
                <p class="text-xs uppercase text-slate-500 dark:text-slate-400">Total Akun</p>
                <p class="text-2xl font-semibold text-slate-900 dark:text-slate-100 mt-1">{{ number_format($stats['total']) }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200 dark:border-emerald-900/40 bg-emerald-50/70 dark:bg-emerald-900/10 p-4">
                <p class="text-xs uppercase text-emerald-600 dark:text-emerald-300">Aktif</p>
                <p class="text-2xl font-semibold text-emerald-700 dark:text-emerald-200 mt-1">{{ number_format($stats['active']) }}</p>
            </div>
            <div class="rounded-xl border border-amber-200 dark:border-amber-900/40 bg-amber-50/80 dark:bg-amber-900/10 p-4">
                <p class="text-xs uppercase text-amber-600 dark:text-amber-300">Header/Non Postable</p>
                <p class="text-2xl font-semibold text-amber-700 dark:text-amber-200 mt-1">{{ number_format($stats['non_postable']) }}</p>
            </div>
        </div>
    </x-card>

    @if(session('success'))
        <x-alert variant="success">{{ session('success') }}</x-alert>
    @endif

    <x-card>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label class="text-xs uppercase text-slate-500 dark:text-slate-400">Cari Kode / Nama</label>
                <input 
                    type="text" 
                    name="q" 
                    value="{{ request('q') }}" 
                    placeholder="Contoh: 1100 atau Kas" 
                    class="mt-2 w-full rounded-lg border border-slate-300 dark:border-[#3d3d3d] bg-transparent px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
                >
            </div>
            <div>
                <label class="text-xs uppercase text-slate-500 dark:text-slate-400">Tipe</label>
                <select 
                    name="type" 
                    class="mt-2 w-full rounded-lg border border-slate-300 dark:border-[#3d3d3d] bg-transparent px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="">Semua</option>
                    @foreach($types as $key => $label)
                        <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs uppercase text-slate-500 dark:text-slate-400">Status</label>
                <select 
                    name="status" 
                    class="mt-2 w-full rounded-lg border border-slate-300 dark:border-[#3d3d3d] bg-transparent px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="">Semua</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-4 flex items-center justify-end gap-3">
                <a href="{{ route('chart-of-accounts.index') }}" class="text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-white">Reset</a>
                <x-button type="submit" variant="outline">Terapkan Filter</x-button>
            </div>
        </form>
    </x-card>

    <x-card :noPadding="true">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-[#2d2d2d]">
            <thead class="bg-slate-50 dark:bg-[#252525]">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kode</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tipe</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Parent</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Flag</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-[#1e1e1e] divide-y divide-slate-200 dark:divide-[#2d2d2d]">
                @forelse($accounts as $account)
                    <tr class="hover:bg-slate-50 dark:hover:bg-[#252525] transition-colors">
                        <td class="px-6 py-4 font-semibold text-slate-900 dark:text-slate-100">{{ $account->code }}</td>
                        <td class="px-6 py-4">
                            <div class="text-slate-900 dark:text-slate-100 font-medium">{{ $account->name }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                Level {{ $account->level }} &middot; {{ $account->is_postable ? 'Postable' : 'Header' }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <x-badge>{{ $types[$account->type] ?? strtoupper($account->type) }}</x-badge>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">
                            @if($account->parent)
                                {{ $account->parent->code }} - {{ $account->parent->name }}
                            @else
                                <span class="text-xs uppercase tracking-wide text-slate-400">Root</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-2">
                                @if($account->is_cash)
                                    <x-badge variant="success">Kas</x-badge>
                                @endif
                                @if($account->is_bank)
                                    <x-badge>Bank</x-badge>
                                @endif
                                @unless($account->is_cash || $account->is_bank)
                                    <span class="text-xs text-slate-400">-</span>
                                @endunless
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <x-badge :variant="$account->status === 'active' ? 'success' : 'default'">
                                {{ $statuses[$account->status] ?? $account->status }}
                            </x-badge>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <x-button :href="route('chart-of-accounts.edit', $account)" variant="ghost" size="sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </x-button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                            <div class="flex flex-col items-center gap-3">
                                <span class="text-4xl">🗂️</span>
                                <p class="text-sm">Belum ada akun. Tambahkan akun pertama Anda.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($accounts->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 dark:border-[#2d2d2d]">
                {{ $accounts->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection

