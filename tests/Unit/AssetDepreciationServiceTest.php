<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Accounting\AssetDepreciationService;
use App\Services\Accounting\PostingService;
use App\Models\FixedAsset;
use App\Models\Accounting\ChartOfAccount;
use Illuminate\Support\Facades\DB;

class AssetDepreciationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed fiscal period for current month required by PostingService
        DB::table('fiscal_periods')->insert([
            'year' => (int) now()->year,
            'month' => (int) now()->month,
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        // minimal COA untuk pengujian
        ChartOfAccount::create(['code'=>'1610','name'=>'Kendaraan','type'=>'asset','is_postable'=>true]);
        ChartOfAccount::create(['code'=>'1620','name'=>'Akumulasi Penyusutan Kendaraan','type'=>'asset','is_postable'=>true]);
        ChartOfAccount::create(['code'=>'6100','name'=>'Beban Gaji & Upah Karyawan','type'=>'expense','is_postable'=>true]);
    }

    public function test_post_depreciation_success()
    {
        $asset = FixedAsset::create([
            'code'=>'FA-TEST','name'=>'Kendaraan Uji','acquisition_date'=>now()->subMonths(1)->toDateString(),
            'acquisition_cost'=>12000000,'useful_life_months'=>12,'residual_value'=>0,
            'depreciation_method'=>'straight_line','account_asset_id'=>ChartOfAccount::where('code','1610')->value('id'),
            'account_accum_id'=>ChartOfAccount::where('code','1620')->value('id'),
            'account_expense_id'=>ChartOfAccount::where('code','6100')->value('id'),'status'=>'active'
        ]);

        $service = new AssetDepreciationService(app(PostingService::class));
        $dep = $service->postCurrentMonth($asset);

        $this->assertEquals(now()->format('Y-m'), $dep->period_ym);
        $this->assertTrue($dep->amount > 0);
        $this->assertDatabaseHas('asset_depreciations', ['id'=>$dep->id]);
    }

    public function test_stop_after_useful_life()
    {
        $asset = FixedAsset::create([
            'code'=>'FA-LIMIT','name'=>'Kendaraan Limit','acquisition_date'=>now()->subMonths(20)->toDateString(),
            'acquisition_cost'=>12000000,'useful_life_months'=>1,'residual_value'=>0,
            'depreciation_method'=>'straight_line','account_asset_id'=>ChartOfAccount::where('code','1610')->value('id'),
            'account_accum_id'=>ChartOfAccount::where('code','1620')->value('id'),
            'account_expense_id'=>ChartOfAccount::where('code','6100')->value('id'),'status'=>'active'
        ]);

        $service = new AssetDepreciationService(app(PostingService::class));
        $service->postCurrentMonth($asset);
        $this->expectException(\InvalidArgumentException::class);
        $service->postCurrentMonth($asset); // kedua harus gagal
    }
}
