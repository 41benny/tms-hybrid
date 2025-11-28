<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;

class DriverAdvanceController extends Controller
{
    public function post(\App\Models\Operations\DriverAdvance $driverAdvance)
    {
        try {
            // Check if already posted
            if ($driverAdvance->journal_status === 'posted') {
                return redirect()->back()->with('error', 'Driver advance already posted to journal');
            }
            
            // Post to journal
            if (class_exists('App\\Services\\Accounting\\JournalService')) {
                $journalService = app('App\\Services\\Accounting\\JournalService');
                $journalService->postDriverAdvance($driverAdvance);
            }
            
            return redirect()->back()->with('success', 'Driver advance posted to journal successfully');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to post: ' . $e->getMessage());
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
