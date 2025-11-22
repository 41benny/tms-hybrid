<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name', 'contact_person', 'address', 'phone', 'email', 'npwp', 'payment_term',
    ];

    public function jobOrders(): HasMany
    {
        return $this->hasMany(\App\Models\Operations\JobOrder::class);
    }
}
