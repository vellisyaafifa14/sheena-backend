<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactInfo extends Model
{
    protected $table = 'contact_infos';
    protected $primaryKey = 'id_contact';

    protected $fillable = [
        'contact_type',
        'contact_label',
        'contact_value',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
