@php($title = 'Masuk')
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} &mdash; {{ config('app.name', 'TMS') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gradient-to-br from-slate-50 via-slate-100 to-slate-50 dark:bg-[#1e1e1e]">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="w-full max-w-md space-y-6">
            <div class="text-center space-y-2">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-600 to-indigo-800 shadow-lg shadow-indigo-500/30">
                    <span class="text-white text-2xl font-semibold">T</span>
                </div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ config('app.name', 'TMS') }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Silakan masuk untuk melanjutkan</p>
            </div>
            <div class="bg-white dark:bg-[#252525] rounded-2xl shadow-xl border border-slate-200 dark:border-[#2d2d2d] p-8 space-y-6">
                @if ($errors->any())
                    <div class="rounded-lg border border-red-200 bg-red-50 text-red-700 text-sm p-3">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                    @csrf
                    <div class="space-y-2">
                        <label for="email" class="text-sm font-medium text-slate-600 dark:text-slate-300">Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            class="w-full rounded-xl border border-slate-200 dark:border-[#2d2d2d] bg-white dark:bg-[#1e1e1e] px-4 py-3 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                    </div>
                    <div class="space-y-2">
                        <label for="password" class="text-sm font-medium text-slate-600 dark:text-slate-300">Password</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            class="w-full rounded-xl border border-slate-200 dark:border-[#2d2d2d] bg-white dark:bg-[#1e1e1e] px-4 py-3 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <label class="inline-flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            Ingat saya
                        </label>
                        <span class="text-slate-400 dark:text-slate-500">Lupa password?</span>
                    </div>
                    <button type="submit" class="w-full inline-flex justify-center items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all">
                        Masuk
                    </button>
                </form>
            </div>
            <p class="text-center text-xs text-slate-400 dark:text-slate-500">
                &copy; {{ date('Y') }} {{ config('app.name', 'TMS') }}. Semua hak dilindungi.
            </p>
        </div>
    </div>
</body>
</html>

