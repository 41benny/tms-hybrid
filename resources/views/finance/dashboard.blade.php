@extends('layouts.app', ['title' => 'Dashboard Keuangan'])

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Dashboard Keuangan</h1>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Overview keuangan dan performa bisnis</p>
    </div>

    {{-- Alerts --}}
    @if($alerts['overdue_invoices'] > 0 || $alerts['bills_due_this_week'] > 0 || $alerts['low_cash'])
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @if($alerts['overdue_invoices'] > 0)
                <a href="{{ route('invoices.index') }}?status=sent" class="block p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-red-100 dark:bg-red-900/50 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-red-900 dark:text-red-200">{{ $alerts['overdue_invoices'] }} Invoice Overdue</p>
                            <p class="text-xs text-red-600 dark:text-red-400">Klik untuk lihat detail</p>
                        </div>
                    </div>
                </a>
            @endif

            @if($alerts['bills_due_this_week'] > 0)
                <a href="{{ route('vendor-bills.index') }}" class="block p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg hover:bg-yellow-100 dark:hover:bg-yellow-900/30 transition">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-yellow-100 dark:bg-yellow-900/50 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-yellow-900 dark:text-yellow-200">{{ $alerts['bills_due_this_week'] }} Bills Jatuh Tempo Minggu Ini</p>
                            <p class="text-xs text-yellow-600 dark:text-yellow-400">Klik untuk lihat detail</p>
                        </div>
                    </div>
                </a>
            @endif

            @if($alerts['low_cash'])
                <div class="p-4 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-orange-100 dark:bg-orange-900/50 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-orange-900 dark:text-orange-200">Saldo Kas & Bank Rendah</p>
                            <p class="text-xs text-orange-600 dark:text-orange-400">Rp {{ number_format($alerts['cash_balance'], 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Kas & Bank --}}
        <x-card class="!p-0">
            <div class="p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-slate-600 dark:text-slate-400">Kas & Bank</span>
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                    Rp {{ number_format($summary['cash_bank'], 0, ',', '.') }}
                </p>
            </div>
        </x-card>

        {{-- Piutang --}}
        <x-card class="!p-0">
            <div class="p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-slate-600 dark:text-slate-400">Piutang Belum Lunas</span>
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                    Rp {{ number_format($summary['piutang_belum_lunas'], 0, ',', '.') }}
                </p>
            </div>
        </x-card>

        {{-- Hutang --}}
        <x-card class="!p-0">
            <div class="p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-slate-600 dark:text-slate-400">Hutang Belum Lunas</span>
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                    Rp {{ number_format($summary['hutang_belum_lunas'], 0, ',', '.') }}
                </p>
            </div>
        </x-card>

        {{-- Profit Bulan Ini --}}
        <x-card class="!p-0">
            <div class="p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-slate-600 dark:text-slate-400">Profit Bulan Ini</span>
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
                <p class="text-2xl font-bold {{ $summary['profit_this_month'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    Rp {{ number_format($summary['profit_this_month'], 0, ',', '.') }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Margin: {{ number_format($summary['profit_margin'], 1) }}%
                </p>
            </div>
        </x-card>
    </div>

    {{-- Revenue vs Expense Bulan Ini --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-card>
            <x-slot:header>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Revenue Bulan Ini</h3>
            </x-slot:header>
            <div class="text-center py-4">
                <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                    Rp {{ number_format($summary['revenue_this_month'], 0, ',', '.') }}
                </p>
            </div>
        </x-card>

        <x-card>
            <x-slot:header>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Expense Bulan Ini</h3>
            </x-slot:header>
            <div class="text-center py-4">
                <p class="text-3xl font-bold text-red-600 dark:text-red-400">
                    Rp {{ number_format($summary['expense_this_month'], 0, ',', '.') }}
                </p>
            </div>
        </x-card>
    </div>

    {{-- Chart: Revenue vs Expense (6 Months) --}}
    <x-card>
        <x-slot:header>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Revenue vs Expense (6 Bulan Terakhir)</h3>
        </x-slot:header>
        <div class="p-4">
            <canvas id="revenueExpenseChart" height="80"></canvas>
        </div>
    </x-card>

    {{-- Top Customers & Top Expenses --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Top 5 Customers --}}
        <x-card>
            <x-slot:header>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Top 5 Customers (Tahun Ini)</h3>
            </x-slot:header>
            <div class="divide-y divide-slate-200 dark:divide-[#2d2d2d]">
                @forelse($topCustomers as $customer)
                    <div class="flex items-center justify-between py-3 px-4">
                        <span class="text-sm text-slate-700 dark:text-slate-300">{{ $customer['name'] }}</span>
                        <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                            Rp {{ number_format($customer['total'], 0, ',', '.') }}
                        </span>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-500 dark:text-slate-400">
                        Belum ada data
                    </div>
                @endforelse
            </div>
        </x-card>

        {{-- Top 5 Expenses --}}
        <x-card>
            <x-slot:header>
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Top 5 Beban (Tahun Ini)</h3>
            </x-slot:header>
            <div class="divide-y divide-slate-200 dark:divide-[#2d2d2d]">
                @forelse($topExpenses as $expense)
                    <div class="flex items-center justify-between py-3 px-4">
                        <div>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $expense['account_name'] }}</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 block">{{ $expense['account_code'] }}</span>
                        </div>
                        <span class="text-sm font-semibold text-red-600 dark:text-red-400">
                            Rp {{ number_format($expense['total'], 0, ',', '.') }}
                        </span>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-500 dark:text-slate-400">
                        Belum ada data
                    </div>
                @endforelse
            </div>
        </x-card>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue vs Expense Chart
    const ctx = document.getElementById('revenueExpenseChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($revenueExpenseChart['months']),
                datasets: [
                    {
                        label: 'Revenue',
                        data: @json($revenueExpenseChart['revenues']),
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Expense',
                        data: @json($revenueExpenseChart['expenses']),
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact', compactDisplay: 'short' }).format(value);
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection

