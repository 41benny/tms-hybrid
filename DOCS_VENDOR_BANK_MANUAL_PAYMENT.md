# Dokumentasi: Rekening Bank Vendor & Manual Payment Request

## 📋 Ringkasan Fitur

Sistem sekarang mendukung:
1. **Multiple rekening bank per vendor**
2. **Payment request dari vendor bill** (existing - enhanced)
3. **Payment request manual** (NEW) - untuk pembayaran diluar vendor bill

---

## 🏦 Fitur 1: Rekening Bank Vendor

### Database Schema

**Tabel:** `vendor_bank_accounts`

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `vendor_id` | bigint | FK ke vendors (cascade delete) |
| `bank_name` | string | Nama bank (BCA, Mandiri, BNI, dll) |
| `account_number` | string | Nomor rekening |
| `account_holder_name` | string | Nama pemilik rekening |
| `branch` | string (nullable) | Cabang bank |
| `is_primary` | boolean | Rekening utama (default: false) |
| `is_active` | boolean | Status aktif (default: true) |
| `notes` | text (nullable) | Catatan |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

**Index:**
- `vendor_id`, `is_primary`
- `vendor_id`, `is_active`

### Model Relationships

**Vendor Model:**
```php
// app/Models/Master/Vendor.php

public function bankAccounts(): HasMany
{
    return $this->hasMany(VendorBankAccount::class);
}

public function activeBankAccounts(): HasMany
{
    return $this->hasMany(VendorBankAccount::class)
        ->where('is_active', true);
}

public function primaryBankAccount()
{
    return $this->hasOne(VendorBankAccount::class)
        ->where('is_primary', true)
        ->where('is_active', true);
}
```

**VendorBankAccount Model:**
```php
// app/Models/Master/VendorBankAccount.php

public function vendor(): BelongsTo
{
    return $this->belongsTo(Vendor::class);
}

// Attribute helper
public function getFormattedAccountAttribute(): string
{
    return "{$this->bank_name} - {$this->account_number} ({$this->account_holder_name})";
}
```

### UI/UX - Vendors

#### **Create/Edit Vendor** (`/vendors/create`, `/vendors/{id}/edit`)

**Section Rekening Bank:**
- Button **"Tambah Rekening"** (dynamic JavaScript)
- Form per rekening:
  - Nama Bank (required)
  - Nomor Rekening (required)
  - Nama Pemilik (required)
  - Cabang (optional)
  - Checkbox: Rekening Utama
  - Checkbox: Aktif
  - Button: Hapus
- Empty state: "Belum ada rekening bank"

**JavaScript Features:**
- Dynamic add/remove rekening tanpa reload
- Soft delete untuk rekening existing (marked `_destroy`)
- Hard delete dari DOM untuk rekening baru

#### **Index Vendor** (`/vendors`)

**Kolom Rekening Bank:**
- Tampil rekening primary/aktif pertama
- Format: `BCA - 1234567890`
- Indicator: `+2 rekening lainnya` (jika punya lebih dari 1)
- Italic: `Belum ada rekening` (jika kosong)

---

## 💳 Fitur 2: Payment Request Enhancement

### Database Schema Updates

**Tabel:** `payment_requests` (Updated)

| Field Baru | Type | Description |
|------------|------|-------------|
| `vendor_bank_account_id` | bigint (nullable) | FK ke vendor_bank_accounts |
| `vendor_id` | bigint (nullable) | FK ke vendors (untuk manual payment) |
| `payment_type` | enum | 'vendor_bill' atau 'manual' |
| `description` | string (nullable) | Deskripsi untuk manual payment |

**Changes:**
- `vendor_bill_id` sekarang **NULLABLE** (untuk manual payment)
- Default `payment_type` = `'vendor_bill'`

### Model Relationships

**PaymentRequest Model:**
```php
// app/Models/Operations/PaymentRequest.php

public function vendorBill(): BelongsTo
{
    return $this->belongsTo(VendorBill::class);
}

public function vendor(): BelongsTo  // NEW
{
    return $this->belongsTo(Vendor::class);
}

public function vendorBankAccount(): BelongsTo  // NEW
{
    return $this->belongsTo(VendorBankAccount::class);
}
```

