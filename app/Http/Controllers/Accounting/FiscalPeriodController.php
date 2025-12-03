<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\FiscalPeriod;
use Illuminate\Http\Request;

class FiscalPeriodController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) ($request->get('year') ?: date('Y'));
        $periods = FiscalPeriod::query()
            ->where('year', $year)
            ->orderBy('month')
            ->get();

        $years = FiscalPeriod::query()->select('year')->distinct()->orderByDesc('year')->pluck('year');

        return view('accounting.periods.index', compact('periods', 'year', 'years'));
    }

    protected function ensureSuperAdmin(): void
    {
        if (! auth()->user()?->isSuperAdmin()) {
            abort(403, 'Hanya super admin yang boleh melakukan aksi ini');
        }
    }

    public function close(FiscalPeriod $period, Request $request)
    {
        $this->ensureSuperAdmin();
        if ($period->status !== 'open') {
            return back()->with('error', 'Hanya periode OPEN yang bisa di-close');
        }

        // SAFEGUARD 1: Tidak bisa close periode bulan berjalan
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('m');
        if ($period->year == $currentYear && $period->month == $currentMonth) {
            return back()->with('error', 'Tidak bisa close periode bulan berjalan. Tunggu sampai bulan depan.');
        }

        // SAFEGUARD 2: Tidak bisa close periode masa depan
        $periodDate = mktime(0, 0, 0, $period->month, 1, $period->year);
        $currentDate = time();
        if ($periodDate > $currentDate) {
            return back()->with('error', 'Tidak bisa close periode masa depan.');
        }

        // Validasi: Cek apakah ada transaksi yang belum di-jurnal
        $startDate = $period->start_date;
        $endDate = $period->end_date;
        $previewOnly = $request->boolean('preview');

        $errors = [];

        // ========================================
        // VALIDASI CRITICAL: EXPENSE-REVENUE MATCHING
        // ========================================
        
        // 1. CRITICAL: Cek Vendor Bill yang masih DRAFT padahal Invoice terkait sudah SENT
        // Ini sangat berbahaya karena revenue sudah tercatat tapi expense belum tercatat
        $draftVendorBillsWithSentInvoices = \App\Models\Finance\VendorBill::query()
            ->whereBetween('bill_date', [$startDate, $endDate])
            ->where('status', 'draft')
            ->whereHas('items.shipmentLeg.jobOrder.invoices', function($q) {
                $q->whereIn('status', ['sent', 'partial', 'paid']);
            })
            ->count();

        if ($draftVendorBillsWithSentInvoices > 0) {
            $errors[] = "CRITICAL: {$draftVendorBillsWithSentInvoices} vendor bill masih DRAFT padahal invoice terkait sudah SENT (revenue tercatat tapi expense tidak tercatat - profit overstated!)";
        }

        // ========================================
        // VALIDASI: UNGENERATED TRANSACTIONS
        // ========================================

        // 2. Cek Shipment Leg yang belum di-generate Vendor Bill padahal Job Order sudah di-invoice dan invoice sudah SENT
        // Ini penting untuk expense-revenue matching
        $legsWithoutVendorBill = \App\Models\Operations\ShipmentLeg::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereDoesntHave('vendorBillItems')
            ->whereIn('status', ['completed', 'in_transit', 'delivered'])
            ->whereHas('jobOrder.invoices', function($q) {
                $q->whereIn('status', ['sent', 'partial', 'paid']);
            })
            ->count();

        if ($legsWithoutVendorBill > 0) {
            $errors[] = "{$legsWithoutVendorBill} shipment leg belum di-generate vendor bill padahal job order sudah di-invoice dan invoice sudah SENT (beban belum tercatat tapi revenue sudah tercatat)";
        }

        // 3. Cek Job Order yang belum di-generate Invoice
        $jobOrdersWithoutInvoice = \App\Models\Operations\JobOrder::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereDoesntHave('invoiceItems')
            ->whereIn('status', ['completed', 'in_progress'])
            ->count();

        if ($jobOrdersWithoutInvoice > 0) {
            $errors[] = "{$jobOrdersWithoutInvoice} job order belum di-generate invoice (pendapatan belum tercatat)";
        }

        // ========================================
        // VALIDASI: UNPUBLISHED TRANSACTIONS
        // ========================================

        // 4. Cek Invoice yang belum di-mark sent (status: draft)
        $unpublishedInvoices = \App\Models\Finance\Invoice::query()
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->where('status', 'draft')
            ->count();

        if ($unpublishedInvoices > 0) {
            $errors[] = "{$unpublishedInvoices} invoice masih berstatus DRAFT (belum di-mark as sent)";
        }

        // 5. Cek Vendor Bill yang belum di-mark received (status: draft)
        $unpublishedVendorBills = \App\Models\Finance\VendorBill::query()
            ->whereBetween('bill_date', [$startDate, $endDate])
            ->where('status', 'draft')
            ->count();

        if ($unpublishedVendorBills > 0) {
            $errors[] = "{$unpublishedVendorBills} vendor bill masih berstatus DRAFT (belum di-mark as received)";
        }

        // ========================================
        // VALIDASI: MISSING JOURNAL ENTRIES
        // ========================================

        // 6. Cek apakah ada invoice yang sudah di-mark sent tapi belum ada jurnalnya
        $invoicesSent = \App\Models\Finance\Invoice::query()
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->whereIn('status', ['sent', 'partial', 'paid'])
            ->get();

        $invoicesWithoutJournal = 0;
        foreach ($invoicesSent as $inv) {
            $hasJournal = \App\Models\Accounting\Journal::query()
                ->where('source_type', 'invoice')
                ->where('source_id', $inv->id)
                ->exists();
            if (!$hasJournal) {
                $invoicesWithoutJournal++;
            }
        }

        if ($invoicesWithoutJournal > 0) {
            $errors[] = "{$invoicesWithoutJournal} invoice sudah di-mark sent tapi belum ada jurnalnya (error posting)";
        }

        // 7. Cek apakah ada vendor bill yang sudah di-mark received tapi belum ada jurnalnya
        $vendorBillsReceived = \App\Models\Finance\VendorBill::query()
            ->whereBetween('bill_date', [$startDate, $endDate])
            ->whereIn('status', ['received', 'partially_paid', 'paid'])
            ->get();

        $vendorBillsWithoutJournal = 0;
        foreach ($vendorBillsReceived as $bill) {
            $hasJournal = \App\Models\Accounting\Journal::query()
                ->where('source_type', 'vendor_bill')
                ->where('source_id', $bill->id)
                ->exists();
            if (!$hasJournal) {
                $vendorBillsWithoutJournal++;
            }
        }

        if ($vendorBillsWithoutJournal > 0) {
            $errors[] = "{$vendorBillsWithoutJournal} vendor bill sudah di-mark received tapi belum ada jurnalnya (error posting)";
        }

        // Jika ada error, tampilkan dan jangan close
        if (count($errors) > 0) {
            $errorMessage = "Periode tidak bisa di-close karena:\n" . implode("\n", array_map(fn($e) => "- {$e}", $errors));
            return back()->with('error', $errorMessage);
        }

        if ($previewOnly) {
            return back()->with('success', 'Validasi lulus. Tidak ada blocker, periode aman untuk di-close.');
        }

        // Jika semua aman, close periode
        $period->update(['status' => 'closed']);

        // AUTO-CREATE: buat periode bulan berikut jika belum ada
        $nextYear = $period->month === 12 ? $period->year + 1 : $period->year;
        $nextMonth = $period->month === 12 ? 1 : $period->month + 1;
        $existingNext = FiscalPeriod::query()
            ->where('year', $nextYear)
            ->where('month', $nextMonth)
            ->first();
        if (! $existingNext) {
            $startDate = date('Y-m-01', mktime(0,0,0,$nextMonth,1,$nextYear));
            $endDate = date('Y-m-t', mktime(0,0,0,$nextMonth,1,$nextYear));
            FiscalPeriod::create([
                'year' => $nextYear,
                'month' => $nextMonth,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'open',
            ]);
        }

        return back()->with('success', 'Periode berhasil di-close. Periode berikut otomatis dibuka (OPEN) untuk transaksi baru.');
    }

    public function reopen(FiscalPeriod $period)
    {
        $this->ensureSuperAdmin();
        if ($period->status !== 'closed') {
            return back()->with('error', 'Hanya periode CLOSED yang bisa di-reopen');
        }
        $period->update(['status' => 'open']);
        return back()->with('success', 'Periode berhasil di-reopen');
    }

    public function lock(FiscalPeriod $period)
    {
        $this->ensureSuperAdmin();
        if ($period->status !== 'closed') {
            return back()->with('error', 'Lock hanya untuk periode yang sudah CLOSED');
        }
        $period->update(['status' => 'locked']);
        return back()->with('success', 'Periode berhasil di-lock');
    }

    public function createPeriod(Request $request)
    {
        $this->ensureSuperAdmin();

        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        // Cek apakah periode sudah ada
        $existing = FiscalPeriod::where('year', $validated['year'])
            ->where('month', $validated['month'])
            ->first();

        if ($existing) {
            return back()->with('error', 'Periode fiskal untuk ' . date('F Y', mktime(0, 0, 0, $validated['month'], 1, $validated['year'])) . ' sudah ada.');
        }

        $startDate = date('Y-m-01', mktime(0,0,0,$validated['month'],1,$validated['year']));
        $endDate = date('Y-m-t', mktime(0,0,0,$validated['month'],1,$validated['year']));
        // Buat periode baru
        $period = FiscalPeriod::create([
            'year' => $validated['year'],
            'month' => $validated['month'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'open',
        ]);

        return back()->with('success', 'Periode fiskal ' . date('F Y', mktime(0, 0, 0, $validated['month'], 1, $validated['year'])) . ' berhasil dibuat dengan status OPEN.');
    }

    public function createCurrentMonth()
    {
        $this->ensureSuperAdmin();

        $year = (int) date('Y');
        $month = (int) date('m');

        // Cek apakah periode sudah ada
        $existing = FiscalPeriod::where('year', $year)
            ->where('month', $month)
            ->first();

        if ($existing) {
            return back()->with('info', 'Periode fiskal untuk ' . date('F Y') . ' sudah ada dengan status: ' . $existing->status);
        }

        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');
        // Buat periode baru
        $period = FiscalPeriod::create([
            'year' => $year,
            'month' => $month,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'open',
        ]);

        return back()->with('success', 'Periode fiskal ' . date('F Y') . ' berhasil dibuat dengan status OPEN.');
    }
}
