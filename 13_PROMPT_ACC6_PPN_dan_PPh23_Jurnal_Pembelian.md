# Dokumentasi: PPN & PPh 23 di Jurnal Pembelian

## Overview
Implementasi **PPN (Pajak Pertambahan Nilai)** dan **PPh 23 (Pajak Penghasilan Pasal 23)** dalam jurnal pembelian untuk vendor bills dan part purchases. Jurnal akan breakdown menjadi DPP, PPN Masukan, dan PPh 23 dipotong sesuai dengan aturan perpajakan Indonesia.

---

## 1. Konsep Perpajakan

### A. DPP (Dasar Pengenaan Pajak)
- Nilai transaksi **sebelum** PPN
- Basis untuk menghitung PPN dan PPh 23

### B. PPN (Pajak Pertambahan Nilai) - 11%
- **PPN Masukan** = PPN yang dibayar saat **beli barang/jasa**
- Bisa dikreditkan (dikurangi) dengan PPN Keluaran
- Dicatat sebagai **ASET** (akun 2220)

### C. PPh 23 - 2% (untuk jasa)
- **PPh 23 dipotong** = Pajak yang **dipotong** dari pembayaran vendor
- Vendor menerima uang **dikurangi** PPh 23
- Perusahaan wajib setor PPh 23 ke negara (Hutang PPh 23)
- Dicatat sebagai **LIABILITY** (akun 2240)

---

## 2. Contoh Perhitungan

### Skenario: Pembelian Jasa Vendor (Shipment Leg)
```
DPP (Nilai Jasa):           Rp 10.000.000
PPN 11%:                    Rp  1.100.000
PPh 23 (2% dari DPP):       Rp    200.000
                            ---------------
Total Tagihan:              Rp 11.100.000
PPh 23 dipotong:            Rp    200.000
                            ---------------
Net Bayar ke Vendor:        Rp 10.900.000
```

### Jurnal yang Dihasilkan:
```
Dr  Biaya Dimuka (1500)         10.000.000  (DPP)
Dr  PPN Masukan (2220)           1.100.000  (PPN)
    Cr  Hutang PPh 23 (2240)                   200.000  (PPh dipotong)
    Cr  Hutang Usaha (2100)                 10.900.000  (Net bayar)
```

**Penjelasan:**
1. **DPP** → Biaya Dimuka (karena vendor bill untuk shipment leg, pendapatan belum diakui)
2. **PPN Masukan** → Aset (bisa dikreditkan nanti)
3. **Hutang PPh 23** → Liability (kewajiban setor ke negara)
4. **Hutang Usaha** → Liability (yang benar-benar dibayar ke vendor)

---

## 3. Struktur Database

### A. Migration: Tambah Kolom Pajak

**File:** `database/migrations/2025_01_15_120000_add_tax_columns_to_vendor_bills_and_part_purchases.php`

```php
Schema::table('vendor_bills', function (Blueprint $table) {
    $table->decimal('dpp', 15, 2)->default(0)->after('total_amount');
    $table->decimal('ppn', 15, 2)->default(0)->after('dpp');
    $table->decimal('pph23', 15, 2)->default(0)->after('ppn');
});

Schema::table('part_purchases', function (Blueprint $table) {
    $table->decimal('dpp', 15, 2)->default(0)->after('total_amount');
    $table->decimal('ppn', 15, 2)->default(0)->after('dpp');
    $table->decimal('pph23', 15, 2)->default(0)->after('ppn');
});
```

### B. Kolom di Table

| Kolom | Tipe | Deskripsi |
|-------|------|-----------|
| `total_amount` | decimal | Total tagihan (DPP + PPN - PPh23) |
| `dpp` | decimal | Dasar Pengenaan Pajak |
| `ppn` | decimal | PPN 11% |
| `pph23` | decimal | PPh 23 dipotong (biasanya 2%) |

**Formula:**
```
total_amount = dpp + ppn - pph23
```

---

## 4. Chart of Accounts (COA)

### Akun yang Ditambahkan:

| Kode | Nama | Tipe | Keterangan |
|------|------|------|------------|
| **2220** | PPN Masukan | Asset | PPN yang dibayar saat beli |
| **2230** | PPh 21 | Liability | PPh 21 karyawan |
| **2240** | PPh 23 | Liability | PPh 23 dipotong vendor |

**File:** `database/seeders/ChartOfAccountsSeeder.php`

