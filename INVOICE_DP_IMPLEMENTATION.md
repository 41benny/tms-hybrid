# Invoice DP (Down Payment) System - Implementation Summary

**Date:** November 24, 2025  
**Status:** ✅ COMPLETED - Fully Implemented with Proper Accounting

---

## 🎯 Objective

Memperbaiki implementasi invoice DP (Down Payment) ke customer dengan:
1. ✅ Jurnal akuntansi yang benar (menggunakan Hutang Uang Muka, bukan Pendapatan)
2. ✅ Tracking invoice type (DP vs Normal vs Final)
3. ✅ Pembalikan biaya dimuka yang benar (hanya untuk invoice normal/final, bukan DP)
4. ✅ Relasi untuk tracking DP yang dipotong di invoice final

---

## ✅ What Has Been Implemented

### 1. **Database Changes**

#### Migration: `2025_11_24_000001_add_invoice_type_to_invoices_table.php`

**Kolom Baru:**
- `invoice_type` (enum): 'normal', 'down_payment', 'progress', 'final'
- `related_invoice_id` (foreign key): Tracking invoice DP yang dipotong (untuk invoice final)

```sql
ALTER TABLE invoices ADD COLUMN invoice_type ENUM('normal','down_payment','progress','final') DEFAULT 'normal';
ALTER TABLE invoices ADD COLUMN related_invoice_id BIGINT UNSIGNED NULL;
```

---

### 2. **Model Updates** (`Invoice.php`)

#### Added Relationships:
```php
public function relatedInvoice(): BelongsTo
{
    return $this->belongsTo(Invoice::class, 'related_invoice_id');
}
```

#### Added Helper Methods:
```php
public function isDownPayment(): bool
{
    return $this->invoice_type === 'down_payment';
}

public function isFinal(): bool
{
    return $this->invoice_type === 'final';
}

public function isNormal(): bool
{
    return $this->invoice_type === 'normal';
}
```

---

### 3. **Account Mapping** (`config/account_mapping.php`)

**Added:**
```php
'customer_deposit' => env('ACC_CUSTOMER_DEPOSIT_CODE', '2150'), // Hutang Uang Muka Customer (DP)
```

**Default COA:** 2150 - Hutang Uang Muka Customer

---

### 4. **Journal Service Updates** (`JournalService.php`)

#### A. **Perbaikan Jurnal Invoice DP**

**SEBELUM (SALAH):**
```
Dr. Piutang Usaha (1200)        Rp 5,500,000
    Cr. Pendapatan (4100)           Rp 5,000,000  ← SALAH!
    Cr. PPN Keluaran (2200)         Rp   500,000
```

**SESUDAH (BENAR):**
```
Dr. Piutang Usaha (1200)        Rp 5,500,000
    Cr. Hutang Uang Muka (2150)     Rp 5,000,000  ← BENAR!
    Cr. PPN Keluaran (2200)         Rp   500,000
```

#### B. **Jurnal Invoice Final dengan Potongan DP**

```
Dr. Piutang Usaha (1200)        Rp 5,500,000
Dr. Hutang Uang Muka (2150)     Rp 5,000,000  ← Mengurangi hutang DP
    Cr. Pendapatan (4100)           Rp 10,000,000 ← Mengakui pendapatan penuh
    Cr. PPN Keluaran (2200)         Rp  1,000,000
```

#### C. **Perbaikan `reversePrepaidsForInvoice()`**

**SEBELUM (TIDAK JALAN):**
```php
// Mencari transport_id yang TIDAK ADA di invoice_items
$transportIds = $invoice->items()->whereNotNull('transport_id')->pluck('transport_id');
```

**SESUDAH (BENAR):**
```php
// Mencari via shipment_leg_id yang SUDAH ADA
$shipmentLegIds = $invoice->items()
    ->whereNotNull('shipment_leg_id')
    ->pluck('shipment_leg_id');

// Fallback: via job_order_id
if ($shipmentLegIds->isEmpty()) {
    $jobOrderIds = $invoice->items()
        ->whereNotNull('job_order_id')
        ->pluck('job_order_id');
    
    $shipmentLegIds = ShipmentLeg::whereIn('job_order_id', $jobOrderIds)
        ->pluck('id');
}

// Ambil vendor bills via shipment legs
$vendorBills = VendorBill::whereHas('items', function($q) use ($shipmentLegIds) {
    $q->whereIn('shipment_leg_id', $shipmentLegIds);
})->get();
```

#### D. **Conditional Prepaid Reversal**

```php
// Balik biaya dimuka HANYA untuk invoice NORMAL atau FINAL (bukan DP)
if ($invoice->invoice_type !== 'down_payment') {
    $this->reversePrepaidsForInvoice($invoice);
}
```

---

### 5. **Controller Updates** (`InvoiceController.php`)

#### Added Validation:
```php
'invoice_type' => ['nullable', 'string', 'in:normal,down_payment,progress,final'],
'is_dp' => ['nullable', 'boolean'], // Legacy support
```

#### Set Invoice Type:
```php
// Tentukan invoice_type
$invoiceType = $data['invoice_type'] ?? 'normal';

// Legacy support: jika is_dp=true, override invoice_type
if (!empty($data['is_dp'])) {
    $invoiceType = 'down_payment';
}

$inv->fill([
    // ...
    'invoice_type' => $invoiceType,
    // ...
]);
```

---

## 📊 Complete Accounting Flow

### **Scenario 1: Invoice DP (50% dari Rp 10,000,000)**

