# Quick Commands - Vendor Master Data

## 🚀 Import Commands

### Import menggunakan Seeder
```bash
php artisan db:seed --class=VendorMasterDataSeeder
```

### Import menggunakan standalone script
```bash
php import_vendors.php
```

## 📊 Query Commands

### Total vendor
```bash
php artisan tinker --execute="echo App\Models\Master\Vendor::count();"
```

### Vendor dengan alamat
```bash
php artisan tinker --execute="echo App\Models\Master\Vendor::whereNotNull('address')->count();"
```

### Vendor tanpa alamat
```bash
php artisan tinker --execute="echo App\Models\Master\Vendor::whereNull('address')->count();"
```

### List 10 vendor terbaru
```bash
php artisan tinker --execute="print_r(App\Models\Master\Vendor::latest()->take(10)->pluck('name')->toArray());"
```

### List vendor tanpa alamat
```bash
php artisan tinker --execute="print_r(App\Models\Master\Vendor::whereNull('address')->orWhere('address', '')->pluck('name')->toArray());"
```

### Cari vendor by name
```bash
php artisan tinker --execute="print_r(App\Models\Master\Vendor::where('name', 'like', '%HEXINDO%')->get()->toArray());"
```

## 🔄 Update Commands

### Update vendor type untuk semua vendor
```bash
php artisan tinker --execute="App\Models\Master\Vendor::whereNull('vendor_type')->update(['vendor_type' => 'subcon']);"
```

### Set semua vendor aktif
```bash
php artisan tinker --execute="App\Models\Master\Vendor::update(['is_active' => true]);"
```

## 🗑️ Delete Commands (Hati-hati!)

### Delete vendor tanpa alamat
```bash
php artisan tinker --execute="App\Models\Master\Vendor::whereNull('address')->delete();"
```

### Delete semua vendor (DANGER!)
```bash
php artisan tinker --execute="App\Models\Master\Vendor::truncate();"
```

## 📁 File Locations

- Seeder: `database/seeders/VendorMasterDataSeeder.php`
- Script: `import_vendors.php`
- Source Data: `Mastervendor.txt`
- Model: `app/Models/Master/Vendor.php`