---

## 📝 Fitur 3: Manual Payment Request (NEW)

### Use Cases

| Scenario | Contoh |
|----------|--------|
| Sewa Kantor | Pembayaran sewa ruangan bulanan |
| Service & Maintenance | Service truck, perbaikan, dll |
| Utilities | Listrik, air, internet |
| Operasional | Biaya yang tidak terkait shipment |
| Emergency | Pembayaran mendadak diluar vendor bill |

### Flow Manual Payment Request

```
1. Payment Requests Index
   ↓
2. Klik "Buat Pengajuan Manual"
   ↓
3. Form Manual Payment Request
   - Pilih Vendor (dropdown)
   - Rekening auto-load based on vendor
   - Isi Deskripsi (required)
   - Isi Jumlah (required)
   - Catatan (optional)
   ↓
4. Submit → Payment Request dibuat (type: manual)
   ↓
5. Approval flow sama seperti vendor bill payment
   ↓
6. Paid → Tercatat di cash/bank transaction
```

### Controller Logic

**PaymentRequestController:**

```php
public function create(Request $request)
{
    if ($vendorBillId = $request->get('vendor_bill_id')) {
        // Load vendor bill dengan rekening
        $vendorBill = VendorBill::with(['vendor.activeBankAccounts', ...])->findOrFail($vendorBillId);
        return view('...', compact('vendorBill'));
    } else {
        // Load all vendors untuk manual request
        $vendors = Vendor::with('activeBankAccounts')->where('is_active', true)->get();
        return view('...', compact('vendors'));
    }
}

public function store(Request $request)
{
    // Conditional validation
    $validated = $request->validate([
        'payment_type' => ['required', 'in:vendor_bill,manual'],
        'vendor_bill_id' => ['required_if:payment_type,vendor_bill', ...],
        'vendor_id' => ['required_if:payment_type,manual', ...],
        'description' => ['required_if:payment_type,manual', ...],
        'vendor_bank_account_id' => ['nullable', ...],
        'amount' => ['required', ...],
        'notes' => ['nullable', ...],
    ]);
    
    // Validate amount untuk vendor_bill type
    if ($validated['payment_type'] === 'vendor_bill') {
        $vendorBill = VendorBill::with('payments')->findOrFail($validated['vendor_bill_id']);
        $remaining = $vendorBill->total_amount - $vendorBill->payments->sum('amount');
        
        if ($validated['amount'] > $remaining) {
            return back()->withErrors(['amount' => 'Jumlah melebihi sisa tagihan']);
        }
    }
    
    PaymentRequest::create($validated);
    // ...
}
```

### Validasi Rules

#### **Vendor Bill Payment:**
- `payment_type` = `'vendor_bill'` (required)
- `vendor_bill_id` (required)
- `vendor_bank_account_id` (optional)
- `amount` (required, max = vendor bill remaining)
- `notes` (optional)

#### **Manual Payment:**
- `payment_type` = `'manual'` (required)
- `vendor_id` (required)
- `vendor_bank_account_id` (optional tapi recommended)
- `description` (required)
- `amount` (required, no max limit)
- `notes` (optional)

---

## 🎨 UI/UX Features

### **Payment Requests Index** (`/payment-requests`)

**Header:**
- Button **"Buat Pengajuan Manual"** → ke `/payment-requests/create`

**Table Columns:**
| Column | Vendor Bill | Manual |
|--------|-------------|--------|
| Nomor | PR-202511-0001 | PR-202511-0002 |
| Tipe | Badge: VENDOR BILL | Badge: MANUAL (warning) |
| Vendor Bill / Deskripsi | VBL-123 (link) | "Sewa Kantor Nov" (italic) |
| Vendor | PT ABC | PT XYZ |
| Tanggal | 13 Nov 2025 | 13 Nov 2025 |
| Jumlah | Rp 5.000.000 | Rp 10.000.000 |
| Status | PENDING | APPROVED |

