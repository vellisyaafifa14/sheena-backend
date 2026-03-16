<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'id_order';

    protected $fillable = [
        'id_connection',
        'channel_order_id',
        'total_amount',
        'order_status',
        'ordered_at_channel',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'ordered_at_channel' => 'datetime',
    ];

    public function channelConnection(): BelongsTo
    {
        return $this->belongsTo(ChannelConnection::class, 'id_connection', 'id_connection');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'id_order', 'id_order');
    }
}
