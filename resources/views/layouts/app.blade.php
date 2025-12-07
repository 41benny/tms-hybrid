<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'TMS Premium') }}</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    
    {{-- Apply saved theme as early as possible to avoid flicker / unexpected theme changes after redirects --}}
    <script>
        (function () {
            try {
                var savedTheme = localStorage.getItem('tms-theme');
                if (savedTheme && savedTheme !== 'default') {
                    document.documentElement.setAttribute('data-theme', savedTheme);
                }
            } catch (e) {
                // Ignore errors (e.g. disabled localStorage)
            }
        })();
    </script>

    @vite(['resources/css/app.css','resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full font-sans antialiased selection:bg-cyan-500 selection:text-white">

@php
    $role = auth()->user()->role ?? null;
    // Sales: sembunyikan sidebar di mobile, tampil mulai md (tablet/laptop). Role lain: selalu tampil.
    $sidebarClass = $role === \App\Models\User::ROLE_SALES ? 'hidden md:flex md:w-72' : 'flex w-72';
@endphp

    <div class="fixed inset-0 z-[-1] theme-bg-gradient transition-all duration-700"></div>

    <div class="h-screen flex overflow-hidden">
        
        {{-- SIDEBAR --}}
        <aside id="sidebar" class="{{ $sidebarClass }} flex-col shrink-0 theme-sidebar backdrop-blur-xl relative z-20 transition-all duration-300 fixed lg:relative inset-y-0 left-0 h-full overflow-y-auto lg:overflow-visible">
            
            {{-- Logo / Brand --}}
            <div class="p-6 pb-3 relative">
                <div class="absolute top-0 left-0 w-full h-1" style="background: linear-gradient(90deg, var(--color-primary), transparent);"></div>
                <div class="sidebar-brand-shell flex items-center gap-3 relative">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shadow-lg border theme-border shrink-0" style="background: rgba(255,255,255,0.05); color: var(--color-primary);">
                        <span class="font-bold text-xl">T</span>
                    </div>
                    <div class="sidebar-text flex-1">
                        <h1 class="font-bold text-lg tracking-wider text-white whitespace-nowrap">NEXUS<span class="theme-text-primary">TMS</span></h1>
                        <p class="text-[10px] theme-text-muted tracking-[0.2em] uppercase whitespace-nowrap">Transport System</p>
                    </div>
                </div>
            </div>
            
            {{-- Toggle Button --}}
            <div class="px-4 pb-4">
                <button id="sidebarToggle" class="tms-btn w-full py-2 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg hover:shadow-indigo-500/50 transition-all border border-indigo-400/30 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                    </svg>
                    <span class="text-xs font-semibold sidebar-text">Collapse</span>
                </button>
            </div>

            {{-- Navigation --}}
            @php
                $canAccessMenu = static fn (string $slug): bool => auth()->user()?->canAccessMenu($slug) ?? false;
            @endphp

            <nav id="sidebarScroll" class="flex-1 px-4 space-y-1 pb-4 overflow-y-auto md:overflow-y-auto">
                @if($role === \App\Models\User::ROLE_SALES)
                    {{-- Minimal menu untuk Sales --}}
                    <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                    <a href="{{ route('job-orders.index') }}" class="nav-item {{ request()->routeIs('job-orders.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        <span class="sidebar-text">Job Orders</span>
                    </a>
                    <a href="{{ route('sales.console') }}" class="nav-item {{ request()->routeIs('sales.console') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h8l2 4h8M3 13h8l2 4h8M3 7v13" />
                        </svg>
                        <span class="sidebar-text">Sales Console</span>
                    </a>
                    <a href="{{ route('payment-requests.index') }}" class="nav-item {{ request()->routeIs('payment-requests.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span class="sidebar-text">Payment Requests</span>
                    </a>
                @else

                {{-- Dashboard --}}
                <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span class="sidebar-text">Dashboard</span>
                </a>

                {{-- Master Data Group --}}
                <div class="menu-group" data-group="master-data">
                    <button class="group-header w-full flex items-center justify-between pt-6 pb-2 px-2 hover:bg-white/5 rounded transition-colors cursor-pointer">
                        <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest opacity-90 sidebar-text">Master Data</p>
                        <svg class="w-3 h-3 text-slate-300 transition-transform duration-200 sidebar-text chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="group-items">
                        <a href="{{ route('customers.index') }}" class="nav-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <span class="sidebar-text">Customers</span>
                        </a>
                        <a href="{{ route('vendors.index') }}" class="nav-item {{ request()->routeIs('vendors.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            <span class="sidebar-text">Vendors</span>
                        </a>
                        <a href="{{ route('trucks.index') }}" class="nav-item {{ request()->routeIs('trucks.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                            <span class="sidebar-text">Trucks</span>
                        </a>
                        <a href="{{ route('drivers.index') }}" class="nav-item {{ request()->routeIs('drivers.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <span class="sidebar-text">Drivers</span>
                        </a>
                        <a href="{{ route('sales.index') }}" class="nav-item {{ request()->routeIs('sales.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            <span class="sidebar-text">Sales</span>
                        </a>
                        <a href="{{ route('equipment.index') }}" class="nav-item {{ request()->routeIs('equipment.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            <span class="sidebar-text">Equipment</span>
                        </a>
                        <a href="{{ route('master.cash-bank-accounts.index') }}" class="nav-item {{ request()->routeIs('master.cash-bank-accounts.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            <span class="sidebar-text">Cash & Bank</span>
                        </a>
                    </div>
                </div>

                {{-- Operations Group --}}
                <div class="menu-group" data-group="operations">
                    <button class="group-header w-full flex items-center justify-between pt-4 pb-2 px-2 hover:bg-white/5 rounded transition-colors cursor-pointer">
                        <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest opacity-90 sidebar-text">Operations</p>
                        <svg class="w-3 h-3 text-slate-300 transition-transform duration-200 sidebar-text chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="group-items">
                        <a href="{{ route('job-orders.index') }}" class="nav-item {{ request()->routeIs('job-orders.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            <span class="sidebar-text">Job Orders</span>
                        </a>
                        @if(in_array($role, ['sales','admin','super_admin'], true))
                            <a href="{{ route('sales.console') }}" class="nav-item {{ request()->routeIs('sales.console') ? 'active' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h8l2 4h8M3 13h8l2 4h8M3 7v13" />
                                </svg>
                                <span class="sidebar-text">Sales Console</span>
                            </a>
                @endif
                        <a href="{{ route('invoices.index') }}" class="nav-item {{ request()->routeIs('invoices.index') || request()->routeIs('invoices.create') || request()->routeIs('invoices.show') || request()->routeIs('invoices.edit') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="sidebar-text">Invoices</span>
                        </a>
                        <a href="{{ route('invoices.approvals') }}" class="nav-item {{ request()->routeIs('invoices.approvals') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="sidebar-text">Invoice Approvals</span>
                        </a>
                        <a href="{{ route('payment-requests.index') }}" class="nav-item {{ request()->routeIs('payment-requests.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="sidebar-text">Payment Requests</span>
                        </a>
                        <a href="{{ route('driver-advances.index') }}" class="nav-item {{ request()->routeIs('driver-advances.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <span class="sidebar-text">Driver Advances</span>
                        </a>
                        <a href="{{ route('vendor-bills.index') }}" class="nav-item {{ request()->routeIs('vendor-bills.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="sidebar-text">Vendor Bills</span>
                        </a>
                    </div>
                </div>

                {{-- Finance Group --}}
                <div class="menu-group" data-group="finance">
                    <button class="group-header w-full flex items-center justify-between pt-4 pb-2 px-2 hover:bg-white/5 rounded transition-colors cursor-pointer">
                        <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest opacity-90 sidebar-text">Finance</p>
                        <svg class="w-3 h-3 text-slate-300 transition-transform duration-200 sidebar-text chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="group-items">
                        <a href="{{ route('finance.dashboard') }}" class="nav-item {{ request()->routeIs('finance.dashboard') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="sidebar-text">Finance Dash</span>
                        </a>
                        <a href="{{ route('hutang.dashboard') }}" class="nav-item {{ request()->routeIs('hutang.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            <span class="sidebar-text">Payables Dash</span>
                        </a>
                        <a href="{{ route('cash-banks.index') }}" class="nav-item {{ request()->routeIs('cash-banks.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            <span class="sidebar-text">Cash/Bank</span>
                        </a>
                    </div>
                </div>
                 {{-- Inventory Group --}}
                <div class="menu-group" data-group="inventory">
                    <button class="group-header w-full flex items-center justify-between pt-4 pb-2 px-2 hover:bg-white/5 rounded transition-colors cursor-pointer">
                        <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest opacity-90 sidebar-text">Inventory</p>
                        <svg class="w-3 h-3 text-slate-300 transition-transform duration-200 sidebar-text chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="group-items">
                        <a href="{{ route('inventory.dashboard') }}" class="nav-item {{ request()->routeIs('inventory.dashboard') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            <span class="sidebar-text">Inventory Dash</span>
                        </a>
                        <a href="{{ route('parts.index') }}" class="nav-item {{ request()->routeIs('parts.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            <span class="sidebar-text">Spare Parts</span>
                        </a>
                        <a href="{{ route('part-purchases.index') }}" class="nav-item {{ request()->routeIs('part-purchases.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            <span class="sidebar-text">Part Purchases</span>
                        </a>
                        <a href="{{ route('part-usages.index') }}" class="nav-item {{ request()->routeIs('part-usages.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            <span class="sidebar-text">Part Usage</span>
                        </a>
                    </div>
                </div>

                {{-- Accounting Group --}}
                <div class="menu-group" data-group="accounting">
                    <button class="group-header w-full flex items-center justify-between pt-4 pb-2 px-2 hover:bg-white/5 rounded transition-colors cursor-pointer">
                        <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest opacity-90 sidebar-text">Accounting</p>
                        <svg class="w-3 h-3 text-slate-300 transition-transform duration-200 sidebar-text chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="group-items">
                        <a href="{{ route('journals.index') }}" class="nav-item {{ request()->routeIs('journals.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="sidebar-text">Journals</span>
                        </a>
                        <a href="{{ route('chart-of-accounts.index') }}" class="nav-item {{ request()->routeIs('chart-of-accounts.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                            <span class="sidebar-text">Chart of Accounts</span>
                        </a>
                        <a href="{{ route('accounting.periods.index') }}" class="nav-item {{ request()->routeIs('accounting.periods.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="sidebar-text">Fiscal Periods</span>
                        </a>
                        <a href="{{ route('reports.general-ledger') }}" class="nav-item {{ request()->routeIs('reports.general-ledger') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            <span class="sidebar-text">General Ledger</span>
                        </a>
                        <a href="{{ route('reports.trial-balance') }}" class="nav-item {{ request()->routeIs('reports.trial-balance') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            <span class="sidebar-text">Trial Balance</span>
                        </a>
                        <a href="{{ route('reports.profit-loss') }}" class="nav-item {{ request()->routeIs('reports.profit-loss') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            <span class="sidebar-text">Profit & Loss</span>
                        </a>
                        <a href="{{ route('reports.balance-sheet') }}" class="nav-item {{ request()->routeIs('reports.balance-sheet') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            <span class="sidebar-text">Balance Sheet</span>
                        </a>
                        <a href="{{ route('reports.cash-flow') }}" class="nav-item {{ request()->routeIs('reports.cash-flow') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="sidebar-text">Cash Flow</span>
                        </a>
                        <a href="{{ route('fixed-assets.index') }}" class="nav-item {{ request()->routeIs('fixed-assets.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l9-4 9 4-9 4-9-4zm0 6l9 4 9-4m-9 4v6"></path></svg>
                            <span class="sidebar-text">Fixed Assets</span>
                        </a>
                    </div>
                </div>

                {{-- Tax Reports Group --}}
                <div class="menu-group" data-group="tax-reports">
                    <button class="group-header w-full flex items-center justify-between pt-4 pb-2 px-2 hover:bg-white/5 rounded transition-colors cursor-pointer">
                        <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest opacity-90 sidebar-text">Tax Reports</p>
                        <svg class="w-3 h-3 text-slate-300 transition-transform duration-200 sidebar-text chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="group-items">
                        <a href="{{ route('reports.tax.ppn-summary') }}" class="nav-item {{ request()->routeIs('reports.tax.ppn-summary') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            <span class="sidebar-text">PPN Summary</span>
                        </a>
                        <a href="{{ route('tax-invoices.index') }}" class="nav-item {{ request()->routeIs('tax-invoices.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span class="sidebar-text">Faktur Pajak</span>
                        </a>
                        <a href="{{ route('reports.tax.ppn-keluaran') }}" class="nav-item {{ request()->routeIs('reports.tax.ppn-keluaran') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            <span class="sidebar-text">PPN Keluaran</span>
                        </a>
                        <a href="{{ route('reports.tax.ppn-masukan') }}" class="nav-item {{ request()->routeIs('reports.tax.ppn-masukan') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                            <span class="sidebar-text">PPN Masukan</span>
                        </a>
                        <a href="{{ route('reports.tax.pph23-summary') }}" class="nav-item {{ request()->routeIs('reports.tax.pph23-summary') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            <span class="sidebar-text">PPh 23 Summary</span>
                        </a>
                        <a href="{{ route('reports.tax.pph23-dipotong') }}" class="nav-item {{ request()->routeIs('reports.tax.pph23-dipotong') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                            <span class="sidebar-text">PPh 23 Dipotong</span>
                        </a>
                        <a href="{{ route('reports.tax.pph23-dipungut') }}" class="nav-item {{ request()->routeIs('reports.tax.pph23-dipungut') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                            <span class="sidebar-text">PPh 23 Dipungut</span>
                        </a>
                    </div>
                </div>

                {{-- AI Assistant --}}
                <div class="pt-6 pb-2">
                    <a href="{{ route('ai-assistant.index') }}" class="tms-btn ai-assistant-btn w-full flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg hover:shadow-indigo-500/50 transition-all border border-indigo-400/30">
                        {{-- Base icon (all themes) --}}
                        <svg class="ai-icon-base w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        {{-- Gemini-style icon (Aurora only, via CSS) --}}
                        <svg class="ai-icon-gem w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2.5c-.3 0-.6.16-.76.44L9.03 7.18 4.1 8.02a.85.85 0 0 0-.48 1.42l3.55 3.46-.84 4.98a.86.86 0 0 0 1.25.9L12 16.98l4.42 2.3a.86.86 0 0 0 1.25-.9l-.84-4.98 3.55-3.46a.85.85 0 0 0-.48-1.42l-4.93-.84-2.21-4.24A.86.86 0 0 0 12 2.5Z" />
                        </svg>
                        <span class="font-bold text-xs sidebar-text">AI Assistant</span>
                    </a>
                </div>

                {{-- Admin --}}
                <div class="pt-4 pb-2 px-2 sidebar-text"><p class="text-[10px] font-bold theme-text-primary uppercase tracking-widest opacity-80">Admin</p></div>
                
                <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 1115 21v-2a6 6 0 00-5.879-6 4.5 4.5 0 10-4 0 6 6 0 00-.121 4.804z"></path></svg>
                    <span class="sidebar-text">User Management</span>
                </a>

                @endif

            </nav>

            {{-- Footer Sidebar --}}
            <div class="p-4 mt-auto border-t theme-border">
                <div class="sidebar-user-card flex items-center gap-3 px-3 py-2 rounded-xl bg-white/10 dark:bg-black/30 border theme-border">
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 flex-1 min-w-0">
                        @php $avatarUrl = auth()->user()?->avatarUrl(); @endphp
                        @if($avatarUrl)
                            <div class="w-8 h-8 rounded-full overflow-hidden border border-slate-300/40 bg-slate-900/60">
                                <img src="{{ $avatarUrl }}" alt="Avatar" class="w-full h-full object-cover">
                            </div>
                        @else
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-black border border-slate-300/40" style="background: var(--color-primary);">
                                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="sidebar-user-name text-xs font-medium text-white truncate">{{ auth()->user()->name ?? 'Guest' }}</p>
                            <p class="text-[10px] theme-text-primary truncate">Profil &amp; akun</p>
                        </div>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-xs text-red-400 hover:text-white" title="Logout">
                           <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- MAIN CONTENT --}}
        <main class="flex-1 flex flex-col relative z-10 overflow-hidden">
            
            {{-- HEADER --}}
            <header class="h-16 theme-panel flex items-center justify-between px-6 sticky top-0 z-30 transition-colors duration-500" style="border-width: 0 0 1px 0;">
                <div class="flex items-center gap-4">
                    <h2 class="text-lg font-semibold tracking-wide text-contrast">{{ $header ?? ($title ?? 'Dashboard') }}</h2>
                    <div class="h-4 w-[1px] bg-slate-700 hidden sm:block"></div>
                    <span class="hidden sm:flex items-center gap-2 text-xs font-mono px-2 py-1 rounded border theme-border" style="background: rgba(0,0,0,0.2); color: var(--color-primary);">
                        <span class="system-status-dot w-1.5 h-1.5 rounded-full animate-pulse"></span>
                        SYSTEM: OPTIMAL
                    </span>
                </div>
                
                <div class="flex items-center gap-4">
                     {{-- Notifications --}}
                     <div class="relative" x-data="{ 
                        open: false,
                        notifications: [],
                        unreadCount: 0,
                        loading: false,
                        async fetchNotifications() {
                            this.loading = true;
                            try {
                                const response = await fetch('{{ route('notifications.index') }}');
                                const data = await response.json();
                                this.notifications = data.notifications;
                                this.unreadCount = data.unread_count;
                            } catch (error) {
                                console.error('Error fetching notifications:', error);
                            } finally {
                                this.loading = false;
                            }
                        },
                        async markAsRead(id) {
                            try {
                                const url = '{{ route('notifications.read', ':id') }}'.replace(':id', id);
                                await fetch(url, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    },
                                });
                                await this.fetchNotifications();
                            } catch (error) {
                                console.error('Error marking as read:', error);
                            }
                        },
                        getNotificationUrl(notification) {
                            // If URL exists in data, use it
                            if (notification.data?.url) {
                                return notification.data.url;
                            }
                            
                            // Fallback: generate URL based on notification type
                            if (notification.type === 'App\\Notifications\\PaymentRequestCreated' && notification.data?.payment_request_id) {
                                return `/payment-requests/${notification.data.payment_request_id}`;
                            }
                            if (notification.type === 'App\\Notifications\\InvoiceSubmittedForApproval' && notification.data?.invoice_id) {
                                return `/invoices/${notification.data.invoice_id}`;
                            }
                            if (notification.type === 'App\\Notifications\\TaxInvoiceRequestedNotification') {
                                return '/tax-invoices';
                            }
                            
                            return '#'; // Default fallback
                        },
                        init() {
                            this.fetchNotifications();
                            // Poll for new notifications every minute
                            setInterval(() => this.fetchNotifications(), 60000);
                        }
                     }">
                        <button @click="open = !open" class="relative p-2 rounded-lg hover:bg-white/10 transition-colors theme-text-muted hover:text-contrast notification-toggle">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            <span x-show="unreadCount > 0" class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-red-500 shadow-[0_0_8px_red]" style="display: none;"></span>
                        </button>
                        <div
                            x-show="open"
                            @click.away="open = false"
                            style="display: none;"
                            class="absolute mt-2 right-2 left-2 sm:left-auto sm:right-0 sm:w-96 w-auto max-w-[calc(100vw-1rem)] rounded-xl shadow-2xl z-50 overflow-hidden bg-white/95 dark:bg-slate-800/95 backdrop-blur-xl border border-slate-200 dark:border-slate-700">
                            <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
                                <span class="text-sm font-bold text-slate-900 dark:text-white">Notifications</span>
                                <span x-text="unreadCount > 0 ? `${unreadCount} new` : 'No new'" class="text-xs text-slate-600 dark:text-slate-400"></span>
                            </div>
                            <div class="max-h-96 overflow-y-auto">
                                <template x-if="loading && notifications.length === 0">
                                    <div class="p-4 text-center text-slate-400 text-xs">Loading...</div>
                                </template>
                                <template x-if="!loading && notifications.length === 0">
                                    <div class="p-4 text-center text-slate-400 text-xs">No notifications</div>
                                </template>
                                <template x-for="notification in notifications" :key="notification.id">
                                    <a 
                                        :href="getNotificationUrl(notification)" 
                                        @click.prevent="async function() {
                                            const url = getNotificationUrl(notification);
                                            console.log('Notification clicked:', notification);
                                            console.log('Navigating to:', url);
                                            
                                            try {
                                                // Mark as read first
                                                await markAsRead(notification.id);
                                                
                                                // Close dropdown
                                                open = false;
                                                
                                                // Navigate after a short delay
                                                if (url && url !== '#') {
                                                    setTimeout(() => {
                                                        window.location.href = url;
                                                    }, 100);
                                                }
                                            } catch (error) {
                                                console.error('Navigation error:', error);
                                                // Navigate anyway even if mark as read fails
                                                if (url && url !== '#') {
                                                    window.location.href = url;
                                                }
                                            }
                                        }"
                                        class="block p-4 border-b border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors cursor-pointer" 
                                        :class="notification.read_at ? 'bg-slate-50 dark:bg-slate-800/50' : 'bg-white dark:bg-slate-800'"
                                    >
                                        <div class="flex items-start gap-3">
                                            <div class="flex-shrink-0 mt-1" :class="notification.read_at ? 'opacity-40' : ''">
                                                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-slate-900 dark:text-white leading-relaxed" x-text="notification.message"></p>
                                                <div class="flex items-center gap-2 mt-2">
                                                    <p class="text-xs text-slate-400" x-text="notification.created_at"></p>
                                                    <template x-if="!notification.read_at">
                                                        <span class="px-2 py-0.5 text-[10px] bg-indigo-500 text-white rounded-full font-medium">New</span>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </div>
                     </div>

                    {{-- Theme Switcher (macOS Style - Right Aligned) --}}
                    <div class="hidden md:flex items-center gap-2 pl-4 border-l border-white/10">
                        <button onclick="setTheme('default')" class="w-2.5 h-2.5 rounded-full bg-[#22D3EE] hover:brightness-110 transition-all shadow-[0_0_5px_rgba(34,211,238,0.5)] hover:scale-125" title="Midnight Cyan"></button>
                        <button onclick="setTheme('gold')" class="w-2.5 h-2.5 rounded-full bg-[#FBBF24] hover:brightness-110 transition-all shadow-[0_0_5px_rgba(251,191,36,0.5)] hover:scale-125" title="Royal Gold"></button>
                        <button onclick="setTheme('aurora')" class="w-2.5 h-2.5 rounded-full bg-gradient-to-br from-violet-500 via-fuchsia-500 to-pink-500 hover:brightness-110 transition-all shadow-[0_0_8px_rgba(168,85,247,0.6)] hover:scale-125" title="Aurora Nebula"></button>
                    </div>
                </div>
            </header>

            {{-- CONTENT SCROLL AREA --}}
            <div class="flex-1 overflow-y-auto p-6 pb-24 md:p-8 md:pb-8 space-y-6">
                {{ $slot ?? '' }}
                @yield('content')
            </div>

        </main>

        {{-- SALES BOTTOM NAV (mobile only) --}}
        @if($role === 'sales')
            <nav class="fixed bottom-0 inset-x-0 z-40 border-t border-slate-700/60 bg-slate-900/95 backdrop-blur md:hidden">
                <div class="flex">
                    <a href="{{ route('sales.console') }}"
                       class="flex-1 flex flex-col items-center justify-center py-2 text-[11px] {{ request()->routeIs('sales.console') ? 'text-cyan-300' : 'text-slate-400' }}">
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3" />
                        </svg>
                        <span>Home</span>
                    </a>
                    <a href="{{ route('job-orders.index') }}"
                       class="flex-1 flex flex-col items-center justify-center py-2 text-[11px] {{ request()->routeIs('job-orders.*') ? 'text-cyan-300' : 'text-slate-400' }}">
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <span>Job Order</span>
                    </a>
                    <a href="{{ route('payment-requests.index') }}"
                       class="flex-1 flex flex-col items-center justify-center py-2 text-[11px] {{ request()->routeIs('payment-requests.*') ? 'text-cyan-300' : 'text-slate-400' }}">
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Pay Request</span>
                    </a>
                    <a href="{{ route('invoices.index') }}"
                       class="flex-1 flex flex-col items-center justify-center py-2 text-[11px] {{ request()->routeIs('invoices.*') ? 'text-cyan-300' : 'text-slate-400' }}">
                        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Invoice</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="flex-1 flex flex-col items-center justify-center py-2 text-[11px]">
                        @csrf
                        <button type="submit" class="flex flex-col items-center justify-center text-[11px] text-slate-400">
                            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </nav>
        @endif



        {{-- SCRIPT GANTI TEMA --}}
        <script>
            const savedTheme = localStorage.getItem('tms-theme');
            if (savedTheme) document.documentElement.setAttribute('data-theme', savedTheme);

            function setTheme(themeName) {
                if (themeName === 'default') {
                    document.documentElement.removeAttribute('data-theme');
                    localStorage.removeItem('tms-theme');
                } else {
                    document.documentElement.setAttribute('data-theme', themeName);
                    localStorage.setItem('tms-theme', themeName);
                }
            }
        </script>
        
        {{-- SIDEBAR COLLAPSE SCRIPT --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const sidebar = document.getElementById('sidebar');
                const scrollArea = document.getElementById('sidebarScroll') || sidebar;
                const toggleBtn = document.getElementById('sidebarToggle');
                
                // Load saved sidebar state
                const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
                if (isCollapsed) {
                    sidebar.classList.add('collapsed');
                }

                // ========================================
                // RESTORE SIDEBAR SCROLL POSITION
                // ========================================
                const savedScrollPos = sessionStorage.getItem('sidebar-scroll-position');
                if (savedScrollPos) {
                    scrollArea.scrollTop = parseInt(savedScrollPos, 10);
                }

                // SAVE SIDEBAR SCROLL POSITION before navigation
                scrollArea.addEventListener('scroll', function() {
                    sessionStorage.setItem('sidebar-scroll-position', scrollArea.scrollTop);
                });

                // Also save scroll position when clicking any link
                const allLinks = sidebar.querySelectorAll('a');
                allLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        sessionStorage.setItem('sidebar-scroll-position', scrollArea.scrollTop);
                    });
                });
                // ========================================

                // Add simple tooltips for nav items (using their text labels)
                const navItems = document.querySelectorAll('#sidebar .nav-item');
                navItems.forEach(function (item) {
                    if (!item.getAttribute('title')) {
                        const labelEl = item.querySelector('.sidebar-text');
                        if (labelEl && labelEl.textContent.trim().length > 0) {
                            item.setAttribute('title', labelEl.textContent.trim());
                        }
                    }
                });
                
                // Toggle sidebar collapse/expand
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    const collapsed = sidebar.classList.contains('collapsed');
                    localStorage.setItem('sidebar-collapsed', collapsed);
                });
                
                // Menu group collapse/expand functionality
                const groupHeaders = document.querySelectorAll('.group-header');
                
                groupHeaders.forEach(header => {
                    const group = header.closest('.menu-group');
                    const groupName = group.dataset.group;
                    const groupItems = group.querySelector('.group-items');
                    const chevron = header.querySelector('.chevron');
                    
                    // Load saved group state (default: expanded)
                    const isGroupCollapsed = localStorage.getItem(`menu-group-${groupName}`) === 'true';
                    if (isGroupCollapsed) {
                        groupItems.style.maxHeight = '0';
                        groupItems.style.overflow = 'hidden';
                        chevron.style.transform = 'rotate(-90deg)';
                    } else {
                        groupItems.style.maxHeight = groupItems.scrollHeight + 'px';
                    }
                    
                    // Toggle group on header click
                    header.addEventListener('click', function() {
                        const isCurrentlyCollapsed = groupItems.style.maxHeight === '0px';
                        
                        if (isCurrentlyCollapsed) {
                            // Expand
                            groupItems.style.maxHeight = groupItems.scrollHeight + 'px';
                            groupItems.style.overflow = 'visible';
                            chevron.style.transform = 'rotate(0deg)';
                            localStorage.setItem(`menu-group-${groupName}`, 'false');
                        } else {
                            // Collapse
                            groupItems.style.maxHeight = '0';
                            groupItems.style.overflow = 'hidden';
                            chevron.style.transform = 'rotate(-90deg)';
                            localStorage.setItem(`menu-group-${groupName}`, 'true');
                        }
                    });
                });
            });
        </script>
        
        {{-- SIDEBAR COLLAPSE STYLES --}}
        <style>
            #sidebar {
                transition: width 0.3s ease;
            }
            
            #sidebar.collapsed {
                width: 6rem !important;
            }
            
            /* Hide all text elements when collapsed */
            #sidebar.collapsed .sidebar-text {
                display: none !important;
            }
            
            #sidebar:not(.collapsed) .sidebar-text {
                opacity: 1;
                width: auto;
            }
            
            /* Nav items when collapsed - show icons centered */
            #sidebar.collapsed .nav-item {
                justify-content: center !important;
                padding: 0.75rem !important;
            }
            
            #sidebar.collapsed .nav-item svg {
                margin: 0 !important;
            }
            
            /* Adjust logo section when collapsed */
            #sidebar.collapsed .p-6 {
                padding: 1rem !important;
            }
            
            /* Adjust footer when collapsed */
            #sidebar.collapsed .p-4.mt-auto {
                padding: 1rem !important;
            }
            
            #sidebar.collapsed .p-4.mt-auto .flex-1 {
                display: none;
            }
            
            #sidebar.collapsed .p-4.mt-auto .flex {
                justify-content: center;
            }
            
            /* Toggle button icon rotation when collapsed */
            #sidebar.collapsed #sidebarToggle svg {
                transform: rotate(180deg);
            }
            
            /* Adjust toggle button container when collapsed */
            #sidebar.collapsed .px-4.pb-4 {
                padding: 0.5rem !important;
            }
            
            /* Menu Group Styles */
            .menu-group {
                margin-bottom: 0.25rem;
            }
            
            .group-header {
                outline: none;
                border: none;
                background: transparent;
            }
            
            .group-header:focus {
                outline: none;
            }
            
            .group-items {
                transition: max-height 0.3s ease, overflow 0.3s ease;
                overflow: visible;
            }
            
            .chevron {
                transition: transform 0.2s ease;
            }
            
            /* Hide group headers when sidebar collapsed */
            #sidebar.collapsed .group-header {
                display: none;
            }
            
            /* Show group items directly when sidebar collapsed */
            #sidebar.collapsed .group-items {
                max-height: none !important;
                overflow: visible !important;
            }
            
            /* System status dot - Default theme (Midnight Cyan) */
            .system-status-dot {
                background: #22D3EE !important; /* Cyan-400 */
                box-shadow: 0 0 8px rgba(34, 211, 238, 0.6) !important;
            }
            
            /* Gold theme */
            html[data-theme="gold"] .system-status-dot {
                background: #FBBF24 !important; /* Amber-400 */
                box-shadow: 0 0 8px rgba(251, 191, 36, 0.6) !important;
            }
            
            /* Aurora theme - Pink breathing dot */
            html[data-theme="aurora"] .system-status-dot {
                background: #ec4899 !important; /* Pink-500 */
                box-shadow: 0 0 8px rgba(236, 72, 153, 0.6) !important;
            }
        </style>
        
        {{-- FORM KEYBOARD SHORTCUTS (Alt+S untuk tombol Simpan/Save saja) --}}
        <script src="{{ asset('js/form-shortcuts.js') }}"></script>
        @stack('scripts')
    </div>
</body>
</html>