```php
['code' => '2220', 'name' => 'PPN Masukan', 'type' => 'asset'],
['code' => '2230', 'name' => 'PPh 21', 'type' => 'liability'],
['code' => '2240', 'name' => 'PPh 23', 'type' => 'liability'],
```

**File:** `config/account_mapping.php`

```php
'vat_in' => env('ACC_VAT_IN_CODE', '2220'),    // PPN Masukan
'pph21' => env('ACC_PPH21_CODE', '2230'),      // PPh 21
'pph23' => env('ACC_PPH23_CODE', '2240'),      // PPh 23
```

---

## 5. Logic Jurnal di `JournalService`

### A. `postVendorBill()` - Update

```php
public function postVendorBill(VendorBill $bill): Journal
{
    // Breakdown: DPP, PPN, PPh 23
    $dpp = (float) ($bill->dpp ?? $bill->total_amount);
    $ppn = (float) ($bill->ppn ?? 0);
    $pph23 = (float) ($bill->pph23 ?? 0);
    $netPayable = $dpp + $ppn - $pph23;
    
    $lines = [];
    
    // 1. Dr Biaya Dimuka/Expense (DPP)
    if ($hasShipmentLeg) {
        $lines[] = ['account_code' => $prepaid, 'debit' => $dpp, ...];
    } else {
        $lines[] = ['account_code' => $expense, 'debit' => $dpp, ...];
    }
    
    // 2. Dr PPN Masukan (jika ada)
    if ($ppn > 0) {
        $lines[] = ['account_code' => $vatIn, 'debit' => $ppn, ...];
    }
    
    // 3. Cr Hutang PPh 23 (jika ada)
    if ($pph23 > 0) {
        $lines[] = ['account_code' => $pph23Payable, 'debit' => 0, 'credit' => $pph23, ...];
    }
    
    // 4. Cr Hutang Usaha (net payable)
    $lines[] = ['account_code' => $ap, 'debit' => 0, 'credit' => $netPayable, ...];
    
    return $this->posting->postGeneral([...], $lines);
}
```

### B. `postPartPurchase()` - Update

Logic sama dengan `postVendorBill()`, breakdown menjadi DPP, PPN, PPh 23.

```php
public function postPartPurchase(PartPurchase $purchase): Journal
{
    // Breakdown: DPP, PPN, PPh 23
    $dpp = (float) ($purchase->dpp ?? $purchase->total_amount);
    $ppn = (float) ($purchase->ppn ?? 0);
    $pph23 = (float) ($purchase->pph23 ?? 0);
    $netPayable = $dpp + $ppn - $pph23;
    
    $lines = [];
    
    // 1. Dr Inventory (DPP)
    $lines[] = ['account_code' => $inventory, 'debit' => $dpp, ...];
    
    // 2. Dr PPN Masukan (jika ada)
    if ($ppn > 0) {
        $lines[] = ['account_code' => $vatIn, 'debit' => $ppn, ...];
    }
    
    // 3. Cr Hutang PPh 23 (jika ada)
    if ($pph23 > 0) {
        $lines[] = ['account_code' => $pph23Payable, 'debit' => 0, 'credit' => $pph23, ...];
    }
    
    // 4. Cr Hutang Usaha (net payable)
    $lines[] = ['account_code' => $ap, 'debit' => 0, 'credit' => $netPayable, ...];
    
    return $this->posting->postGeneral([...], $lines);
}
```

---

## 6. Contoh Lengkap: Flow Vendor Bill

### **Step 1: Buat Vendor Bill**

```
DPP:            10.000.000
PPN 11%:         1.100.000
PPh 23 (2%):       200.000
Total:          10.900.000 (Net Bayar)
```

**Jurnal Otomatis:**
```
Dr  Biaya Dimuka (1500)         10.000.000
Dr  PPN Masukan (2220)           1.100.000
    Cr  Hutang PPh 23 (2240)                   200.000
    Cr  Hutang Usaha (2100)                 10.900.000
```

### **Step 2: Bayar ke Vendor**

```
Dr  Hutang Usaha (2100)         10.900.000
    Cr  Kas/Bank (1110)                     10.900.000
```

### **Step 3: Setor PPh 23 ke Negara**

```
Dr  Hutang PPh 23 (2240)           200.000
    Cr  Kas/Bank (1110)                        200.000
```

### **Step 4: Buat Invoice (Pembalikan Biaya Dimuka)**

