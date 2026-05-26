<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'unit_price_catalog',
    'unit_price_own',
    'unit_price_catalog_discount',
    'unit_price_own_discount',
    'qty_discount',
    'custom',
])]
class Price extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'unit_price_catalog' => 'decimal:2',
            'unit_price_own' => 'decimal:2',
            'unit_price_catalog_discount' => 'decimal:2',
            'unit_price_own_discount' => 'decimal:2',
            'qty_discount' => 'integer',
            'custom' => 'array',
        ];
    }
}
