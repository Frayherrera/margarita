<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvProduct extends Model
{
    public const UNITS = ['unidad', 'botella', 'litro', 'caja', 'kg', 'paquete'];

    protected $fillable = [
        'inv_category_id',
        'name',
        'unit',
        'min_stock',
        'purchase_price',
        'sale_price',
        'active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'current_stock' => 'decimal:2',
            'min_stock' => 'decimal:2',
            'purchase_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InvCategory::class, 'inv_category_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InvMovement::class, 'inv_product_id');
    }

    public function isLowStock(): bool
    {
        return $this->active && $this->min_stock > 0 && (float) $this->current_stock <= (float) $this->min_stock;
    }

    public function inventoryValue(): float
    {
        return (float) $this->current_stock * (float) ($this->purchase_price ?? 0);
    }
}
