<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['required', Rule::in(['pending','in_progress','completed'])],
            'priority'    => ['required', Rule::in(['low','medium','high'])],
            'due_date'    => ['nullable', 'date'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'    => 'Title is required.',
            'title.max'         => 'Title must be at most 255 characters.',
            'status.required'   => 'Status is required.',
            'status.in'         => 'Invalid status value.',
            'priority.required' => 'Priority is required.',
            'priority.in'       => 'Invalid priority value.',
            'due_date.date'     => 'Due date must be a valid date.',
            'category_id.exists'=> 'Selected category is invalid.',
        ];
    }
}
