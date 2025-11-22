<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\TaxInvoiceRequest;
use App\Models\Finance\Invoice;
use App\Notifications\TaxInvoiceRequestedNotification;
use App\Models\User;
use App\Services\TaxInvoiceExtractionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TaxInvoiceRequestController extends Controller
{
    /**
     * Display a listing of tax invoice requests
     */
    public function index(Request $request)
    {
        $query = TaxInvoiceRequest::with(['invoice.customer', 'requester', 'completer']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('requested_at', [
                $request->from . ' 00:00:00',
                $request->to . ' 23:59:59'
            ]);
        }

        if ($request->filled('customer')) {
            $query->where('customer_name', 'like', '%' . $request->customer . '%');
        }

        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        $requests = $query->latest('requested_at')->paginate(20);

        return view('tax-invoices.index', compact('requests'));
    }

    /**
     * Show the form for creating a new tax invoice request
     */
    public function create()
    {
        // Get invoices that need tax invoice (sent, has PPN, not yet requested)
        $invoices = Invoice::with('customer')
            ->needsTaxInvoice()
            ->latest('invoice_date')
            ->get();

        return view('tax-invoices.create', compact('invoices'));
    }

    /**
     * Store a newly created tax invoice request
     */
    public function store(Request $request)
    {
        $request->validate([
            'invoice_ids' => 'required|array|min:1',
            'invoice_ids.*' => 'exists:invoices,id',
        ]);

        DB::beginTransaction();
        try {
            $createdRequests = [];

            foreach ($request->invoice_ids as $invoiceId) {
                $invoice = Invoice::with('customer')->findOrFail($invoiceId);

                // Validate invoice can be requested
                // Allow invoices with tax_amount = 0 (e.g., transaction type 08) as they still require tax invoice
                if ($invoice->tax_invoice_status !== 'none' || $invoice->status === 'cancelled') {
                    continue;
                }

                // Create tax invoice request
                $taxRequest = TaxInvoiceRequest::create([
                    'request_number' => TaxInvoiceRequest::generateRequestNumber(),
                    'invoice_id' => $invoice->id,
                    'transaction_type' => $invoice->transaction_type ?? '04',
                    'customer_name' => $invoice->customer->name,
                    'customer_npwp' => $invoice->customer->npwp,
                    'dpp' => $invoice->subtotal,
                    'ppn' => $invoice->tax_amount,
                    'total_amount' => $invoice->total_amount,
                    'description' => $invoice->notes,
                    'status' => 'requested',
                    'requested_by' => auth()->id(),
                    'requested_at' => now(),
                ]);

                // Update invoice status
                $invoice->update([
                    'tax_invoice_status' => 'requested',
                    'tax_requested_at' => now(),
                    'tax_requested_by' => auth()->id(),
                ]);

                $createdRequests[] = $taxRequest;
            }

            DB::commit();

            // Send notification to tax department
            $this->notifyTaxDepartment($createdRequests);

            return redirect()->route('tax-invoices.index')
                ->with('success', count($createdRequests) . ' tax invoice request(s) submitted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to submit tax invoice request: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified tax invoice request
     */
    public function show(TaxInvoiceRequest $taxInvoiceRequest)
    {
        $taxInvoiceRequest->load(['invoice.customer', 'requester', 'completer']);
        
        return view('tax-invoices.show', compact('taxInvoiceRequest'));
    }

    /**
     * Show the form for completing tax invoice
     */
    public function complete(TaxInvoiceRequest $taxInvoiceRequest)
    {
        if ($taxInvoiceRequest->status === 'completed') {
            return redirect()->route('tax-invoices.show', $taxInvoiceRequest)
                ->with('warning', 'This tax invoice request has already been completed.');
        }

        $taxInvoiceRequest->load(['invoice.customer', 'requester']);

        return view('tax-invoices.complete-form', compact('taxInvoiceRequest'));
    }

    /**
     * Update the tax invoice with nomor faktur
     */
    public function updateComplete(Request $request, TaxInvoiceRequest $taxInvoiceRequest, TaxInvoiceExtractionService $extractionService)
    {
        Log::info('updateComplete started', [
            'request_id' => $taxInvoiceRequest->id,
            'data' => $request->except(['tax_invoice_file', '_token'])
        ]);

        $request->validate([
            'tax_invoice_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tax_invoice_requests', 'tax_invoice_number')
                    ->ignore($taxInvoiceRequest->id),
                Rule::unique('invoices', 'tax_invoice_number'),
            ],
            'tax_invoice_date' => [
                'required',
                'date',
            ],
            'notes' => 'nullable|string|max:1000',
            'tax_invoice_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);

        Log::info('Validation passed');

        DB::beginTransaction();
        try {
            // Handle file upload if present
            $filePath = null;
            if ($request->hasFile('tax_invoice_file')) {
                try {
                    Log::info('Processing file upload');
                    $filePath = $extractionService->compressAndStore(
                        $request->file('tax_invoice_file'),
                        $taxInvoiceRequest->invoice_id
                    );
                    Log::info('Tax invoice file uploaded', ['path' => $filePath]);
                } catch (\Exception $e) {
                    Log::error('Failed to upload tax invoice file', ['error' => $e->getMessage()]);
                    // Continue without file if upload fails
                }
            }

            Log::info('Updating tax invoice request');

            // Update tax invoice request
            $taxInvoiceRequest->update([
                'tax_invoice_number' => $request->tax_invoice_number,
                'tax_invoice_date' => $request->tax_invoice_date,
                'notes' => $request->notes,
                'tax_invoice_file_path' => $filePath,
                'status' => 'completed',
                'completed_by' => auth()->id(),
                'completed_at' => now(),
            ]);

            Log::info('Updating invoice');

            // Update invoice
            $taxInvoiceRequest->invoice->update([
                'tax_invoice_status' => 'completed',
                'tax_invoice_number' => $request->tax_invoice_number,
                'tax_invoice_date' => $request->tax_invoice_date,
                'tax_invoice_file_path' => $filePath,
                'tax_completed_at' => now(),
                'tax_completed_by' => auth()->id(),
            ]);

            DB::commit();
            
            Log::info('updateComplete successful');

            return redirect()->route('tax-invoices.show', $taxInvoiceRequest)
                ->with('success', 'Tax invoice completed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('updateComplete failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to complete tax invoice: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Extract tax invoice data from uploaded file (AJAX endpoint)
     */
    public function extractFile(Request $request, TaxInvoiceExtractionService $extractionService)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return specific validation errors
            $errors = $e->errors();
            if (isset($errors['file'])) {
                $errorMsg = $errors['file'][0];
                
                // Translate common validation errors to user-friendly messages
                if (str_contains($errorMsg, 'mimes')) {
                    return response()->json([
                        'success' => false,
                        'error_type' => 'invalid_format',
                        'message' => 'Format file tidak didukung. Gunakan PDF, JPG, atau PNG.'
                    ], 200);
                } elseif (str_contains($errorMsg, 'max')) {
                    return response()->json([
                        'success' => false,
                        'error_type' => 'file_too_large',
                        'message' => 'File terlalu besar. Maksimal 5MB.'
                    ], 200);
                }
            }
            
            return response()->json([
                'success' => false,
                'error_type' => 'validation_error',
                'message' => 'File tidak valid. Pastikan format dan ukuran sesuai.'
            ], 200);
        }

        try {
            // Check API key first
            if (!config('ai_assistant.api_key')) {
                Log::warning('Gemini API key not configured for extraction');
                return response()->json([
                    'success' => false,
                    'error_type' => 'api_key_missing',
                    'message' => 'API key belum dikonfigurasi. Silakan input manual.'
                ], 200);
            }

            // Store file temporarily
            $tempPath = $request->file('file')->store('temp');
            $fullPath = Storage::path($tempPath); // Use Storage::path() for correct separators

            try {
                // Try extraction
                $extracted = $extractionService->extractFromFile($fullPath);

                if ($extracted) {
                    $warnings = [];
                    
                    // Validate against request data if request_id is present
                    if ($request->has('request_id')) {
                        $taxRequest = TaxInvoiceRequest::with('invoice')->find($request->request_id);
                        
                        if ($taxRequest) {
                            // Validate DPP
                            if (isset($extracted['dpp']) && $extracted['dpp'] > 0) {
                                if (abs($extracted['dpp'] - $taxRequest->dpp) > 100) {
                                    $warnings[] = 'DPP pada file (Rp ' . number_format($extracted['dpp'], 0, ',', '.') . ') berbeda dengan DPP sistem (Rp ' . number_format($taxRequest->dpp, 0, ',', '.') . ')';
                                }
                            }
                            
                            // Validate Reference
                            if (isset($extracted['reference']) && $extracted['reference']) {
                                $ref = strtoupper(preg_replace('/[^A-Z0-9]/', '', $extracted['reference']));
                                $inv = strtoupper(preg_replace('/[^A-Z0-9]/', '', $taxRequest->invoice->invoice_number));
                                
                                if (!str_contains($ref, $inv) && !str_contains($inv, $ref)) {
                                    $warnings[] = 'Nomor referensi pada file (' . $extracted['reference'] . ') tidak sesuai dengan nomor invoice (' . $taxRequest->invoice->invoice_number . ')';
                                }
                            }

                            // Validate Date
                            if (isset($extracted['date']) && $extracted['date']) {
                                $invoiceDate = $taxRequest->invoice->invoice_date->format('Y-m-d');
                                // Compare dates
                                if ($extracted['date'] !== $invoiceDate) {
                                    $warnings[] = 'Tanggal faktur pada file (' . date('d/m/Y', strtotime($extracted['date'])) . ') berbeda dengan tanggal invoice (' . $taxRequest->invoice->invoice_date->format('d/m/Y') . ')';
                                }
                            }
                        }
                    }

                    return response()->json([
                        'success' => true,
                        'data' => $extracted,
                        'warnings' => $warnings
                    ]);
                }
                
                return response()->json([
                    'success' => false,
                    'error_type' => 'extraction_failed',
                    'message' => 'Gagal mengekstrak data. Silakan input manual.'
                ], 200);
            } finally {
                if (file_exists($fullPath)) {
                    @unlink($fullPath);
                }
            }
            
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
             Log::error('Network error during extraction', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error_type' => 'network_error',
                'message' => 'Koneksi ke AI gagal. Periksa internet Anda atau input manual.'
            ], 200);
            
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('API request error during extraction', ['error' => $e->getMessage()]);
            
            // Check if it's a timeout
            if (str_contains($e->getMessage(), 'timeout')) {
                return response()->json([
                    'success' => false,
                    'error_type' => 'timeout',
                    'message' => 'Proses AI timeout. File terlalu besar atau koneksi lambat. Silakan input manual.'
                ], 200);
            }
            
            return response()->json([
                'success' => false,
                'error_type' => 'api_error',
                'message' => 'API error. Silakan coba lagi atau input manual.'
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Tax invoice extraction failed', [
                'error' => $e->getMessage(),
                'type' => get_class($e)
            ]);
            
            return response()->json([
                'success' => false,
                'error_type' => 'unknown_error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage() . '. Silakan input manual.'
            ], 200);
        }
    }

    /**
     * Download tax invoice file
     */
    public function downloadFile(TaxInvoiceRequest $taxInvoiceRequest)
    {
        if (!$taxInvoiceRequest->tax_invoice_file_path || !Storage::exists($taxInvoiceRequest->tax_invoice_file_path)) {
            abort(404, 'File not found');
        }

        return Storage::download($taxInvoiceRequest->tax_invoice_file_path);
    }

    /**
     * Export tax invoice requests to Excel
     */
    public function export(Request $request)
    {
        $query = TaxInvoiceRequest::with(['invoice.customer', 'requester', 'completer']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('requested_at', [
                $request->from . ' 00:00:00',
                $request->to . ' 23:59:59'
            ]);
        }

        if ($request->filled('customer')) {
            $query->where('customer_name', 'like', '%' . $request->customer . '%');
        }

        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        $requests = $query->latest('requested_at')->get();

        // For now, return CSV. We'll implement Excel export later
        $filename = 'tax_invoice_requests_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($requests) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Request No',
                'Request Date',
                'Invoice No',
                'Invoice Date',
                'Customer Name',
                'NPWP',
                'Transaction Type',
                'Description',
                'DPP',
                'PPN',
                'Total Amount',
                'Status',
                'Nomor Faktur Pajak',
                'Tanggal Faktur Pajak',
                'Requested By',
                'Completed By',
                'Completed Date',
            ]);

            // Data
            foreach ($requests as $req) {
                fputcsv($file, [
                    $req->request_number,
                    $req->requested_at->format('Y-m-d H:i'),
                    $req->invoice->invoice_number,
                    $req->invoice->invoice_date->format('Y-m-d'),
                    $req->customer_name,
                    $req->customer_npwp ?? '-',
                    $req->transaction_type,
                    $req->description ?? '-',
                    number_format($req->dpp, 2, '.', ''),
                    number_format($req->ppn, 2, '.', ''),
                    number_format($req->total_amount, 2, '.', ''),
                    ucfirst($req->status),
                    $req->tax_invoice_number ?? '-',
                    $req->tax_invoice_date ? $req->tax_invoice_date->format('Y-m-d') : '-',
                    $req->requester->name,
                    $req->completer->name ?? '-',
                    $req->completed_at ? $req->completed_at->format('Y-m-d H:i') : '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Send notification to tax department
     */
    protected function notifyTaxDepartment(array $requests)
    {
        // Get users with tax department role/permission
        // For now, we'll notify all users with email containing 'tax' or specific role
        // You can customize this based on your role/permission system
        
        $taxUsers = User::where('email', 'like', '%tax%')
            ->orWhere('email', 'like', '%pajak%')
            ->get();

        foreach ($taxUsers as $user) {
            $user->notify(new TaxInvoiceRequestedNotification($requests));
        }
    }

    /**
     * Preview tax invoice file
     */
    public function preview(TaxInvoiceRequest $taxInvoiceRequest)
    {
        if (!$taxInvoiceRequest->tax_invoice_file_path) {
            abort(404, 'File not found');
        }

        $filePath = Storage::path($taxInvoiceRequest->tax_invoice_file_path);

        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
        ]);
    }
}
