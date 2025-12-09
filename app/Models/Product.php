<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_group_id',
        'name',
        'sku',
        'usage_type',
        'package_size_grams',
        'stock_units',
        'min_units',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'package_size_grams' => 'float',
        'stock_units' => 'float',
        'min_units' => 'float',
        'is_active' => 'bool',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(ProductGroup::class, 'product_group_id');
    }

    public function productGroup(): BelongsTo
    {
        return $this->belongsTo(ProductGroup::class, 'product_group_id');
    }

    public function serviceLines(): HasMany
    {
        return $this->hasMany(VisitServiceProduct::class);
    }

    public function retailLines(): HasMany
    {
        return $this->hasMany(VisitRetailItem::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }
}
