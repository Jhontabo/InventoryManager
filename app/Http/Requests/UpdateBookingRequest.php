<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_type' => ['sometimes', 'string', 'max:255'],
            'academic_program' => ['sometimes', 'string', 'max:255'],
            'semester' => ['sometimes', 'integer', 'min:1', 'max:10'],
            'research_name' => ['sometimes', 'string', 'max:255'],
            'products' => ['sometimes', 'array'],
            'products.*' => ['integer', 'exists:products,id'],
            'status' => ['sometimes', 'in:pending,approved,reserved,rejected'],
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
