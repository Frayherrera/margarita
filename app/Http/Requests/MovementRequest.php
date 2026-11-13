<?php

namespace App\Http\Requests;

use App\Models\CashMovement;
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

    protected function prepareForValidation(): void
    {
        // Ignora filas vacías que el usuario agregó y no llenó.
        $items = collect($this->input('items', []))
            ->filter(fn ($item) => filled($item['inv_product_id'] ?? null) || filled($item['quantity'] ?? null))
            ->values()
            ->all();

        $this->merge(['items' => $items]);
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(InvMovement::TYPES)],
            'reason' => ['required', Rule::in(InvMovement::REASONS)],
            'method' => ['nullable', Rule::in(CashMovement::METHODS)],
            'moved_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.inv_product_id' => ['required', 'distinct', 'exists:inv_products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01', 'max:999999999'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0', 'max:99999999999'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Agrega al menos un producto.',
            'items.*.inv_product_id.required' => 'Selecciona el producto.',
            'items.*.inv_product_id.distinct' => 'Este producto está repetido; suma las cantidades en una sola fila.',
            'items.*.quantity.required' => 'Indica la cantidad.',
            'items.*.quantity.min' => 'La cantidad debe ser mayor a 0.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $products = InvProduct::whereIn('id', collect($this->items)->pluck('inv_product_id'))->get()->keyBy('id');

            foreach ($this->items as $i => $item) {
                $product = $products[$item['inv_product_id']] ?? null;

                if (! $product) {
                    continue;
                }

                if ($this->type === 'salida' && (float) $item['quantity'] > (float) $product->current_stock) {
                    $validator->errors()->add(
                        "items.$i.quantity",
                        "Stock insuficiente: solo hay {$product->current_stock} {$product->unit} de «{$product->name}»."
                    );
                }

                if ($this->type === 'salida' && $this->reason === 'venta') {
                    $unitPrice = (float) ($item['unit_cost'] ?? $product->sale_price ?? 0);

                    if ($unitPrice <= 0) {
                        $validator->errors()->add(
                            "items.$i.unit_cost",
                            "Indica el precio de venta de «{$product->name}» para registrar el ingreso en caja."
                        );
                    }
                }

                if ($this->type === 'entrada' && $this->reason === 'compra') {
                    $unitCost = (float) ($item['unit_cost'] ?? $product->purchase_price ?? 0);

                    if ($unitCost <= 0) {
                        $validator->errors()->add(
                            "items.$i.unit_cost",
                            "Indica el costo de compra de «{$product->name}» para registrar el egreso en caja."
                        );
                    }
                }
            }
        });
    }
}
