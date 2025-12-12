# 📊 DAFTAR LENGKAP PERMISSION - TMS HYBRID

## ⚠️ PENTING: Hak Dasar Role SANGAT TERBATAS!

**Hak dasar role (di `config/permissions.php`) HANYA mencakup Invoice & Accounting saja!**  
**Untuk module lain (Job Order, Customer, Vendor, dll), TIDAK ADA permission default!**

Artinya:
- ✅ Super Admin tetap bisa akses semua (bypass check)
- ❌ Admin/Sales/Accounting **TIDAK otomatis** punya permission untuk Job Order, Customer, Vendor, dll
- ✅ Akses ditentukan dari **MENU ACCESS** (tabel `menu_user`), bukan permission

---

## 🎯 HAK DASAR PER ROLE (Default dari Config)

### 🔴 SUPER ADMIN (`super_admin`)
**Sistem:** Bypass semua check, otomatis dapat SEMUA permission

**Permission Invoice:**
```
invoices.view
invoices.create
invoices.update
invoices.submit
invoices.approve
invoices.manage_status
invoices.cancel
```

**CATATAN:** Super Admin tidak perlu permission list, langsung full access!

---

### 🟠 ADMIN (`admin`)
**Permission Invoice (7 items):**
```
✅ invoices.view           - Lihat invoice
✅ invoices.create         - Buat invoice baru
✅ invoices.update         - Edit invoice (draft)
✅ invoices.submit         - Submit untuk approval
✅ invoices.manage_status  - Post, mark as sent/paid
✅ invoices.cancel         - Batalkan invoice
❌ invoices.approve        - TIDAK BISA approve/reject (khusus Super Admin)
```

**Module Lain:** TIDAK ADA permission default! Akses tergantung Menu Access.

---

### 🟢 SALES (`sales`)
**Permission Invoice (3 items):**
```
✅ invoices.view    - Lihat invoice
✅ invoices.create  - Buat invoice baru
✅ invoices.submit  - Submit untuk approval
```

**Module Lain:** TIDAK ADA permission default! Akses tergantung Menu Access.

---

### 🔵 ACCOUNTING (`accounting`)
**Permission Operasional - Read Only (13 items):**
```
✅ dashboard.view
✅ customers.view
✅ vendors.view
✅ trucks.view
✅ drivers.view
✅ sales.view
✅ equipment.view
✅ job_orders.view
✅ shipment_legs.view
✅ hutang.view
✅ invoices.view
✅ payment_requests.view
✅ cash_banks.view
```

**Permission Accounting - Full Access (14 items):**
```
✅ accounting.journals.view
✅ accounting.journals.create
✅ accounting.journals.update
✅ accounting.journals.delete
✅ accounting.coa.view
✅ accounting.coa.create
✅ accounting.coa.update
✅ accounting.coa.delete
✅ accounting.general_ledger.view
✅ accounting.general_ledger.export
✅ accounting.periods.view
✅ accounting.periods.manage
✅ accounting.trial_balance.view
✅ accounting.trial_balance.export
✅ accounting.profit_loss.view
✅ accounting.profit_loss.export
✅ accounting.balance_sheet.view
✅ accounting.balance_sheet.export
```

**TOTAL:** 27 permissions

---

## 📋 DAFTAR SEMUA PERMISSION YANG TERSEDIA

Dari `config/permissions.php`, section `available_permissions`:

### 1️⃣ DASHBOARD
```
dashboard.view - Access Dashboard
```

### 2️⃣ CUSTOMERS
```
customers.view   - View Customer
customers.create - Create Customer
customers.update - Edit Customer
customers.delete - Delete Customer
```

### 3️⃣ VENDORS
```
vendors.view   - View Vendor
vendors.create - Create Vendor
vendors.update - Edit Vendor
vendors.delete - Delete Vendor
```

### 4️⃣ TRUCKS
```
trucks.view   - View Truck
trucks.create - Create Truck
trucks.update - Edit Truck
trucks.delete - Delete Truck
```

