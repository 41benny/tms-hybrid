<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    protected $fillable = [
        'name', 'address', 'phone', 'email', 'npwp', 'vendor_type', 'pic_name', 'pic_phone', 'pic_email', 'is_active',
    ];

    public function trucks(): HasMany
    {
        return $this->hasMany(Truck::class);
    }

    public function drivers(): HasMany
    {
        return $this->hasMany(Driver::class);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(VendorBankAccount::class);
    }

    public function activeBankAccounts(): HasMany
    {
        return $this->hasMany(VendorBankAccount::class)->where('is_active', true);
    }

    public function primaryBankAccount()
    {
        return $this->hasOne(VendorBankAccount::class)->where('is_primary', true)->where('is_active', true);
    }
}
