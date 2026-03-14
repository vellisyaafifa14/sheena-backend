<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
     protected $table = 'categories';
    protected $primaryKey = 'id_category';

    protected $fillable = [
        'category_name',
        'category_slug'
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class,'id_category','id_category');
    }
}
