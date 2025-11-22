<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FixedAsset;
use App\Services\Accounting\AssetDepreciationService;

class AssetDepreciateCommand extends Command
{
    protected $signature = 'asset:depreciate {--force : Jalankan meski sudah ada sebagian}';
    protected $description = 'Post depresiasi bulan berjalan untuk seluruh aset aktif.';

    public function handle(AssetDepreciationService $service): int
    {
        $this->info('Memulai depresiasi aset...');
        $count = 0;
        FixedAsset::where('status','active')->chunk(100, function ($assets) use (&$count, $service) {
            foreach ($assets as $asset) {
                $existing = $asset->depreciations()->where('period_ym', now()->format('Y-m'))->first();
                if ($existing && ! $this->option('force')) {
                    $this->line('Skip '.$asset->code.' (sudah)');
                    continue;
                }
                try {
                    $service->postCurrentMonth($asset);
                    $this->line('Posted depresiasi: '.$asset->code);
                    $count++;
                } catch (\Throwable $e) {
                    $this->error('Gagal '.$asset->code.': '.$e->getMessage());
                }
            }
        });
        $this->info('Selesai. Total: '.$count.' aset diposting.');
        return Command::SUCCESS;
    }
}
