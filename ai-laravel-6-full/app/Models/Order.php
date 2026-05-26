<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'status',
    'customer_id',
    'date',
    'total_price',
    'notes',
    'reason_for_cancellation',
    'nif',
    'address',
    'payment_type',
    'payment_ref',
    'receipt_url',
    'custom',
])]
class Order extends Model
{
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total_price' => 'decimal:2',
            'custom' => 'array',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->items();
    }
}
