<?php

namespace App\Services\Accounting;

use App\Models\FixedAsset;
use App\Models\AssetDepreciation;
use App\Services\Accounting\PostingService;
use App\Models\Accounting\ChartOfAccount;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class AssetDepreciationService
{
    public function __construct(protected PostingService $posting) {}

    public function postCurrentMonth(FixedAsset $asset): AssetDepreciation
    {
        $periodYm = now()->format('Y-m');
        if ($asset->status !== 'active') {
            throw new InvalidArgumentException('Aset tidak aktif atau sudah didisposal.');
        }
        // Stop jika umur sudah habis
        $postedCount = AssetDepreciation::where('fixed_asset_id',$asset->id)->count();
        if ($postedCount >= $asset->useful_life_months) {
            throw new InvalidArgumentException('Umur aset sudah habis, depresiasi dihentikan.');
        }
        $existing = AssetDepreciation::where('fixed_asset_id',$asset->id)->where('period_ym',$periodYm)->first();
        if ($existing) {
            return $existing; // sudah diposting periode ini
        }

        // Tidak boleh depresiasi sebelum tanggal perolehan
        $acqMonthStart = \Carbon\Carbon::parse($asset->acquisition_date)->startOfMonth();
        if (now()->startOfMonth()->lt($acqMonthStart)) {
            throw new InvalidArgumentException('Belum waktunya depresiasi (bulan perolehan belum dimulai).');
        }

        $monthly = $this->calculateMonthly($asset);
        if ($monthly <= 0) {
            throw new InvalidArgumentException('Nilai depresiasi 0.');
        }

        $accumulatedBefore = $asset->accumulatedDepreciation();
        $maxDepreciable = $asset->acquisition_cost - $asset->residual_value;
        if ($accumulatedBefore + $monthly > $maxDepreciable + 0.01) {
            $monthly = max(0, $maxDepreciable - $accumulatedBefore); // koreksi terakhir
        }
        if ($monthly <= 0) {
            throw new InvalidArgumentException('Tidak ada sisa nilai untuk didepresiasi.');
        }

        $accumAccountCode = ChartOfAccount::find($asset->account_accum_id)?->code;
        $expenseAccountCode = ChartOfAccount::find($asset->account_expense_id)?->code;
        if (! $accumAccountCode || ! $expenseAccountCode) {
            throw new InvalidArgumentException('Mapping akun depresiasi tidak lengkap.');
        }

        $journal = $this->posting->postGeneral([
            'journal_date' => now()->toDateString(),
            'source_type' => 'asset_depreciation',
            'source_id' => $asset->id,
            'memo' => 'Depresiasi aset '.$asset->code.' periode '.$periodYm
        ], [
            ['account_code' => $expenseAccountCode, 'debit' => $monthly, 'credit' => 0, 'desc' => 'Depresiasi '.$asset->code],
            ['account_code' => $accumAccountCode, 'debit' => 0, 'credit' => $monthly, 'desc' => 'Akumulasi Depresiasi '.$asset->code]
        ]);

        return AssetDepreciation::create([
            'fixed_asset_id' => $asset->id,
            'period_ym' => $periodYm,
            'amount' => $monthly,
            'posted_journal_id' => $journal->id
        ]);
    }

    public function calculateMonthly(FixedAsset $asset): float
    {
        $base = (float) ($asset->acquisition_cost - $asset->residual_value);
        if ($base <= 0) return 0.0;
        return round($base / $asset->useful_life_months, 2);
    }
}
