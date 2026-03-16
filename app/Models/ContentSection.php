<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentSection extends Model
{
    protected $table = 'content_sections';
    protected $primaryKey = 'id_section';

    protected $fillable = [
        'id_page',
        'section_name',
        'section_key',
        'section_content',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function sitePage(): BelongsTo
    {
        return $this->belongsTo(SitePage::class, 'id_page', 'id_page');
    }
}
