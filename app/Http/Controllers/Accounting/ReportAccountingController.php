<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportAccountingController extends Controller
{
    public function trialBalance(Request $request)
    {
        $from = $request->get('from') ?: now()->startOfMonth()->toDateString();
        $to = $request->get('to') ?: now()->endOfMonth()->toDateString();
        $year = (int) date('Y', strtotime($from));

        $periodRows = DB::table('journal_lines as jl')
            ->join('journals as j', 'j.id', '=', 'jl.journal_id')
            ->where('j.status', 'posted')
            ->whereBetween('j.journal_date', [$from, $to])
            ->groupBy('jl.account_id')
            ->selectRaw('jl.account_id, SUM(jl.debit) as period_debit, SUM(jl.credit) as period_credit')
            ->get()
            ->keyBy('account_id');

        $openingRows = DB::table('opening_balances as ob')
            ->where('year', $year)
            ->groupBy('account_id')
            ->selectRaw('account_id, SUM(debit) as opening_debit, SUM(credit) as opening_credit')
            ->get()
            ->keyBy('account_id');

        $accounts = ChartOfAccount::orderBy('code')->get();

        $rows = [];
        $tot = ['opening' => 0, 'debit' => 0, 'credit' => 0, 'closing' => 0];
        foreach ($accounts as $acc) {
            $p = $periodRows->get($acc->id);
            $o = $openingRows->get($acc->id);
            $opening = ($o->opening_debit ?? 0) - ($o->opening_credit ?? 0);
            $debit = (float) ($p->period_debit ?? 0);
            $credit = (float) ($p->period_credit ?? 0);
            $closing = $opening + $debit - $credit;
            if ($opening == 0 && $debit == 0 && $credit == 0 && $closing == 0) {
                continue;
            }
            $rows[] = compact('acc', 'opening', 'debit', 'credit', 'closing');
            $tot['opening'] += $opening;
            $tot['debit'] += $debit;
            $tot['credit'] += $credit;
            $tot['closing'] += $closing;
        }

        return view('reports.trial-balance', [
            'rows' => $rows,
            'tot' => $tot,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function generalLedger(Request $request)
    {
        $from = $request->get('from') ?: now()->startOfMonth()->toDateString();
        $to = $request->get('to') ?: now()->endOfMonth()->toDateString();
        $accountId = (int) $request->get('account_id');
        $accounts = ChartOfAccount::where('status', 'active')->where('is_postable', true)->orderBy('code')->get();

        // Jika belum memilih akun, tampilkan halaman kosong dengan dropdown akun
        if (! $accountId) {
            return view('reports.general-ledger', [
                'accounts' => $accounts,
                'from' => $from,
                'to' => $to,
            ]);
        }

        $account = ChartOfAccount::find($accountId);
        if (! $account) {
            return view('reports.general-ledger', [
                'accounts' => $accounts,
                'from' => $from,
                'to' => $to,
            ])->with('error', 'Akun tidak ditemukan');
        }

        $year = (int) date('Y', strtotime($from));
        $openOb = DB::table('opening_balances')->where('year', $year)->where('account_id', $accountId)
            ->selectRaw('SUM(debit) as d, SUM(credit) as c')->first();
        $openMov = DB::table('journal_lines as jl')->join('journals as j', 'j.id', '=', 'jl.journal_id')
            ->where('j.status', 'posted')->where('jl.account_id', $accountId)
            ->where('j.journal_date', '<', $from)
            ->selectRaw('SUM(jl.debit) as d, SUM(jl.credit) as c')->first();
        $opening = ($openOb->d ?? 0) - ($openOb->c ?? 0) + (($openMov->d ?? 0) - ($openMov->c ?? 0));

        $entries = DB::table('journal_lines as jl')
            ->join('journals as j', 'j.id', '=', 'jl.journal_id')
            ->where('j.status', 'posted')
            ->where('jl.account_id', $accountId)
            ->whereBetween('j.journal_date', [$from, $to])
            ->orderBy('j.journal_date')
            ->orderBy('jl.id')
            ->select(
                'j.id as journal_id',
                'j.journal_date',
                'j.journal_no',
                'j.memo',
                'j.source_type',
                'j.source_id',
                'jl.description',
                'jl.debit',
                'jl.credit'
            )
            ->get();

        return view('reports.general-ledger', [
            'accounts' => $accounts,
            'account' => $account,
            'from' => $from,
            'to' => $to,
            'opening' => $opening,
            'entries' => $entries,
        ]);
    }

    public function profitLoss(Request $request)
    {
        $from = $request->get('from') ?: now()->startOfMonth()->toDateString();
        $to = $request->get('to') ?: now()->endOfMonth()->toDateString();

        $rows = DB::table('journal_lines as jl')
            ->join('journals as j', 'j.id', '=', 'jl.journal_id')
            ->join('chart_of_accounts as a', 'a.id', '=', 'jl.account_id')
            ->where('j.status', 'posted')
            ->whereBetween('j.journal_date', [$from, $to])
            ->whereIn('a.type', ['revenue', 'expense'])
            ->groupBy('jl.account_id', 'a.code', 'a.name', 'a.type')
            ->selectRaw('jl.account_id, a.code, a.name, a.type, SUM(jl.debit) as debit, SUM(jl.credit) as credit')
            ->orderBy('a.code')
            ->get();

        $revenue = [];
        $cogs = []; // Kepala 5
        $opex = []; // Kepala 6
        $other_income_expense = []; // Kepala 7

        $totalRevenue = 0;
        $totalCogs = 0;
        $totalOpex = 0;
        $totalOther = 0;

        foreach ($rows as $r) {
            $net = 0;
            // Revenue: Credit - Debit
            if ($r->type === 'revenue') {
                $net = (float) $r->credit - (float) $r->debit;
            } 
            // Expense: Debit - Credit
            else {
                $net = (float) $r->debit - (float) $r->credit;
            }
            $r->net = $net;

            // Categorize based on Account Code
            $code = $r->code;

            if (str_starts_with($code, '4')) {
                $revenue[] = $r;
                $totalRevenue += $net;
            } elseif (str_starts_with($code, '5')) {
                $cogs[] = $r;
                $totalCogs += $net;
            } elseif (str_starts_with($code, '6')) {
                $opex[] = $r;
                $totalOpex += $net;
            } elseif (str_starts_with($code, '7')) {
                // Kepala 7 bisa Revenue atau Expense
                // Jika Revenue (Credit > Debit), net positif. Jika Expense (Debit > Credit), net positif (karena logika expense di atas).
                // Namun untuk Other Income/Expense, biasanya kita ingin net value yang konsisten.
                // Mari kita buat standar: Pendapatan positif, Beban negatif untuk kategori ini agar penjumlahannya benar.
                
                // Reset net calculation for Head 7 to be consistent (Credit - Debit)
                $realNet = (float) $r->credit - (float) $r->debit;
                $r->net = $realNet;
                
                $other_income_expense[] = $r;
                $totalOther += $realNet;
            }
        }

        // Calculations
        $grossProfit = $totalRevenue - $totalCogs;
        $operatingProfit = $grossProfit - $totalOpex;
        $netProfit = $operatingProfit + $totalOther;

        return view('reports.profit-loss', compact(
            'from', 'to', 
            'revenue', 'totalRevenue',
            'cogs', 'totalCogs',
            'opex', 'totalOpex',
            'other_income_expense', 'totalOther',
            'grossProfit', 'operatingProfit', 'netProfit'
        ));
    }

    public function balanceSheet(Request $request)
    {
        $asOf = $request->get('as_of') ?: now()->toDateString();
        $year = (int) date('Y', strtotime($asOf));

        $types = ['asset', 'liability', 'equity'];
        $accounts = ChartOfAccount::whereIn('type', $types)->orderBy('type')->orderBy('code')->get();

        // 1. Get Balance Sheet Accounts Movements & Opening
        $moves = DB::table('journal_lines as jl')
            ->join('journals as j', 'j.id', '=', 'jl.journal_id')
            ->where('j.status', 'posted')
            ->where('j.journal_date', '<=', $asOf)
            ->groupBy('jl.account_id')
            ->selectRaw('jl.account_id, SUM(jl.debit) as d, SUM(jl.credit) as c')
            ->get()->keyBy('account_id');

        $opens = DB::table('opening_balances')->where('year', $year)
            ->groupBy('account_id')->selectRaw('account_id, SUM(debit) as d, SUM(credit) as c')->get()->keyBy('account_id');

        $sections = ['asset' => [], 'liability' => [], 'equity' => []];
        $totals = ['asset' => 0, 'liability' => 0, 'equity' => 0];

        foreach ($accounts as $acc) {
            $bal = (($opens->get($acc->id)->d ?? 0) - ($opens->get($acc->id)->c ?? 0))
                + (($moves->get($acc->id)->d ?? 0) - ($moves->get($acc->id)->c ?? 0));
            
            // For Liability and Equity, Balance is usually Credit (Negative in our Debit-Credit logic if Asset is positive)
            // But usually we display them as positive numbers on the report.
            // Asset: Debit - Credit (Positive)
            // Liability/Equity: Credit - Debit (Positive)
            
            if (abs($bal) < 0.00001) {
                continue;
            }

            // Adjust sign for display
            $displayBal = $bal;
            if ($acc->type == 'liability' || $acc->type == 'equity') {
                $displayBal = -1 * $bal;
            }

            $sections[$acc->type][] = ['acc' => $acc, 'balance' => $displayBal];
            $totals[$acc->type] += $displayBal;
        }

        // 2. Calculate Current Year Earnings (Revenue - Expense)
        // From start of year to As Of date
        $startOfYear = $year . '-01-01';

        // Get all Revenue and Expense movements
        $plMoves = DB::table('journal_lines as jl')
            ->join('journals as j', 'j.id', '=', 'jl.journal_id')
            ->join('chart_of_accounts as a', 'a.id', '=', 'jl.account_id')
            ->where('j.status', 'posted')
            ->whereBetween('j.journal_date', [$startOfYear, $asOf])
            ->whereIn('a.type', ['revenue', 'expense'])
            ->selectRaw('SUM(jl.credit) - SUM(jl.debit) as net_amount')
            ->value('net_amount');
            
        $currentEarnings = (float) ($plMoves ?? 0);

        // Add Current Earnings to Equity Total
        $totals['equity'] += $currentEarnings;

        return view('reports.balance-sheet', compact('asOf', 'sections', 'totals', 'currentEarnings'));
    }

    public function cashFlow(Request $request)
    {
        $from = $request->get('from') ?: now()->startOfMonth()->toDateString();
        $to = $request->get('to') ?: now()->endOfMonth()->toDateString();

        // Get all movements in the period
        $movements = DB::table('journal_lines as jl')
            ->join('journals as j', 'j.id', '=', 'jl.journal_id')
            ->join('chart_of_accounts as a', 'a.id', '=', 'jl.account_id')
            ->where('j.status', 'posted')
            ->whereBetween('j.journal_date', [$from, $to])
            ->groupBy('jl.account_id', 'a.code', 'a.name', 'a.type', 'a.is_cash', 'a.is_bank')
            ->selectRaw('jl.account_id, a.code, a.name, a.type, a.is_cash, a.is_bank, SUM(jl.debit) as debit, SUM(jl.credit) as credit')
            ->get();

        // Structure based on user request
        $report = [
            'operating' => [
                'net_income' => 0,
                'adjustments' => [
                    'depreciation' => 0,
                    'other' => 0,
                ],
                'changes' => [
                    'receivables' => 0, // 1200, 1300
                    'inventory' => 0,   // 1400
                    'prepayments' => 0, // 1500, 2220 (PPN Masukan)
                    'payables' => 0,    // 2100
                    'driver_balances' => 0, // 2155, 2160, 2170
                    'taxes_payable' => 0, // 2200, 2210, 2230, 2240
                    'accruals' => 0,    // 2300 group
                    'other_current_liabilities' => 0,
                ]
            ],
            'investing' => [
                'fixed_assets' => 0, // 16xx (excluding accumulated depreciation)
                'other' => 0,
            ],
            'financing' => [
                'loans' => 0, // 24xx, 25xx
                'equity' => 0, // 3xxx
            ]
        ];

        // 1. Calculate Net Income
        $revenue = 0;
        $expense = 0;

        foreach ($movements as $mov) {
            // Skip cash accounts for direct method, but we are doing indirect.
            // Net income calculation:
            if ($mov->type === 'revenue') {
                $revenue += ($mov->credit - $mov->debit);
            } elseif ($mov->type === 'expense') {
                $expense += ($mov->debit - $mov->credit);
            }
        }
        $report['operating']['net_income'] = $revenue - $expense;

        // 2. Process Balance Sheet Items
        foreach ($movements as $mov) {
            // Skip P&L accounts as they are already in Net Income
            if ($mov->type === 'revenue' || $mov->type === 'expense') {
                continue;
            }
            
            // Skip Cash/Bank accounts (they are the result)
            if ($mov->is_cash || $mov->is_bank) {
                continue;
            }

            // Cash Flow Impact = Credit - Debit
            // Asset Increase (Debit) -> Cash Outflow (Negative)
            // Liability Increase (Credit) -> Cash Inflow (Positive)
            $cfImpact = $mov->credit - $mov->debit;

            if (abs($cfImpact) < 0.01) continue;

            $code = $mov->code;

            // --- OPERATING: ADJUSTMENTS ---
            // Accumulated Depreciation (1620, 1640, etc - usually contain "Akumulasi" or "Penyusutan")
            if (str_contains(strtolower($mov->name), 'akumulasi') || str_contains(strtolower($mov->name), 'penyusutan')) {
                $report['operating']['adjustments']['depreciation'] += $cfImpact;
                continue;
            }

            // --- OPERATING: WORKING CAPITAL ---
            // Receivables (12xx, 13xx, 1530)
            if (str_starts_with($code, '12') || str_starts_with($code, '13') || $code == '1530') {
                $report['operating']['changes']['receivables'] += $cfImpact;
            }
            // Inventory (14xx)
            elseif (str_starts_with($code, '14')) {
                $report['operating']['changes']['inventory'] += $cfImpact;
            }
            // Prepayments & PPN Masukan (15xx, 2220)
            elseif (str_starts_with($code, '15') || $code == '2220') {
                $report['operating']['changes']['prepayments'] += $cfImpact;
            }
            // Trade Payables (2100)
            elseif ($code == '2100') {
                $report['operating']['changes']['payables'] += $cfImpact;
            }
            // Driver Balances (2155, 2160, 2170)
            elseif (in_array($code, ['2155', '2160', '2170'])) {
                $report['operating']['changes']['driver_balances'] += $cfImpact;
            }
            // Taxes Payable (2200, 2210, 2230, 2240) - Excluding 2220 (PPN Masukan) which is asset
            elseif (str_starts_with($code, '22') && $code != '2220') {
                $report['operating']['changes']['taxes_payable'] += $cfImpact;
            }
            // Accruals (23xx)
            elseif (str_starts_with($code, '23')) {
                $report['operating']['changes']['accruals'] += $cfImpact;
            }
            
            // --- INVESTING ---
            // Fixed Assets (16xx) - Excluding Accum Depr which is handled above
            elseif (str_starts_with($code, '16')) {
                $report['investing']['fixed_assets'] += $cfImpact;
            }

            // --- FINANCING ---
            // Loans (24xx, 25xx)
            elseif (str_starts_with($code, '24') || str_starts_with($code, '25')) {
                $report['financing']['loans'] += $cfImpact;
            }
            // Equity (3xxx)
            elseif (str_starts_with($code, '3')) {
                $report['financing']['equity'] += $cfImpact;
            }
            
            // Fallback for others
            else {
                // If it's a liability, likely operating
                if ($mov->type === 'liability') {
                    $report['operating']['changes']['other_current_liabilities'] += $cfImpact;
                }
                // If it's an asset, likely investing (if non-current) or operating (if current)
                // Assuming others are investing for now if not captured above
                else {
                    $report['investing']['other'] += $cfImpact;
                }
            }
        }

        // Calculate Opening Cash Balance
        $cashAccounts = ChartOfAccount::where(function($q) {
            $q->where('is_cash', true)->orWhere('is_bank', true);
        })->pluck('id');

        $openingCash = 0;
        $year = (int) date('Y', strtotime($from));
        
        $obSum = DB::table('opening_balances')
            ->where('year', $year)
            ->whereIn('account_id', $cashAccounts)
            ->selectRaw('SUM(debit) - SUM(credit) as val')
            ->value('val');
        $openingCash += ($obSum ?? 0);

        $prevMov = DB::table('journal_lines as jl')
            ->join('journals as j', 'j.id', '=', 'jl.journal_id')
            ->where('j.status', 'posted')
            ->where('j.journal_date', '<', $from)
            ->whereIn('jl.account_id', $cashAccounts)
            ->selectRaw('SUM(jl.debit) - SUM(jl.credit) as val')
            ->value('val');
        $openingCash += ($prevMov ?? 0);

        return view('reports.cash-flow', compact('from', 'to', 'report', 'openingCash'));
    }
}
