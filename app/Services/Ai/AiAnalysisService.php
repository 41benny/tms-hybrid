<?php

namespace App\Services\Ai;

use App\Models\Finance\Invoice;
use App\Models\Finance\VendorBill;
use App\Models\Operations\JobOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AiAnalysisService
{
    public function analyze(string $question): string
    {
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
            default:
                $summary += $this->summarizeGeneral();
                break;
        }

        $answer = $this->callLlmApi($question, $summary);
        if (! $answer) {
            $answer = $this->fallbackNarrative($summary);
        }

        return $answer;
    }

    public function detectIntent(string $q): string
    {
        $t = mb_strtolower($q);
        $map = [
            'piutang' => ['piutang', 'invoice', 'tagihan customer', 'aging'],
            'hutang' => ['hutang', 'vendor bill', 'tagihan vendor', 'ap'],
            'profit' => ['laba', 'rugi', 'profit', 'loss', 'pendapatan', 'beban'],
            'cash_flow' => ['arus kas', 'cash flow', 'kas bank', 'penerimaan', 'pengeluaran'],
            'job_customer' => ['job', 'order', 'customer', 'pelanggan', 'status job'],
            'vendor_performance' => ['vendor', 'perform', 'terlambat', 'ontime'],
        ];
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

        return [
            'pl_period' => [$from, $to],
            'pl_total_revenue' => $totalRevenue,
            'pl_total_expense' => $totalExpense,
            'pl_profit' => $totalRevenue - $totalExpense,
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

    protected function fallbackNarrative(array $summary): string
    {
        $intent = $summary['intent'] ?? 'general_financial';
        switch ($intent) {
            case 'piutang':
                $ar = number_format($summary['ar_total_outstanding'] ?? 0, 2, ',', '.');

                return "Ringkasan piutang: total outstanding Rp {$ar}. Cek aging dan top customer pada halaman invoice.";
            case 'hutang':
                $ap = number_format($summary['ap_total_outstanding'] ?? 0, 2, ',', '.');

                return "Ringkasan hutang: total outstanding Rp {$ap}. Lihat daftar vendor dengan tagihan terbesar.";
            case 'profit':
                $p = number_format($summary['pl_profit'] ?? 0, 2, ',', '.');

                return "Laba/rugi periode ini: Rp {$p}. Detail tersedia di laporan Laba Rugi.";
            case 'cash_flow':
                $net = number_format($summary['cash_net'] ?? 0, 2, ',', '.');

                return "Arus kas bersih periode terpilih: Rp {$net}.";
            default:
                return 'Berikut ringkasan singkat telah dihitung. Untuk rincian, gunakan halaman laporan terkait.';
        }
    }
}
