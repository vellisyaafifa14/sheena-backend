<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SitePage extends Model
{
    protected $table = 'site_pages';
    protected $primaryKey = 'id_page';

    protected $fillable = [
        'page_name', 
        'page_slug', 
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function contentSections(): HasMany
    {
        return $this->hasMany(ContentSection::class, 'id_page', 'id_page');
    }
}
