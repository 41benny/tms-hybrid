<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\FixedAsset;
use App\Models\Accounting\ChartOfAccount;
use Illuminate\Http\Request;
use App\Services\Accounting\AssetDepreciationService;
use App\Services\Accounting\AssetDisposalService;

class FixedAssetController extends Controller
{
    public function index()
    {
        $assets = FixedAsset::query()->orderBy('code')->get();
        return view('fixed-assets.index', compact('assets'));
    }

    public function create()
    {
        // Akun Aset Tetap (16xx, non-akumulasi)
        $assetAccounts = ChartOfAccount::query()
            ->where('code', 'like', '16%')
            ->where('name', 'not like', '%Akumulasi%')
            ->where('is_postable', true)
            ->orderBy('code')
            ->get();
        
        // Akun Akumulasi Penyusutan (16xx dengan "Akumulasi")
        $accumAccounts = ChartOfAccount::query()
            ->where('code', 'like', '16%')
            ->where('name', 'like', '%Akumulasi%')
            ->where('is_postable', true)
            ->orderBy('code')
            ->get();
        
        // Akun Beban Penyusutan (semua akun expense/6xxx)
        $expenseAccounts = ChartOfAccount::query()
            ->where(function($q) {
                $q->where('type', 'expense')
                  ->orWhere('code', 'like', '6%');
            })
            ->where('is_postable', true)
            ->orderBy('code')
            ->get();
        
        return view('fixed-assets.create', compact('assetAccounts', 'accumAccounts', 'expenseAccounts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|unique:fixed_assets,code',
            'name' => 'required|string',
            'acquisition_date' => 'required|date',
            'acquisition_cost' => 'required|numeric|min:0',
            'useful_life_months' => 'required|integer|min:1',
            'residual_value' => 'nullable|numeric|min:0',
            'account_asset_id' => 'required|exists:chart_of_accounts,id',
            'account_accum_id' => 'required|exists:chart_of_accounts,id',
            'account_expense_id' => 'required|exists:chart_of_accounts,id',
        ]);
        $data['depreciation_method'] = 'straight_line';
        $data['status'] = 'active';
        FixedAsset::create($data);
        return redirect()->route('fixed-assets.index')->with('success','Aset berhasil ditambahkan');
    }

    public function show(FixedAsset $fixedAsset)
    {
        $fixedAsset->load('depreciations','disposals');
        return view('fixed-assets.show', ['asset' => $fixedAsset]);
    }

    public function edit(FixedAsset $fixedAsset)
    {
        $accounts = ChartOfAccount::query()->orderBy('code')->get();
        return view('fixed-assets.edit', ['asset' => $fixedAsset, 'accounts' => $accounts]);
    }

    public function update(Request $request, FixedAsset $fixedAsset)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'acquisition_date' => 'required|date',
            'acquisition_cost' => 'required|numeric|min:0',
            'useful_life_months' => 'required|integer|min:1',
            'residual_value' => 'nullable|numeric|min:0',
            'account_asset_id' => 'required|exists:chart_of_accounts,id',
            'account_accum_id' => 'required|exists:chart_of_accounts,id',
            'account_expense_id' => 'required|exists:chart_of_accounts,id',
        ]);
        $fixedAsset->update($data);
        return redirect()->route('fixed-assets.show', $fixedAsset)->with('success','Aset diperbarui');
    }

    public function depreciate(FixedAsset $fixedAsset, AssetDepreciationService $service)
    {
        $service->postCurrentMonth($fixedAsset);
        return redirect()->route('fixed-assets.show', $fixedAsset)->with('success','Depresiasi bulan ini diposting');
    }

    public function disposeForm(FixedAsset $fixedAsset)
    {
        return view('fixed-assets.dispose', ['asset' => $fixedAsset]);
    }

    public function dispose(Request $request, FixedAsset $fixedAsset, AssetDisposalService $service)
    {
        $data = $request->validate([
            'disposal_date' => 'required|date',
            'proceed_amount' => 'required|numeric|min:0',
            'gain_loss_account_code' => 'required|string',
            'cash_account_code' => 'required|string'
        ]);
        $service->dispose($fixedAsset, $data['disposal_date'], (float)$data['proceed_amount'], $data['gain_loss_account_code'], $data['cash_account_code']);
        return redirect()->route('fixed-assets.index')->with('success','Aset telah didisposal');
    }
}
