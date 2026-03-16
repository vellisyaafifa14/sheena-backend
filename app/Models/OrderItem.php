<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $table = 'order_items';
    protected $primaryKey = 'id_order_item';

    protected $fillable = [
        'id_order',
        'id_listing',
        'price_snapshot',
        'title_snapshot',
        'quantity',
    ];

    protected $casts = [
        'price_snapshot' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'id_order', 'id_order');
    }

    public function productListing(): BelongsTo
    {
        return $this->belongsTo(ProductListing::class, 'id_listing', 'id_listing');
    }
}
