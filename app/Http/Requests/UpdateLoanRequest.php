<?php

namespace App\Http\Requests;

use App\Models\Loan;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'in:'.implode(',', [
                Loan::STATUS_PENDING,
                Loan::STATUS_APPROVED,
                Loan::STATUS_REJECTED,
                Loan::STATUS_RETURNED,
                Loan::STATUS_OVERDUE,
            ])],
            'approved_at' => ['nullable', 'date'],
            'estimated_return_at' => ['nullable', 'date'],
            'actual_return_at' => ['nullable', 'date'],
            'observations' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
