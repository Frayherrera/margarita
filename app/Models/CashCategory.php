<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashCategory extends Model
{
    public const TYPES = ['ingreso', 'egreso'];

    protected $fillable = ['name', 'type'];

    public function movements(): HasMany
    {
        return $this->hasMany(CashMovement::class);
    }
}
