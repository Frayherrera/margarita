<?php

namespace App\Http\Requests;

use App\Models\InvProduct;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:150'],
            'inv_category_id' => ['nullable', 'exists:inv_categories,id'],
            'unit' => ['required', 'string', Rule::in(InvProduct::UNITS)],
            'min_stock' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'purchase_price' => ['nullable', 'numeric', 'min:0', 'max:99999999999'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'max:99999999999'],
            'active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        if ($this->isMethod('POST')) {
            $rules['initial_stock'] = ['nullable', 'numeric', 'min:0', 'max:999999999'];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'active' => $this->boolean('active'),
        ]);
    }
}
