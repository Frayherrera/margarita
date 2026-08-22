<?php

namespace App\Http\Requests;

use App\Models\CashMovement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CashRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(CashMovement::TYPES)],
            'concept' => ['required', 'string', 'max:150'],
            'amount' => ['required', 'numeric', 'min:1', 'max:99999999999'],
            'method' => ['required', Rule::in(CashMovement::METHODS)],
            'cash_category_id' => ['nullable', 'exists:cash_categories,id'],
            'occurred_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
