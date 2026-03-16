<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteBanner extends Model
{
    protected $table = 'website_banners';
    protected $primaryKey = 'id_banner';

    protected $fillable = [
        'banner_title',
        'banner_image',
        'banner_link',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];
}
