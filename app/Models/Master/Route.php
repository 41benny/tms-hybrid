<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    protected $fillable = [
        'origin', 'destination', 'distance_km', 'description',
    ];
}