#### Step 1: Vendor Bill Shipment Leg (BY2)
```
Dr. Biaya Dimuka (1500)         Rp 8,000,000
Dr. PPN Masukan (2220)          Rp   800,000
    Cr. Hutang Usaha (2100)         Rp 8,800,000
```
**Status:** Biaya masih di Biaya Dimuka (belum jadi beban)

---

#### Step 2: Invoice DP ke Customer (50% = Rp 5,000,000)
```
Dr. Piutang Usaha (1200)        Rp 5,500,000
    Cr. Hutang Uang Muka (2150)     Rp 5,000,000
    Cr. PPN Keluaran (2210)         Rp   500,000
```
**Status:** 
- ✅ Piutang dicatat
- ✅ Hutang DP dicatat (bukan pendapatan)
- ✅ Biaya Dimuka TETAP di 1500 (tidak dibalik)

---

#### Step 3: Invoice Final (Sisa 50% dengan Potongan DP)
```
Dr. Piutang Usaha (1200)        Rp 5,500,000
Dr. Hutang Uang Muka (2150)     Rp 5,000,000
    Cr. Pendapatan (4100)           Rp 10,000,000
    Cr. PPN Keluaran (2210)         Rp  1,000,000
```

**PLUS Jurnal Pembalik Biaya Dimuka:**
```
Dr. Beban Vendor (5200)         Rp 8,000,000
    Cr. Biaya Dimuka (1500)         Rp 8,000,000
```

**Status:**
- ✅ Pendapatan diakui penuh (Rp 10,000,000)
- ✅ Hutang DP dibalik (Rp 5,000,000)
- ✅ Biaya Dimuka dibalik jadi Beban (Rp 8,000,000)

---

## 🔍 Key Differences: Before vs After

| Aspect | BEFORE (SALAH) | AFTER (BENAR) |
|--------|----------------|---------------|
| **Jurnal Invoice DP** | Cr. Pendapatan | Cr. Hutang Uang Muka |
| **Pengakuan Pendapatan DP** | Langsung saat DP | Saat invoice final |
| **Pembalikan Biaya Dimuka** | Tidak jalan (transport_id tidak ada) | Jalan via shipment_leg_id |
| **DP Reversal Timing** | Saat invoice DP (salah) | Saat invoice final (benar) |
| **Tracking Invoice Type** | Tidak ada | Ada (normal/down_payment/final) |

---

## 🛡️ Validation & Prevention

### 1. **Invoice Type Validation**
- Hanya menerima: 'normal', 'down_payment', 'progress', 'final'
- Default: 'normal'

### 2. **Legacy Support**
- Checkbox `is_dp` dari form tetap didukung
- Otomatis convert ke `invoice_type = 'down_payment'`

### 3. **Prepaid Reversal Prevention**
- Invoice DP **TIDAK** membalik biaya dimuka
- Hanya invoice normal/final yang membalik

---

## 📈 Benefits

### 1. **Akuntansi yang Benar**
- ✅ DP tidak diakui sebagai pendapatan
- ✅ Pendapatan diakui saat pekerjaan selesai (invoice final)
- ✅ Matching principle terpenuhi

### 2. **Laporan Keuangan Akurat**
- ✅ Laba/Rugi tidak overstated
- ✅ Hutang DP tercatat di Neraca
- ✅ Biaya dimuka dibalik saat yang tepat

### 3. **Audit Trail**
- ✅ Jelas tracking invoice DP vs Normal
- ✅ Relasi antara invoice final dan DP yang dipotong
- ✅ Log lengkap untuk setiap pembalikan biaya dimuka

---

## 🧪 Testing Checklist

- [x] Migration berhasil dijalankan
- [x] Invoice DP menggunakan akun Hutang Uang Muka (2150)
- [x] Invoice Normal menggunakan akun Pendapatan (4100)
- [x] Biaya Dimuka tidak dibalik saat invoice DP
- [x] Biaya Dimuka dibalik saat invoice normal/final
- [x] Invoice Final dengan DP membalik hutang uang muka
- [x] Legacy support `is_dp` checkbox masih berfungsi

---

## 📝 Database Schema Reference

### Invoices Table (Updated):
```sql
invoices
  - id
  - invoice_number
  - customer_id
  - invoice_type (NEW: normal/down_payment/progress/final)
  - related_invoice_id (NEW: FK to invoices.id)
  - invoice_date
  - due_date
  - subtotal
  - tax_amount
  - total_amount
  - status
```

### Invoice Items Table:
```sql
invoice_items
  - id
  - invoice_id
  - job_order_id (untuk tracking)
  - shipment_leg_id (untuk reversal biaya dimuka)
  - description
  - quantity
  - unit_price
  - amount
  - exclude_tax
```

---

## 🎓 Key Learnings

1. **DP ≠ Pendapatan**: Uang muka customer adalah kewajiban (liability), bukan pendapatan
2. **Matching Principle**: Pendapatan diakui saat pekerjaan selesai, bukan saat terima uang
3. **Prepaid Timing**: Biaya dimuka dibalik saat invoice final, bukan saat invoice DP
4. **Data Integrity**: Gunakan kolom yang sudah ada (shipment_leg_id) daripada tambah kolom baru

---

## 🚀 Future Enhancements (Optional)

1. **UI Indicator**: Badge "DP" di invoice list
2. **DP Validation**: Cek duplikasi DP untuk job order yang sama
3. **Auto DP Deduction**: Otomatis potong DP saat buat invoice final
4. **DP Tracking di Job Order**: Tampilkan status DP di job order detail
5. **Report**: Laporan outstanding DP customer

---

**Status:** ✅ FULLY IMPLEMENTED  
**Accounting:** Compliant with GAAP/PSAK  
**Prepaid Reversal:** Fixed and Working  
**Invoice Type Tracking:** Implemented

