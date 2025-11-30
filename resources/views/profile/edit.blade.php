@extends('layouts.app', ['title' => 'Profil Saya'])

@section('content')
    <div class="max-w-3xl mx-auto">
        <x-card>
            <x-slot:header>
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Profil Pengguna</h1>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Perbarui informasi akun dan foto profil Anda.</p>
                    </div>
                </div>
            </x-slot:header>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div class="flex items-center gap-6">
                    <div class="relative">
                        @php $avatarUrl = $user->avatarUrl(); @endphp
                        @if($avatarUrl)
                            <img
                                src="{{ $avatarUrl }}"
                                alt="Avatar"
                                class="w-20 h-20 rounded-full object-cover border border-slate-200 dark:border-slate-700 shadow-sm"
                            >
                        @else
                            <div class="w-20 h-20 rounded-full flex items-center justify-center text-2xl font-bold text-slate-900 dark:text-slate-100 border border-slate-200 dark:border-slate-700 shadow-sm" style="background: var(--color-primary);">
                                {{ substr($user->name ?? 'U', 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Foto Profil</label>
                        <input
                            type="file"
                            name="avatar"
                            accept="image/*"
                            class="block w-full text-sm text-slate-700 dark:text-slate-200 file:mr-4 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/40 dark:file:text-indigo-200"
                        >
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Maksimal 2 MB. Format: JPG, PNG.</p>
                        @error('avatar')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama</label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            class="w-full rounded-lg bg-[var(--bg-surface-secondary)] border border-[var(--border-color)] px-3 py-2 text-sm text-[var(--color-text-main)] focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            required
                        >
                        @error('name')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email', $user->email) }}"
                            class="w-full rounded-lg bg-[var(--bg-surface-secondary)] border border-[var(--border-color)] px-3 py-2 text-sm text-[var(--color-text-main)] focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            required
                        >
                        @error('email')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Password Baru</label>
                        <input
                            type="password"
                            name="password"
                            class="w-full rounded-lg bg-[var(--bg-surface-secondary)] border border-[var(--border-color)] px-3 py-2 text-sm text-[var(--color-text-main)] focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Biarkan kosong jika tidak diubah"
                        >
                        @error('password')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Konfirmasi Password</label>
                        <input
                            type="password"
                            name="password_confirmation"
                            class="w-full rounded-lg bg-[var(--bg-surface-secondary)] border border-[var(--border-color)] px-3 py-2 text-sm text-[var(--color-text-main)] focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-between">
                    @if (session('success'))
                        <p class="text-xs text-emerald-600 dark:text-emerald-400">{{ session('success') }}</p>
                    @endif
                    <x-button type="submit" variant="primary" size="sm" class="ml-auto">
                        Simpan Perubahan
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection
