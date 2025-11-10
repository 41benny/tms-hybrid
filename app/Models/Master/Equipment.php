<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $table = 'equipments';

    protected $fillable = [
        'name', 'category', 'brand', 'model', 'serial_number', 'capacity', 'description',
    ];
}
