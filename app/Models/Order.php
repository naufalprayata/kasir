<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'customer_name',
        'gross_amount',
        'snap_token',
        'user_id',
        'total',
        'status',
        'items',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'float',
            'gross_amount' => 'integer',
            'items' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
