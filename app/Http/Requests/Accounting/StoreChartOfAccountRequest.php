<?php

namespace App\Http\Requests\Accounting;

use App\Models\Accounting\ChartOfAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChartOfAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', 'unique:chart_of_accounts,code'],
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', Rule::in(array_keys(ChartOfAccount::TYPES))],
            'parent_id' => ['nullable', 'exists:chart_of_accounts,id'],
            'is_postable' => ['nullable', 'boolean'],
            'is_cash' => ['nullable', 'boolean'],
            'is_bank' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(array_keys(ChartOfAccount::STATUSES))],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_postable' => $this->boolean('is_postable'),
            'is_cash' => $this->boolean('is_cash'),
            'is_bank' => $this->boolean('is_bank'),
        ]);
    }
}
