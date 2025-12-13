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
            ->orderBy('journal_id', 'asc')
            ->get();
        
        // Group by journal_id to combine savings + guarantee in one row
        $groupedByJournal = $mutations->groupBy('journal_id');
        
        // Calculate running balances
        $savingsBalance = 0;
        $guaranteeBalance = 0;
        
        $formattedMutations = collect();
        
        foreach ($groupedByJournal as $journalId => $lines) {
            // Calculate savings and guarantee amounts for this journal
            $savingsIn = 0; $savingsOut = 0;
            $guaranteeIn = 0; $guaranteeOut = 0;
            
            $firstLine = $lines->first();
            $journal = $firstLine->journal;
            $jobOrder = $firstLine->jobOrder;
            
            foreach ($lines as $line) {
                $credit = (float) $line->credit;
                $debit = (float) $line->debit;
                $balanceChange = $credit - $debit;
                
                if ($line->account->code == $savingsCode) {
                    $savingsBalance += $balanceChange;
                    if ($balanceChange >= 0) $savingsIn += $balanceChange;
                    else $savingsOut += abs($balanceChange);
                } else {
                    $guaranteeBalance += $balanceChange;
                    if ($balanceChange >= 0) $guaranteeIn += $balanceChange;
                    else $guaranteeOut += abs($balanceChange);
                }
            }
            
            // Extract trip info
            $tripDate = null;
            $routeInfo = null;
            $nopol = '-';
            $joNumber = '-';
            $voucherNo = $journal->memo; // Default memo (usually holds voucher no or desc)
            
            if ($jobOrder) {
                $driverLeg = $jobOrder->shipmentLegs()->where('driver_id', $driver->id)->first();
                // Use Schedule Date as main date, fallback to Journal Date
                $tripDate = $driverLeg?->schedule_date ?? $journal->journal_date;
                
                // Get cargo/equipment names (unit yang dimuat)
                $equipmentNames = $jobOrder->items()
                    ->with('equipment')
                    ->get()
                    ->pluck('equipment.name')
                    ->filter()
                    ->implode(', ');
                
                $cargoInfo = $equipmentNames ?: 'Muatan N/A';
                $nopol = $driverLeg?->truck?->plate_number ?? '-';
                
                // Format: [Cargo/Equipment] - Origin - Destination
                $routeInfo = $cargoInfo . ' - ' . ($jobOrder->origin ?? '?') . ' - ' . ($jobOrder->destination ?? '?');
                $joNumber = $jobOrder->job_number;
            } elseif ($journal->source_type === 'driver_withdrawal') {
                // For withdrawal, get the voucher number
                if ($journal->source_type === 'driver_withdrawal' && $journal->source_id) {
                     $cbTrx = \App\Models\Finance\CashBankTransaction::find($journal->source_id);
                     if ($cbTrx) {
                         $voucherNo = $cbTrx->transaction_number;
                     }
                }
                $routeInfo = 'Penarikan Tabungan ' . $voucherNo;
                $tripDate = $journal->journal_date;
                $joNumber = '-';
            }
            
            // Description Column Content
            // Request: "kolom rute juga isinya bisa replace isi di kolom keterangan"
            $finalDescription = $routeInfo;
            
            $formattedMutations->push((object) [
                'date' => $tripDate, // Use Trip Date as the main Display Date
                'description' => $finalDescription,
                'nopol' => $nopol,
                'doc_ref' => $joNumber,
                'job_order_id' => $firstLine->job_order_id,
                
                // Savings
                'savings_in' => $savingsIn,
                'savings_out' => $savingsOut,
                'savings_balance' => $savingsBalance,
                
                // Guarantee
                'guarantee_in' => $guaranteeIn,
                'guarantee_out' => $guaranteeOut,
                'guarantee_balance' => $guaranteeBalance,
            ]);
        }
        
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