```
// Jurnal 1: Pengakuan Pendapatan
Dr  Piutang Usaha (1200)        15.000.000
    Cr  Pendapatan (4100)                   15.000.000

// Jurnal 2: Pembalikan Biaya Dimuka
Dr  Beban Vendor (5200)         10.000.000
    Cr  Biaya Dimuka (1500)                 10.000.000
```

**Balance Sheet setelah Step 4:**
- Piutang Usaha: Rp 15.000.000
- PPN Masukan: Rp 1.100.000
- Kas/Bank: (dikurangi pembayaran vendor & PPh)
- Hutang Usaha: Rp 0 (sudah dibayar)
- Hutang PPh 23: Rp 0 (sudah disetor)

**Income Statement:**
- Pendapatan: Rp 15.000.000
- Beban Vendor: Rp 10.000.000
- **Laba Kotor: Rp 5.000.000**

---

## 7. Backward Compatibility

### Data Lama (Tanpa DPP/PPN/PPh)

Jika `dpp`, `ppn`, `pph23` = NULL atau 0:

```php
$dpp = (float) ($bill->dpp ?? $bill->total_amount);  // Fallback ke total_amount
$ppn = (float) ($bill->ppn ?? 0);                    // Default 0
$pph23 = (float) ($bill->pph23 ?? 0);                // Default 0
```

**Jurnal untuk data lama (tanpa breakdown):**
```
Dr  Beban Vendor (5200)         10.000.000  (total_amount)
    Cr  Hutang Usaha (2100)                 10.000.000
```

✅ **Data lama tetap berfungsi** tanpa perlu update manual!

---

## 8. TODO: Update Form Input (Future Enhancement)

Untuk memudahkan input, form perlu ditambahkan field:

### Form Vendor Bill / Part Purchase:

```html
<div>
    <label>DPP (Nilai Dasar)</label>
    <input type="number" name="dpp" x-model="formData.dpp" @input="calculateTax()">
</div>

<div>
    <label>
        <input type="checkbox" name="has_ppn" x-model="formData.has_ppn" @change="calculateTax()">
        Include PPN 11%
    </label>
    <input type="number" name="ppn" x-model="formData.ppn" readonly>
</div>

<div>
    <label>
        <input type="checkbox" name="has_pph23" x-model="formData.has_pph23" @change="calculateTax()">
        Potong PPh 23 (2%)
    </label>
    <input type="number" name="pph23" x-model="formData.pph23" readonly>
</div>

<div>
    <label>Total Bayar (Net)</label>
    <input type="number" name="total_amount" x-model="formData.total_amount" readonly>
</div>

<script>
function calculateTax() {
    let dpp = parseFloat(this.formData.dpp) || 0;
    let ppn = this.formData.has_ppn ? dpp * 0.11 : 0;
    let pph23 = this.formData.has_pph23 ? dpp * 0.02 : 0;
    let total = dpp + ppn - pph23;
    
    this.formData.ppn = ppn;
    this.formData.pph23 = pph23;
    this.formData.total_amount = total;
}
</script>
```

---

## 9. Testing Checklist

### ✅ Test Case 1: Vendor Bill dengan PPN & PPh 23
1. Buat vendor bill:
   - DPP: 10.000.000
   - PPN: 1.100.000
   - PPh 23: 200.000
2. Cek jurnal:
   - ✅ Dr Biaya Dimuka 10.000.000
   - ✅ Dr PPN Masukan 1.100.000
   - ✅ Cr Hutang PPh 23 200.000
   - ✅ Cr Hutang Usaha 10.900.000

### ✅ Test Case 2: Vendor Bill tanpa PPN/PPh (Data Lama)
1. Buat vendor bill:
   - total_amount: 10.000.000
   - dpp, ppn, pph23: NULL
2. Cek jurnal:
   - ✅ Dr Beban Vendor 10.000.000
   - ✅ Cr Hutang Usaha 10.000.000

### ✅ Test Case 3: Part Purchase dengan PPN
1. Buat part purchase:
   - DPP: 5.000.000
   - PPN: 550.000
   - PPh 23: 0
2. Cek jurnal:
   - ✅ Dr Inventory 5.000.000
   - ✅ Dr PPN Masukan 550.000
   - ✅ Cr Hutang Usaha 5.550.000

---

## 10. Laporan Pajak

### A. Laporan PPN Masukan
Query untuk mendapatkan total PPN Masukan per bulan:

