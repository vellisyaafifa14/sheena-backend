<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Channel extends Model
{
    protected $table = 'channels';
    protected $primaryKey = 'id_channel';

    protected $fillable = [
        'channel_name',
    ];

    public function channelConnections(): HasMany
    {
        return $this->hasMany(ChannelConnection::class, 'id_channel', 'id_channel');
    }
}