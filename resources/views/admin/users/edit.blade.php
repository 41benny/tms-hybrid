@php($title = 'Edit User')
@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Edit User</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Perbarui informasi akun dan akses menu.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 dark:border-[#2d2d2d] px-4 py-2 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#2d2d2d]">
            Kembali
        </a>
    </div>

    @php
        $isPrimarySuperAdmin = $user->id === 1 && $user->isSuperAdmin();
    @endphp

    @if($isPrimarySuperAdmin)
        <div class="mb-6 p-4 rounded-xl border-2 border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div>
                    <h4 class="font-semibold text-amber-900 dark:text-amber-100">Akun Super Admin Utama</h4>
                    <p class="text-sm text-amber-800 dark:text-amber-200 mt-1">
                        Ini adalah akun Super Admin utama sistem. Email dan status akun tidak dapat diubah untuk menjaga keamanan sistem. 
                        Anda hanya dapat mengubah nama dan password.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.users._form', ['user' => $user, 'isPrimarySuperAdmin' => $isPrimarySuperAdmin])
        <div class="flex items-center justify-end gap-3">
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-indigo-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-indigo-500/30 hover:shadow-md">
                Simpan Perubahan
            </button>
        </div>
    </form>
@endsection

