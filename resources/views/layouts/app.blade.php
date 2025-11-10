<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'TMS') }}</title>
    <script>
        // Apply saved theme ASAP to avoid flash
        (function() {
            try {
                var key = 'tms-theme';
                var saved = localStorage.getItem(key);
                var isDark = saved ? saved === 'dark' : true; // default: dark
                var root = document.documentElement;
                if (isDark) root.classList.add('dark'); else root.classList.remove('dark');
            } catch (e) {
                // no-op
            }
        })();
    </script>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="h-full bg-white text-slate-900 dark:bg-slate-900 dark:text-slate-100">
<div class="min-h-screen flex">
    <aside class="hidden md:block w-64 shrink-0 border-r border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950">
        <div class="p-4 text-lg font-semibold">TMS</div>
        <nav class="px-2 space-y-1">
            <a href="/" class="block rounded px-3 py-2 hover:bg-slate-200 dark:hover:bg-slate-800">Dashboard</a>
            <div class="mt-3 text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400 px-3">Master</div>
            <a href="{{ route('customers.index') }}" class="block rounded px-3 py-2 hover:bg-slate-200 dark:hover:bg-slate-800">Customers</a>
            <a href="{{ route('vendors.index') }}" class="block rounded px-3 py-2 hover:bg-slate-200 dark:hover:bg-slate-800">Vendors</a>
            <a href="{{ route('trucks.index') }}" class="block rounded px-3 py-2 hover:bg-slate-200 dark:hover:bg-slate-800">Trucks</a>
            <a href="{{ route('drivers.index') }}" class="block rounded px-3 py-2 hover:bg-slate-200 dark:hover:bg-slate-800">Drivers</a>
            <div class="mt-3 text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400 px-3">Operations</div>
            <a href="{{ route('job-orders.index') }}" class="block rounded px-3 py-2 hover:bg-slate-200 dark:hover:bg-slate-800">Job Orders</a>
            <a href="{{ route('transports.index') }}" class="block rounded px-3 py-2 hover:bg-slate-200 dark:hover:bg-slate-800">Transports</a>
            <div class="mt-3 text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400 px-3">Finance</div>
            <a href="{{ route('invoices.index') }}" class="block rounded px-3 py-2 hover:bg-slate-200 dark:hover:bg-slate-800">Invoices</a>
            <a href="{{ route('vendor-bills.index') }}" class="block rounded px-3 py-2 hover:bg-slate-200 dark:hover:bg-slate-800">Vendor Bills</a>
            <a href="{{ route('cash-banks.index') }}" class="block rounded px-3 py-2 hover:bg-slate-200 dark:hover:bg-slate-800">Cash/Bank</a>
            <div class="mt-3 text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400 px-3">Reports</div>
            <a href="{{ route('reports.trial-balance') }}" class="block rounded px-3 py-2 hover:bg-slate-200 dark:hover:bg-slate-800">Trial Balance</a>
            <a href="{{ route('reports.profit-loss') }}" class="block rounded px-3 py-2 hover:bg-slate-200 dark:hover:bg-slate-800">Profit &amp; Loss</a>
            <a href="{{ route('reports.balance-sheet') }}" class="block rounded px-3 py-2 hover:bg-slate-200 dark:hover:bg-slate-800">Balance Sheet</a>
            <div class="mt-3 text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400 px-3">AI</div>
            <a href="{{ route('ai-assistant.index') }}" class="block rounded px-3 py-2 hover:bg-slate-200 dark:hover:bg-slate-800">AI Assistant</a>
        </nav>
    </aside>

    <main class="flex-1 min-w-0">
        <header class="sticky top-0 z-10 border-b border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <button class="md:hidden p-2 rounded hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Open menu">☰</button>
                    <div class="font-semibold">{{ $header ?? ($title ?? 'Dashboard') }}</div>
                </div>
                <div class="flex items-center gap-2">
                    <button id="theme-toggle" class="p-2 rounded hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Toggle dark mode">
                        <span class="inline dark:hidden">🌙</span>
                        <span class="hidden dark:inline">☀️</span>
                    </button>
                    <div class="text-sm text-slate-600 dark:text-slate-300">{{ auth()->user()->name ?? 'Guest' }}</div>
                </div>
            </div>
        </header>
        <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
            {{ $slot ?? '' }}
            @yield('content')
        </div>
    </main>
</div>
</body>
</html>
