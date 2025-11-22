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
        $year = (int) $date->format('Y');
        $month = (int) $date->format('m');

        $period = FiscalPeriod::query()
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if (! $period) {
            throw new InvalidArgumentException("Periode fiskal untuk {$date->format('F Y')} belum dibuat. Silakan buat periode fiskal terlebih dahulu di menu Accounting → Fiscal Periods.");
        }

        if ($period->status !== 'open') {
            throw new InvalidArgumentException("Periode fiskal {$date->format('F Y')} berstatus '{$period->status}'. Hanya periode dengan status 'open' yang bisa diposting.");
        }

        // Validasi: periode sebelumnya harus sudah closed atau locked sebelum bulan baru diposting
        $prevYear = $month === 1 ? $year - 1 : $year;
        $prevMonth = $month === 1 ? 12 : $month - 1;

        // Cari periode sebelumnya
        $prevPeriod = FiscalPeriod::query()
            ->where('year', $prevYear)
            ->where('month', $prevMonth)
            ->first();

        // Jika ada periode sebelumnya dan belum closed/locked, blok posting
        if ($prevPeriod && ! in_array($prevPeriod->status, ['closed', 'locked'], true)) {
            $prevLabel = (new \DateTimeImmutable(sprintf('%04d-%02d-01', $prevYear, $prevMonth)))->format('F Y');
            throw new InvalidArgumentException("Periode sebelumnya ({$prevLabel}) belum di-close. Tutup periode tersebut terlebih dahulu sebelum membuat jurnal untuk {$date->format('F Y')}.");
        }

        // Jika periode sebelumnya tidak ada tetapi ada periode yang lebih lama masih open, juga blok
        if (! $prevPeriod) {
            $openEarlier = FiscalPeriod::query()
                ->where(function ($q) use ($year, $month) {
                    $q->where('year', $year)->where('month', '<', $month)
                      ->orWhere('year', '<', $year);
                })
                ->where('status', 'open')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->first();
            if ($openEarlier) {
                $earlierLabel = (new \DateTimeImmutable(sprintf('%04d-%02d-01', $openEarlier->year, $openEarlier->month)))->format('F Y');
                throw new InvalidArgumentException("Masih ada periode terbuka ({$earlierLabel}) yang belum di-close. Tutup semua periode lebih lama sebelum mem-posting {$date->format('F Y')}.");
            }
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
                \Log::error('Jurnal tidak seimbang', [
                    'total_debit' => $totalDebit,
                    'total_credit' => $totalCredit,
                    'lines' => $lines,
                    'header' => $header
                ]);
                throw new InvalidArgumentException('Jurnal tidak seimbang atau nol. Debit: '.$totalDebit.' Credit: '.$totalCredit);
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
