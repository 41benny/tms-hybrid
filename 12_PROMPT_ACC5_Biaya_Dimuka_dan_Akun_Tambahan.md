# Dokumentasi: Biaya Dimuka & Akun Tambahan

## Overview
Implementasi sistem akuntansi **Biaya Dimuka** untuk vendor bills terkait shipment legs, dan penambahan akun-akun penting yang kurang (Persediaan, Maintenance, dan Beban Administrasi & Umum).

---

## 1. Akun Baru yang Ditambahkan

### A. Aset
- **1300** - Persediaan Sparepart
- **1400** - Biaya Dimuka (untuk vendor bill shipment legs)

### B. Beban HPP/COGS (Kepala 5)
- **5210** - Beban Maintenance

### C. Beban Administrasi & Umum (Kepala 6)
- **6100** - Beban Gaji Karyawan
- **6110** - Beban Pajak
- **6120** - Beban ATK
- **6130** - Beban Administrasi Bank
- **6140** - Beban Listrik & Air
- **6150** - Beban Telepon & Internet
- **6200** - Beban Umum Lainnya

---

## 2. Konsep Biaya Dimuka (Prepaid Expense)

### Prinsip MCAR (Matching Cost Against Revenue)
**Vendor bills untuk shipment legs tidak langsung diakui sebagai beban**, karena pendapatan belum diakui (invoice belum dibuat).

### Flow Akuntansi:

#### **Saat Vendor Bill Dibuat (Shipment Leg)**
```
Dr  Biaya Dimuka (1400)        xxx
    Cr  Hutang Usaha (2100)            xxx
```
- **Jurnal Source:** `vendor_bill`
- **Logic:** Cek `vendor_bill_items.shipment_leg_id IS NOT NULL`
- **File:** `app/Services/Accounting/JournalService.php::postVendorBill()`

#### **Saat Invoice Dibuat**
```
Dr  Piutang Usaha (1200)       xxx
    Cr  Pendapatan (4100)              xxx

Dr  Beban Vendor (5200)         xxx
    Cr  Biaya Dimuka (1400)            xxx
```
- **Jurnal Source:** `invoice` (pengakuan pendapatan)
- **Jurnal Source:** `prepaid_reverse_vb{vendor_bill_id}_inv{invoice_id}` (pembalikan biaya dimuka)
- **Logic:** Load `transport.shipmentLegs.vendorBill`, lalu balik jurnal biaya dimuka
- **File:** `app/Services/Accounting/JournalService.php::reversePrepaidsForInvoice()`

---

## 3. Update File

### A. `database/seeders/ChartOfAccountsSeeder.php`
Menambahkan akun 1300, 1400, 5210, dan 6xxx.

```php
$rows = [
    // Aset
    ['code' => '1300', 'name' => 'Persediaan Sparepart', 'type' => 'asset'],
    ['code' => '1400', 'name' => 'Biaya Dimuka', 'type' => 'asset'],
    
    // Beban HPP/COGS (kepala 5)
    ['code' => '5210', 'name' => 'Beban Maintenance', 'type' => 'expense'],
    
    // Beban Administrasi & Umum (kepala 6)
    ['code' => '6100', 'name' => 'Beban Gaji Karyawan', 'type' => 'expense'],
    ['code' => '6110', 'name' => 'Beban Pajak', 'type' => 'expense'],
    // ... dst
];
```

### B. `config/account_mapping.php`
Menambahkan mapping untuk akun baru.

```php
return [
    // Aset
    'inventory' => env('ACC_INVENTORY_CODE', '1300'),
    'prepaid_expense' => env('ACC_PREPAID_CODE', '1400'),
    
    // Beban HPP/COGS (kepala 5)
    'expense_maintenance' => env('ACC_EXP_MAINTENANCE_CODE', '5210'),
    
    // Beban Administrasi & Umum (kepala 6)
    'expense_salary' => env('ACC_EXP_SALARY_CODE', '6100'),
    'expense_tax' => env('ACC_EXP_TAX_CODE', '6110'),
    // ... dst
];
```

### C. `app/Services/Accounting/JournalService.php`

#### **Method `postVendorBill()` - Bedakan Shipment Leg vs Non-Shipment**

```php
public function postVendorBill(VendorBill $bill): Journal
{
    // ...
    
    // Cek apakah vendor bill terkait shipment leg
    $hasShipmentLeg = $bill->items()->whereNotNull('shipment_leg_id')->exists();
    
    if ($hasShipmentLeg) {
        // Dr Biaya Dimuka, Cr AP
        $prepaid = $this->map('prepaid_expense');
        $lines = [
            ['account_code' => $prepaid, 'debit' => $amt, ...],
            ['account_code' => $ap, 'debit' => 0, 'credit' => $amt, ...],
        ];
    } else {
        // Dr Beban Vendor, Cr AP (langsung expense)
        $exp = $this->map('expense_vendor');
        $lines = [
            ['account_code' => $exp, 'debit' => $amt, ...],
            ['account_code' => $ap, 'debit' => 0, 'credit' => $amt, ...],
        ];
    }
    
    // ...
}
```

