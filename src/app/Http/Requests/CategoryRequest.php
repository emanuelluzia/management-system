<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'parent_id'  => ['nullable', 'integer', 'exists:categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'Name is required.',
            'name.max'           => 'Name must be at most 255 characters.',
            'parent_id.integer'  => 'Parent category must be a valid ID.',
            'parent_id.exists'   => 'Selected parent category is invalid.',
        ];
    }
}
