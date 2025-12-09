<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisitService extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_id',
        'title',
        'note',
        'position',
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(VisitServiceProduct::class);
    }
}