#### **Method `postInvoice()` - Balik Jurnal Biaya Dimuka**

```php
public function postInvoice(Invoice $invoice): Journal
{
    // ... jurnal Dr AR, Cr Revenue ...
    
    $journal = $this->posting->postGeneral([...]);
    
    // Balik jurnal biaya dimuka
    $this->reversePrepaidsForInvoice($invoice);
    
    return $journal;
}
```

#### **Method `reversePrepaidsForInvoice()` - Pembalikan Biaya Dimuka**

```php
protected function reversePrepaidsForInvoice(Invoice $invoice): void
{
    // Load transport dan shipment legs
    $transport = $invoice->transport;
    if (!$transport) return;
    
    foreach ($transport->shipmentLegs as $leg) {
        $vendorBill = $leg->vendorBill;
        if (!$vendorBill) continue;
        
        // Cek apakah vendor bill punya jurnal biaya dimuka
        $vendorBillJournal = Journal::where('source_type', 'vendor_bill')
            ->where('source_id', $vendorBill->id)
            ->first();
        
        if (!$vendorBillJournal) continue;
        
        // Cek apakah jurnal tersebut menggunakan biaya dimuka (1400)
        $prepaidCode = $this->map('prepaid_expense');
        $hasPrepaid = $vendorBillJournal->lines()
            ->whereHas('account', fn($q) => $q->where('code', $prepaidCode))
            ->exists();
        
        if (!$hasPrepaid) continue;
        
        // Cek apakah jurnal pembalik sudah dibuat
        $reverseKey = 'prepaid_reverse_vb'.$vendorBill->id.'_inv'.$invoice->id;
        if ($this->alreadyPosted($reverseKey, 0)) continue;
        
        // Buat jurnal pembalik: Dr Beban Vendor, Cr Biaya Dimuka
        $expense = $this->map('expense_vendor');
        $amt = (float) $vendorBill->total_amount;
        
        $lines = [
            ['account_code' => $expense, 'debit' => $amt, 'credit' => 0, ...],
            ['account_code' => $prepaidCode, 'debit' => 0, 'credit' => $amt, ...],
        ];
        
        $this->posting->postGeneral([
            'journal_date' => $invoice->invoice_date->toDateString(),
            'source_type' => $reverseKey,
            'source_id' => 0,
            'memo' => 'Pembalikan biaya dimuka untuk invoice '.$invoice->invoice_number,
        ], $lines);
    }
}
```

---

## 4. Contoh Flow Lengkap

### Scenario: Shipment dengan 2 vendor legs, kemudian dibuat invoice

#### **Step 1: Buat Vendor Bill untuk Leg 1 (Rp 1.000.000)**
```
Dr  Biaya Dimuka (1400)         1.000.000
    Cr  Hutang Usaha (2100)                 1.000.000
```

#### **Step 2: Buat Vendor Bill untuk Leg 2 (Rp 1.500.000)**
```
Dr  Biaya Dimuka (1400)         1.500.000
    Cr  Hutang Usaha (2100)                 1.500.000
```

**Balance Sheet saat ini:**
- Biaya Dimuka (1400): Rp 2.500.000 (Aset)
- Hutang Usaha (2100): Rp 2.500.000 (Liabilities)
- **Belum ada beban**, karena pendapatan belum diakui

#### **Step 3: Buat Invoice (Rp 5.000.000)**

**Jurnal 1 - Pengakuan Pendapatan:**
```
Dr  Piutang Usaha (1200)        5.000.000
    Cr  Pendapatan (4100)                   5.000.000
```

**Jurnal 2 - Pembalikan Biaya Dimuka Leg 1:**
```
Dr  Beban Vendor (5200)         1.000.000
    Cr  Biaya Dimuka (1400)                 1.000.000
```

**Jurnal 3 - Pembalikan Biaya Dimuka Leg 2:**
```
Dr  Beban Vendor (5200)         1.500.000
    Cr  Biaya Dimuka (1400)                 1.500.000
```

**Balance Sheet setelah invoice:**
- Piutang Usaha (1200): Rp 5.000.000
- Biaya Dimuka (1400): Rp 0
- Hutang Usaha (2100): Rp 2.500.000

**Income Statement:**
- Pendapatan (4100): Rp 5.000.000
- Beban Vendor (5200): Rp 2.500.000
- **Laba Kotor: Rp 2.500.000**

---

## 5. Penggunaan Akun Administrasi & Umum (Kepala 6)

Untuk pengeluaran kas/bank non-operasional, gunakan akun kepala 6:

### Contoh: Bayar Gaji Karyawan (Rp 10.000.000)
```
Dr  Beban Gaji Karyawan (6100)  10.000.000
    Cr  Kas/Bank (1100/1110)               10.000.000
```

### Contoh: Bayar Pajak (Rp 2.000.000)
```
Dr  Beban Pajak (6110)          2.000.000
    Cr  Kas/Bank (1100/1110)                2.000.000
```

