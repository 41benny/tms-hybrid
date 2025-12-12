<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StoreJournalRequest;
use App\Http\Requests\Accounting\UpdateJournalRequest;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\Journal;
use App\Models\Accounting\JournalLine;
use App\Models\Finance\CashBankTransaction;
use App\Models\Finance\Invoice;
use App\Models\Finance\VendorBill;
use App\Services\Accounting\PostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalController extends Controller
{
    public function __construct(protected PostingService $posting) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Journal::query()->with(['period', 'lines.account']);

        // Filter by source type
        if ($sourceType = $request->get('source_type')) {
            $query->where('source_type', $sourceType);
        }

        // Filter by date range
        if ($from = $request->get('from')) {
            $query->whereDate('journal_date', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('journal_date', '<=', $to);
        }

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Search by journal number or memo
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('journal_no', 'like', "%{$search}%")
                    ->orWhere('memo', 'like', "%{$search}%");
            });
        }

        $journals = $query->latest('journal_date')->latest('id')->paginate(20)->withQueryString();

        // Get source references for display
        $journals->getCollection()->transform(function ($journal) {
            $journal->source_reference = $this->getSourceReference($journal);

            return $journal;
        });

        $sourceTypes = [
            'invoice' => 'Penjualan',
            'customer_payment' => 'Penjualan',
            'vendor_bill' => 'Pembelian',
            'vendor_payment' => 'Pembelian',
            'driver_advance' => 'Uang Jalan',
            'uang_jalan' => 'Pembayaran Uang Jalan',
            'expense' => 'Kas/Bank',
            'cash_in' => 'Kas/Bank',
            'cash_out' => 'Kas/Bank',
            'part_purchase' => 'Inventory',
            'part_usage' => 'Inventory',
            'adjustment' => 'Adjustment',
        ];

        return view('journals.index', compact('journals', 'sourceTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $accounts = ChartOfAccount::where('status', 'active')
            ->where('is_postable', true)
            ->orderBy('code')
            ->get();

        return view('journals.create', compact('accounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJournalRequest $request)
    {
        $validated = $request->validated();

        try {
            $journal = DB::transaction(function () use ($validated) {
                $header = [
                    'journal_date' => $validated['journal_date'],
                    'source_type' => 'adjustment',
                    'source_id' => 0,
                    'memo' => $validated['memo'] ?? null,
                    'currency' => $validated['currency'] ?? 'IDR',
                    'posted_by' => auth()->id(),
                ];

                $lines = [];
                foreach ($validated['lines'] as $line) {
                    $account = ChartOfAccount::findOrFail($line['account_id']);
                    $lines[] = [
                        'account_code' => $account->code,
                        'debit' => (float) ($line['debit'] ?? 0),
                        'credit' => (float) ($line['credit'] ?? 0),
                        'desc' => $line['description'] ?? null,
                        'customer_id' => $line['customer_id'] ?? null,
                        'vendor_id' => $line['vendor_id'] ?? null,
                        'job_order_id' => $line['job_order_id'] ?? null,
                    ];
                }

                return $this->posting->postGeneral($header, $lines);
            });

            return redirect()->route('journals.show', $journal)
                ->with('success', 'Jurnal adjustment berhasil dibuat.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Gagal membuat jurnal: '.$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Journal $journal)
    {
        $journal->load(['lines.account', 'period']);

        $sourceReference = $this->getSourceReference($journal);

        return view('journals.show', compact('journal', 'sourceReference'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Journal $journal)
    {
        // Only allow editing draft or adjustment journals
        if ($journal->status === 'posted' && $journal->source_type !== 'adjustment') {
            return redirect()->route('journals.show', $journal)
                ->with('error', 'Jurnal otomatis tidak dapat diedit.');
        }

        $journal->load('lines.account');
        $accounts = ChartOfAccount::where('status', 'active')
            ->where('is_postable', true)
            ->orderBy('code')
            ->get();

        return view('journals.edit', compact('journal', 'accounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateJournalRequest $request, Journal $journal)
    {
        // Only allow editing draft or adjustment journals
        if ($journal->status === 'posted' && $journal->source_type !== 'adjustment') {
            return redirect()->route('journals.show', $journal)
                ->with('error', 'Jurnal otomatis tidak dapat diedit.');
        }

        $validated = $request->validated();

        try {
            DB::transaction(function () use ($journal, $validated) {
                // Update journal header
                $journal->journal_date = $validated['journal_date'];
                $journal->memo = $validated['memo'] ?? null;
                $journal->currency = $validated['currency'] ?? 'IDR';

                // Recalculate totals
                $totalDebit = 0;
                $totalCredit = 0;
                foreach ($validated['lines'] as $line) {
                    $totalDebit += (float) ($line['debit'] ?? 0);
                    $totalCredit += (float) ($line['credit'] ?? 0);
                }

                $journal->total_debit = $totalDebit;
                $journal->total_credit = $totalCredit;

                // Delete old lines
                $journal->lines()->delete();

                // Create new lines
                foreach ($validated['lines'] as $line) {
                    JournalLine::create([
                        'journal_id' => $journal->id,
                        'account_id' => $line['account_id'],
                        'description' => $line['description'] ?? null,
                        'debit' => (float) ($line['debit'] ?? 0),
                        'credit' => (float) ($line['credit'] ?? 0),
                        'customer_id' => $line['customer_id'] ?? null,
                        'vendor_id' => $line['vendor_id'] ?? null,
                        'job_order_id' => $line['job_order_id'] ?? null,
                    ]);
                }

                $journal->save();
            });

            return redirect()->route('journals.show', $journal)
                ->with('success', 'Jurnal berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Gagal memperbarui jurnal: '.$e->getMessage());
        }
    }

    /**
     * Get source reference for display
     */
    protected function getSourceReference(Journal $journal): ?array
    {
        $result = null;
        $jobOrderNumbers = [];

        if ($journal->source_type === 'invoice' && $journal->source_id) {
            $invoice = Invoice::with('jobOrders')->find($journal->source_id);
            if ($invoice) {
                $result = [
                    'type' => 'Invoice',
                    'number' => $invoice->invoice_number,
                    'url' => route('invoices.show', $invoice),
                ];
                // Get related job orders
                if ($invoice->jobOrders && $invoice->jobOrders->isNotEmpty()) {
                    $jobOrderNumbers = $invoice->jobOrders->pluck('job_number')->toArray();
                }
            }
        }

        if ($journal->source_type === 'vendor_bill' && $journal->source_id) {
            $bill = VendorBill::with('items.shipmentLeg.jobOrder')->find($journal->source_id);
            if ($bill) {
                $result = [
                    'type' => 'Vendor Bill',
                    'number' => $bill->vendor_bill_number,
                    'url' => route('vendor-bills.show', $bill),
                ];
                // Get related job orders from vendor bill items
                if ($bill->items && $bill->items->isNotEmpty()) {
                    $jobOrderNumbers = $bill->items
                        ->pluck('shipmentLeg.jobOrder.job_number')
                        ->filter()
                        ->unique()
                        ->values()
                        ->toArray();
                }
            }
        }

        if ($journal->source_type === 'driver_advance' && $journal->source_id) {
            // Initial driver advance journal (when shipment leg is created)
            $driverAdvance = \App\Models\Operations\DriverAdvance::with('shipmentLeg.jobOrder')->find($journal->source_id);
            if ($driverAdvance) {
                $result = [
                    'type' => 'Driver Advance',
                    'number' => $driverAdvance->advance_number,
                    'url' => route('driver-advances.show', $driverAdvance),
                ];
                // Get related job order from driver advance
                if ($driverAdvance->shipmentLeg && $driverAdvance->shipmentLeg->jobOrder) {
                    $jobOrderNumbers = [$driverAdvance->shipmentLeg->jobOrder->job_number];
                }
            }
        }

        if ($journal->source_type === 'uang_jalan' && $journal->source_id) {
            // Payment of driver advance (cash bank transaction)
            $trx = CashBankTransaction::with('driverAdvancePayments.driverAdvance.shipmentLeg.jobOrder')->find($journal->source_id);
            if ($trx) {
                $result = [
                    'type' => 'Pembayaran Uang Jalan',
                    'number' => $trx->voucher_number ?? $trx->reference_number ?? '#'.$trx->id,
                    'url' => route('cash-banks.show', $trx),
                ];
                // Get related job orders from driver advance payments
                if ($trx->driverAdvancePayments && $trx->driverAdvancePayments->isNotEmpty()) {
                    $jobOrderNumbers = $trx->driverAdvancePayments
                        ->pluck('driverAdvance.shipmentLeg.jobOrder.job_number')
                        ->filter()
                        ->unique()
                        ->values()
                        ->toArray();
                }
            }
        }

        if (in_array($journal->source_type, ['customer_payment', 'vendor_payment', 'expense']) && $journal->source_id) {
            $trx = CashBankTransaction::find($journal->source_id);
            if ($trx) {
                $result = [
                    'type' => 'Transaksi Kas/Bank',
                    'number' => $trx->reference_number ?? '#'.$trx->id,
                    'url' => route('cash-banks.show', $trx),
                ];
            }
        }

        if ($journal->source_type === 'part_purchase' && $journal->source_id) {
            $purchase = \App\Models\Inventory\PartPurchase::find($journal->source_id);
            if ($purchase) {
                $result = [
                    'type' => 'Pembelian Part',
                    'number' => $purchase->purchase_number,
                    'url' => route('part-purchases.show', $purchase),
                ];
            }
        }

        if ($journal->source_type === 'part_usage' && $journal->source_id) {
            $usage = \App\Models\Inventory\PartUsage::find($journal->source_id);
            if ($usage) {
                $result = [
                    'type' => 'Pemakaian Part',
                    'number' => $usage->usage_number,
                    'url' => route('part-usages.show', $usage),
                ];
            }
        }

        // Add job order numbers to result if found
        if ($result && !empty($jobOrderNumbers)) {
            $result['job_orders'] = $jobOrderNumbers;
        }

        return $result;
    }

    /**
     * Display traditional journal ledger view (all entries in one table)
     */
    public function traditional(Request $request)
    {
        $query = JournalLine::query()
            ->with(['journal', 'account', 'customer', 'vendor', 'transport.driver', 'jobOrder'])
            ->whereHas('journal', function ($q) {
                $q->where('status', 'posted');
            });

        // Filter by date range
        if ($from = $request->get('from')) {
            $query->whereHas('journal', function ($q) use ($from) {
                $q->whereDate('journal_date', '>=', $from);
            });
        }
        if ($to = $request->get('to')) {
            $query->whereHas('journal', function ($q) use ($to) {
                $q->whereDate('journal_date', '<=', $to);
            });
        }

        // Get all entries
        $entries = $query->get();

        // Define class order for sorting
        $classOrder = [
            'vendor_bill' => 1,
            'vendor_payment' => 2,
            'invoice' => 3,
            'customer_payment' => 4,
            'cash_in' => 5,
            'cash_out' => 5,
            'expense' => 5,
            'uang_jalan' => 5,
            'driver_advance' => 6,
            'part_purchase' => 7,
            'part_usage' => 7,
            'fixed_asset_depreciation' => 8,
            'adjustment' => 9,
        ];

        // Sort entries by class order, then by date, then by journal number
        $entries = $entries->sort(function ($a, $b) use ($classOrder) {
            $classA = $classOrder[$a->journal->source_type] ?? 99;
            $classB = $classOrder[$b->journal->source_type] ?? 99;

            if ($classA !== $classB) {
                return $classA <=> $classB;
            }

            // Then by date
            $dateCompare = $a->journal->journal_date <=> $b->journal->journal_date;
            if ($dateCompare !== 0) {
                return $dateCompare;
            }

            // Then by journal number
            return strcmp($a->journal->journal_no, $b->journal->journal_no);
        });

        // Calculate totals
        $totalDebit = $entries->sum('debit');
        $totalCredit = $entries->sum('credit');

        // Class labels - Standardize all cash/bank related to "Kas/Bank"
        $classLabels = [
            'vendor_bill' => 'Pembelian',
            'vendor_payment' => 'Pembayaran Pembelian',
            'invoice' => 'Penjualan',
            'customer_payment' => 'Pembayaran Penjualan',
            'cash_in' => 'Kas/Bank',
            'cash_out' => 'Kas/Bank',
            'expense' => 'Kas/Bank',
            'uang_jalan' => 'Kas/Bank',
            'driver_advance' => 'Uang Jalan',
            'part_purchase' => 'Pembelian Part',
            'part_usage' => 'Pemakaian Part (HPP)',
            'fixed_asset_depreciation' => 'Depresiasi Aset',
            'adjustment' => 'Adjustment Manual',
        ];

        // Load Cash/Bank transaction data for entries that need recipient_name
        $cashBankSourceTypes = ['customer_payment', 'vendor_payment', 'expense', 'cash_in', 'cash_out', 'uang_jalan'];
        $cashBankTransactions = [];
        
        $cashBankJournals = $entries->filter(function ($entry) use ($cashBankSourceTypes) {
            return in_array($entry->journal->source_type, $cashBankSourceTypes) && $entry->journal->source_id;
        })->pluck('journal.source_id')->unique()->values()->toArray();
        
        if (!empty($cashBankJournals)) {
            $cashBankTransactions = CashBankTransaction::whereIn('id', $cashBankJournals)
                ->get()
                ->keyBy('id');
        }

        return view('journals.traditional', compact('entries', 'totalDebit', 'totalCredit', 'classLabels', 'cashBankSourceTypes', 'cashBankTransactions'));
    }

    /**
     * Export traditional journal ledger to Excel
     */
    public function exportTraditional(Request $request)
    {
        $query = JournalLine::query()
            ->with(['journal', 'account', 'customer', 'vendor', 'transport.driver', 'jobOrder'])
            ->whereHas('journal', function ($q) {
                $q->where('status', 'posted');
            });

        // Filter by date range
        if ($from = $request->get('from')) {
            $query->whereHas('journal', function ($q) use ($from) {
                $q->whereDate('journal_date', '>=', $from);
            });
        }
        if ($to = $request->get('to')) {
            $query->whereHas('journal', function ($q) use ($to) {
                $q->whereDate('journal_date', '<=', $to);
            });
        }

        // Get all entries
        $entries = $query->get();

        // Define class order for sorting
        $classOrder = [
            'vendor_bill' => 1,
            'vendor_payment' => 2,
            'invoice' => 3,
            'customer_payment' => 4,
            'cash_in' => 5,
            'cash_out' => 5,
            'expense' => 5,
            'uang_jalan' => 5,
            'driver_advance' => 6,
            'part_purchase' => 7,
            'part_usage' => 7,
            'fixed_asset_depreciation' => 8,
            'adjustment' => 9,
        ];

        // Sort entries
        $entries = $entries->sort(function ($a, $b) use ($classOrder) {
            $classA = $classOrder[$a->journal->source_type] ?? 99;
            $classB = $classOrder[$b->journal->source_type] ?? 99;

            if ($classA !== $classB) {
                return $classA <=> $classB;
            }

            $dateCompare = $a->journal->journal_date <=> $b->journal->journal_date;
            if ($dateCompare !== 0) {
                return $dateCompare;
            }

            return strcmp($a->journal->journal_no, $b->journal->journal_no);
        });

        // Class labels - Standardize all cash/bank related to "Kas/Bank"
        $classLabels = [
            'vendor_bill' => 'Pembelian',
            'vendor_payment' => 'Pembayaran Pembelian',
            'invoice' => 'Penjualan',
            'customer_payment' => 'Pembayaran Penjualan',
            'cash_in' => 'Kas/Bank',
            'cash_out' => 'Kas/Bank',
            'expense' => 'Kas/Bank',
            'uang_jalan' => 'Kas/Bank',
            'driver_advance' => 'Uang Jalan',
            'part_purchase' => 'Pembelian Part',
            'part_usage' => 'Pemakaian Part (HPP)',
            'fixed_asset_depreciation' => 'Depresiasi Aset',
            'adjustment' => 'Adjustment Manual',
        ];

        // Load Cash/Bank transaction data for entries that need recipient_name
        $cashBankSourceTypes = ['customer_payment', 'vendor_payment', 'expense', 'cash_in', 'cash_out', 'uang_jalan'];
        $cashBankTransactions = [];
        
        $cashBankJournals = $entries->filter(function ($entry) use ($cashBankSourceTypes) {
            return in_array($entry->journal->source_type, $cashBankSourceTypes) && $entry->journal->source_id;
        })->pluck('journal.source_id')->unique()->values()->toArray();
        
        if (!empty($cashBankJournals)) {
            $cashBankTransactions = CashBankTransaction::whereIn('id', $cashBankJournals)
                ->get()
                ->keyBy('id');
        }

        // Create Spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Buku Besar');

        // Header row
        $headers = ['Class', 'Tanggal', 'No Jurnal', 'Kode Akun', 'Nama Akun', 'Keterangan', 'Nama', 'Debit', 'Kredit'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('4F46E5');
            $sheet->getStyle($col . '1')->getFont()->getColor()->setRGB('FFFFFF');
            $col++;
        }

        // Data rows
        $row = 2;
        foreach ($entries as $entry) {
            $class = $classLabels[$entry->journal->source_type] ?? $entry->journal->source_type;
            $tanggal = $entry->journal->journal_date->format('d/m/Y');
            $noJurnal = $entry->journal->journal_no;
            $kodeAkun = $entry->account->code;
            $namaAkun = $entry->account->name;
            
            // Keterangan with JO
            $keterangan = $entry->journal->memo ?: $entry->description;
            if ($entry->jobOrder) {
                $keterangan .= ' - JO: ' . $entry->jobOrder->job_number;
            }
            
            // Nama - For Cash/Bank transactions, use recipient_name
            if (in_array($entry->journal->source_type, $cashBankSourceTypes) && $entry->journal->source_id) {
                $cashBankTrx = $cashBankTransactions[$entry->journal->source_id] ?? null;
                $nama = $cashBankTrx?->recipient_name 
                     ?? $entry->customer?->name 
                     ?? $entry->vendor?->name 
                     ?? $entry->transport?->driver?->name 
                     ?? '-';
            } else {
                $nama = $entry->customer?->name 
                     ?? $entry->vendor?->name 
                     ?? $entry->transport?->driver?->name 
                     ?? $entry->transport?->plate_number
                     ?? ($entry->journal->source_type === 'fixed_asset_depreciation' ? 'Sistem' : '-');
            }

            $sheet->setCellValue('A' . $row, $class);
            $sheet->setCellValue('B' . $row, $tanggal);
            $sheet->setCellValue('C' . $row, $noJurnal);
            $sheet->setCellValue('D' . $row, $kodeAkun);
            $sheet->setCellValue('E' . $row, $namaAkun);
            $sheet->setCellValue('F' . $row, $keterangan);
            $sheet->setCellValue('G' . $row, $nama);
            
            // Set numeric values for Debit and Kredit
            $sheet->setCellValue('H' . $row, (float) $entry->debit);
            $sheet->setCellValue('I' . $row, (float) $entry->credit);
            
            $row++;
        }

        // Add totals row
        $totalRow = $row;
        $sheet->setCellValue('A' . $totalRow, 'TOTAL');
        $sheet->getStyle('A' . $totalRow)->getFont()->setBold(true);
        $sheet->setCellValue('H' . $totalRow, $entries->sum('debit'));
        $sheet->setCellValue('I' . $totalRow, $entries->sum('credit'));
        $sheet->getStyle('H' . $totalRow . ':I' . $totalRow)->getFont()->setBold(true);

        // Format number columns (Debit and Kredit)
        $lastRow = $totalRow;
        $numberFormat = '#,##0.00';
        $sheet->getStyle('H2:H' . $lastRow)->getNumberFormat()->setFormatCode($numberFormat);
        $sheet->getStyle('I2:I' . $lastRow)->getNumberFormat()->setFormatCode($numberFormat);

        // Auto-size columns
        foreach (range('A', 'I') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Generate filename
        $filename = 'buku_besar_' . date('Y-m-d_His') . '.xlsx';

        // Output
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
