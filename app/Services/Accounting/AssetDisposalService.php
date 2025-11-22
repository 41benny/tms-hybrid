<?php

namespace App\Services\Accounting;

use App\Models\FixedAsset;
use App\Models\AssetDisposal;
use App\Models\Accounting\ChartOfAccount;
use InvalidArgumentException;

class AssetDisposalService
{
    public function __construct(protected PostingService $posting) {}

    public function dispose(FixedAsset $asset, string $date, float $proceed, string $gainLossAccountCode, string $cashAccountCode): AssetDisposal
    {
        if ($asset->status === 'disposed') {
            throw new InvalidArgumentException('Aset sudah didisposal.');
        }

        $bookValue = $asset->bookValue();
        if ($bookValue < -0.01) {
            throw new InvalidArgumentException('Nilai buku negatif, periksa data depresiasi.');
        }
        if (\Carbon\Carbon::parse($date)->lt(\Carbon\Carbon::parse($asset->acquisition_date))) {
            throw new InvalidArgumentException('Tanggal disposal tidak boleh sebelum tanggal perolehan.');
        }
        $gainLoss = $proceed - $bookValue;

        $assetAccountCode = ChartOfAccount::find($asset->account_asset_id)?->code;
        $accumAccountCode = ChartOfAccount::find($asset->account_accum_id)?->code;

        $lines = [];
        // Dr Cash (proceeds)
        if ($proceed > 0) {
            $lines[] = ['account_code' => $cashAccountCode, 'debit' => $proceed, 'credit' => 0, 'desc' => 'Disposal aset '.$asset->code];
        }
        // Dr Accumulated Depreciation (remove accumulation)
        $accumulated = $asset->accumulatedDepreciation();
        if ($accumulated > 0) {
            $lines[] = ['account_code' => $accumAccountCode, 'debit' => $accumulated, 'credit' => 0, 'desc' => 'Pengalihan akumulasi '.$asset->code];
        }
        // Cr Asset Cost (remove asset cost)
        $lines[] = ['account_code' => $assetAccountCode, 'debit' => 0, 'credit' => $asset->acquisition_cost, 'desc' => 'Penghapusan aset '.$asset->code];

        // Gain or Loss line
        if ($gainLoss > 0) {
            // gain -> credit
            $lines[] = ['account_code' => $gainLossAccountCode, 'debit' => 0, 'credit' => $gainLoss, 'desc' => 'Keuntungan disposal '.$asset->code];
        } elseif ($gainLoss < 0) {
            $lines[] = ['account_code' => $gainLossAccountCode, 'debit' => abs($gainLoss), 'credit' => 0, 'desc' => 'Kerugian disposal '.$asset->code];
        }

        $journal = $this->posting->postGeneral([
            'journal_date' => $date,
            'source_type' => 'asset_disposal',
            'source_id' => $asset->id,
            'memo' => 'Disposal aset '.$asset->code
        ], $lines);

        $asset->update(['status' => 'disposed']);

        return AssetDisposal::create([
            'fixed_asset_id' => $asset->id,
            'disposal_date' => $date,
            'proceed_amount' => $proceed,
            'gain_loss_account_id' => ChartOfAccount::where('code', $gainLossAccountCode)->value('id'),
            'posted_journal_id' => $journal->id
        ]);
    }
}
