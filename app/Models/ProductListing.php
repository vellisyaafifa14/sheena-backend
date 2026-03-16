<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductListing extends Model
{
    protected $table = 'product_listings';
    protected $primaryKey = 'id_listing';

    protected $fillable = [
        'id_product',
        'id_connection',
        'channel_product_id',
        'variant_id',
        'variant_name',
        'channel_sku',
        'price',
        'stock',
        'product_url',
        'listing_status',
    ];

     protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'id_product', 'id_product');
    }

    public function channelConnection(): BelongsTo
    {
        return $this->belongsTo(ChannelConnection::class, 'id_connection', 'id_connection');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'id_listing', 'id_listing');
    }
}
