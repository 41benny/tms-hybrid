@extends('layouts.app', ['title' => 'Customers'])

@section('content')
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">Customers</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Master data pelanggan</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('customers.create') }}" class="px-4 py-2 rounded bg-indigo-600 hover:bg-indigo-700 text-white">+ Tambah Baru</a>
            <form method="get" class="flex items-center gap-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama..." class="rounded bg-transparent border border-slate-300/50 dark:border-slate-700 px-2 py-2">
                <button class="px-3 py-2 rounded bg-slate-200 dark:bg-slate-800">Cari</button>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/80">
        <table class="min-w-full text-sm">
            <thead class="text-left border-b border-slate-200 dark:border-slate-800">
                <tr class="text-slate-500">
                    <th class="px-4 py-2">Nama</th>
                    <th class="px-4 py-2">Telepon</th>
                    <th class="px-4 py-2">Email</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $it)
                    <tr class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <td class="px-4 py-2">{{ $it->name }}</td>
                        <td class="px-4 py-2">{{ $it->phone }}</td>
                        <td class="px-4 py-2">{{ $it->email }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
@endsection