### 5️⃣ DRIVERS
```
drivers.view   - View Driver
drivers.create - Create Driver
drivers.update - Edit Driver
drivers.delete - Delete Driver
```

### 6️⃣ SALES
```
sales.view   - View Sales
sales.create - Create Sales
sales.update - Edit Sales
sales.delete - Delete Sales
```

### 7️⃣ EQUIPMENT
```
equipment.view   - View Equipment
equipment.create - Create Equipment
equipment.update - Edit Equipment
equipment.delete - Delete Equipment
```

### 8️⃣ JOB ORDERS
```
job_orders.view   - Lihat Job Order
job_orders.create - Tambah Job Order
job_orders.update - Edit Job Order
job_orders.delete - Hapus Job Order
job_orders.export - Export Job Order
```

### 9️⃣ SHIPMENT LEGS
```
shipment_legs.view   - Lihat Shipment Leg
shipment_legs.create - Tambah Shipment Leg
shipment_legs.update - Edit Shipment Leg
shipment_legs.delete - Hapus Shipment Leg
```

### 🔟 DASHBOARD HUTANG
```
hutang.view - View Payable Dashboard
```

### 1️⃣1️⃣ INVOICES
```
invoices.view          - View Invoice
invoices.create        - Create Invoice
invoices.update        - Edit Invoice
invoices.submit        - Submit for Approval
invoices.approve       - Approve / Reject
invoices.manage_status - Manage Status (post, mark as sent/paid)
invoices.cancel        - Cancel Invoice
```

### 1️⃣2️⃣ PAYMENT REQUESTS (Pengajuan Pembayaran)
```
payment_requests.view    - View Request
payment_requests.create  - Create Request
payment_requests.update  - Edit Request
payment_requests.approve - Approve Request
payment_requests.delete  - Delete Request
```

### 1️⃣3️⃣ CASH & BANK
```
cash_banks.view    - View Transaction
cash_banks.create  - Create Transaction
cash_banks.update  - Edit Transaction
cash_banks.approve - Approve Transaction
cash_banks.delete  - Void / Delete Transaction
```

### 1️⃣4️⃣ ACCOUNTING - JOURNALS (Jurnal Umum)
```
accounting.journals.view   - View Journal
accounting.journals.create - Create Manual Journal
accounting.journals.update - Edit Journal
accounting.journals.delete - Delete Journal
```

### 1️⃣5️⃣ ACCOUNTING - COA (Chart of Accounts)
```
accounting.coa.view   - View Account (COA)
accounting.coa.create - Create Account
accounting.coa.update - Edit Account
accounting.coa.delete - Delete Account
```

### 1️⃣6️⃣ ACCOUNTING - GENERAL LEDGER
```
accounting.general_ledger.view   - Access General Ledger
accounting.general_ledger.export - Export General Ledger
```

### 1️⃣7️⃣ ACCOUNTING - PERIODS
```
accounting.periods.view   - View Periods
accounting.periods.manage - Open/Close Periods
```

### 1️⃣8️⃣ ACCOUNTING - TRIAL BALANCE
```
accounting.trial_balance.view   - Access Trial Balance
accounting.trial_balance.export - Export Trial Balance
```

### 1️⃣9️⃣ ACCOUNTING - PROFIT & LOSS
```
accounting.profit_loss.view   - Access Profit & Loss
accounting.profit_loss.export - Export Profit & Loss
```

### 2️⃣0️⃣ ACCOUNTING - BALANCE SHEET
```
accounting.balance_sheet.view   - Access Balance Sheet
accounting.balance_sheet.export - Export Balance Sheet
```

### 2️⃣1️⃣ AI ASSISTANT
```
ai_assistant.view - Access AI Assistant
ai_assistant.chat - Chat with AI
```

### 2️⃣2️⃣ ADMIN - USER MANAGEMENT
```
admin.users.view               - View User
admin.users.create             - Create User
admin.users.update             - Edit User
admin.users.delete             - Delete User
admin.users.manage_permissions - Manage Permissions
```

---

## 🛠️ CARA BUAT CUSTOM PERMISSION DARI NOL

