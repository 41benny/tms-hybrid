<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalLine;
use App\Models\Finance\Invoice;
use App\Models\Finance\VendorBill;
use Illuminate\Support\Facades\DB;

class FinanceDashboardController extends Controller
{
    public function index()
    {
        // 1. Summary Cards
        $summary = $this->getSummaryCards();

        // 2. Revenue vs Expense Chart (6 months)
        $revenueExpenseChart = $this->getRevenueExpenseChart();

        // 3. Top 5 Customers by Revenue
        $topCustomers = $this->getTopCustomers();

        // 4. Top 5 Expense Accounts
        $topExpenses = $this->getTopExpenses();

        // 5. Alerts
        $alerts = $this->getAlerts();

        return view('finance.dashboard', compact(
            'summary',
            'revenueExpenseChart',
            'topCustomers',
            'topExpenses',
            'alerts'
        ));
    }

    protected function getSummaryCards(): array
    {
        // Kas & Bank (akun 1100, 1110)
        $cashBankAccounts = ChartOfAccount::whereIn('code', ['1100', '1110'])->pluck('id');
        $cashBank = JournalLine::whereIn('account_id', $cashBankAccounts)
            ->selectRaw('SUM(debit - credit) as balance')
            ->value('balance') ?? 0;

        // Piutang (akun 1200)
        $arAccount = ChartOfAccount::where('code', '1200')->first();
        $piutang = 0;
        if ($arAccount) {
            $piutang = JournalLine::where('account_id', $arAccount->id)
                ->selectRaw('SUM(debit - credit) as balance')
                ->value('balance') ?? 0;
        }

        // Piutang Belum Lunas (dari invoices)
        $piutangBelumLunas = Invoice::whereIn('status', ['sent', 'partially_paid'])
            ->sum('total_amount');

        // Hutang (akun 2100)
        $apAccount = ChartOfAccount::where('code', '2100')->first();
        $hutang = 0;
        if ($apAccount) {
            // Hutang = Credit - Debit (liability account)
            $hutang = JournalLine::where('account_id', $apAccount->id)
                ->selectRaw('SUM(credit - debit) as balance')
                ->value('balance') ?? 0;
        }

        // Hutang Belum Lunas (dari vendor bills)
        $hutangBelumLunas = VendorBill::whereIn('status', ['received', 'partially_paid'])
            ->sum('total_amount');

        // Revenue bulan ini (akun 4xxx)
        $revenueAccounts = ChartOfAccount::where('code', 'like', '4%')->pluck('id');
        $revenueThisMonth = JournalLine::whereIn('account_id', $revenueAccounts)
            ->whereHas('journal', function ($q) {
                $q->whereYear('journal_date', now()->year)
                    ->whereMonth('journal_date', now()->month);
            })
            ->selectRaw('SUM(credit - debit) as total')
            ->value('total') ?? 0;

        // Expense bulan ini (akun 5xxx, 6xxx)
        $expenseAccounts = ChartOfAccount::where(function ($q) {
            $q->where('code', 'like', '5%')
                ->orWhere('code', 'like', '6%');
        })->pluck('id');
        $expenseThisMonth = JournalLine::whereIn('account_id', $expenseAccounts)
            ->whereHas('journal', function ($q) {
                $q->whereYear('journal_date', now()->year)
                    ->whereMonth('journal_date', now()->month);
            })
            ->selectRaw('SUM(debit - credit) as total')
            ->value('total') ?? 0;

        // Profit bulan ini
        $profitThisMonth = $revenueThisMonth - $expenseThisMonth;
        $profitMargin = $revenueThisMonth > 0 ? ($profitThisMonth / $revenueThisMonth) * 100 : 0;

        return [
            'cash_bank' => $cashBank,
            'piutang' => $piutang,
            'piutang_belum_lunas' => $piutangBelumLunas,
            'hutang' => $hutang,
            'hutang_belum_lunas' => $hutangBelumLunas,
            'revenue_this_month' => $revenueThisMonth,
            'expense_this_month' => $expenseThisMonth,
            'profit_this_month' => $profitThisMonth,
            'profit_margin' => $profitMargin,
        ];
    }

