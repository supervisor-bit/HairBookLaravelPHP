<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'occurred_at',
        'status',
        'total_price',
        'retail_price',
        'note',
        'closed_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'closed_at' => 'datetime',
        'total_price' => 'float',
        'retail_price' => 'float',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(VisitService::class)->orderBy('position');
    }

    public function retailItems(): HasMany
    {
        return $this->hasMany(VisitRetailItem::class);
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }
}
