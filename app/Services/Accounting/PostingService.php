<?php

namespace App\Services\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FiscalPeriod;
use App\Models\Accounting\Journal;
use App\Models\Accounting\JournalLine;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PostingService
{
    public function ensureOpenPeriod(\DateTimeInterface $date): FiscalPeriod
    {
        $period = FiscalPeriod::query()
            ->where('year', (int) $date->format('Y'))
            ->where('month', (int) $date->format('m'))
            ->first();

        if (! $period) {
            throw new InvalidArgumentException('Periode fiskal belum dibuat.');
        }
        if ($period->status !== 'open') {
            throw new InvalidArgumentException('Periode fiskal tidak open.');
        }

        return $period;
    }

    public function generateJournalNo(\DateTimeInterface $date): string
    {
        $prefix = 'JNL-'.$date->format('Ym').'-';
        $last = Journal::query()
            ->where('journal_no', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('journal_no');

        $seq = 1;
        if ($last && preg_match('/(\d{4})$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function postGeneral(array $header, array $lines): Journal
    {
        // header: journal_date, source_type, source_id, memo, currency
        // lines: [ ['account_code'=>..., 'debit'=>..., 'credit'=>..., 'desc'=>..., dims...] ]
        return DB::transaction(function () use ($header, $lines) {
            $date = new \DateTimeImmutable($header['journal_date']);
            $period = $this->ensureOpenPeriod($date);

            $totalDebit = 0;
            $totalCredit = 0;
            foreach ($lines as $l) {
                $totalDebit += (float) ($l['debit'] ?? 0);
                $totalCredit += (float) ($l['credit'] ?? 0);
            }
            if (round($totalDebit, 2) !== round($totalCredit, 2) || $totalDebit <= 0) {
                throw new InvalidArgumentException('Jurnal tidak seimbang atau nol.');
            }

            $journal = new Journal;
            $journal->journal_no = $this->generateJournalNo($date);
            $journal->journal_date = $date->format('Y-m-d');
            $journal->fiscal_period_id = $period->id;
            $journal->source_type = (string) ($header['source_type'] ?? 'adjustment');
            $journal->source_id = (int) ($header['source_id'] ?? 0);
            $journal->memo = $header['memo'] ?? null;
            $journal->status = 'posted';
            $journal->currency = $header['currency'] ?? 'IDR';
            $journal->total_debit = $totalDebit;
            $journal->total_credit = $totalCredit;
            $journal->posted_by = $header['posted_by'] ?? null;
            $journal->posted_at = now();
            $journal->save();

            foreach ($lines as $l) {
                $account = ChartOfAccount::query()->where('code', $l['account_code'])->first();
                if (! $account) {
                    throw new InvalidArgumentException('Akun tidak ditemukan: '.$l['account_code']);
                }
                if ($account->status !== 'active' || ! $account->is_postable) {
                    throw new InvalidArgumentException('Akun tidak aktif/tidak postable: '.$account->code);
                }

                JournalLine::create([
                    'journal_id' => $journal->id,
                    'account_id' => $account->id,
                    'description' => $l['desc'] ?? null,
                    'debit' => (float) ($l['debit'] ?? 0),
                    'credit' => (float) ($l['credit'] ?? 0),
                    'job_order_id' => $l['job_order_id'] ?? null,
                    'transport_id' => $l['transport_id'] ?? null,
                    'customer_id' => $l['customer_id'] ?? null,
                    'vendor_id' => $l['vendor_id'] ?? null,
                ]);
            }

            return $journal->load('lines');
        });
    }
}