### Scenario: Buat user "Finance Manager" dengan permission custom

**Step 1: Buat User**
```php
$user = User::create([
    'name' => 'Finance Manager',
    'email' => 'finance.manager@company.com',
    'password' => Hash::make('password123'),
    'role' => 'admin', // Role dasar (tapi akan di-override dengan custom)
    'is_active' => true,
]);
```

**Step 2: Set Custom Permission (KOSONGKAN dulu)**
```php
$user->update(['permissions' => []]);
// User sekarang TIDAK PUNYA permission sama sekali!
```

**Step 3: Tambahkan Permission Satu Per Satu**
```php
// Finance Manager boleh:
// - Lihat semua operasional (customers, vendors, job orders, invoices)
// - Buat & approve payment requests
// - Full access cash & bank
// - Approve invoice

$permissions = [
    // Dashboard
    'dashboard.view',
    
    // Master Data - Read Only
    'customers.view',
    'vendors.view',
    'trucks.view',
    'drivers.view',
    
    // Job Orders - Read Only
    'job_orders.view',
    'shipment_legs.view',
    
    // Invoices - Full
    'invoices.view',
    'invoices.create',
    'invoices.update',
    'invoices.submit',
    'invoices.approve',      // ← Beda dari admin biasa
    'invoices.manage_status',
    'invoices.cancel',
    
    // Payment Requests - Full
    'payment_requests.view',
    'payment_requests.create',
    'payment_requests.approve',
    'payment_requests.delete',
    
    // Cash Banks - Full
    'cash_banks.view',
    'cash_banks.create',
    'cash_banks.approve',
    'cash_banks.delete',
    
    // Hutang Dashboard
    'hutang.view',
    
    // Accounting - View Only
    'accounting.journals.view',
    'accounting.coa.view',
    'accounting.general_ledger.view',
];

$user->update(['permissions' => $permissions]);
```

**Step 4: Assign Menu Access**
```php
$menuSlugs = [
    'dashboard',
    'customers',
    'vendors',
    'job-orders',
    'invoices',
    'payment-requests',
    'cash-banks',
    'hutang',
    'accounting.journals',
];

$menuIds = Menu::whereIn('slug', $menuSlugs)->pluck('id');
$user->menus()->sync($menuIds);
```

**SELESAI!** User Finance Manager sekarang punya permission custom yang sangat spesifik.

---

## 🎯 TEMPLATE CUSTOM PERMISSION BERDASARKAN POSISI

### 📌 Template 1: Operasional Manager
```php
$permissions = [
    'dashboard.view',
    
    // Master Data - Full
    'customers.view', 'customers.create', 'customers.update',
    'vendors.view', 'vendors.create', 'vendors.update',
    'trucks.view', 'trucks.create', 'trucks.update',
    'drivers.view', 'drivers.create', 'drivers.update',
    'equipment.view', 'equipment.create', 'equipment.update',
    
    // Job Orders - Full
    'job_orders.view', 'job_orders.create', 'job_orders.update', 'job_orders.delete',
    'shipment_legs.view', 'shipment_legs.create', 'shipment_legs.update', 'shipment_legs.delete',
    
    // Invoices - No Approve
    'invoices.view', 'invoices.create', 'invoices.update', 'invoices.submit',
    
    // Payment - No Approve
    'payment_requests.view', 'payment_requests.create',
    
    // Cash Bank - View Only
    'cash_banks.view',
];
```

### 📌 Template 2: Sales Team Leader
```php
$permissions = [
    'dashboard.view',
    
    // Master Data
    'customers.view', 'customers.create', 'customers.update',
    
    // Job Orders - Full
    'job_orders.view', 'job_orders.create', 'job_orders.update', 'job_orders.export',
    'shipment_legs.view', 'shipment_legs.create', 'shipment_legs.update',
    
    // Invoices - Bisa Approve
    'invoices.view', 'invoices.create', 'invoices.update', 'invoices.submit', 
    'invoices.approve', // ← Extra
    
    // Payment Request
    'payment_requests.view', 'payment_requests.create',
];
```