### Contoh: Beli ATK (Rp 500.000)
```
Dr  Beban ATK (6120)            500.000
    Cr  Kas/Bank (1100/1110)                500.000
```

---

## 6. Laporan Keuangan

### A. Profit & Loss (Income Statement)
```
Pendapatan (4xxx):
  4100 - Pendapatan Jasa Angkutan          xxx

Beban Pokok Penjualan / COGS (5xxx):
  5100 - Beban BBM                         (xxx)
  5110 - Beban Tol                         (xxx)
  5120 - Beban Uang Makan                  (xxx)
  5200 - Beban Vendor                      (xxx)
  5210 - Beban Maintenance                 (xxx)
  5300 - Beban Operasional Lainnya         (xxx)
                                           ------
  Total COGS                               (xxx)
                                           ------
Laba Kotor                                 xxx

Beban Administrasi & Umum (6xxx):
  6100 - Beban Gaji Karyawan               (xxx)
  6110 - Beban Pajak                       (xxx)
  6120 - Beban ATK                         (xxx)
  6130 - Beban Administrasi Bank           (xxx)
  6140 - Beban Listrik & Air               (xxx)
  6150 - Beban Telepon & Internet          (xxx)
  6200 - Beban Umum Lainnya                (xxx)
                                           ------
  Total Beban Adm & Umum                   (xxx)
                                           ------
Laba Bersih                                xxx
```

### B. Balance Sheet
```
Aset:
  1100 - Kas                               xxx
  1110 - Bank                              xxx
  1200 - Piutang Usaha                     xxx
  1300 - Persediaan Sparepart              xxx
  1400 - Biaya Dimuka                      xxx
                                           ------
  Total Aset                               xxx

Kewajiban:
  2100 - Hutang Usaha                      xxx
  2210 - PPN Keluaran                      xxx
                                           ------
  Total Kewajiban                          xxx

Ekuitas:
  3100 - Modal                             xxx
  3200 - Laba Ditahan                      xxx
                                           ------
  Total Ekuitas                            xxx
                                           ------
Total Kewajiban & Ekuitas                  xxx
```

---

## 7. Command untuk Setup

```bash
# Jalankan seeder untuk menambahkan akun baru
php artisan db:seed --class=ChartOfAccountsSeeder

# Cek akun baru sudah dibuat
php artisan tinker
>>> \App\Models\Accounting\ChartOfAccount::whereIn('code', ['1300', '1400', '5210', '6100', '6110', '6120'])->get(['code', 'name']);
```

---

## 8. Testing Flow

### Test 1: Vendor Bill untuk Shipment Leg
1. Buat shipment leg
2. Buat vendor bill dengan `vendor_bill_items.shipment_leg_id` terisi
3. Cek jurnal: harus menggunakan **Biaya Dimuka (1400)**, bukan Beban Vendor

### Test 2: Vendor Bill Non-Shipment (contoh: pembelian part)
1. Buat pembelian part dengan `is_direct_usage = true`
2. Vendor Bill otomatis dibuat (tanpa `shipment_leg_id`)
3. Cek jurnal: harus menggunakan **Beban Vendor (5200)** (langsung expense)

### Test 3: Invoice dengan Pembalikan Biaya Dimuka
1. Buat shipment dengan 2 legs
2. Buat vendor bill untuk masing-masing leg (biaya dimuka)
3. Buat invoice untuk shipment tersebut
4. Cek jurnal:
   - Ada jurnal invoice: Dr AR, Cr Revenue
   - Ada jurnal pembalikan untuk leg 1: Dr Beban Vendor, Cr Biaya Dimuka
   - Ada jurnal pembalikan untuk leg 2: Dr Beban Vendor, Cr Biaya Dimuka

---

## 9. Catatan Penting

1. **Biaya Dimuka hanya untuk vendor bill dengan shipment leg**
   - Vendor bill non-shipment tetap langsung expense

2. **Pembalikan otomatis saat invoice dibuat**
   - Tidak perlu manual
   - Menggunakan source_type unik: `prepaid_reverse_vb{vendor_bill_id}_inv{invoice_id}`

3. **Tracking hutang tetap menggunakan vendor_id**
   - Tidak ada sub jurnal
   - Hutang per vendor bisa dilihat dari `journal_lines.vendor_id`

4. **Akun kepala 6 untuk beban non-operasional**
   - Gaji, pajak, ATK, utilitas, dll
   - Berbeda dengan beban operasional (kepala 5) yang terkait langsung dengan revenue

---

## 10. File yang Diubah

1. `database/seeders/ChartOfAccountsSeeder.php` - Tambah akun baru
2. `config/account_mapping.php` - Tambah mapping akun baru
3. `app/Services/Accounting/JournalService.php`:
   - Update `postVendorBill()` - bedakan shipment leg vs non-shipment
   - Update `postInvoice()` - panggil `reversePrepaidsForInvoice()`
   - Tambah `reversePrepaidsForInvoice()` - logic pembalikan biaya dimuka

---

**Selesai!** Sistem akuntansi sekarang sudah lengkap dengan biaya dimuka dan akun-akun tambahan untuk administrasi & umum.

