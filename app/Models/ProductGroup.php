<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'accent_color',
        'display_order',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class)->orderBy('name');
    }
}
