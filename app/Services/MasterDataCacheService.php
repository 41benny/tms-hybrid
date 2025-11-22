<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Master\Vendor;
use App\Models\Master\Driver;
use App\Models\Master\Truck;
use App\Models\Master\Sales;
use App\Models\Master\Customer;
use App\Models\Master\Equipment;

class MasterDataCacheService
{
    /**
     * Cache duration in seconds (1 hour)
     */
    protected int $cacheDuration = 3600;

    /**
     * Get active vendors with caching
     */
    public function getActiveVendors()
    {
        return Cache::remember('master.vendors.active', $this->cacheDuration, function () {
            return Vendor::where('is_active', true)
                ->select('id', 'code', 'name')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get active drivers with caching
     */
    public function getActiveDrivers()
    {
        return Cache::remember('master.drivers.active', $this->cacheDuration, function () {
            return Driver::where('is_active', true)
                ->select('id', 'code', 'name', 'phone')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get active trucks with driver with caching
     */
    public function getActiveTrucks()
    {
        return Cache::remember('master.trucks.active', $this->cacheDuration, function () {
            return Truck::with(['driver:id,name,code'])
                ->where('is_active', true)
                ->select('id', 'plate_number', 'truck_type', 'driver_id')
                ->orderBy('plate_number')
                ->get();
        });
    }

    /**
     * Get active sales with caching
     */
    public function getActiveSales()
    {
        return Cache::remember('master.sales.active', $this->cacheDuration, function () {
            return Sales::where('is_active', true)
                ->select('id', 'code', 'name', 'email')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get customers with caching
     */
    public function getCustomers()
    {
        return Cache::remember('master.customers', $this->cacheDuration, function () {
            return Customer::select('id', 'code', 'name', 'email', 'phone')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get equipments with caching
     */
    public function getEquipments()
    {
        return Cache::remember('master.equipments', $this->cacheDuration, function () {
            return Equipment::select('id', 'code', 'name', 'category')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Clear all master data cache
     */
    public function clearAllCache(): void
    {
        $keys = [
            'master.vendors.active',
            'master.drivers.active',
            'master.trucks.active',
            'master.sales.active',
            'master.customers',
            'master.equipments',
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Clear specific cache
     */
    public function clearCache(string $type): void
    {
        Cache::forget("master.{$type}");
    }
}
