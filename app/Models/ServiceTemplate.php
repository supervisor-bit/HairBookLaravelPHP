<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceTemplate extends Model
{
    protected $fillable = [
        'name',
        'note',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];
}
