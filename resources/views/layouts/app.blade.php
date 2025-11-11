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
<body class="h-full bg-gradient-to-br from-slate-50 via-slate-100 to-slate-50 dark:bg-[#1e1e1e] text-slate-900 dark:text-slate-100">
<div class="min-h-screen flex">
    {{-- Sidebar --}}
    <aside class="hidden md:flex flex-col w-64 shrink-0 border-r border-slate-200 dark:border-[#2d2d2d] bg-white dark:bg-[#1e1e1e] shadow-lg">
        {{-- Logo / Brand --}}
        <div class="p-6 border-b border-slate-200 dark:border-[#2d2d2d]">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-600 to-indigo-800 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                    <span class="text-white text-lg">T</span>
                </div>
                <div>
                    <div class="text-lg bg-gradient-to-r from-indigo-600 to-indigo-800 bg-clip-text text-transparent">TMS</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">Transport System</div>
                </div>
            </div>
        </div>
        
        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <a href="/" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->is('/') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }}">
                <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->is('/') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->is('/') ? 'text-slate-900 dark:text-white' : '' }} text-sm">Dashboard</span>
            </a>
            
            {{-- Master Section --}}
            <div class="pt-4">
                <div class="px-3 pb-2 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-500">Master Data</div>
                <a href="{{ route('customers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('customers.*') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('customers.*') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('customers.*') ? 'text-slate-900 dark:text-white' : '' }} text-sm">Customers</span>
                </a>
                <a href="{{ route('vendors.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('vendors.*') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('vendors.*') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('vendors.*') ? 'text-slate-900 dark:text-white' : '' }} text-sm">Vendors</span>
                </a>
                <a href="{{ route('trucks.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('trucks.*') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('trucks.*') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('trucks.*') ? 'text-slate-900 dark:text-white' : '' }} text-sm">Trucks</span>
                </a>
                <a href="{{ route('drivers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('drivers.*') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('drivers.*') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('drivers.*') ? 'text-slate-900 dark:text-white' : '' }} text-sm">Drivers</span>
                </a>
                <a href="{{ route('sales.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('sales.*') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('sales.*') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('sales.*') ? 'text-slate-900 dark:text-white' : '' }} text-sm">Sales</span>
                </a>
                <a href="{{ route('equipment.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('equipment.*') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('equipment.*') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('equipment.*') ? 'text-slate-900 dark:text-white' : '' }} text-sm">Equipment</span>
                </a>
            </div>
            
            {{-- Operations Section --}}
            <div class="pt-4">
                <div class="px-3 pb-2 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-500">Operations</div>
                <a href="{{ route('job-orders.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('job-orders.*') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('job-orders.*') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('job-orders.*') ? 'text-slate-900 dark:text-white' : '' }} text-sm">Job Orders</span>
                </a>
                <a href="{{ route('transports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('transports.*') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('transports.*') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('transports.*') ? 'text-slate-900 dark:text-white' : '' }} text-sm">Transports</span>
                </a>
            </div>
            
            {{-- Finance Section --}}
            <div class="pt-4">
                <div class="px-3 pb-2 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-500">Finance</div>
                <a href="{{ route('invoices.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('invoices.*') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('invoices.*') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('invoices.*') ? 'text-slate-900 dark:text-white' : '' }} text-sm">Invoices</span>
                </a>
                <a href="{{ route('vendor-bills.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('vendor-bills.*') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('vendor-bills.*') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('vendor-bills.*') ? 'text-slate-900 dark:text-white' : '' }} text-sm">Vendor Bills</span>
                </a>
                <a href="{{ route('cash-banks.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('cash-banks.*') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('cash-banks.*') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('cash-banks.*') ? 'text-slate-900 dark:text-white' : '' }} text-sm">Cash/Bank</span>
                </a>
            </div>
            
            {{-- Reports Section --}}
            <div class="pt-4">
                <div class="px-3 pb-2 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-500">Reports</div>
                <a href="{{ route('reports.trial-balance') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('reports.trial-balance') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('reports.trial-balance') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('reports.trial-balance') ? 'text-slate-900 dark:text-white' : '' }} text-sm">Trial Balance</span>
                </a>
                <a href="{{ route('reports.profit-loss') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('reports.profit-loss') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('reports.profit-loss') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('reports.profit-loss') ? 'text-slate-900 dark:text-white' : '' }} text-sm">Profit & Loss</span>
                </a>
                <a href="{{ route('reports.balance-sheet') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('reports.balance-sheet') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('reports.balance-sheet') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('reports.balance-sheet') ? 'text-slate-900 dark:text-white' : '' }} text-sm">Balance Sheet</span>
                </a>
            </div>
            
            {{-- AI Section --}}
            <div class="pt-4 pb-4">
                <div class="px-3 pb-2 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-500">AI Tools</div>
                <a href="{{ route('ai-assistant.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/30 hover:shadow-xl hover:shadow-indigo-500/50 transition-all {{ request()->routeIs('ai-assistant.*') ? 'ring-2 ring-indigo-500' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                    <span>AI Assistant</span>
                </a>
            </div>
        </nav>
    </aside>

    {{-- Main Content --}}
    <main class="flex-1 min-w-0 flex flex-col bg-slate-50 dark:bg-[#1e1e1e]">
        {{-- Header --}}
        <header class="sticky top-0 z-20 border-b border-slate-200 dark:border-[#2d2d2d] bg-white dark:bg-[#1e1e1e] shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button class="md:hidden p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors" aria-label="Open menu">
                        <span class="text-xl">☰</span>
                    </button>
                    <div>
                        <h1 class="text-lg text-slate-900 dark:text-slate-100">{{ $header ?? ($title ?? 'Dashboard') }}</h1>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button id="theme-toggle" class="p-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-all hover:scale-105" aria-label="Toggle dark mode" title="Toggle theme">
                        <span class="text-xl inline dark:hidden">🌙</span>
                        <span class="text-xl hidden dark:inline">☀️</span>
                    </button>
                    <div class="hidden sm:flex items-center gap-3 px-4 py-2 rounded-lg bg-slate-100 dark:bg-[#2d2d2d]">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center text-white">
                            {{ substr(auth()->user()->name ?? 'G', 0, 1) }}
                        </div>
                        <div class="text-sm">
                            <div class="text-slate-900 dark:text-slate-100">{{ auth()->user()->name ?? 'Guest' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        {{-- Page Content --}}
        <div class="flex-1 max-w-7xl mx-auto w-full p-4 sm:p-6 lg:p-8">
            {{ $slot ?? '' }}
            @yield('content')
        </div>
    </main>
</div>
</body>
</html>
