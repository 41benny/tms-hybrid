<?php

namespace App\Http\Requests\Accounting;

use App\Models\Accounting\ChartOfAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChartOfAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $accountId = $this->route('chart_of_account')?->id ?? null;

        return [
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('chart_of_accounts', 'code')->ignore($accountId),
            ],
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

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $account = $this->route('chart_of_account');
            $parentId = $this->input('parent_id');

            if ($account && $parentId && (int) $parentId === (int) $account->id) {
                $validator->errors()->add('parent_id', 'Akun tidak boleh menjadi parent dirinya sendiri.');
            }
        });
    }
}
