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
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
        @php
            $canAccessMenu = static fn (string $slug): bool => auth()->user()?->canAccessMenu($slug) ?? false;
        @endphp
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            @php($dashboardAccess = $canAccessMenu('dashboard'))
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('dashboard') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }} {{ $dashboardAccess ? '' : 'opacity-60' }}">
                <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('dashboard') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('dashboard') ? 'text-slate-900 dark:text-white' : '' }} text-sm {{ $dashboardAccess ? '' : 'opacity-80' }}">Dashboard</span>
            </a>
            
            {{-- Master Section --}}
            <div class="pt-4">
                <div class="px-3 pb-2 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-500">Master Data</div>
                @php($customersAccess = $canAccessMenu('customers'))
                <a href="{{ route('customers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('customers.*') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }} {{ $customersAccess ? '' : 'opacity-60' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('customers.*') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('customers.*') ? 'text-slate-900 dark:text-white' : '' }} text-sm {{ $customersAccess ? '' : 'opacity-80' }}">Customers</span>
                </a>
                @php($vendorsAccess = $canAccessMenu('vendors'))
                <a href="{{ route('vendors.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('vendors.*') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }} {{ $vendorsAccess ? '' : 'opacity-60' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('vendors.*') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('vendors.*') ? 'text-slate-900 dark:text-white' : '' }} text-sm {{ $vendorsAccess ? '' : 'opacity-80' }}">Vendors</span>
                </a>
                @php($trucksAccess = $canAccessMenu('trucks'))
                <a href="{{ route('trucks.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('trucks.*') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }} {{ $trucksAccess ? '' : 'opacity-60' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('trucks.*') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('trucks.*') ? 'text-slate-900 dark:text-white' : '' }} text-sm {{ $trucksAccess ? '' : 'opacity-80' }}">Trucks</span>
                </a>
                @php($driversAccess = $canAccessMenu('drivers'))
                <a href="{{ route('drivers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('drivers.*') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }} {{ $driversAccess ? '' : 'opacity-60' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('drivers.*') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('drivers.*') ? 'text-slate-900 dark:text-white' : '' }} text-sm {{ $driversAccess ? '' : 'opacity-80' }}">Drivers</span>
                </a>
                @php($salesAccess = $canAccessMenu('sales'))
                <a href="{{ route('sales.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('sales.*') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }} {{ $salesAccess ? '' : 'opacity-60' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('sales.*') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('sales.*') ? 'text-slate-900 dark:text-white' : '' }} text-sm {{ $salesAccess ? '' : 'opacity-80' }}">Sales</span>
                </a>
                @php($equipmentAccess = $canAccessMenu('equipment'))
                <a href="{{ route('equipment.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('equipment.*') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }} {{ $equipmentAccess ? '' : 'opacity-60' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('equipment.*') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('equipment.*') ? 'text-slate-900 dark:text-white' : '' }} text-sm {{ $equipmentAccess ? '' : 'opacity-80' }}">Equipment</span>
                </a>
            </div>
            
            {{-- Operations Section --}}
            <div class="pt-4">
                <div class="px-3 pb-2 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-500">Operations</div>
                @php($jobOrdersAccess = $canAccessMenu('job-orders'))
                <a href="{{ route('job-orders.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('job-orders.*') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }} {{ $jobOrdersAccess ? '' : 'opacity-60' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('job-orders.*') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('job-orders.*') ? 'text-slate-900 dark:text-white' : '' }} text-sm {{ $jobOrdersAccess ? '' : 'opacity-80' }}">Job Orders</span>
                </a>
            </div>
            
            {{-- Inventory Section --}}
            <div class="pt-4">
                <div class="px-3 pb-2 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-500">Inventory</div>
                @php($inventoryDashboardAccess = $canAccessMenu('inventory.dashboard'))
                <a href="{{ route('inventory.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('inventory.dashboard') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }} {{ $inventoryDashboardAccess ? '' : 'opacity-60' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('inventory.dashboard') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('inventory.dashboard') ? 'text-slate-900 dark:text-white' : '' }} text-sm {{ $inventoryDashboardAccess ? '' : 'opacity-80' }}">Dashboard Inventory</span>
                </a>
                @php($partsAccess = $canAccessMenu('parts'))
                <a href="{{ route('parts.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('parts.*') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }} {{ $partsAccess ? '' : 'opacity-60' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('parts.*') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('parts.*') ? 'text-slate-900 dark:text-white' : '' }} text-sm {{ $partsAccess ? '' : 'opacity-80' }}">Sparepart</span>
                </a>
                @php($partPurchasesAccess = $canAccessMenu('part-purchases'))
                <a href="{{ route('part-purchases.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('part-purchases.*') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }} {{ $partPurchasesAccess ? '' : 'opacity-60' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('part-purchases.*') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('part-purchases.*') ? 'text-slate-900 dark:text-white' : '' }} text-sm {{ $partPurchasesAccess ? '' : 'opacity-80' }}">Pembelian Part</span>
                </a>
                @php($partUsagesAccess = $canAccessMenu('part-usages'))
                <a href="{{ route('part-usages.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('part-usages.*') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }} {{ $partUsagesAccess ? '' : 'opacity-60' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('part-usages.*') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('part-usages.*') ? 'text-slate-900 dark:text-white' : '' }} text-sm {{ $partUsagesAccess ? '' : 'opacity-80' }}">Pemakaian Part</span>
                </a>
            </div>
            
            {{-- Finance Section --}}
            <div class="pt-4">
                <div class="px-3 pb-2 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-500">Finance</div>
                @php($financeDashboardAccess = $canAccessMenu('finance.dashboard'))
                <a href="{{ route('finance.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('finance.dashboard') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }} {{ $financeDashboardAccess ? '' : 'opacity-60' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('finance.dashboard') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('finance.dashboard') ? 'text-slate-900 dark:text-white' : '' }} text-sm {{ $financeDashboardAccess ? '' : 'opacity-80' }}">Dashboard Keuangan</span>
                </a>
                @php($hutangAccess = $canAccessMenu('hutang'))
                <a href="{{ route('hutang.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('hutang.*') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }} {{ $hutangAccess ? '' : 'opacity-60' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('hutang.*') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('hutang.*') ? 'text-slate-900 dark:text-white' : '' }} text-sm {{ $hutangAccess ? '' : 'opacity-80' }}">Dashboard Hutang</span>
                </a>
                @php($invoicesAccess = $canAccessMenu('invoices'))
                <a href="{{ route('invoices.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('invoices.*') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }} {{ $invoicesAccess ? '' : 'opacity-60' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('invoices.*') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('invoices.*') ? 'text-slate-900 dark:text-white' : '' }} text-sm {{ $invoicesAccess ? '' : 'opacity-80' }}">Invoices</span>
                </a>
                @php($paymentRequestsAccess = $canAccessMenu('payment-requests'))
                <a href="{{ route('payment-requests.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('payment-requests.*') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }} {{ $paymentRequestsAccess ? '' : 'opacity-60' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('payment-requests.*') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('payment-requests.*') ? 'text-slate-900 dark:text-white' : '' }} text-sm {{ $paymentRequestsAccess ? '' : 'opacity-80' }}">Pengajuan Pembayaran</span>
                </a>
                @php($cashBanksAccess = $canAccessMenu('cash-banks'))
                <a href="{{ route('cash-banks.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('cash-banks.*') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }} {{ $cashBanksAccess ? '' : 'opacity-60' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('cash-banks.*') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('cash-banks.*') ? 'text-slate-900 dark:text-white' : '' }} text-sm {{ $cashBanksAccess ? '' : 'opacity-80' }}">Cash/Bank</span>
                </a>
                @php($journalsAccess = $canAccessMenu('journals'))
                <a href="{{ route('journals.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('journals.*') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }} {{ $journalsAccess ? '' : 'opacity-60' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('journals.*') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('journals.*') ? 'text-slate-900 dark:text-white' : '' }} text-sm {{ $journalsAccess ? '' : 'opacity-80' }}">Jurnal</span>
                </a>
                @php($chartAccountsAccess = $canAccessMenu('chart-of-accounts'))
                <a href="{{ route('chart-of-accounts.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('chart-of-accounts.*') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }} {{ $chartAccountsAccess ? '' : 'opacity-60' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('chart-of-accounts.*') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('chart-of-accounts.*') ? 'text-slate-900 dark:text-white' : '' }} text-sm {{ $chartAccountsAccess ? '' : 'opacity-80' }}">Chart of Accounts</span>
                </a>
            </div>
            
            {{-- Reports Section --}}
            <div class="pt-4">
                <div class="px-3 pb-2 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-500">Reports</div>
                @php($trialBalanceAccess = $canAccessMenu('reports.trial-balance'))
                <a href="{{ route('reports.trial-balance') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('reports.trial-balance') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }} {{ $trialBalanceAccess ? '' : 'opacity-60' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('reports.trial-balance') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('reports.trial-balance') ? 'text-slate-900 dark:text-white' : '' }} text-sm {{ $trialBalanceAccess ? '' : 'opacity-80' }}">Trial Balance</span>
                </a>
                @php($profitLossAccess = $canAccessMenu('reports.profit-loss'))
                <a href="{{ route('reports.profit-loss') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('reports.profit-loss') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }} {{ $profitLossAccess ? '' : 'opacity-60' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('reports.profit-loss') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('reports.profit-loss') ? 'text-slate-900 dark:text-white' : '' }} text-sm {{ $profitLossAccess ? '' : 'opacity-80' }}">Profit & Loss</span>
                </a>
                @php($balanceSheetAccess = $canAccessMenu('reports.balance-sheet'))
                <a href="{{ route('reports.balance-sheet') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('reports.balance-sheet') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }} {{ $balanceSheetAccess ? '' : 'opacity-60' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('reports.balance-sheet') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('reports.balance-sheet') ? 'text-slate-900 dark:text-white' : '' }} text-sm {{ $balanceSheetAccess ? '' : 'opacity-80' }}">Balance Sheet</span>
                </a>
            </div>
            
            {{-- AI Section --}}
            <div class="pt-4 pb-4">
                <div class="px-3 pb-2 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-500">AI Tools</div>
                @php($aiAccess = $canAccessMenu('ai-assistant'))
                <a href="{{ route('ai-assistant.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/30 hover:shadow-xl hover:shadow-indigo-500/50 transition-all {{ request()->routeIs('ai-assistant.*') ? 'ring-2 ring-indigo-500' : '' }} {{ $aiAccess ? '' : 'opacity-60' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                    <span>AI Assistant</span>
                </a>
            </div>

            <div class="pt-4">
                <div class="px-3 pb-2 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-500">Admin</div>
                @php($userManagementAccess = $canAccessMenu('admin.users'))
                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors group {{ request()->routeIs('admin.users.*') ? 'bg-slate-100 dark:bg-[#2d2d2d]' : '' }} {{ $userManagementAccess ? '' : 'opacity-60' }}">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('admin.users.*') ? 'text-slate-900 dark:text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 1115 21v-2a6 6 0 00-5.879-6 4.5 4.5 0 10-4 0 6 6 0 00-.121 4.804z" />
                    </svg>
                    <span class="text-slate-700 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white {{ request()->routeIs('admin.users.*') ? 'text-slate-900 dark:text-white' : '' }} text-sm {{ $userManagementAccess ? '' : 'opacity-80' }}">Manajemen User</span>
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
                    {{-- Notifications --}}
                    @auth
                    <div class="relative" x-data="{ open: false }">
                        <button 
                            @click="open = !open" 
                            class="relative p-2.5 rounded-lg hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-all hover:scale-105" 
                            aria-label="Notifications"
                            id="notification-button"
                        >
                            <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span id="notification-badge" class="absolute top-1 right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
                        </button>
                        
                        {{-- Dropdown --}}
                        <div 
                            x-show="open"
                            @click.away="open = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-80 bg-white dark:bg-[#252525] rounded-lg shadow-lg border border-slate-200 dark:border-[#2d2d2d] z-50 max-h-96 overflow-hidden flex flex-col"
                            style="display: none;"
                            id="notification-dropdown"
                        >
                            <div class="p-4 border-b border-slate-200 dark:border-[#2d2d2d] flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Notifikasi</h3>
                                <button id="mark-all-read" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Tandai semua dibaca</button>
                            </div>
                            <div id="notification-list" class="overflow-y-auto max-h-64">
                                <div class="p-4 text-center text-sm text-slate-500 dark:text-slate-400">
                                    Memuat notifikasi...
                                </div>
                            </div>
                            <div class="p-2 border-t border-slate-200 dark:border-[#2d2d2d] text-center">
                                <a href="{{ route('payment-requests.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Lihat semua pengajuan</a>
                            </div>
                        </div>
                    </div>
                    @endauth
                    
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
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 text-xs font-semibold rounded-lg border border-slate-200 dark:border-[#2d2d2d] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#2d2d2d] transition-colors">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </header>
        
        {{-- Page Content --}}
        <div class="flex-1 max-w-7xl mx-auto w-full p-4 sm:p-6 lg:p-8">
            <div class="bg-white dark:bg-[#252525] rounded-2xl shadow-sm border border-slate-200 dark:border-[#2d2d2d] p-6 sm:p-8">
                {{ $slot ?? '' }}
                @yield('content')
            </div>
        </div>
    </main>
</div>
</body>
</html>