### **Payment Request Create** (`/payment-requests/create`)

#### **Mode A: Dari Vendor Bill** (`?vendor_bill_id=123`)
- Form auto-fill data vendor bill
- Dropdown rekening vendor
- Jumlah max = vendor bill remaining
- Submit → payment_type = 'vendor_bill'

#### **Mode B: Manual** (no query params)
- Form manual dengan field:
  1. **Vendor** (dropdown) → triggers rekening load
  2. **Rekening Tujuan** (dropdown, auto-load based on vendor)
  3. **Deskripsi** (text input, required)
  4. **Jumlah** (number with formatting)
  5. **Catatan** (textarea, optional)
- Warning jika vendor belum punya rekening
- Submit → payment_type = 'manual'

**JavaScript:**
```javascript
// Auto-load bank accounts when vendor selected
vendorSelect.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const bankAccounts = JSON.parse(selectedOption.getAttribute('data-bank-accounts') || '[]');
    
    if (bankAccounts.length > 0) {
        // Populate dropdown
        bankAccounts.forEach(account => {
            // Auto-select primary account
            if (account.is_primary) option.selected = true;
        });
    } else {
        // Show warning
        noBankWarning.classList.remove('hidden');
    }
});
```

### **Payment Request Show** (`/payment-requests/{id}`)

#### **Vendor Bill Payment:**
- Section "Vendor Bill Terkait" dengan:
  - Nomor Bill (link)
  - Total tagihan
  - Status bill
- Button "Lihat Vendor Bill"

#### **Manual Payment:**
- Section "Informasi Pembayaran" dengan:
  - Badge: MANUAL PAYMENT
  - Deskripsi pembayaran
  - Data vendor (nama, phone)
- Button "Lihat Vendor"

**Keduanya Tampil:**
- Info Rekening Tujuan Transfer (jika dipilih):
  - Icon bank
  - Nama bank
  - Nomor rekening (font-mono)
  - Nama pemilik
  - Cabang (jika ada)

---

## 🔀 Migration Flow

