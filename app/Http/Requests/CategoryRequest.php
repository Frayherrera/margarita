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
        $id = $this->route('categoria')?->id;

        return [
            'name' => ['required', 'string', 'max:80', Rule::unique('inv_categories', 'name')->ignore($id)],
        ];
    }
}
