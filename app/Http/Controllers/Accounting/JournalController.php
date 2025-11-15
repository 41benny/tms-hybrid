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
        if ($journal->source_type === 'invoice' && $journal->source_id) {
            $invoice = Invoice::find($journal->source_id);
            if ($invoice) {
                return [
                    'type' => 'Invoice',
                    'number' => $invoice->invoice_number,
                    'url' => route('invoices.show', $invoice),
                ];
            }
        }

        if ($journal->source_type === 'vendor_bill' && $journal->source_id) {
            $bill = VendorBill::find($journal->source_id);
            if ($bill) {
                return [
                    'type' => 'Vendor Bill',
                    'number' => $bill->vendor_bill_number,
                    'url' => route('vendor-bills.show', $bill),
                ];
            }
        }

        if (in_array($journal->source_type, ['customer_payment', 'vendor_payment', 'expense']) && $journal->source_id) {
            $trx = CashBankTransaction::find($journal->source_id);
            if ($trx) {
                return [
                    'type' => 'Transaksi Kas/Bank',
                    'number' => $trx->reference_number ?? '#'.$trx->id,
                    'url' => route('cash-banks.show', $trx),
                ];
            }
        }

        if ($journal->source_type === 'part_purchase' && $journal->source_id) {
            $purchase = \App\Models\Inventory\PartPurchase::find($journal->source_id);
            if ($purchase) {
                return [
                    'type' => 'Pembelian Part',
                    'number' => $purchase->purchase_number,
                    'url' => route('part-purchases.show', $purchase),
                ];
            }
        }

        if ($journal->source_type === 'part_usage' && $journal->source_id) {
            $usage = \App\Models\Inventory\PartUsage::find($journal->source_id);
            if ($usage) {
                return [
                    'type' => 'Pemakaian Part',
                    'number' => $usage->usage_number,
                    'url' => route('part-usages.show', $usage),
                ];
            }
        }

        return null;
    }
}
