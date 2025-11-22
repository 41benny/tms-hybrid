# Database Query Optimization Guide

## Controllers yang Perlu Dioptimasi

Berikut adalah area yang perlu ditingkatkan:

### 1. Caching untuk Data Master
Data seperti vendors, drivers, trucks yang sering digunakan sebaiknya di-cache:

```php
// Contoh implementasi di Controller
use Illuminate\Support\Facades\Cache;

$vendors = Cache::remember('active_vendors', 3600, function () {
    return Vendor::where('is_active', true)->orderBy('name')->get();
});
```

### 2. Select Specific Columns
Hindari SELECT * jika tidak perlu semua kolom:

```php
// Sebelum
$vendors = Vendor::where('is_active', true)->orderBy('name')->get();

// Sesudah
$vendors = Vendor::where('is_active', true)
    ->select('id', 'name', 'code')
    ->orderBy('name')
    ->get();
```

### 3. Eager Loading
Sudah baik di ShipmentLegController (line 21):
```php
$trucks = Truck::with('driver')->where('is_active', true)->orderBy('plate_number')->get();
```

### 4. Index Database
Pastikan kolom yang sering di-query memiliki index:
- `is_active` pada tabel vendors, drivers, trucks
- `name` untuk sorting
- Foreign keys

### 5. Query Optimization Tips
- Gunakan `paginate()` untuk list panjang ✓ (sudah diimplementasikan)
- Hindari N+1 problem dengan eager loading
- Cache hasil query yang jarang berubah
- Gunakan database indexing

## Area yang Sudah Optimal
✓ Pagination sudah digunakan di JobOrderController dan PartPurchaseController
✓ Eager loading untuk relasi truck->driver
✓ Query filtering dengan where clauses

## Rekomendasi Implementasi

### Priority 1: Caching Master Data
Buat helper atau service untuk cache master data yang sering digunakan.

### Priority 2: Add Database Indexes
```sql
-- Contoh indexes yang disarankan
CREATE INDEX idx_vendors_active ON vendors(is_active);
CREATE INDEX idx_drivers_active ON drivers(is_active);
CREATE INDEX idx_trucks_active ON trucks(is_active);
```

### Priority 3: Optimize Select Queries
Review setiap controller dan tambahkan select() untuk kolom yang benar-benar digunakan.
