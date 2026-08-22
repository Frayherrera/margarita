<?php

namespace App\Http\Requests;

use App\Models\InvMovement;
use App\Models\InvProduct;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'inv_product_id' => ['required', 'exists:inv_products,id'],
            'type' => ['required', Rule::in(InvMovement::TYPES)],
            'reason' => ['required', Rule::in(InvMovement::REASONS)],
            'quantity' => ['required', 'numeric', 'min:0.01', 'max:999999999'],
            'unit_cost' => ['nullable', 'numeric', 'min:0', 'max:99999999999'],
            'moved_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $product = InvProduct::find($this->inv_product_id);

            if (! $product) {
                return;
            }

            if ($this->type === 'salida' && (float) $this->quantity > (float) $product->current_stock) {
                $validator->errors()->add(
                    'quantity',
                    "Stock insuficiente: solo hay {$product->current_stock} {$product->unit} de «{$product->name}»."
                );
            }
        });
    }
}
