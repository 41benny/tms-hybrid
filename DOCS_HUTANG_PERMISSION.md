# Dokumentasi: Akses Dashboard Hutang vs Pengajuan Pembayaran

## Masalah
- Dashboard Hutang mengandung data sensitif (semua hutang vendor)
- Tidak semua user boleh lihat dashboard hutang
- Tapi Sales/Admin tetap perlu bisa ajukan pembayaran

## Solusi
Sistem menggunakan **menu-based permission** yang terpisah:

### 1. Menu `hutang` (Dashboard Hutang)
- **Route:** `/hutang` → `hutang.dashboard`
- **Akses:** Hanya user dengan permission menu `hutang`
- **Isi:** Overview semua hutang vendor, pending bills, dll
- **Protected by:** Middleware `can:access-menu,hutang`

### 2. Menu `payment-requests` (Pengajuan Pembayaran)  
- **Route:** `/payment-requests` → `payment-requests.*`
- **Akses:** User dengan permission menu `payment-requests`
- **Isi:** Formulir ajukan pembayaran, list pengajuan
- **Bisa diakses:** Sales, Admin, Finance (siapa saja yang perlu ajukan pembayaran)

## Cara Setting Permission User

### A. Via Database (Manual)
```sql
-- 1. Lihat ID menu yang tersedia
SELECT id, slug, label FROM menus WHERE slug IN ('hutang', 'payment-requests');

-- 2. Berikan akses payment-requests ke user Sales (misal user_id = 5)
INSERT INTO menu_user (menu_id, user_id, created_at, updated_at)
VALUES 
  ((SELECT id FROM menus WHERE slug = 'payment-requests'), 5, NOW(), NOW());

-- 3. Berikan akses hutang dashboard ke user Finance (misal user_id = 3)
INSERT INTO menu_user (menu_id, user_id, created_at, updated_at)
VALUES 
  ((SELECT id FROM menus WHERE slug = 'hutang'), 3, NOW(), NOW());
```

### B. Via Seeder (Recommended)
Buat seeder atau update `UserSeeder` untuk auto-assign permission:

```php
// database/seeders/UserSeeder.php

public function run(): void
{
    // ... existing super admin setup ...

    // Contoh: Buat user Sales
    $sales = User::query()->firstOrCreate(
        ['email' => 'sales@tms.local'],
        [
            'name' => 'Sales User',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SALES,
            'is_active' => true,
        ]
    );

    // Berikan akses menu untuk Sales
    $salesMenus = Menu::query()
        ->whereIn('slug', [
            'dashboard',
            'customers',
            'job-orders',
            'payment-requests', // ← Sales bisa ajukan pembayaran
            'invoices',
        ])
        ->pluck('id')
        ->all();

    $sales->menus()->sync($salesMenus);

    // Contoh: Buat user Finance/Admin
    $finance = User::query()->firstOrCreate(
        ['email' => 'finance@tms.local'],
        [
            'name' => 'Finance User',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]
    );

    // Berikan akses menu untuk Finance (termasuk hutang dashboard)
    $financeMenus = Menu::query()
        ->whereIn('slug', [
            'dashboard',
            'hutang',              // ← Finance bisa akses dashboard hutang
            'payment-requests',
            'invoices',
            'cash-banks',
            'accounting.journals',
        ])
        ->pluck('id')
        ->all();

    $finance->menus()->sync($financeMenus);
}
```

### C. Via Admin UI (Future)
Nanti bisa ditambahkan di halaman `admin/users/edit` untuk assign menu permission via checkbox.

## Skenario Penggunaan

### Skenario 1: Sales mengajukan pembayaran
```
1. Sales login
2. Sales pergi ke menu "Pengajuan Pembayaran"
3. Sales klik "Ajukan Pembayaran" 
4. Isi form pembayaran manual atau pilih vendor bill
5. Submit → Finance dapat notifikasi
```

**Sales TIDAK bisa:**
- Akses Dashboard Hutang (akan dapat 403 Forbidden)
- Lihat overview semua hutang vendor
- Generate vendor bills dari shipment legs

### Skenario 2: Finance mengelola hutang
```
1. Finance login
2. Finance bisa akses "Dashboard Hutang"
3. Lihat semua pending vendor legs
4. Generate vendor bills
5. Approve/reject payment requests
6. Proses pembayaran
```

## Testing

### Test Permission Sales
```bash
# Login sebagai sales@tms.local

# ✅ Ini harus berhasil
GET /payment-requests
GET /payment-requests/create

# ❌ Ini harus gagal (403 Forbidden)
GET /hutang
```

### Test Permission Finance
```bash
# Login sebagai finance@tms.local

# ✅ Semua harus berhasil
GET /hutang
GET /payment-requests
GET /payment-requests/create
```

## Keuntungan Pendekatan Ini

1. **Separation of Concerns:** Dashboard hutang terpisah dari form pengajuan
2. **Flexible:** Bisa assign permission per-user, tidak harus per-role
3. **Secure:** Dashboard hutang protected, tidak semua orang bisa lihat
4. **User-friendly:** Sales tetap bisa ajukan pembayaran tanpa akses sensitif
5. **Scalable:** Mudah tambah menu permission baru di masa depan

## Middleware Flow

```
Request → /hutang
  ↓
Auth middleware (check login)
  ↓
Active middleware (check user active)
  ↓
can:access-menu,hutang middleware
  ↓
  ├─ Super Admin? → ✅ Allow
  ├─ User has menu 'hutang'? → ✅ Allow
  └─ Else → ❌ 403 Forbidden
```

## Catatan Penting

- **Super Admin** selalu bisa akses semua menu (bypass permission check)
- User bisa punya multiple menu permission
- Permission disimpan di tabel pivot `menu_user` (many-to-many)
- Middleware `can:access-menu` otomatis load user menus via relationship

## File yang Diubah

1. `routes/web.php` - Tambah middleware ke route hutang.dashboard
2. `app/Http/Middleware/EnsureUserCanAccessMenu.php` - Middleware baru
3. `bootstrap/app.php` - Register middleware alias
4. `app/Http/Controllers/Finance/HutangController.php` - Tambah documentation
5. `app/Http/Controllers/Finance/PaymentRequestController.php` - Tambah documentation
