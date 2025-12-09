<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitServiceProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_service_id',
        'product_id',
        'used_grams',
        'deducted_units',
    ];

    protected $casts = [
        'used_grams' => 'float',
        'deducted_units' => 'float',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(VisitService::class, 'visit_service_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
