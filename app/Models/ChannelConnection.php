<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChannelConnection extends Model
{
    protected $table = 'channel_connections';
    protected $primaryKey = 'id_connection';

    protected $fillable = [
        'id_channel',
        'shop_id',
        'shop_name',
        'access_token',
        'refresh_token',
        'status_connection',
        'last_product_synced_at',
        'last_order_synced_at',
    ];

    protected $casts = [
        'last_product_synced_at' => 'datetime',
        'last_order_synced_at' => 'datetime',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class, 'id_channel', 'id_channel');
    }

    public function productListings(): HasMany
    {
        return $this->hasMany(ProductListing::class, 'id_connection', 'id_connection');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'id_connection', 'id_connection');
    }
}