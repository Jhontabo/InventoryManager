<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'available_quantity' => ['required', 'integer', 'min:0'],
            'laboratory_id' => ['required', 'exists:laboratories,id'],
            'status' => ['required', 'in:new,used,damaged,decommissioned,lost,maintenance'],
            'available_for_loan' => ['required', 'boolean'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
