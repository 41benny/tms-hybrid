# 📋 Panduan Role & Permission - TMS Hybrid

## Role yang Tersedia

| Role | Kode | Deskripsi | Use Case |
|------|------|-----------|----------|
| **Super Admin** | `super_admin` | Akses penuh tanpa batasan | Owner, Manager Puncak |
| **Admin** | `admin` | Operasional penuh kecuali Approval | Manager Operasional, Finance Staff |
| **Sales** | `sales` | Fokus penjualan & job order | Tim Sales |
| **Accounting** | `accounting` | Fokus akuntansi & laporan | Akuntan, Bookkeeper |

---

## Cara Kerja Sistem

### 1️⃣ **Menu Access** (Sidebar)
- **Lokasi:** Tabel `menu_user` (pivot table)
- **Fungsi:** Mengontrol menu apa yang muncul di sidebar
- **Setting:** Via seeder `database/seeders/UserSeeder.php` atau manual via tinker

**Contoh:**
```php
// Berikan akses menu "invoices" ke user ID 5
$user = User::find(5);
$invoiceMenu = Menu::where('slug', 'invoices')->first();
$user->menus()->attach($invoiceMenu->id);
```

### 2️⃣ **Permission** (Aksi di Menu)
- **Lokasi:** `config/permissions.php`
- **Fungsi:** Mengontrol AKSI apa yang bisa dilakukan (view, create, update, delete, approve, dll)
- **Check:** Via `$user->hasPermission('invoices.approve')`

---

## Default Permission per Role

### 🔴 **Super Admin**
```
✅ SEMUA PERMISSION (bypass check)
✅ SEMUA MENU
```

### 🟠 **Admin**
**Invoices:**
- ✅ `invoices.view` - Lihat invoice
- ✅ `invoices.create` - Buat invoice
- ✅ `invoices.update` - Edit invoice
- ✅ `invoices.submit` - Submit untuk approval
- ✅ `invoices.manage_status` - Post/Mark as sent/paid
- ✅ `invoices.cancel` - Batalkan invoice
- ❌ `invoices.approve` - **TIDAK BISA approve/reject**

**Menu Default:**
- Dashboard, Finance Dashboard, Hutang Dashboard, Payment Requests, Invoices, Cash Banks, Journals, COA

### 🟢 **Sales**
**Invoices:**
- ✅ `invoices.view` - Lihat invoice
- ✅ `invoices.create` - Buat invoice
- ✅ `invoices.submit` - Submit untuk approval
- ❌ Update, Approve, Manage Status

**Menu Default:**
- Dashboard, Customers, Job Orders, Payment Requests, Invoices

### 🔵 **Accounting**
**Operasional (Read-Only):**
- ✅ View: Dashboard, Customers, Vendors, Trucks, Drivers, Job Orders, Invoices, Cash Banks

**Accounting (Full Access):**
- ✅ Journals: Create, Update, Delete
- ✅ COA: Create, Update, Delete
- ✅ Reports: General Ledger, Trial Balance, P&L, Balance Sheet
- ✅ Periods: View, Manage (Open/Close)

---

## Cara Assign Menu & Permission

### Opsi 1: Via Seeder (Recommended untuk Setup Awal)
Edit `database/seeders/UserSeeder.php`:

```php
// Contoh: Tambah menu "vendor-bills" untuk finance user
$financeMenus = Menu::query()
    ->whereIn('slug', [
        'dashboard',
        'finance.dashboard',
        'hutang',
        'invoices',
        'vendor-bills', // ← Tambah ini
        'cash-banks',
    ])
    ->pluck('id')
    ->all();

$finance->menus()->sync($financeMenus);
```

Jalankan:
```bash
php artisan db:seed --class=UserSeeder
```

### Opsi 2: Via Tinker (Manual untuk User Spesifik)
```bash
php artisan tinker
```

**Cek menu user:**
```php
$user = User::find(1);
$user->menus->pluck('label', 'slug');
```

**Tambah menu:**
```php
$user = User::find(5);
$menu = Menu::where('slug', 'invoices')->first();
$user->menus()->attach($menu->id);
```

**Hapus menu:**
```php
$user->menus()->detach($menu->id);
```

