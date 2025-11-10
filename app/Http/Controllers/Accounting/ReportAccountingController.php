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
        $account = ChartOfAccount::findOrFail($accountId);

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
            ->select('j.journal_date', 'j.journal_no', 'j.memo', 'jl.description', 'jl.debit', 'jl.credit')
            ->get();

        return view('reports.general-ledger', compact('account', 'from', 'to', 'opening', 'entries'));
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
            ->orderBy('a.type')
            ->orderBy('a.code')
            ->get();

        $revenue = [];
        $expense = [];
        $totalRevenue = 0;
        $totalExpense = 0;
        foreach ($rows as $r) {
            if ($r->type === 'revenue') {
                $net = (float) $r->credit - (float) $r->debit;
                $totalRevenue += $net;
                $revenue[] = $r + (object) ['net' => $net];
            } else {
                $net = (float) $r->debit - (float) $r->credit;
                $totalExpense += $net;
                $expense[] = $r + (object) ['net' => $net];
            }
        }
        $profit = $totalRevenue - $totalExpense;

        return view('reports.profit-loss', compact('from', 'to', 'revenue', 'expense', 'totalRevenue', 'totalExpense', 'profit'));
    }

    public function balanceSheet(Request $request)
    {
        $asOf = $request->get('as_of') ?: now()->toDateString();
        $year = (int) date('Y', strtotime($asOf));

        $types = ['asset', 'liability', 'equity'];
        $accounts = ChartOfAccount::whereIn('type', $types)->orderBy('type')->orderBy('code')->get();

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
            if (abs($bal) < 0.00001) {
                continue;
            }
            $sections[$acc->type][] = ['acc' => $acc, 'balance' => $bal];
            $totals[$acc->type] += $bal;
        }

        return view('reports.balance-sheet', compact('asOf','sections','totals'));
    }
}
