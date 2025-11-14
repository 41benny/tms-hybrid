@php($title = 'Tambah User')
@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Tambah User</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Buat akun baru dan atur akses menu.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 dark:border-[#2d2d2d] px-4 py-2 text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#2d2d2d]">
            Kembali
        </a>
    </div>

    <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('admin.users._form', ['user' => new \App\Models\User(), 'selectedMenus' => []])
        <div class="flex items-center justify-end gap-3">
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-indigo-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-indigo-500/30 hover:shadow-md">
                Simpan
            </button>
        </div>
    </form>
@endsection

