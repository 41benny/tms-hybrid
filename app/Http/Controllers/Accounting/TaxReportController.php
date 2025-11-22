<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaxReportController extends Controller
{
    /**
     * Laporan PPN Keluaran (Output VAT)
     * Menampilkan daftar PPN yang ditagihkan ke customer dari invoice
     */
    public function ppnKeluaran(Request $request)
    {
        $month = $request->get('month') ?: now()->format('Y-m');
        $from = date('Y-m-01', strtotime($month));
        $to = date('Y-m-t', strtotime($month));

        // Query PPN Keluaran dari invoices
        $data = DB::table('journal_lines as jl')
            ->join('journals as j', 'j.id', '=', 'jl.journal_id')
            ->join('chart_of_accounts as a', 'a.id', '=', 'jl.account_id')
            ->join('invoices as i', function ($join) {
                $join->on('j.source_type', '=', DB::raw("'invoice'"))
                    ->on('j.source_id', '=', 'i.id');
            })
            ->join('customers as c', 'c.id', '=', 'i.customer_id')
            ->where('a.code', '2210') // PPN Keluaran
            ->where('j.status', 'posted')
            ->whereBetween('j.journal_date', [$from, $to])
            ->select(
                'j.journal_date',
                'i.invoice_number',
                'c.name as customer_name',
                'c.npwp',
                'i.subtotal as dpp',
                'jl.credit as ppn',
                DB::raw('(i.subtotal + jl.credit) as total')
            )
            ->orderBy('j.journal_date')
            ->get();

        $totalDpp = $data->sum('dpp');
        $totalPpn = $data->sum('ppn');
        $grandTotal = $data->sum('total');

        return view('reports.tax.ppn-keluaran', compact('data', 'month', 'from', 'to', 'totalDpp', 'totalPpn', 'grandTotal'));
    }

    /**
     * Laporan PPN Masukan (Input VAT)
     * Menampilkan daftar PPN yang dibayarkan ke vendor
     */
    public function ppnMasukan(Request $request)
    {
        $month = $request->get('month') ?: now()->format('Y-m');
        $from = date('Y-m-01', strtotime($month));
        $to = date('Y-m-t', strtotime($month));

        // Query PPN Masukan dari vendor bills
        $vendorBills = DB::table('journal_lines as jl')
            ->join('journals as j', 'j.id', '=', 'jl.journal_id')
            ->join('chart_of_accounts as a', 'a.id', '=', 'jl.account_id')
            ->join('vendor_bills as vb', function ($join) {
                $join->on('j.source_type', '=', DB::raw("'vendor_bill'"))
                    ->on('j.source_id', '=', 'vb.id');
            })
            ->join('vendors as v', 'v.id', '=', 'vb.vendor_id')
            ->where('a.code', '2220') // PPN Masukan
            ->where('j.status', 'posted')
            ->whereBetween('j.journal_date', [$from, $to])
            ->select(
                'j.journal_date',
                'vb.vendor_bill_number as document_number',
                'v.name as vendor_name',
                'v.npwp',
                DB::raw('(jl.debit / 0.11) as dpp'), // DPP = PPN / 11%
                'jl.debit as ppn',
                DB::raw('(jl.debit / 0.11 + jl.debit) as total'),
                DB::raw("'Vendor Bill' as source_type")
            );

        // Query PPN Masukan dari part purchases
        $partPurchases = DB::table('journal_lines as jl')
            ->join('journals as j', 'j.id', '=', 'jl.journal_id')
            ->join('chart_of_accounts as a', 'a.id', '=', 'jl.account_id')
            ->join('part_purchases as pp', function ($join) {
                $join->on('j.source_type', '=', DB::raw("'part_purchase'"))
                    ->on('j.source_id', '=', 'pp.id');
            })
            ->join('vendors as v', 'v.id', '=', 'pp.vendor_id')
            ->where('a.code', '2220') // PPN Masukan
            ->where('j.status', 'posted')
            ->whereBetween('j.journal_date', [$from, $to])
            ->select(
                'j.journal_date',
                'pp.purchase_number as document_number',
                'v.name as vendor_name',
                'v.npwp',
                DB::raw('(jl.debit / 0.11) as dpp'),
                'jl.debit as ppn',
                DB::raw('(jl.debit / 0.11 + jl.debit) as total'),
                DB::raw("'Part Purchase' as source_type")
            );

        // Union kedua query
        $data = $vendorBills->union($partPurchases)
            ->orderBy('journal_date')
            ->get();

        $totalDpp = $data->sum('dpp');
        $totalPpn = $data->sum('ppn');
        $grandTotal = $data->sum('total');

        return view('reports.tax.ppn-masukan', compact('data', 'month', 'from', 'to', 'totalDpp', 'totalPpn', 'grandTotal'));
    }

    /**
     * Rekapitulasi PPN
     * Summary PPN Keluaran vs PPN Masukan
     */
    public function ppnSummary(Request $request)
    {
        $month = $request->get('month') ?: now()->format('Y-m');
        $year = $request->get('year') ?: now()->format('Y');
        $view = $request->get('view', 'monthly'); // monthly or annual
        
        $from = date('Y-m-01', strtotime($month));
        $to = date('Y-m-t', strtotime($month));

        // Total PPN Keluaran (Monthly)
        $ppnKeluaran = DB::table('journal_lines as jl')
            ->join('journals as j', 'j.id', '=', 'jl.journal_id')
            ->join('chart_of_accounts as a', 'a.id', '=', 'jl.account_id')
            ->where('a.code', '2210')
            ->where('j.status', 'posted')
            ->whereBetween('j.journal_date', [$from, $to])
            ->sum('jl.credit');

        // Total PPN Masukan (Monthly)
        $ppnMasukan = DB::table('journal_lines as jl')
            ->join('journals as j', 'j.id', '=', 'jl.journal_id')
            ->join('chart_of_accounts as a', 'a.id', '=', 'jl.account_id')
            ->where('a.code', '2220')
            ->where('j.status', 'posted')
            ->whereBetween('j.journal_date', [$from, $to])
            ->sum('jl.debit');

        // PPN Kurang/(Lebih) Bayar
        $ppnKurangBayar = $ppnKeluaran - $ppnMasukan;

        // Annual Recap - Breakdown per bulan
        $annualData = [];
        if ($view === 'annual') {
            for ($m = 1; $m <= 12; $m++) {
                $monthStart = sprintf('%s-%02d-01', $year, $m);
                $monthEnd = date('Y-m-t', strtotime($monthStart));
                
                $keluaran = DB::table('journal_lines as jl')
                    ->join('journals as j', 'j.id', '=', 'jl.journal_id')
                    ->join('chart_of_accounts as a', 'a.id', '=', 'jl.account_id')
                    ->where('a.code', '2210')
                    ->where('j.status', 'posted')
                    ->whereBetween('j.journal_date', [$monthStart, $monthEnd])
                    ->sum('jl.credit');
                
                $masukan = DB::table('journal_lines as jl')
                    ->join('journals as j', 'j.id', '=', 'jl.journal_id')
                    ->join('chart_of_accounts as a', 'a.id', '=', 'jl.account_id')
                    ->where('a.code', '2220')
                    ->where('j.status', 'posted')
                    ->whereBetween('j.journal_date', [$monthStart, $monthEnd])
                    ->sum('jl.debit');
                
                $annualData[] = [
                    'month' => $m,
                    'month_name' => date('F', strtotime($monthStart)),
                    'month_name_id' => \Carbon\Carbon::parse($monthStart)->locale('id')->isoFormat('MMMM'),
                    'ppn_keluaran' => $keluaran,
                    'ppn_masukan' => $masukan,
                    'kurang_bayar' => $keluaran - $masukan,
                ];
            }
        }

        return view('reports.tax.ppn-summary', compact(
            'month', 
            'year',
            'from', 
            'to', 
            'view',
            'ppnKeluaran', 
            'ppnMasukan', 
            'ppnKurangBayar',
            'annualData'
        ));
    }

    /**
     * Laporan PPh 23 Dipotong
     * PPh 23 yang dipotong perusahaan dari pembayaran ke vendor
     */
    public function pph23Dipotong(Request $request)
    {
        $month = $request->get('month') ?: now()->format('Y-m');
        $from = date('Y-m-01', strtotime($month));
        $to = date('Y-m-t', strtotime($month));

        // Query PPh 23 dari vendor bills
        $vendorBills = DB::table('journal_lines as jl')
            ->join('journals as j', 'j.id', '=', 'jl.journal_id')
            ->join('chart_of_accounts as a', 'a.id', '=', 'jl.account_id')
            ->join('vendor_bills as vb', function ($join) {
                $join->on('j.source_type', '=', DB::raw("'vendor_bill'"))
                    ->on('j.source_id', '=', 'vb.id');
            })
            ->join('vendors as v', 'v.id', '=', 'vb.vendor_id')
            ->where('a.code', '2240') // Hutang PPh 23
            ->where('j.status', 'posted')
            ->whereBetween('j.journal_date', [$from, $to])
            ->select(
                'j.journal_date',
                'vb.vendor_bill_number as document_number',
                'v.name as vendor_name',
                'v.npwp',
                DB::raw('(jl.credit / 0.02) as dpp'), // DPP = PPh23 / 2%
                DB::raw('0.02 as tarif'),
                'jl.credit as pph23',
                DB::raw("'Jasa Angkutan' as jenis_jasa"),
                DB::raw("'Vendor Bill' as source_type")
            );

        // Query PPh 23 dari part purchases
        $partPurchases = DB::table('journal_lines as jl')
            ->join('journals as j', 'j.id', '=', 'jl.journal_id')
            ->join('chart_of_accounts as a', 'a.id', '=', 'jl.account_id')
            ->join('part_purchases as pp', function ($join) {
                $join->on('j.source_type', '=', DB::raw("'part_purchase'"))
                    ->on('j.source_id', '=', 'pp.id');
            })
            ->join('vendors as v', 'v.id', '=', 'pp.vendor_id')
            ->where('a.code', '2240')
            ->where('j.status', 'posted')
            ->whereBetween('j.journal_date', [$from, $to])
            ->select(
                'j.journal_date',
                'pp.purchase_number as document_number',
                'v.name as vendor_name',
                'v.npwp',
                DB::raw('(jl.credit / 0.02) as dpp'),
                DB::raw('0.02 as tarif'),
                'jl.credit as pph23',
                DB::raw("'Pembelian Barang' as jenis_jasa"),
                DB::raw("'Part Purchase' as source_type")
            );

        $data = $vendorBills->union($partPurchases)
            ->orderBy('journal_date')
            ->get();

        $totalDpp = $data->sum('dpp');
        $totalPph23 = $data->sum('pph23');

        return view('reports.tax.pph23-dipotong', compact('data', 'month', 'from', 'to', 'totalDpp', 'totalPph23'));
    }

    /**
     * Laporan PPh 23 Dipungut
     * PPh 23 yang dipotong customer dari pembayaran invoice
     */
    public function pph23Dipungut(Request $request)
    {
        $month = $request->get('month') ?: now()->format('Y-m');
        $from = date('Y-m-01', strtotime($month));
        $to = date('Y-m-t', strtotime($month));

        // Query PPh 23 dari customer payments (cash_bank_transactions dengan withholding)
        $data = DB::table('journal_lines as jl')
            ->join('journals as j', 'j.id', '=', 'jl.journal_id')
            ->join('chart_of_accounts as a', 'a.id', '=', 'jl.account_id')
            ->join('cash_bank_transactions as cbt', function ($join) {
                $join->on('j.source_type', '=', DB::raw("'customer_payment'"))
                    ->on('j.source_id', '=', 'cbt.id');
            })
            ->leftJoin('invoices as i', 'i.id', '=', 'cbt.invoice_id')
            ->leftJoin('customers as c', 'c.id', '=', 'cbt.customer_id')
            ->where('a.code', '1530') // Piutang PPh 23
            ->where('j.status', 'posted')
            ->whereBetween('j.journal_date', [$from, $to])
            ->select(
                'j.journal_date',
                'i.invoice_number as document_number',
                'c.name as customer_name',
                'c.npwp',
                DB::raw('(jl.debit / 0.02) as dpp'),
                DB::raw('0.02 as tarif'),
                'jl.debit as pph23'
            )
            ->orderBy('j.journal_date')
            ->get();

        $totalDpp = $data->sum('dpp');
        $totalPph23 = $data->sum('pph23');

        return view('reports.tax.pph23-dipungut', compact('data', 'month', 'from', 'to', 'totalDpp', 'totalPph23'));
    }

    /**
     * Summary PPh 23
     * Rekapitulasi PPh 23 Dipotong vs Dipungut
     */
    public function pph23Summary(Request $request)
    {
        $month = $request->get('month') ?: now()->format('Y-m');
        $year = $request->get('year') ?: now()->format('Y');
        $view = $request->get('view', 'monthly'); // monthly or annual
        
        $from = date('Y-m-01', strtotime($month));
        $to = date('Y-m-t', strtotime($month));

        // Total PPh 23 Dipotong (Hutang PPh 23) - Monthly
        $pph23Dipotong = DB::table('journal_lines as jl')
            ->join('journals as j', 'j.id', '=', 'jl.journal_id')
            ->join('chart_of_accounts as a', 'a.id', '=', 'jl.account_id')
            ->where('a.code', '2240')
            ->where('j.status', 'posted')
            ->whereBetween('j.journal_date', [$from, $to])
            ->sum('jl.credit');

        // Total PPh 23 Dipungut (Piutang PPh 23) - Monthly
        $pph23Dipungut = DB::table('journal_lines as jl')
            ->join('journals as j', 'j.id', '=', 'jl.journal_id')
            ->join('chart_of_accounts as a', 'a.id', '=', 'jl.account_id')
            ->where('a.code', '1530')
            ->where('j.status', 'posted')
            ->whereBetween('j.journal_date', [$from, $to])
            ->sum('jl.debit');

        // Net PPh 23 Position
        $netPph23 = $pph23Dipotong - $pph23Dipungut;

        // Annual Recap - Breakdown per bulan
        $annualData = [];
        if ($view === 'annual') {
            for ($m = 1; $m <= 12; $m++) {
                $monthStart = sprintf('%s-%02d-01', $year, $m);
                $monthEnd = date('Y-m-t', strtotime($monthStart));
                
                $dipotong = DB::table('journal_lines as jl')
                    ->join('journals as j', 'j.id', '=', 'jl.journal_id')
                    ->join('chart_of_accounts as a', 'a.id', '=', 'jl.account_id')
                    ->where('a.code', '2240')
                    ->where('j.status', 'posted')
                    ->whereBetween('j.journal_date', [$monthStart, $monthEnd])
                    ->sum('jl.credit');
                
                $dipungut = DB::table('journal_lines as jl')
                    ->join('journals as j', 'j.id', '=', 'jl.journal_id')
                    ->join('chart_of_accounts as a', 'a.id', '=', 'jl.account_id')
                    ->where('a.code', '1530')
                    ->where('j.status', 'posted')
                    ->whereBetween('j.journal_date', [$monthStart, $monthEnd])
                    ->sum('jl.debit');
                
                $annualData[] = [
                    'month' => $m,
                    'month_name' => date('F', strtotime($monthStart)),
                    'month_name_id' => \Carbon\Carbon::parse($monthStart)->locale('id')->isoFormat('MMMM'),
                    'pph23_dipotong' => $dipotong,
                    'pph23_dipungut' => $dipungut,
                    'net_pph23' => $dipotong - $dipungut,
                ];
            }
        }

        return view('reports.tax.pph23-summary', compact(
            'month',
            'year',
            'from',
            'to',
            'view',
            'pph23Dipotong',
            'pph23Dipungut',
            'netPph23',
            'annualData'
        ));
    }
}
