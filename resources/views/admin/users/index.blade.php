@php($title = 'Manajemen User')
@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Manajemen User</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Kelola akun, role, dan otorisasi menu.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-indigo-700 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-indigo-500/30 hover:shadow-md">
            Tambah User
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 text-green-700 text-sm p-3">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->has('user'))
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 text-red-700 text-sm p-3">
            {{ $errors->first('user') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-[#2d2d2d] text-sm">
            <thead class="bg-slate-100 dark:bg-[#2d2d2d] text-slate-600 dark:text-slate-300 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3 text-left">Nama</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Role</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Menu Diizinkan</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-[#2d2d2d] text-slate-700 dark:text-slate-300">
                @forelse ($users as $user)
                    <tr class="hover:bg-slate-50 dark:hover:bg-[#2d2d2d]/60 transition-colors">
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900 dark:text-slate-100">{{ $user->name }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $user->isSuperAdmin() ? 'Super Admin' : ucfirst($user->role) }}
                            </div>
                        </td>
                        <td class="px-4 py-3">{{ $user->email }}</td>
                        <td class="px-4 py-3 capitalize">{{ str_replace('_', ' ', $user->role) }}</td>
                        <td class="px-4 py-3">
                            <span @class([
                                'inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium',
                                'bg-green-100 text-green-700' => $user->is_active,
                                'bg-red-100 text-red-700' => ! $user->is_active,
                            ])>
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if ($user->isSuperAdmin())
                                <span class="text-xs text-slate-400 dark:text-slate-500">Semua menu</span>
                            @else
                                <span class="text-xs text-slate-500 dark:text-slate-400">{{ $user->menus->count() }} menu</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-3">
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline text-xs font-semibold">
                                    Edit
                                </a>
                                @if (! $user->isSuperAdmin())
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus user ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 dark:text-red-400 hover:underline text-xs font-semibold">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">
                            Belum ada user.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $users->links() }}
    </div>
@endsection

