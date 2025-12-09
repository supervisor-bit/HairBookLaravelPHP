<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitRetailItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_id',
        'product_id',
        'quantity_units',
        'unit_price',
    ];

    protected $casts = [
        'quantity_units' => 'float',
        'unit_price' => 'float',
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
