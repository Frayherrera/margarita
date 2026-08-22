<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashMovement extends Model
{
    public const TYPES = ['ingreso', 'egreso'];

    public const METHODS = ['efectivo', 'nequi', 'daviplata', 'transferencia', 'datafono'];

    protected $fillable = [
        'cash_category_id',
        'type',
        'concept',
        'amount',
        'method',
        'occurred_at',
        'notes',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'occurred_at' => 'date',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CashCategory::class, 'cash_category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