### Migration 1: Create vendor_bank_accounts
```php
// 2025_11_13_031702_create_vendor_bank_accounts_table.php
Schema::create('vendor_bank_accounts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
    $table->string('bank_name');
    $table->string('account_number');
    $table->string('account_holder_name');
    $table->string('branch')->nullable();
    $table->boolean('is_primary')->default(false);
    $table->boolean('is_active')->default(true);
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

### Migration 2: Add vendor_bank_account_id to payment_requests
```php
// 2025_11_13_032340_add_vendor_bank_account_id_to_payment_requests_table.php
Schema::table('payment_requests', function (Blueprint $table) {
    $table->foreignId('vendor_bank_account_id')
        ->nullable()
        ->after('vendor_bill_id')
        ->constrained('vendor_bank_accounts')
        ->nullOnDelete();
});
```

### Migration 3: Update payment_requests for manual requests
```php
// 2025_11_13_033344_update_payment_requests_for_manual_requests.php
Schema::table('payment_requests', function (Blueprint $table) {
    // Make vendor_bill_id nullable
    $table->foreignId('vendor_bill_id')->nullable()->change();
    
    // Add vendor_id untuk manual payment
    $table->foreignId('vendor_id')->nullable()->after('vendor_bill_id')
        ->constrained('vendors')->nullOnDelete();
    
    // Add payment_type
    $table->enum('payment_type', ['vendor_bill', 'manual'])
        ->default('vendor_bill')->after('vendor_bank_account_id');
    
    // Add description
    $table->string('description')->nullable()->after('payment_type');
});
```

---

## 📊 Data Structure Examples

### Contoh 1: Vendor dengan 2 Rekening

**Vendor:**
- ID: 5
- Name: "PT Mitra Transport"
- Type: trucking

**Bank Accounts:**
```
[
    {
        id: 1,
        vendor_id: 5,
        bank_name: "BCA",
        account_number: "1234567890",
        account_holder_name: "PT Mitra Transport",
        branch: "Jakarta Pusat",
        is_primary: true,   ⭐
        is_active: true
    },
    {
        id: 2,
        vendor_id: 5,
        bank_name: "Mandiri",
        account_number: "9876543210",
        account_holder_name: "PT Mitra Transport",
        branch: null,
        is_primary: false,
        is_active: true
    }
]
```

### Contoh 2: Payment Request dari Vendor Bill

```php
PaymentRequest {
    id: 1,
    payment_type: 'vendor_bill',
    vendor_bill_id: 123,
    vendor_id: null,  // Diambil dari vendor_bill
    vendor_bank_account_id: 1,
    description: null,
    amount: 5000000,
    status: 'pending',
    notes: 'Pembayaran 50% dari total bill'
}
```

### Contoh 3: Payment Request Manual

```php
PaymentRequest {
    id: 2,
    payment_type: 'manual',
    vendor_bill_id: null,
    vendor_id: 5,
    vendor_bank_account_id: 1,
    description: 'Pembayaran Sewa Kantor November 2025',
    amount: 15000000,
    status: 'pending',
    notes: 'Transfer ke rekening utama BCA'
}
```

---

## 🎯 Perbedaan Vendor Bill vs Manual

| Aspek | Vendor Bill Payment | Manual Payment |
|-------|---------------------|----------------|
| **Trigger** | Dari Dashboard Hutang / Vendor Bills | Dari Payment Requests Index |
| **Source** | Terkait shipment leg | Tidak terkait shipment |
| **Vendor** | Auto dari vendor bill | User pilih manual |
| **Amount** | Max = bill remaining | Bebas (no limit) |
| **Description** | Optional (dari vendor bill) | Required (user input) |
| **Validation** | Check remaining amount | No amount limit |
| **Display** | Link ke vendor bill | Tampil deskripsi |
| **Use Case** | Pembayaran vendor dari pengiriman | Pembayaran operasional/lainnya |

---

## 🔧 Controller Methods

### VendorController

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => [...],
        // ... vendor fields
        'bank_accounts' => ['nullable', 'array'],
        'bank_accounts.*.bank_name' => ['required', ...],
        'bank_accounts.*.account_number' => ['required', ...],
        'bank_accounts.*.account_holder_name' => ['required', ...],
        // ...
    ]);
    
    DB::transaction(function () use ($validated, $request) {
        $vendor = Vendor::create($validated);
        
        // Save bank accounts
        if ($request->has('bank_accounts')) {
            foreach ($request->bank_accounts as $accountData) {
                $vendor->bankAccounts()->create($accountData);
            }
        }
    });
}

public function update(Request $request, Vendor $vendor)
{
    // Similar validation
    
    DB::transaction(function () use ($validated, $request, $vendor) {
        $vendor->update($validated);
        
        // Update/Create/Delete bank accounts
        foreach ($request->bank_accounts as $accountData) {
            if (isset($accountData['_destroy'])) {
                // Delete marked accounts
                VendorBankAccount::find($accountData['id'])?->delete();
            } elseif (isset($accountData['id'])) {
                // Update existing
                VendorBankAccount::find($accountData['id'])->update($accountData);
            } else {
                // Create new
                $vendor->bankAccounts()->create($accountData);
            }
        }
    });
}
```

### PaymentRequestController

```php
public function create(Request $request)
{
    if ($vendorBillId = $request->get('vendor_bill_id')) {
        // Mode: Vendor Bill Payment
        $vendorBill = VendorBill::with(['vendor.activeBankAccounts', 'items', 'payments'])
            ->findOrFail($vendorBillId);
        return view('...', compact('vendorBill'));
    } else {
        // Mode: Manual Payment
        $vendors = Vendor::with('activeBankAccounts')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        return view('...', compact('vendors'));
    }
}

public function store(Request $request)
{
    // Conditional validation berdasarkan payment_type
    
    // For vendor_bill: validate amount <= remaining
    if ($validated['payment_type'] === 'vendor_bill') {
        $vendorBill = VendorBill::with('payments')->findOrFail($validated['vendor_bill_id']);
        $remaining = $vendorBill->total_amount - $vendorBill->payments->sum('amount');
        
        if ($validated['amount'] > $remaining) {
            return back()->withErrors(['amount' => 'Jumlah melebihi sisa tagihan']);
        }
    }
    
    PaymentRequest::create($validated);
}
```

