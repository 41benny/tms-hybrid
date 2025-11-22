<?php

namespace App\Services\Ai;

use App\Models\Finance\Invoice;
use App\Models\Finance\VendorBill;
use App\Models\Operations\JobOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AiAnalysisService
{
    public function analyze(string $question): array
    {
        // 1. Detect if this is an Action (Agentic behavior)
        $action = $this->detectAction($question);
        if ($action) {
            return [
                'answer' => $action['message'],
                'action' => $action['payload']
            ];
        }

        // 2. Check for Specific Entity (JO or Customer)
        $entity = $this->detectSpecificEntity($question);
        if ($entity) {
            if ($entity['type'] === 'job') {
                $summary = $this->summarizeSpecificJob($entity['keyword']);
            } else {
                $summary = $this->summarizeSpecificCustomer($entity['keyword']);
            }
            $summary['intent'] = 'specific_analysis';
        } else {
            // 3. If not specific, proceed with General Intent Analysis
            $intent = $this->detectIntent($question);
            $summary = [
                'intent' => $intent,
                'generated_at' => now()->toDateTimeString(),
            ];

            switch ($intent) {
                case 'piutang':
                    $summary += $this->summarizeReceivables();
                    break;
                case 'hutang':
                    $summary += $this->summarizePayables();
                    break;
                case 'profit':
                    $summary += $this->summarizeProfitLoss();
                    break;
                case 'cash_flow':
                    $summary += $this->summarizeCashFlow();
                    break;
                case 'job_customer':
                    $summary += $this->summarizeJobOrders();
                    break;
                case 'vendor_performance':
                    $summary += $this->summarizeVendorPerformance();
                    break;
                case 'fleet':
                    $summary += $this->summarizeFleet();
                    break;
                case 'driver':
                    $summary += $this->summarizeDrivers();
                    break;
                case 'pending_job':
                    $summary += $this->summarizePendingJobOrders();
                    break;
                case 'activity':
                    $summary += $this->summarizeRecentActivity();
                    break;
                case 'margin':
                    $summary += $this->summarizeProfitLoss();
                    break;
                case 'bad_debt':
                    $summary += $this->summarizeBadDebt();
                    break;
                case 'cost_structure':
                    $summary += $this->summarizeExpenseBreakdown();
                    break;
                default:
                    $summary += $this->summarizeGeneral();
                    break;
            }
        }

        $answer = $this->callLlmApi($question, $summary);
        if (! $answer) {
            $answer = $this->fallbackNarrative($summary);
        }

        return [
            'answer' => $answer,
            'action' => null
        ];
    }

    protected function detectAction(string $q): ?array
    {
        $t = mb_strtolower($q);

        // Navigation / Open Menu
        if (str_contains($t, 'buka') || str_contains($t, 'pergi') || str_contains($t, 'lihat') || str_contains($t, 'menu')) {
            $target = $this->resolveActionUrl($t);
            if ($target) {
                return [
                    'message' => "Siap! Membuka halaman {$target['label']} untuk Anda...",
                    'payload' => ['type' => 'navigate', 'url' => $target['url']]
                ];
            }
        }

        // Creation
        if (str_contains($t, 'buat') || str_contains($t, 'tambah') || str_contains($t, 'bikin')) {
            if (str_contains($t, 'invoice')) return ['message' => 'Membuka form pembuatan Invoice baru...', 'payload' => ['type' => 'navigate', 'url' => url('/invoices/create')]];
            if (str_contains($t, 'job') || str_contains($t, 'order')) return ['message' => 'Membuka form Job Order baru...', 'payload' => ['type' => 'navigate', 'url' => url('/job-orders/create')]];
            if (str_contains($t, 'vendor')) return ['message' => 'Membuka form registrasi Vendor...', 'payload' => ['type' => 'navigate', 'url' => url('/vendors/create')]];
            if (str_contains($t, 'customer')) return ['message' => 'Membuka form registrasi Customer...', 'payload' => ['type' => 'navigate', 'url' => url('/customers/create')]];
        }

        return null;
    }

    protected function detectSpecificEntity(string $q): ?array
    {
        // Check for JO pattern: JO-..., or "job order X"
        if (preg_match('/(JO-[\w\-]+)/i', $q, $m)) {
            return ['type' => 'job', 'keyword' => $m[1]];
        }
        
        // Check for "Customer PT X", "Klien X", or typos like "Custemer X"
        if (preg_match('/(customer|custemer|kastemer|cust|klien|client|pt\.?|cv\.?|ud\.?)\s+([\w\s]+)/i', $q, $m)) {
            return ['type' => 'customer', 'keyword' => trim($m[2])];
        }

        return null;
    }

    protected function summarizeSpecificJob(string $keyword): array
    {
        $jo = \App\Models\Operations\JobOrder::where('job_number', 'like', "%$keyword%")
            ->with(['customer', 'shipmentLegs.truck', 'shipmentLegs.driver'])
            ->first();

        if (!$jo) return ['specific_error' => "Job Order dengan nomor '$keyword' tidak ditemukan."];

        return [
            'specific_type' => 'job_order',
            'jo_number' => $jo->job_number,
            'jo_customer' => $jo->customer->name ?? 'N/A',
            'jo_status' => $jo->status,
            'jo_revenue' => $jo->total_revenue,
            'jo_cost' => $jo->total_cost,
            'jo_profit' => $jo->margin,
            'jo_margin_percent' => $jo->margin_percentage,
            'jo_units' => $jo->shipmentLegs->map(fn($leg) => 
                ($leg->truck->plate_number ?? 'Sewa') . " (" . ($leg->driver->name ?? '-') . ")"
            )->unique()->values()->all()
        ];
    }

    protected function summarizeSpecificCustomer(string $keyword): array
    {
        $cust = \App\Models\Master\Customer::where('name', 'like', "%$keyword%")->first();
        
        if (!$cust) return ['specific_error' => "Customer dengan nama '$keyword' tidak ditemukan."];

        $unpaid = \App\Models\Finance\Invoice::where('customer_id', $cust->id)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->sum('total_amount');

        $lastJob = \App\Models\Operations\JobOrder::where('customer_id', $cust->id)->latest()->first();

        return [
            'specific_type' => 'customer',
            'cust_name' => $cust->name,
            'cust_ar' => $unpaid,
            'cust_last_activity' => $lastJob ? $lastJob->created_at->toDateString() : 'Belum ada',
            'cust_total_jobs' => \App\Models\Operations\JobOrder::where('customer_id', $cust->id)->count()
        ];
    }

    protected function resolveActionUrl(string $t): ?array
    {
        if (str_contains($t, 'invoice') || str_contains($t, 'tagihan')) return ['label' => 'Invoice', 'url' => url('/invoices')];
        if (str_contains($t, 'job') || str_contains($t, 'order')) return ['label' => 'Job Order', 'url' => url('/job-orders')];
        if (str_contains($t, 'vendor') || str_contains($t, 'supplier')) return ['label' => 'Vendor', 'url' => url('/vendors')];
        if (str_contains($t, 'customer') || str_contains($t, 'pelanggan')) return ['label' => 'Customer', 'url' => url('/customers')];
        if (str_contains($t, 'fleet') || str_contains($t, 'armada') || str_contains($t, 'truk')) return ['label' => 'Fleet', 'url' => url('/trucks')];
        if (str_contains($t, 'driver') || str_contains($t, 'supir')) return ['label' => 'Driver', 'url' => url('/drivers')];
        if (str_contains($t, 'laporan') || str_contains($t, 'report')) return ['label' => 'Laporan', 'url' => url('/reports')];
        
        return null;
    }

    public function detectIntent(string $q): string
    {
        $t = mb_strtolower($q);
        $map = [
            'piutang' => ['piutang', 'invoice', 'tagihan customer', 'aging'],
            'hutang' => ['hutang', 'vendor bill', 'tagihan vendor', 'account payable'],
            'profit' => ['laba', 'rugi', 'profit', 'loss', 'pendapatan', 'beban'],
            'cash_flow' => ['arus kas', 'cash flow', 'kas bank', 'penerimaan', 'pengeluaran'],
            'job_customer' => ['job', 'order', 'customer', 'pelanggan', 'status job', 'muatan'],
            'vendor_performance' => ['vendor', 'perform', 'terlambat', 'ontime', 'supplier'],
            'fleet' => ['truk', 'armada', 'kendaraan', 'mobil', 'truck', 'fleet', 'stnk', 'kir'],
            'driver' => ['supir', 'driver', 'pengemudi', 'karyawan'],
            'activity' => ['aktivitas', 'terbaru', 'update', 'perubahan'],
            'margin' => ['margin', 'profitability', 'persentase profit', 'untung bersih', 'rasio laba'],
            'bad_debt' => ['macet', 'tak tertagih', 'bad debt', 'tunggakan lama', 'susah bayar'],
            'cost_structure' => ['analisa biaya', 'detail beban', 'pengeluaran terbesar', 'boros', 'biaya operasional'],
            'greeting' => ['halo', 'hi', 'pagi', 'siang', 'sore', 'malam', 'hello', 'hai'],
            'capabilities' => ['bisa apa', 'help', 'bantuan', 'menu', 'fitur', 'panduan', 'menguasai', 'pintar'],
        ];
        // Deteksi intent khusus untuk job order belum invoice
        if (str_contains($t, 'belum') && (str_contains($t, 'invoice') || str_contains($t, 'faktur'))) {
            return 'pending_job';
        }
        // Intent standar
        foreach ($map as $k => $words) {
            foreach ($words as $w) {
                if (str_contains($t, $w)) {
                    return $k;
                }
            }
        }

        return 'general_financial';
    }

    protected function summarizeReceivables(): array
    {
        $aging = [
            '0_7' => $this->sumInvoiceAging(0, 7),
            '8_30' => $this->sumInvoiceAging(8, 30),
            '31_60' => $this->sumInvoiceAging(31, 60),
            'gt_60' => $this->sumInvoiceAging(61, 3650),
        ];

        $rows = Invoice::query()
            ->select('customer_id', DB::raw('SUM(total_amount) as total'))
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->groupBy('customer_id')->orderByDesc(DB::raw('SUM(total_amount)'))
            ->limit(10)->get();

        return [
            'ar_total_outstanding' => (float) Invoice::whereNotIn('status', ['paid', 'cancelled'])->sum('total_amount'),
            'ar_top_customers' => $rows,
            'ar_aging' => $aging,
        ];
    }

    protected function sumInvoiceAging(int $fromDays, int $toDays): float
    {
        $today = now()->startOfDay();
        $from = $today->copy()->subDays($toDays);
        $to = $today->copy()->subDays($fromDays);

        return (float) Invoice::whereNotIn('status', ['paid', 'cancelled'])
            ->whereBetween('due_date', [$from, $to])->sum('total_amount');
    }

    protected function summarizePayables(): array
    {
        $rows = VendorBill::query()
            ->select('vendor_id', DB::raw('SUM(total_amount) as total'))
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->groupBy('vendor_id')->orderByDesc(DB::raw('SUM(total_amount)'))->limit(10)->get();

        return [
            'ap_total_outstanding' => (float) VendorBill::whereNotIn('status', ['paid', 'cancelled'])->sum('total_amount'),
            'ap_top_vendors' => $rows,
        ];
    }

    protected function summarizeProfitLoss(?string $from = null, ?string $to = null): array
    {
        $from = $from ?: now()->startOfMonth()->toDateString();
        $to = $to ?: now()->endOfMonth()->toDateString();
        $rows = DB::table('journal_lines as jl')
            ->join('journals as j', 'j.id', '=', 'jl.journal_id')
            ->join('chart_of_accounts as a', 'a.id', '=', 'jl.account_id')
            ->where('j.status', 'posted')
            ->whereBetween('j.journal_date', [$from, $to])
            ->whereIn('a.type', ['revenue', 'expense'])
            ->groupBy('jl.account_id', 'a.code', 'a.name', 'a.type')
            ->selectRaw('jl.account_id, a.code, a.name, a.type, SUM(jl.debit) as d, SUM(jl.credit) as c')
            ->get();
        $totalRevenue = 0;
        $totalExpense = 0;
        foreach ($rows as $r) {
            if ($r->type === 'revenue') {
                $totalRevenue += (float) $r->c - (float) $r->d;
            } else {
                $totalExpense += (float) $r->d - (float) $r->c;
            }
        }

        $profit = $totalRevenue - $totalExpense;
        $margin = $totalRevenue > 0 ? ($profit / $totalRevenue) * 100 : 0;

        return [
            'pl_period' => [$from, $to],
            'pl_total_revenue' => $totalRevenue,
            'pl_total_expense' => $totalExpense,
            'pl_profit' => $profit,
            'pl_net_profit_margin_percent' => round($margin, 2),
        ];
    }

    protected function summarizeCashFlow(?string $from = null, ?string $to = null): array
    {
        $from = $from ?: now()->startOfMonth()->toDateString();
        $to = $to ?: now()->endOfMonth()->toDateString();
        $in = DB::table('cash_bank_transactions')->whereBetween('tanggal', [$from, $to])->where('jenis', 'cash_in')->sum('amount');
        $out = DB::table('cash_bank_transactions')->whereBetween('tanggal', [$from, $to])->where('jenis', 'cash_out')->sum('amount');

        return [
            'cash_in' => (float) $in,
            'cash_out' => (float) $out,
            'cash_net' => (float) $in - (float) $out,
            'period' => [$from, $to],
        ];
    }

    protected function summarizeJobOrders(): array
    {
        $counts = JobOrder::select('status', DB::raw('COUNT(*) as c'))->groupBy('status')->pluck('c', 'status');
        $recent = JobOrder::latest()->take(10)->get(['id', 'job_number', 'status', 'order_date', 'customer_id']);

        return [
            'jo_counts' => $counts,
            'jo_recent' => $recent,
        ];
    }

    protected function summarizeVendorPerformance(): array
    {
        $byVendor = Transport::select('vendor_id', DB::raw("SUM(CASE WHEN status='delivered' THEN 1 ELSE 0 END) as delivered"))
            ->where('executor_type', 'vendor')->groupBy('vendor_id')->orderByDesc('delivered')->limit(10)->get();

        return ['vendor_top_delivered' => $byVendor];
    }

    protected function summarizeFleet(): array
    {
        // Assuming Truck model has 'status' or similar. If not, we just count.
        // Checking Truck model structure via assumption or generic count.
        // Ideally we check column names but for now we'll do basic counts.
        $total = \App\Models\Master\Truck::count();
        // If status exists:
        // $byStatus = \App\Models\Master\Truck::select('status', DB::raw('count(*) as c'))->groupBy('status')->pluck('c', 'status');
        
        $recent = \App\Models\Master\Truck::latest()->take(5)->get();

        return [
            'fleet_total' => $total,
            'fleet_recent_added' => $recent,
        ];
    }

    protected function summarizeDrivers(): array
    {
        $total = \App\Models\Master\Driver::count();
        $recent = \App\Models\Master\Driver::latest()->take(5)->get();

        return [
            'driver_total' => $total,
            'driver_recent_added' => $recent,
        ];
    }

    protected function summarizeRecentActivity(): array
    {
        // Fetch latest updated JobOrders and Invoices
        $jobs = JobOrder::latest('updated_at')->take(5)->get(['job_number', 'status', 'updated_at']);
        $invoices = Invoice::latest('updated_at')->take(5)->get(['invoice_number', 'status', 'updated_at']);

        return [
            'recent_jobs' => $jobs,
            'recent_invoices' => $invoices,
        ];
    }

    protected function summarizeBadDebt(): array
    {
        // Invoices overdue > 90 days
        $query = Invoice::whereNotIn('status', ['paid', 'cancelled'])
            ->where('due_date', '<', now()->subDays(90));
            
        $totalBadDebt = (float) $query->sum('total_amount');
        
        $rows = $query->select('invoice_number', 'customer_id', 'total_amount', 'due_date')
            ->with('customer:id,name')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        return [
            'bad_debt_total' => $totalBadDebt,
            'bad_debt_risk_invoices' => $rows->map(fn($i) => [
                'inv' => $i->invoice_number,
                'customer' => $i->customer->name ?? $i->customer_id,
                'amount' => (float) $i->total_amount,
                'days_overdue' => now()->diffInDays($i->due_date),
            ]),
        ];
    }

    protected function summarizePendingJobOrders(): array
    {
        // Job orders yang belum memiliki invoice (asumsi relasi invoices via job_order_id)
        $jobs = \App\Models\Operations\JobOrder::whereDoesntHave('invoices')
            ->with('customer')
            ->select('job_number', 'status', 'created_at', 'customer_id')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $list = $jobs->map(function($jo){
            return [
                'job_number' => $jo->job_number,
                'status' => $jo->status,
                'created_at' => $jo->created_at->toDateString(),
                'customer_name' => $jo->customer ? $jo->customer->name : 'N/A',
            ];
        })->toArray();

        return [
            'pending_jobs' => $list,
            'pending_count' => count($list),
        ];
    }


    protected function summarizeExpenseBreakdown(): array
    {
        $from = now()->startOfMonth()->toDateString();
        $to = now()->endOfMonth()->toDateString();
        
        $rows = DB::table('journal_lines as jl')
            ->join('journals as j', 'j.id', '=', 'jl.journal_id')
            ->join('chart_of_accounts as a', 'a.id', '=', 'jl.account_id')
            ->where('j.status', 'posted')
            ->whereBetween('j.journal_date', [$from, $to])
            ->where('a.type', 'expense')
            ->select('a.name', DB::raw('SUM(jl.debit - jl.credit) as total'))
            ->groupBy('a.id', 'a.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
            
        return [
            'top_expenses' => $rows,
            'expense_period' => [$from, $to]
        ];
    }

    protected function summarizeGeneral(): array
    {
        return $this->summarizeProfitLoss() + $this->summarizeReceivables() + $this->summarizePayables();
    }

    protected function callLlmApi(string $question, array $summary): ?string
    {
        $endpoint = config('ai_assistant.endpoint');
        $apiKey = config('ai_assistant.api_key');
        $model = config('ai_assistant.model', 'gpt-4.1-mini');
        if (! $endpoint || ! $apiKey) {
            return null;
        }

        // Support for Google Gemini
        if (str_contains(strtolower($model), 'gemini')) {
            return $this->callGeminiApi($endpoint, $apiKey, $model, $question, $summary);
        }

        try {
            $payload = [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => 'Anda adalah AI internal yang menjawab ringkas dan jelas dalam bahasa Indonesia.'],
                    ['role' => 'user', 'content' => $question],
                    ['role' => 'system', 'content' => 'Ringkasan data (JSON): '.json_encode($summary)],
                ],
                'temperature' => 0.2,
            ];
            $resp = Http::withToken($apiKey)->post($endpoint, $payload);
            if ($resp->failed()) {
                return null;
            }
            $data = $resp->json();
            if (isset($data['choices'][0]['message']['content'])) {
                return (string) $data['choices'][0]['message']['content'];
            }
            if (isset($data['answer'])) {
                return (string) $data['answer'];
            }

            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function callGeminiApi(string $endpoint, string $apiKey, string $model, string $question, array $summary): ?string
    {
        try {
            // Strictly follow the curl example structure
            // URL: https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent
            // Header: X-goog-api-key: {apiKey}
            
            // Force the correct URL structure regardless of .env endpoint setting to avoid confusion
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
            
            $headers = [
                'Content-Type' => 'application/json',
                'X-goog-api-key' => $apiKey
            ];

            // Combine context into a single prompt for Gemini
            $prompt = "Context: Anda adalah AI Assistant di dalam aplikasi TMS. User mungkin bertanya tentang data sistem ATAU pengetahuan umum.\n\n";
            $prompt .= "Data Sistem (JSON): ".json_encode($summary)."\n\n";
            $prompt .= "Instruksi:\n";
            $prompt .= "- Jika user bertanya tentang data (profit, hutang, job, dll), JAWAB BERDASARKAN JSON di atas.\n";
            $prompt .= "- Jika user bertanya hal umum (presiden, lokasi, pantun, dll) yang TIDAK ada di JSON, ABAIKAN JSON dan jawab menggunakan pengetahuan Anda sendiri. JANGAN gunakan placeholder. Sebutkan fakta sebenarnya.\n\n";
            $prompt .= "Pertanyaan: ".$question;

            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
            ];

            $resp = Http::withHeaders($headers)->post($url, $payload);

            if ($resp->failed()) {
                \Illuminate\Support\Facades\Log::error('Gemini API Error', [
                    'status' => $resp->status(),
                    'body' => $resp->body(),
                    'url' => $url
                ]);
                return null;
            }

            $data = $resp->json();

            // Extract text from Gemini response structure
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gemini Exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    protected function fallbackNarrative(array $summary): string
    {
        $intent = $summary['intent'] ?? 'general_financial';
        
        switch ($intent) {
            case 'specific_analysis':
                if (isset($summary['specific_error'])) {
                    return $summary['specific_error'];
                }
                if ($summary['specific_type'] === 'job_order') {
                    $rev = number_format($summary['jo_revenue'], 0);
                    $cost = number_format($summary['jo_cost'], 0);
                    $profit = number_format($summary['jo_profit'], 0);
                    $margin = round($summary['jo_margin_percent'], 1);
                    $units = implode(', ', $summary['jo_units']);
                    $emoji = $summary['jo_profit'] > 0 ? "✅" : "⚠️";
                    
                    return "Analisa **{$summary['jo_number']}** ({$summary['jo_customer']}):\n\n" .
                           "💰 Pendapatan: Rp {$rev}\n" .
                           "💸 Biaya: Rp {$cost}\n" .
                           "📈 Profit: **Rp {$profit}** (Margin: {$margin}%)\n" .
                           "🚛 Unit: {$units}\n\n" .
                           "Status: **{$emoji} " . ($summary['jo_profit'] > 0 ? "Untung" : "Rugi") . "**";
                }
                if ($summary['specific_type'] === 'customer') {
                    $ar = number_format($summary['cust_ar'], 0);
                    return "Info Customer **{$summary['cust_name']}**:\n\n" .
                           "📄 Total Job: {$summary['cust_total_jobs']}\n" .
                           "💵 Sisa Piutang: **Rp {$ar}**\n" .
                           "📅 Aktivitas Terakhir: {$summary['cust_last_activity']}";
                }
                return "Data ditemukan.";

            case 'greeting':
                return "Halo! Saya Gemini Assistant. Saya terhubung langsung dengan database TMS Anda. Silakan tanya tentang **Profit**, **Piutang**, **Hutang**, atau **Status Armada**.";
            
            case 'capabilities':
                return "Saya bisa menganalisa data real-time sistem ini. Coba tanyakan:\n" .
                       "- \"Berapa profit bulan ini?\"\n" .
                       "- \"Siapa customer dengan piutang terbesar?\"\n" .
                       "- \"Cek invoice yang macet > 90 hari\"\n" .
                       "- \"Bagaimana status armada hari ini?\"";

            case 'piutang':
                $ar = number_format($summary['ar_total_outstanding'] ?? 0, 2, ',', '.');
                $topCustomer = $summary['ar_top_customers'][0]->customer->name ?? 'N/A';
                return "Saat ini total piutang (AR) yang belum lunas adalah **Rp {$ar}**.\n\n" .
                       "Customer dengan tagihan terbesar adalah **{$topCustomer}**. " .
                       "Sebaiknya segera dilakukan penagihan untuk invoice yang sudah jatuh tempo.";

            case 'hutang':
                $ap = number_format($summary['ap_total_outstanding'] ?? 0, 2, ',', '.');
                return "Total kewajiban hutang (AP) tercatat sebesar **Rp {$ap}**.\n" .
                       "Pastikan cash flow mencukupi untuk pembayaran vendor prioritas minggu ini.";

            case 'profit':
            case 'margin':
                $p = number_format($summary['pl_profit'] ?? 0, 2, ',', '.');
                $rev = number_format($summary['pl_total_revenue'] ?? 0, 2, ',', '.');
                $margin = $summary['pl_net_profit_margin_percent'] ?? 0;
                
                $analysis = $margin > 20 ? "Sangat sehat! 🚀" : ($margin > 0 ? "Positif, namun perlu efisiensi." : "Perhatian! Margin negatif. ⚠️");
                
                return "Laporan Laba Rugi Bulan Ini:\n" .
                       "- Pendapatan: Rp {$rev}\n" .
                       "- Net Profit: **Rp {$p}**\n" .
                       "- Margin: **{$margin}%**\n\n" .
                       "Analisa: {$analysis}";

            case 'cash_flow':
                $net = number_format($summary['cash_net'] ?? 0, 2, ',', '.');
                $in = number_format($summary['cash_in'] ?? 0, 2, ',', '.');
                $out = number_format($summary['cash_out'] ?? 0, 2, ',', '.');
                return "Arus Kas Periode Ini:\n" .
                       "🟢 Masuk: Rp {$in}\n" .
                       "🔴 Keluar: Rp {$out}\n" .
                       "💰 Net: **Rp {$net}**";

            case 'fleet':
                $total = $summary['fleet_total'] ?? 0;
                return "Data Armada:\n" .
                       "Kita memiliki total **{$total} unit** truk yang terdaftar dalam sistem.\n" .
                       "Silakan cek menu 'Fleet' untuk detail status maintenance dan ketersediaan.";

            case 'driver':
                $total = $summary['driver_total'] ?? 0;
                return "Data Driver:\n" .
                       "Total pengemudi aktif saat ini: **{$total} orang**.";

            case 'bad_debt':
                $total = number_format($summary['bad_debt_total'] ?? 0, 2, ',', '.');
                $count = count($summary['bad_debt_risk_invoices'] ?? []);
                if ($count > 0) {
                    $list = collect($summary['bad_debt_risk_invoices'])->map(fn($i) => "- {$i['inv']} ({$i['customer']}): Rp ".number_format($i['amount'],0))->join("\n");
                    return "⚠️ **Peringatan Piutang Macet**\n\n" .
                           "Ditemukan **{$count} invoice** yang menunggak lebih dari 90 hari dengan total **Rp {$total}**.\n\n" .
                           "Detail:\n{$list}";
                }
                return "Kabar baik! Tidak ada invoice macet (>90 hari) saat ini. Pertahankan penagihan yang disiplin. ✅";

            case 'cost_structure':
                $top = collect($summary['top_expenses'] ?? [])->map(fn($x) => "- {$x->name}: Rp ".number_format($x->total,0))->join("\n");
                return "📊 **Struktur Biaya Operasional**\n\n" .
                       "Pengeluaran terbesar bulan ini didominasi oleh:\n" .
                       "{$top}\n\n" .
                       "Cek apakah ada pemborosan di pos-pos tersebut.";

            case 'pending_job':
                $count = $summary['pending_count'] ?? 0;
                $jobs = $summary['pending_jobs'] ?? [];
                $msg = "Terdapat *{$count}* job order yang belum memiliki invoice.\\n\\n";
                foreach ($jobs as $j) {
                    $cust = $j['customer_name'] ?? 'N/A';
                    $msg .= "- {$j['job_number']} (Customer: {$cust}, Status: {$j['status']}, Dibuat: {$j['created_at']})\\n";
                }
                return $msg;


            default:
                // If API is connected, this part is rarely reached for general questions.
                // But if offline/fallback, we should explain limitation.
                return "Maaf, saat ini saya difokuskan untuk membantu urusan **Sistem TMS, Keuangan, dan Operasional** Anda.\n\n" .
                       "Saya belum bisa menjawab pertanyaan umum di luar konteks data perusahaan (seperti resep masakan, cuaca, atau sejarah). " .
                       "Tapi kalau soal **Profit**, **Customer**, atau **Armada**, saya ahlinya! 😎";
        }
    }
}
