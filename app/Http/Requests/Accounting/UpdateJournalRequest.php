<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJournalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'journal_date' => ['required', 'date'],
            'memo' => ['nullable', 'string', 'max:500'],
            'currency' => ['nullable', 'string', 'size:3'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'exists:chart_of_accounts,id'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.description' => ['nullable', 'string', 'max:255'],
            'lines.*.customer_id' => ['nullable', 'exists:customers,id'],
            'lines.*.vendor_id' => ['nullable', 'exists:vendors,id'],
            'lines.*.job_order_id' => ['nullable', 'exists:job_orders,id'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $lines = $this->input('lines', []);
            $totalDebit = 0;
            $totalCredit = 0;

            foreach ($lines as $index => $line) {
                $debit = (float) ($line['debit'] ?? 0);
                $credit = (float) ($line['credit'] ?? 0);

                if ($debit > 0 && $credit > 0) {
                    $validator->errors()->add("lines.{$index}.debit", 'Baris tidak boleh memiliki debit dan kredit sekaligus.');
                }

                if ($debit == 0 && $credit == 0) {
                    $validator->errors()->add("lines.{$index}.debit", 'Baris harus memiliki debit atau kredit.');
                }

                $totalDebit += $debit;
                $totalCredit += $credit;
            }

            if (round($totalDebit, 2) !== round($totalCredit, 2)) {
                $validator->errors()->add('lines', 'Total debit dan kredit harus seimbang.');
            }

            if ($totalDebit <= 0) {
                $validator->errors()->add('lines', 'Total jurnal harus lebih dari nol.');
            }
        });
    }
}