---

## 🧪 Testing Scenarios

### Test 1: Tambah Vendor dengan Multiple Rekening

**Steps:**
1. Buka `/vendors/create`
2. Isi data vendor (PT ABC, trucking, phone, email)
3. Klik **"Tambah Rekening"**
4. Isi rekening BCA:
   - Bank: BCA
   - No: 1234567890
   - Holder: PT ABC
   - Branch: Jakarta Pusat
   - ✅ Rekening Utama
   - ✅ Aktif
5. Klik **"Tambah Rekening"** lagi
6. Isi rekening Mandiri (tidak primary)
7. Simpan

**Expected:**
- ✅ Vendor tersimpan
- ✅ 2 rekening tersimpan
- ✅ BCA sebagai primary
- ✅ Di index, kolom rekening tampil "BCA - 1234567890 + 1 rekening lainnya"

### Test 2: Edit Vendor - Hapus & Tambah Rekening

**Steps:**
1. Edit vendor yang sudah punya rekening
2. Klik **"Hapus"** pada rekening pertama
3. Klik **"Tambah Rekening"** untuk rekening baru
4. Update

**Expected:**
- ✅ Rekening lama terhapus dari database
- ✅ Rekening baru tersimpan
- ✅ Rekening yang tidak diubah tetap utuh

### Test 3: Manual Payment Request - Happy Path

**Steps:**
1. Buka `/payment-requests`
2. Klik **"Buat Pengajuan Manual"**
3. Pilih vendor "PT ABC"
4. **Expected:** Dropdown rekening auto-populate, BCA (⭐) auto-selected
5. Pilih rekening BCA
6. Isi:
   - Deskripsi: "Pembayaran Sewa Kantor November 2025"
   - Jumlah: 15.000.000
   - Catatan: "Pembayaran bulan ke-11"
7. Submit

**Expected:**
- ✅ Redirect ke show page
- ✅ Nomor: PR-202511-XXXX
- ✅ Tipe: MANUAL PAYMENT (badge kuning)
- ✅ Deskripsi tampil: "Pembayaran Sewa Kantor November 2025"
- ✅ Vendor: PT ABC
- ✅ Rekening Tujuan: BCA - 1234567890 (PT ABC)
- ✅ Status: PENDING

### Test 4: Manual Payment - Vendor Tanpa Rekening

**Steps:**
1. Buat pengajuan manual
2. Pilih vendor yang belum punya rekening

**Expected:**
- ✅ Dropdown rekening kosong
- ✅ Warning tampil: "Vendor Belum Punya Rekening Bank"
- ✅ Link "Edit Vendor & Tambah Rekening" berfungsi
- ✅ Tetap bisa submit (rekening optional)

### Test 5: Payment Request Index - Mixed Types

**Steps:**
1. Buat 2 payment requests:
   - 1 dari vendor bill
   - 1 manual
2. Buka `/payment-requests`

**Expected:**
- ✅ Kolom "Tipe" tampil badge berbeda:
  - VENDOR BILL (default/abu-abu)
  - MANUAL (warning/kuning)
- ✅ Kolom "Vendor Bill / Deskripsi":
  - Vendor bill: Link ke bill (VBL-123)
  - Manual: Text italic deskripsi
- ✅ Kedua request tampil di table yang sama

### Test 6: Approval Flow - Manual Payment

**Steps:**
1. Login sebagai super_admin
2. Buka manual payment request dengan status pending
3. Klik **"Setujui"**

**Expected:**
- ✅ Status berubah jadi 'approved'
- ✅ approved_by & approved_at tersimpan
- ✅ Flow approval sama dengan vendor bill payment
- ✅ Bisa di-pay via cash/bank transaction

---

## 🚨 Error Handling