```sql
SELECT 
    DATE_FORMAT(j.journal_date, '%Y-%m') as periode,
    SUM(jl.debit) as ppn_masukan
FROM journal_lines jl
JOIN journals j ON jl.journal_id = j.id
JOIN chart_of_accounts coa ON jl.account_id = coa.id
WHERE coa.code = '2220' -- PPN Masukan
GROUP BY periode
ORDER BY periode DESC;
```

### B. Laporan PPh 23 Dipotong
Query untuk mendapatkan total PPh 23 yang harus disetor:

```sql
SELECT 
    DATE_FORMAT(j.journal_date, '%Y-%m') as periode,
    SUM(jl.credit) as pph23_terutang
FROM journal_lines jl
JOIN journals j ON jl.journal_id = j.id
JOIN chart_of_accounts coa ON jl.account_id = coa.id
WHERE coa.code = '2240' -- PPh 23
  AND jl.credit > 0
GROUP BY periode
ORDER BY periode DESC;
```

---

## 11. Catatan Penting

1. **PPN Masukan = Aset** (bisa dikreditkan)
2. **PPh 23 = Liability** (wajib disetor ke negara)
3. **Net Payable** = Yang benar-benar dibayar ke vendor
4. **Backward compatible** - Data lama tanpa breakdown tetap berfungsi
5. **Formula:** `total_amount = dpp + ppn - pph23`

---

## 12. PPh 23 Penjualan (Customer Payment)

Mulai sekarang penerimaan kas customer yang dipotong PPh 23 langsung dijurnal saat kasir mencatat transaksi di modul Kas/Bank.

### A. Kolom Baru

- `cash_bank_transactions.withholding_pph23` — menyimpan nominal potongan PPh 23.
- Form Kas/Bank menambahkan field **“Potongan PPh 23”** (optional).

### B. COA & Mapping

| Kode | Nama | Tipe | Keterangan |
|------|------|------|------------|
| **1530** | Piutang PPh Dipotong (PPh 23) | Asset | Klaim pajak dari bukti potong |

`config/account_mapping.php`:

```php
'pph23_claim' => env('ACC_PPH23_CLAIM_CODE', '1530'),
```

### C. Jurnal Customer Payment

Misal:
```
Invoice: 100.000.000
Kas diterima: 98.000.000
PPh 23 ditahan: 2.000.000
```

Jurnal otomatis:
```
Dr  Kas/Bank                         98.000.000
Dr  Piutang PPh 23 (1530)             2.000.000
    Cr  Piutang Usaha (1200)                    100.000.000
```

Efek:
- Saldo piutang = 0 (invoice lunas walau kas belum full).
- Saldo kas sama dengan rekening koran (net yang benar-benar diterima).
- Piutang PPh 23 menunjukkan hak klaim pajak yang akan dikreditkan saat pelaporan.

### D. Update Status Invoice

Status invoice sekarang dihitung dari **total penerimaan kas + potongan PPh 23**. Jadi invoice otomatis jadi `paid` jika jumlah gabungan tersebut ≥ nilai invoice.

### E. Flow Operasional Kasir

1. Kasir pilih `sumber = customer_payment`.
2. Isi **Nominal kas masuk** (setara saldo mutasi bank).
3. Isi **Potongan PPh 23** bila customer mengirim bukti potong.
4. Sistem otomatis menjurnal seperti di atas.

---

## 13. Testing (Customer Payment)

1. Buat invoice Rp 10.000.000.
2. Catat kas masuk Rp 9.800.000 + PPh 23 Rp 200.000.
3. Pastikan:
   - Jurnal: Dr Kas 9.800.000, Dr Piutang PPh 23 200.000, Cr Piutang 10.000.000.
   - Invoice status = `paid`.
   - Saldo kas sama dengan mutasi bank.
   - Piutang PPh 23 bertambah Rp 200.000.

---

## 12. File yang Diubah

1. ✅ `database/migrations/2025_01_15_120000_add_tax_columns_to_vendor_bills_and_part_purchases.php` - Migration
2. ✅ `database/seeders/ChartOfAccountsSeeder.php` - Tambah akun PPh 21, PPh 23
3. ✅ `config/account_mapping.php` - Mapping akun pajak
4. ✅ `app/Services/Accounting/JournalService.php`:
   - Update `postVendorBill()` - Breakdown PPN & PPh 23
   - Update `postPartPurchase()` - Breakdown PPN & PPh 23

---

**Selesai!** Jurnal pembelian sekarang sudah otomatis breakdown PPN dan PPh 23 sesuai dengan aturan perpajakan Indonesia. 🎉

