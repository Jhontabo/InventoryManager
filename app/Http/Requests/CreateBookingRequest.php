<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'schedule_id' => ['required', 'exists:schedules,id'],
            'laboratory_id' => ['required', 'exists:laboratories,id'],
            'project_type' => ['required', 'string', 'max:255'],
            'academic_program' => ['required', 'string', 'max:255'],
            'semester' => ['required', 'integer', 'min:1', 'max:10'],
            'applicants' => ['required'],
            'research_name' => ['required', 'string', 'max:255'],
            'advisor' => ['required'],
            'products' => ['required', 'array', 'min:1'],
            'products.*' => ['integer', 'exists:products,id'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
        ];
    }
}