**Sync semua menu (replace):**
```php
$menuIds = Menu::whereIn('slug', ['dashboard', 'invoices', 'job-orders'])->pluck('id');
$user->menus()->sync($menuIds);
```

### Opsi 3: Custom Permission per User
Edit custom permission di tabel `users`, kolom `permissions` (JSON):

```php
$user = User::find(5);
$user->update([
    'permissions' => [
        'invoices.view',
        'invoices.create',
        'invoices.update',
        'invoices.approve', // ← Custom: Sales ini bisa approve
    ]
]);
```

**Catatan:** Custom permission akan **OVERRIDE** default role permission!

---

## Best Practices

### ✅ **DO:**
1. **Gunakan role default** untuk kebanyakan user
2. **Super Admin hanya untuk owner/top management** (dibatasi jumlahnya)
3. **Custom permission** hanya untuk kasus khusus (misal: 1 Sales tertentu boleh approve)
4. **Dokumentasikan** setiap custom permission yang diberikan

### ❌ **DON'T:**
1. Jangan buat terlalu banyak Super Admin (security risk)
2. Jangan beri permission approval ke semua Sales (segregation of duties)
3. Jangan lupa assign menu setelah buat user baru (kalau bukan Super Admin)

---

## Troubleshooting

### User tidak bisa akses halaman tertentu?

**1. Cek Role:**
```php
$user = User::find(ID);
echo $user->role; // super_admin, admin, sales, accounting?
```

**2. Cek Menu Access:**
```php
$user->menus->pluck('slug')->toArray();
// Apakah menu yang dibutuhkan ada di list?
```

**3. Cek Permission:**
```php
$user->permissions();
// List semua permission yang dimiliki
```

**4. Test Permission Specific:**
```php
$user->hasPermission('invoices.approve');
// true = boleh, false = tidak boleh
```

### Error 403 - Akses Ditolak?

**Pesan error sekarang include debug info:**
```
Anda tidak memiliki akses ke halaman ini. 
(Role: admin, ID: 1, Menu: invoices)
```

**Solusi:**
- Cek apakah user punya menu access (`invoices`)
- Cek apakah role/custom permission sesuai
- Super Admin selalu bypass

---

## Quick Reference Commands

```bash
# Cek semua user & role
php artisan tinker --execute="dump(App\Models\User::select('id','name','email','role')->get()->toArray())"

# Cek menu user tertentu
php artisan tinker --execute="\$u=App\Models\User::find(1); dump(\$u->menus->pluck('slug')->toArray())"

# Cek permission user
php artisan tinker --execute="\$u=App\Models\User::find(1); dump(\$u->permissions())"

# Update role user
php artisan tinker --execute="App\Models\User::find(1)->update(['role'=>'super_admin'])"

# Berikan akses semua menu ke Super Admin
php artisan tinker --execute="\$sa=App\Models\User::where('role','super_admin')->get(); foreach(\$sa as \$u){ \$u->menus()->sync(App\Models\Menu::pluck('id')); }"
```

---

## Kapan Butuh Custom Permission?

**Scenario 1:** Sales tertentu dipromosikan jadi Team Lead, boleh approve invoice
```php
User::find(5)->update([
    'permissions' => array_merge(
        config('permissions.role_permissions.sales'),
        ['invoices.approve']
    )
]);
```

**Scenario 2:** Accounting tertentu boleh buat Payment Request (biasanya tidak boleh)
```php
User::find(7)->update([
    'permissions' => array_merge(
        config('permissions.role_permissions.accounting'),
        ['payment_requests.create']
    )
]);
```

**Scenario 3:** Admin tertentu di-restrict, tidak boleh cancel invoice
```php
$currentPerms = config('permissions.role_permissions.admin');
$restricted = array_diff($currentPerms, ['invoices.cancel']);

User::find(4)->update(['permissions' => $restricted]);
```

---

## Kesimpulan

1. **Super Admin** = God Mode (hati-hati siapa yang diberi)
2. **Admin** = Operasional penuh tapi tidak bisa approve invoice
3. **Sales** = Buat invoice & job order, submit approval
4. **Accounting** = Read operasional, full access accounting

**Untuk 90% kasus, gunakan role default saja. Custom permission hanya untuk exception.**