### 📌 Template 3: Finance Staff
```php
$permissions = [
    'dashboard.view',
    'hutang.view',
    
    // Operasional - Read Only
    'customers.view', 'vendors.view', 'job_orders.view',
    
    // Invoices - View & Submit
    'invoices.view', 'invoices.submit',
    
    // Payment - Full
    'payment_requests.view', 'payment_requests.create', 
    'payment_requests.approve', 'payment_requests.delete',
    
    // Cash Bank - Full
    'cash_banks.view', 'cash_banks.create', 'cash_banks.approve',
    
    // Accounting - View
    'accounting.journals.view', 'accounting.coa.view',
];
```

### 📌 Template 4: Accounting Staff
```php
$permissions = [
    'dashboard.view',
    
    // Operasional - Read Only (semua)
    'customers.view', 'vendors.view', 'trucks.view', 'drivers.view',
    'job_orders.view', 'shipment_legs.view', 
    'invoices.view', 'payment_requests.view', 'cash_banks.view',
    
    // Accounting - Full
    'accounting.journals.view', 'accounting.journals.create', 
    'accounting.journals.update', 'accounting.journals.delete',
    'accounting.coa.view', 'accounting.coa.create', 
    'accounting.coa.update', 'accounting.coa.delete',
    'accounting.general_ledger.view', 'accounting.general_ledger.export',
    'accounting.trial_balance.view', 'accounting.trial_balance.export',
    'accounting.profit_loss.view', 'accounting.profit_loss.export',
    'accounting.balance_sheet.view', 'accounting.balance_sheet.export',
    'accounting.periods.view', 'accounting.periods.manage',
];
```

---

## 🔧 QUICK COMMANDS

### Cek permission user tertentu
```bash
php artisan tinker --execute="dump(App\Models\User::find(1)->permissions())"
```

### Set custom permission kosong (reset)
```bash
php artisan tinker --execute="App\Models\User::find(5)->update(['permissions' => []])"
```

### Set custom permission dari array
```bash
php artisan tinker --execute="App\Models\User::find(5)->update(['permissions' => ['invoices.view', 'invoices.create', 'invoices.approve']])"
```

### Copy permission dari user lain
```bash
php artisan tinker --execute="\$source = App\Models\User::find(3); \$target = App\Models\User::find(5); \$target->update(['permissions' => \$source->permissions]);"
```

---

## ⚠️ CATATAN PENTING

### 1. Menu Access vs Permission
**Menu Access** (tabel `menu_user`):
- Kontrol VISIBILITAS menu di sidebar
- Jika tidak ada menu access, user tidak bisa buka URL sama sekali (403)

**Permission** (kolom `permissions`):
- Kontrol AKSI di dalam menu
- Misalnya: Bisa lihat invoice tapi tidak bisa approve

**KEDUANYA HARUS ADA!** Tidak cukup hanya menu atau hanya permission.

### 2. Custom Permission = Full Override
Begitu kolom `permissions` terisi (bukan NULL), sistem akan:
- ❌ IGNORE hak dasar role
- ✅ HANYA pakai yang ada di custom permission

Jadi harus lengkap!

### 3. Super Admin Exception
Super Admin selalu bypass, tidak peduli isi `permissions` kolom apa.

---

## 📊 KESIMPULAN

**Untuk Custom Permission dari Nol:**
1. Set `permissions` = array kosong `[]` dulu (user jadi blank slate)
2. Tambahkan permission satu per satu sesuai kebutuhan
3. Jangan lupa assign Menu Access juga
4. Test dengan login sebagai user tersebut

**Pro:**
- ✅ Kontrol penuh, sangat granular
- ✅ Aman untuk posisi khusus

**Cons:**
- ❌ Harus maintain manual
- ❌ Kalau config berubah, tidak auto-update
- ❌ Lebih ribet dari role default

**Rekomendasi:**
- Gunakan role default untuk 80% user
- Custom permission hanya untuk posisi khusus (Manager, Team Lead, dll)
