<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Master\Driver;
use App\Models\Accounting\JournalLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DriverSavingsController extends Controller
{
    /**
     * Display list of drivers with their savings balance
     */
    public function index(Request $request)
    {
        // Query drivers with savings summary from journal lines (Account 2160 & 2170)
        // Hardcoded for now based on known config, ideally fetch from config config('account_mapping.driver_savings')
        // In existing code: config/account_mapping.php uses env or default.
        // We know standard is 2160 and 2170.
        
        $savingsCode = '2160';
        $guaranteeCode = '2170';
        
        $drivers = Driver::query()
            // Savings Balance: Credit (In) - Debit (Out)
            ->withSum(['journalLines as savings_balance' => function($q) use ($savingsCode) {
                $q->whereHas('account', fn($a) => $a->where('code', $savingsCode));
                $q->select(DB::raw('SUM(credit - debit)'));
            }], 'credit') // The second arg 'credit' is just to satisfy withSum signature, ignored by select override
            // Guarantee Balance: Credit (In) - Debit (Out)
            ->withSum(['journalLines as guarantee_balance' => function($q) use ($guaranteeCode) {
                $q->whereHas('account', fn($a) => $a->where('code', $guaranteeCode));
                 $q->select(DB::raw('SUM(credit - debit)'));
            }], 'credit')
            ->when($request->get('q'), function($q, $search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('reports.driver-savings.index', compact('drivers'));
    }

    /**
     * Display savings detail for specific driver with mutations (Ledger View)
     */
    public function show(Request $request, Driver $driver)
    {
        $savingsCode = '2160'; // Hutang Tabungan
        $guaranteeCode = '2170'; // Hutang Jaminan
        
        // Get mutations from Journal Lines for BOTH accounts
        $mutations = JournalLine::query()
            ->with(['journal', 'jobOrder', 'account'])
            ->where('driver_id', $driver->id)
            ->whereHas('account', fn($a) => $a->whereIn('code', [$savingsCode, $guaranteeCode]))
            ->orderBy('journal_id', 'asc') // Order by insertion sequence usually correlates with time
            ->get();
            
        // Calculate running balances
        $savingsBalance = 0;
        $guaranteeBalance = 0;
        
        $formattedMutations = $mutations->map(function($line) use (&$savingsBalance, &$guaranteeBalance, $savingsCode, $guaranteeCode) {
            $credit = (float) $line->credit; // In
            $debit = (float) $line->debit;   // Out
            $balanceChange = $credit - $debit;
            
            $isSavings = $line->account->code == $savingsCode;
            
            // Mutation values for display
            $savingsIn = 0; $savingsOut = 0;
            $guaranteeIn = 0; $guaranteeOut = 0;
            
            if ($isSavings) {
                $savingsBalance += $balanceChange;
                if ($balanceChange >= 0) $savingsIn = $balanceChange;
                else $savingsOut = abs($balanceChange);
            } else {
                $guaranteeBalance += $balanceChange;
                 if ($balanceChange >= 0) $guaranteeIn = $balanceChange;
                else $guaranteeOut = abs($balanceChange);
            }
            
            // Try to extract Trip Info
            // Priority: JournalLine JO -> Journal Desc -> Manual parsing
            $tripDate = null;
            $route = '-';
            $nopol = '-';
            $joNumber = '-';
            
            if ($line->jobOrder) {
                $jo = $line->jobOrder;
                $tripDate = $jo->shipmentLegs()->first()?->schedule_date ?? $line->journal->journal_date;
                $route = ($jo->origin ?? '?') . ' - ' . ($jo->destination ?? '?');
                $nopol = $jo->shipmentLegs()->first()?->truck?->plate_number ?? '-';
                $joNumber = $jo->job_number;
            } elseif ($line->journal->source_type === 'driver_withdrawal') {
                $route = 'Pencairan Tabungan';
                $joNumber = $line->journal->memo;
            }
            
            return (object) [
                'date' => $line->journal->journal_date, // Posting Date
                'trip_date' => $tripDate,
                'description' => $line->description ?? $line->journal->memo,
                'route' => $route,
                'nopol' => $nopol,
                'doc_ref' => $joNumber,
                'job_order_id' => $line->job_order_id,
                
                // Savings
                'savings_in' => $savingsIn, // Credit
                'savings_out' => $savingsOut, // Debit
                'savings_balance' => $savingsBalance,
                
                // Guarantee
                'guarantee_in' => $guaranteeIn,
                'guarantee_out' => $guaranteeOut,
                'guarantee_balance' => $guaranteeBalance,
            ];
        });
        
        // Final Totals for Summary Cards
        $totalSavings = $savingsBalance;
        $totalGuarantee = $guaranteeBalance;
        
        return view('reports.driver-savings.show', compact('driver', 'formattedMutations', 'totalSavings', 'totalGuarantee'));
    }

    /**
     * API: Get driver savings balance for auto-fill
     */
    public function getBalance(Driver $driver)
    {
        $savingsCode = '2160';
        $balance = JournalLine::where('driver_id', $driver->id)
            ->whereHas('account', fn($a) => $a->where('code', $savingsCode))
            ->sum(DB::raw('credit - debit')); // Liability: Credit - Debit

        // Ensure no negative balance returned for withdrawal limit (?)
        // Or just return actual balance.
        return response()->json(['balance' => max(0, $balance)]);
    }
}