### Error 1: Vendor Bill Not Found
**Scenario:** URL `/payment-requests/create?vendor_bill_id=999` (ID tidak valid)
**Handling:** 404 exception dengan message "Vendor bill tidak ditemukan"

### Error 2: Vendor Tanpa Rekening
**Scenario:** User pilih vendor yang belum punya rekening bank
**Handling:** 
- ✅ Warning tampil
- ✅ Link langsung ke edit vendor
- ✅ Tetap bisa submit (rekening optional)

### Error 3: Amount > Remaining (Vendor Bill)
**Scenario:** User input amount lebih besar dari sisa tagihan vendor bill
**Handling:** Validation error dengan message spesifik + max amount

### Error 4: Payment Type Mismatch
**Scenario:** Form submit dengan payment_type tidak valid
**Handling:** Validation error "payment_type must be vendor_bill or manual"

---

## 📈 Reporting & Analytics (Future Enhancement)

### Dashboard Metrics:
- Total manual payments this month
- Total vendor bill payments this month
- Payment by type (pie chart)
- Top vendors by payment frequency

### Reports:
- Payment History by Type
- Vendor Payment Summary (bill vs manual)
- Bank Account Usage Statistics
- Payment Request Aging Report

---

## 🔐 Security & Permissions

### Current (Development):
- ✅ All users can create manual payment requests
- ✅ Super admin can approve/reject
- ✅ Requester can delete pending requests

### Production (Recommended):
```php
// Role-based access:
- Admin/Finance → Create manual & vendor bill requests
- Super Admin → Approve/Reject/View all
- User → View own requests only

// Implement di controller:
if (Auth::check() && Auth::user()->role !== 'finance' && Auth::user()->role !== 'super_admin') {
    abort(403, 'Unauthorized to create payment requests');
}
```

---

## ✅ Checklist Implementation

### Database:
- [x] Create vendor_bank_accounts table
- [x] Add vendor_bank_account_id to payment_requests
- [x] Make vendor_bill_id nullable
- [x] Add vendor_id, payment_type, description to payment_requests

### Models:
- [x] VendorBankAccount model with relationships
- [x] Vendor model with bankAccounts relationships
- [x] PaymentRequest model with vendor & vendorBankAccount relationships

### Controllers:
- [x] VendorController store/update untuk manage rekening
- [x] PaymentRequestController create untuk dual mode (bill vs manual)
- [x] PaymentRequestController store dengan conditional validation
- [x] PaymentRequestController show/index dengan support manual

### Views:
- [x] vendors/create - form rekening bank (dynamic)
- [x] vendors/index - kolom rekening bank
- [x] payment-requests/create - dual mode form
- [x] payment-requests/index - button manual + kolom tipe
- [x] payment-requests/show - conditional display

### JavaScript:
- [x] Dynamic add/remove bank accounts (vendors form)
- [x] Auto-load bank accounts based on vendor selection
- [x] Number formatting untuk amount input
- [x] Soft delete untuk rekening existing

### Validation:
- [x] Conditional validation based on payment_type
- [x] Amount validation untuk vendor_bill type
- [x] Required fields enforcement

---

## 🎓 Best Practices Applied

1. **Database Design:**
   - Normalized structure (separate table untuk bank accounts)
   - Proper foreign keys dengan cascade/null on delete
   - Indexes untuk performance

2. **Code Organization:**
   - Transaction untuk atomic operations
   - Eager loading untuk prevent N+1
   - Conditional validation
   - Reusable view components

3. **User Experience:**
   - Dynamic forms tanpa reload
   - Auto-selection rekening utama
   - Visual indicators (badges, icons, colors)
   - Clear error messages
   - Mobile responsive
   - Dark mode support

4. **Data Integrity:**
   - Validation di controller & browser
   - Transaction untuk multi-table operations
   - Soft delete pattern untuk edit mode
   - Amount validation untuk vendor bill payments

---

**Update Date:** 13 November 2025  
**Feature:** Rekening Bank Vendor & Manual Payment Request  
**Status:** ✅ Completed & Tested

