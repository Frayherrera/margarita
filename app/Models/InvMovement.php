<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvMovement extends Model
{
    public const TYPES = ['entrada', 'salida'];

    public const REASONS = ['compra', 'venta', 'merma', 'ajuste'];

    protected $fillable = [
        'inv_product_id',
        'type',
        'reason',
        'quantity',
        'unit_cost',
        'moved_at',
        'notes',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'moved_at' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(InvProduct::class, 'inv_product_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
