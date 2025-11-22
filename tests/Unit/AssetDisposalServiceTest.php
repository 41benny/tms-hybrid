<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Accounting\AssetDisposalService;
use App\Services\Accounting\AssetDepreciationService;
use App\Services\Accounting\PostingService;
use App\Models\FixedAsset;
use App\Models\Accounting\ChartOfAccount;
use Illuminate\Support\Facades\DB;

class AssetDisposalServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        DB::table('fiscal_periods')->insert([
            'year' => (int) now()->year,
            'month' => (int) now()->month,
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        ChartOfAccount::create(['code'=>'1610','name'=>'Kendaraan','type'=>'asset','is_postable'=>true]);
        ChartOfAccount::create(['code'=>'1620','name'=>'Akumulasi Penyusutan Kendaraan','type'=>'asset','is_postable'=>true]);
        ChartOfAccount::create(['code'=>'6100','name'=>'Beban Gaji & Upah Karyawan','type'=>'expense','is_postable'=>true]);
        ChartOfAccount::create(['code'=>'1110','name'=>'Kas Besar','type'=>'asset','is_postable'=>true]);
        ChartOfAccount::create(['code'=>'7110','name'=>'Pendapatan Bunga Bank','type'=>'revenue','is_postable'=>true]);
        ChartOfAccount::create(['code'=>'7200','name'=>'Beban Lain-lain','type'=>'expense','is_postable'=>false]);
    }

    public function test_disposal_gain()
    {
        $asset = FixedAsset::create([
            'code'=>'FA-DISP','name'=>'Kendaraan Jual','acquisition_date'=>now()->subMonths(5)->toDateString(),
            'acquisition_cost'=>10000000,'useful_life_months'=>12,'residual_value'=>0,
            'depreciation_method'=>'straight_line','account_asset_id'=>ChartOfAccount::where('code','1610')->value('id'),
            'account_accum_id'=>ChartOfAccount::where('code','1620')->value('id'),
            'account_expense_id'=>ChartOfAccount::where('code','6100')->value('id'),'status'=>'active'
        ]);

        // Post beberapa depresiasi untuk menurunkan book value
        $depService = new AssetDepreciationService(app(PostingService::class));
        $depService->postCurrentMonth($asset);

        $disposeService = new AssetDisposalService(app(PostingService::class));
        $result = $disposeService->dispose($asset, now()->toDateString(), 9500000, '7110', '1110');

        $this->assertEquals('disposed', $asset->fresh()->status);
        $this->assertDatabaseHas('asset_disposals', ['id'=>$result->id]);
    }

    public function test_disposal_block_before_acquisition()
    {
        $asset = FixedAsset::create([
            'code'=>'FA-DISP-ERR','name'=>'Kendaraan Salah','acquisition_date'=>now()->toDateString(),
            'acquisition_cost'=>5000000,'useful_life_months'=>12,'residual_value'=>0,
            'depreciation_method'=>'straight_line','account_asset_id'=>ChartOfAccount::where('code','1610')->value('id'),
            'account_accum_id'=>ChartOfAccount::where('code','1620')->value('id'),
            'account_expense_id'=>ChartOfAccount::where('code','6100')->value('id'),'status'=>'active'
        ]);
        $disposeService = new AssetDisposalService(app(PostingService::class));
        $this->expectException(\InvalidArgumentException::class);
        $disposeService->dispose($asset, now()->subDay()->toDateString(), 1000000, '7110', '1110');
    }
}
