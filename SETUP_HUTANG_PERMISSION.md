# Quick Guide: Setup Permission Dashboard Hutang vs Pengajuan Pembayaran

## Masalah yang Diselesaikan
✅ Sales bisa ajukan pembayaran TANPA akses dashboard hutang  
✅ Finance/Admin bisa akses dashboard hutang penuh  
✅ Dashboard hutang aman dari akses tidak berwenang  

## Cara Kerja

### 1. Dashboard Hutang (Protected)
- URL: `/hutang`
- Hanya bisa diakses user dengan permission menu `hutang`
- Berisi data sensitif: semua hutang vendor, pending bills, dll

### 2. Pengajuan Pembayaran (Accessible)
- URL: `/payment-requests`
- Bisa diakses user dengan permission menu `payment-requests`
- Sales/Admin bisa ajukan pembayaran tanpa lihat dashboard hutang

## Setup User Permission (Run Seeder)

```bash
# Jalankan seeder untuk create contoh user
php artisan db:seed --class=UserSeeder
```

Seeder akan create:
- **Super Admin** (`superadmin@tms.local`) - Akses semua
- **Finance** (`finance@tms.local`) - Akses hutang dashboard + payment requests
- **Sales** (`sales@tms.local`) - Hanya akses payment requests (TIDAK bisa akses hutang)

Password semua user: `password`

## Assign Permission Manual (Via Database)

```sql
-- Berikan akses payment-requests ke user Sales (user_id = 5)
INSERT INTO menu_user (menu_id, user_id, created_at, updated_at)
SELECT id, 5, NOW(), NOW() 
FROM menus 
WHERE slug = 'payment-requests';

-- Berikan akses hutang dashboard ke user Finance (user_id = 3)
INSERT INTO menu_user (menu_id, user_id, created_at, updated_at)
SELECT id, 3, NOW(), NOW() 
FROM menus 
WHERE slug IN ('hutang', 'payment-requests');
```

## Testing

### Test sebagai Sales
1. Login: `sales@tms.local` / `password`
2. ✅ Bisa akses: Menu "Pengajuan Pembayaran"
3. ✅ Bisa ajukan pembayaran baru
4. ❌ Menu "Dashboard Hutang" tidak muncul di sidebar
5. ❌ Akses langsung ke `/hutang` → Error 403

### Test sebagai Finance
1. Login: `finance@tms.local` / `password`
2. ✅ Bisa akses: Menu "Dashboard Hutang"
3. ✅ Bisa akses: Menu "Pengajuan Pembayaran"
4. ✅ Bisa approve/reject payment requests

## File yang Dibuat/Diubah

1. ✅ `app/Http/Middleware/EnsureUserCanAccessMenu.php` - Middleware baru
2. ✅ `bootstrap/app.php` - Register middleware
3. ✅ `routes/web.php` - Tambah middleware ke route hutang
4. ✅ `database/seeders/UserSeeder.php` - Contoh setup permission
5. ✅ `DOCS_HUTANG_PERMISSION.md` - Dokumentasi lengkap

## Troubleshooting

**Q: User tidak bisa akses dashboard hutang**  
A: Pastikan user punya menu permission 'hutang' di tabel `menu_user`

**Q: Sales bisa lihat menu dashboard hutang di sidebar**  
A: Menu akan muncul tapi dengan opacity 60% dan tidak clickable. Jika di-klik akan error 403.

**Q: Ingin ubah permission user tertentu**  
A: Edit di `database/seeders/UserSeeder.php` lalu run `php artisan db:seed --class=UserSeeder`
