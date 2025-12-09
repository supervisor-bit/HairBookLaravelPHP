<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'note',
    ];

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class)->latest('occurred_at');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ClientNote::class)->latest();
    }
}
