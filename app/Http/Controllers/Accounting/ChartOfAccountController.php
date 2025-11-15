<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StoreChartOfAccountRequest;
use App\Http\Requests\Accounting\UpdateChartOfAccountRequest;
use App\Models\Accounting\ChartOfAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ChartOfAccountController extends Controller
{
    public function index(Request $request): View
    {
        $query = ChartOfAccount::query()->with('parent')->orderBy('code');

        if ($search = trim((string) $request->get('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $accounts = $query->paginate(25)->withQueryString();

        $stats = [
            'total' => ChartOfAccount::count(),
            'active' => ChartOfAccount::where('status', 'active')->count(),
            'non_postable' => ChartOfAccount::where('is_postable', false)->count(),
        ];

        return view('accounting.chart-of-accounts.index', [
            'accounts' => $accounts,
            'types' => ChartOfAccount::TYPES,
            'statuses' => ChartOfAccount::STATUSES,
            'stats' => $stats,
        ]);
    }

    public function create(): View
    {
        $account = new ChartOfAccount([
            'type' => 'asset',
            'status' => 'active',
            'is_postable' => true,
        ]);

        return view('accounting.chart-of-accounts.create', [
            'account' => $account,
            'types' => ChartOfAccount::TYPES,
            'statuses' => ChartOfAccount::STATUSES,
            'parentOptions' => $this->parentOptions(),
        ]);
    }

    public function store(StoreChartOfAccountRequest $request): RedirectResponse
    {
        $data = $this->preparePayload($request->validated());

        ChartOfAccount::create($data);

        return redirect()
            ->route('chart-of-accounts.index')
            ->with('success', 'Akun berhasil ditambahkan.');
    }

    public function edit(ChartOfAccount $chartOfAccount): View
    {
        return view('accounting.chart-of-accounts.edit', [
            'account' => $chartOfAccount,
            'types' => ChartOfAccount::TYPES,
            'statuses' => ChartOfAccount::STATUSES,
            'parentOptions' => $this->parentOptions($chartOfAccount->id),
        ]);
    }

    public function update(
        UpdateChartOfAccountRequest $request,
        ChartOfAccount $chartOfAccount
    ): RedirectResponse {
        $data = $this->preparePayload($request->validated());

        $chartOfAccount->update($data);

        return redirect()
            ->route('chart-of-accounts.index')
            ->with('success', 'Akun berhasil diperbarui.');
    }

    private function parentOptions(?int $excludeId = null): Collection
    {
        return ChartOfAccount::query()
            ->orderBy('code')
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->get();
    }

    private function preparePayload(array $data): array
    {
        $parentId = $data['parent_id'] ?? null;
        if ($parentId === '' || $parentId === null) {
            $parentId = null;
        }
        $data['parent_id'] = $parentId;
        $level = 1;

        if ($parentId) {
            $parent = ChartOfAccount::find($parentId);
            if ($parent) {
                $parentLevel = (int) ($parent->level ?? 1);
                $level = min($parentLevel + 1, 10);
            }
        }

        $data['level'] = $level;

        $isTypeAsset = ($data['type'] ?? null) === 'asset';
        $isPostable = array_key_exists('is_postable', $data) ? (bool) $data['is_postable'] : true;

        if (! $isTypeAsset || ! $isPostable) {
            $data['is_cash'] = false;
            $data['is_bank'] = false;
        }

        $data['is_postable'] = $isPostable;

        return $data;
    }
}