    protected function getRevenueExpenseChart(): array
    {
        $months = [];
        $revenues = [];
        $expenses = [];

        // Get data for last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $year = $date->year;
            $month = $date->month;
            $months[] = $date->format('M Y');

            // Revenue (akun 4xxx)
            $revenueAccounts = ChartOfAccount::where('code', 'like', '4%')->pluck('id');
            $revenue = JournalLine::whereIn('account_id', $revenueAccounts)
                ->whereHas('journal', function ($q) use ($year, $month) {
                    $q->whereYear('journal_date', $year)
                        ->whereMonth('journal_date', $month);
                })
                ->selectRaw('SUM(credit - debit) as total')
                ->value('total') ?? 0;
            $revenues[] = $revenue;

            // Expense (akun 5xxx, 6xxx)
            $expenseAccounts = ChartOfAccount::where(function ($q) {
                $q->where('code', 'like', '5%')
                    ->orWhere('code', 'like', '6%');
            })->pluck('id');
            $expense = JournalLine::whereIn('account_id', $expenseAccounts)
                ->whereHas('journal', function ($q) use ($year, $month) {
                    $q->whereYear('journal_date', $year)
                        ->whereMonth('journal_date', $month);
                })
                ->selectRaw('SUM(debit - credit) as total')
                ->value('total') ?? 0;
            $expenses[] = $expense;
        }

        return [
            'months' => $months,
            'revenues' => $revenues,
            'expenses' => $expenses,
        ];
    }

    protected function getTopCustomers(): array
    {
        // Top 5 customers by revenue (this year)
        return JournalLine::whereNotNull('customer_id')
            ->whereHas('journal', function ($q) {
                $q->whereYear('journal_date', now()->year);
            })
            ->select('customer_id', DB::raw('SUM(debit - credit) as total'))
            ->groupBy('customer_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('customer:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->customer->name ?? 'Unknown',
                    'total' => abs($item->total),
                ];
            })
            ->toArray();
    }

    protected function getTopExpenses(): array
    {
        // Top 5 expense accounts (this year)
        $expenseAccounts = ChartOfAccount::where(function ($q) {
            $q->where('code', 'like', '5%')
                ->orWhere('code', 'like', '6%');
        })->pluck('id');

        return JournalLine::whereIn('account_id', $expenseAccounts)
            ->whereHas('journal', function ($q) {
                $q->whereYear('journal_date', now()->year);
            })
            ->select('account_id', DB::raw('SUM(debit - credit) as total'))
            ->groupBy('account_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('account:id,code,name')
            ->get()
            ->map(function ($item) {
                return [
                    'account_code' => $item->account->code ?? 'Unknown',
                    'account_name' => $item->account->name ?? 'Unknown',
                    'total' => abs($item->total),
                ];
            })
            ->toArray();
    }

    protected function getAlerts(): array
    {
        // Overdue invoices
        $overdueInvoices = Invoice::where('status', 'sent')
            ->where('due_date', '<', now())
            ->count();

        // Bills due this week
        $billsDueThisWeek = VendorBill::whereIn('status', ['received', 'partially_paid'])
            ->whereBetween('due_date', [now(), now()->addWeek()])
            ->count();

        // Low cash warning (< 5 juta)
        $cashBankAccounts = ChartOfAccount::whereIn('code', ['1100', '1110'])->pluck('id');
        $cashBank = JournalLine::whereIn('account_id', $cashBankAccounts)
            ->selectRaw('SUM(debit - credit) as balance')
            ->value('balance') ?? 0;
        $lowCash = $cashBank < 5000000;

        return [
            'overdue_invoices' => $overdueInvoices,
            'bills_due_this_week' => $billsDueThisWeek,
            'low_cash' => $lowCash,
            'cash_balance' => $cashBank,
        ];
    }
}
