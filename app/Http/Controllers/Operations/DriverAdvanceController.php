<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use Log;

class DriverAdvanceController extends Controller
{
    public function post(\App\Models\Operations\DriverAdvance $driverAdvance)
    {
        try {
            // Check if already posted
            if ($driverAdvance->journal_status === 'posted') {
                return redirect()->back()->with('error', 'Driver advance already posted to journal');
            }
            
            // Load relationships for validation
            $driverAdvance->load(['shipmentLeg.mainCost', 'driver']);
            
            // Validate required data
            if (!$driverAdvance->shipmentLeg) {
                return redirect()->back()->with('error', 'Driver advance tidak memiliki shipment leg. Tidak bisa di-post.');
            }
            
            if (!$driverAdvance->shipmentLeg->mainCost) {
                return redirect()->back()->with('error', 'Shipment leg tidak memiliki main cost. Tidak bisa di-post.');
            }
            
            // Post to journal
            $journalService = app(\App\Services\Accounting\JournalService::class);
            $journal = $journalService->postDriverAdvance($driverAdvance);
            
            \Log::info('Driver advance posted successfully', [
                'advance_id' => $driverAdvance->id,
                'advance_number' => $driverAdvance->advance_number,
                'journal_id' => $journal->id
            ]);
            
            return redirect()->back()->with('success', 'Driver advance berhasil di-post ke jurnal');
            
        } catch (\Exception $e) {
            \Log::error('Failed to post driver advance', [
                'advance_id' => $driverAdvance->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Gagal post ke jurnal: ' . $e->getMessage());
        }
    }

    public function unpost(\App\Models\Operations\DriverAdvance $driverAdvance)
    {
        try {
            // Check if posted
            if ($driverAdvance->journal_status !== 'posted') {
                return redirect()->back()->with('error', 'Driver advance not posted yet');
            }
            
            // Check if already paid
            if ($driverAdvance->status !== 'pending') {
                return redirect()->back()->with('error', 'Cannot unpost paid driver advance');
            }
            
            // Delete journal
            if ($driverAdvance->journal_id) {
                $journal = \App\Models\Accounting\Journal::find($driverAdvance->journal_id);
                if ($journal) {
                    $journal->lines()->delete();
                    $journal->delete();
                }
            }
            
            // Update status
            $driverAdvance->update([
                'journal_status' => 'unposted',
                'journal_id' => null
            ]);
            
            return redirect()->back()->with('success', 'Driver advance unposted successfully');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to unpost: ' . $e->getMessage());
        }
    }
}
