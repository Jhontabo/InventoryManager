<?php

namespace App\Http\Requests;

use App\Models\Loan;
use Illuminate\Foundation\Http\FormRequest;

class CreateLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'user_id' => ['required', 'exists:users,id'],
            'status' => ['nullable', 'in:'.implode(',', [
                Loan::STATUS_PENDING,
                Loan::STATUS_APPROVED,
                Loan::STATUS_REJECTED,
                Loan::STATUS_RETURNED,
                Loan::STATUS_OVERDUE,
            ])],
            'requested_at' => ['required', 'date'],
            'estimated_return_at' => ['nullable', 'date', 'after_or_equal:requested_at'],
            'observations' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
