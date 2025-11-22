# 📊 Analisis Modul Accounting TMS Hybrid

**Tanggal Analisis:** 21 November 2025  
**Fokus:** Laporan Keuangan & Laporan Pajak

---

## ✅ FITUR ACCOUNTING YANG SUDAH ADA

### 1. **Chart of Accounts (COA)** ✅
- ✅ Complete COA structure
- ✅ Account types: asset, liability, equity, revenue, expense
- ✅ Account categories
- ✅ Active/inactive status
- ✅ Postable flag

### 2. **Journal System** ✅
- ✅ Journal entry creation & management
- ✅ Journal lines dengan debit/credit
- ✅ Auto-posting dari berbagai transaksi
- ✅ Status: draft, posted
- ✅ Source tracking (polymorphic)

### 3. **Fiscal Period Management** ✅
- ✅ Period creation (monthly)
- ✅ Period status: open, closed, locked
- ✅ Period closing & reopening
- ✅ Period locking untuk prevent changes

### 4. **Journal Service (Auto-Posting)** ✅
Sistem sudah memiliki auto-posting untuk:
- ✅ **Invoice** → Dr. Piutang Usaha, Cr. Pendapatan, Cr. PPN Keluaran
- ✅ **Customer Payment** → Dr. Kas/Bank, Dr. Piutang PPh 23 (jika ada), Cr. Piutang Usaha
- ✅ **Vendor Bill** → Dr. Biaya Dimuka/Expense, Dr. PPN Masukan, Cr. Hutang PPh 23, Cr. Hutang Usaha
- ✅ **Vendor Payment** → Dr. Hutang Usaha, Cr. Kas/Bank
- ✅ **Expense** → Dr. Beban, Cr. Kas/Bank
- ✅ **Part Purchase** → Dr. Inventory, Dr. PPN Masukan, Cr. Hutang PPh 23, Cr. Hutang Usaha
- ✅ **Part Usage** → Dr. Beban Maintenance, Cr. Inventory
- ✅ **Prepaid Reversal** → Dr. Beban Vendor, Cr. Biaya Dimuka (saat invoice)

### 5. **Tax Handling** ✅
Sistem sudah handle:
- ✅ **PPN (11%)** - Input & Output
  - PPN Keluaran (dari Invoice)
  - PPN Masukan (dari Vendor Bill & Part Purchase)
- ✅ **PPh 23 (2%)** - Withholding Tax
  - PPh 23 dipotong dari Vendor Bill
  - PPh 23 dipotong dari Customer Payment
  - Akun: Hutang PPh 23 (liability) & Piutang PPh 23 (asset)

---

## 📈 LAPORAN KEUANGAN YANG SUDAH ADA

### 1. **Trial Balance (Neraca Saldo)** ✅ LENGKAP
**File:** `resources/views/reports/trial-balance.blade.php`

**Fitur:**
- ✅ Filter by date range (from - to)
- ✅ Menampilkan semua akun dengan transaksi
- ✅ Kolom: Account Code, Account Name, Opening Balance, Debit, Credit, Closing Balance
- ✅ Total debit & credit
- ✅ Format angka dengan separator ribuan

**Query Logic:**
```php
- Opening balance dari tabel opening_balances (by year)
- Period movements dari journal_lines (by date range)
- Closing = Opening + Debit - Credit
```

**Status:** ✅ **SUDAH LENGKAP & BERFUNGSI**

---

### 2. **General Ledger (Buku Besar)** ✅ LENGKAP
**File:** `resources/views/reports/general-ledger.blade.php`

**Fitur:**
- ✅ Filter by account (dropdown)
- ✅ Filter by date range (from - to)
- ✅ Menampilkan opening balance
- ✅ Detail transaksi per akun:
  - Date
  - Journal Number
  - Description
  - Debit
  - Credit
  - Running Balance
- ✅ Source tracking (link ke source document)

**Status:** ✅ **SUDAH LENGKAP & BERFUNGSI**

---

### 3. **Profit & Loss Statement (Laba Rugi)** ✅ LENGKAP
**File:** `resources/views/reports/profit-loss.blade.php`

**Fitur:**
- ✅ Filter by date range (from - to)
- ✅ **Section Pendapatan:**
  - List semua akun revenue
  - Total Pendapatan
- ✅ **Section Beban:**
  - List semua akun expense
  - Total Beban
- ✅ **Laba/Rugi Bersih:**
  - Calculation: Total Pendapatan - Total Beban
  - Displayed prominently

**Query Logic:**
```php
- Ambil journal_lines untuk account type 'revenue' & 'expense'
- Revenue: Credit - Debit (normal balance credit)
- Expense: Debit - Credit (normal balance debit)
- Profit = Total Revenue - Total Expense
```

**Status:** ✅ **SUDAH LENGKAP & BERFUNGSI**

**Catatan:** Laporan ini sudah menampilkan **kutipan laba rugi** dengan breakdown per akun pendapatan dan beban.

---

### 4. **Balance Sheet (Neraca)** ✅ LENGKAP
**File:** `resources/views/reports/balance-sheet.blade.php`

**Fitur:**
- ✅ Filter by date (as of)
- ✅ **Section Aset:**
  - List semua akun asset
  - Total Aset
- ✅ **Section Kewajiban:**
  - List semua akun liability
  - Total Kewajiban
- ✅ **Section Ekuitas:**
  - List semua akun equity
  - Total Ekuitas

**Query Logic:**
```php
- Ambil opening balance (by year)
- Ambil movements sampai tanggal "as of"
- Balance = Opening + Debit - Credit
- Group by type: asset, liability, equity
```

**Layout:** 3 kolom grid (responsive)

**Status:** ✅ **SUDAH LENGKAP & BERFUNGSI**

**Catatan:** Laporan ini sudah menampilkan **kutipan neraca** dengan breakdown per akun aset, kewajiban, dan ekuitas.

---

## ❌ LAPORAN PAJAK YANG BELUM ADA

### 1. **Laporan PPN (Pajak Pertambahan Nilai)** ❌ BELUM ADA

**Yang Dibutuhkan:**

#### A. **Laporan PPN Keluaran (Output VAT)** 
Untuk pelaporan SPT Masa PPN

**Kolom yang dibutuhkan:**
- Tanggal Faktur
- Nomor Faktur Pajak
- Nama Customer (NPWP)
- DPP (Dasar Pengenaan Pajak)
- PPN (11%)
- Total

**Source Data:**
- Dari `invoices` yang sudah mark as sent
- Filter by month/period
- Ambil dari journal_lines dengan account PPN Keluaran

**Format Output:**
```
LAPORAN PPN KELUARAN
Periode: November 2025

No | Tgl Faktur | No. Faktur | Customer | NPWP | DPP | PPN 11% | Total
---|------------|------------|----------|------|-----|---------|-------
1  | 01/11/2025 | INV-2025-001 | PT ABC | 01.234.567.8-901.000 | 10,000,000 | 1,100,000 | 11,100,000
...

Total DPP: Rp 100,000,000
Total PPN Keluaran: Rp 11,000,000
```

---

#### B. **Laporan PPN Masukan (Input VAT)**
Untuk pelaporan SPT Masa PPN

**Kolom yang dibutuhkan:**
- Tanggal Faktur
- Nomor Faktur Pajak Vendor
- Nama Vendor (NPWP)
- DPP (Dasar Pengenaan Pajak)
- PPN (11%)
- Total

**Source Data:**
- Dari `vendor_bills` yang sudah received
- Dari `part_purchases`
- Filter by month/period
- Ambil dari journal_lines dengan account PPN Masukan

**Format Output:**
```
LAPORAN PPN MASUKAN
Periode: November 2025

No | Tgl Faktur | No. Faktur | Vendor | NPWP | DPP | PPN 11% | Total
---|------------|------------|--------|------|-----|---------|-------
1  | 05/11/2025 | VB-2025-001 | PT XYZ Vendor | 02.345.678.9-012.000 | 5,000,000 | 550,000 | 5,550,000
...

Total DPP: Rp 50,000,000
Total PPN Masukan: Rp 5,500,000
```

---

#### C. **Rekapitulasi PPN (Summary)**
**Format Output:**
```
REKAPITULASI PPN
Periode: November 2025

PPN Keluaran (Output VAT):     Rp 11,000,000
PPN Masukan (Input VAT):       Rp  5,500,000
                               ---------------
PPN Kurang/(Lebih) Bayar:      Rp  5,500,000
```

---

### 2. **Laporan PPh 23 (Pajak Penghasilan Pasal 23)** ❌ BELUM ADA

**Yang Dibutuhkan:**

#### A. **Laporan PPh 23 Dipotong (Sebagai Pemotong)**
Untuk pelaporan SPT Masa PPh 23 - saat perusahaan memotong PPh 23 dari vendor

**Kolom yang dibutuhkan:**
- Tanggal Transaksi
- Nomor Bukti Potong
- Nama Vendor (NPWP)
- Jenis Jasa
- DPP (Dasar Pengenaan Pajak)
- Tarif (2%)
- PPh 23 Dipotong
- Status Setor

**Source Data:**
- Dari `vendor_bills` dengan PPh 23
- Dari `part_purchases` dengan PPh 23
- Filter by month/period
- Ambil dari journal_lines dengan account Hutang PPh 23

**Format Output:**
```
LAPORAN PPh 23 DIPOTONG
Periode: November 2025

No | Tgl | No. Bukti | Vendor | NPWP | Jenis Jasa | DPP | Tarif | PPh 23 | Status
---|-----|-----------|--------|------|------------|-----|-------|--------|--------
1  | 05/11 | VB-2025-001 | PT XYZ | 02.345.678 | Jasa Angkutan | 5,000,000 | 2% | 100,000 | Belum Setor
...

Total DPP: Rp 50,000,000
Total PPh 23 Dipotong: Rp 1,000,000
Total Belum Disetor: Rp 1,000,000
Total Sudah Disetor: Rp 0
```

---

#### B. **Laporan PPh 23 Dipungut (Sebagai Penerima Potong)**
Untuk tracking PPh 23 yang dipotong customer dari invoice perusahaan

**Kolom yang dibutuhkan:**
- Tanggal Penerimaan
- Nomor Invoice
- Nama Customer (NPWP)
- DPP
- PPh 23 Dipotong Customer
- Status Bukti Potong

**Source Data:**
- Dari `cash_bank_transactions` dengan withholding_pph23 > 0
- Dari `invoices` dengan pph23_amount > 0 dan show_pph23 = true
- Filter by month/period
- Ambil dari journal_lines dengan account Piutang PPh 23

**Format Output:**
```
LAPORAN PPh 23 DIPUNGUT (Dipotong Customer)
Periode: November 2025

No | Tgl | No. Invoice | Customer | NPWP | DPP | PPh 23 | Bukti Potong
---|-----|-------------|----------|------|-----|--------|-------------
1  | 10/11 | INV-2025-001 | PT ABC | 01.234.567 | 10,000,000 | 200,000 | Sudah Diterima
...

Total DPP: Rp 100,000,000
Total PPh 23 Dipungut: Rp 2,000,000
Bukti Potong Diterima: Rp 2,000,000
Bukti Potong Belum Diterima: Rp 0
```

---

### 3. **Laporan Rekonsiliasi Pajak** ❌ BELUM ADA

**Yang Dibutuhkan:**

#### A. **Rekonsiliasi PPN**
Membandingkan PPN di sistem dengan SPT yang dilaporkan

**Format:**
```
REKONSILIASI PPN
Periode: November 2025

PPN Keluaran (Sistem):         Rp 11,000,000
PPN Keluaran (SPT):            Rp 11,000,000
Selisih:                       Rp          0

PPN Masukan (Sistem):          Rp  5,500,000
PPN Masukan (SPT):             Rp  5,500,000
Selisih:                       Rp          0

PPN Kurang Bayar (Sistem):     Rp  5,500,000
PPN Kurang Bayar (SPT):        Rp  5,500,000
Selisih:                       Rp          0
```

---

#### B. **Rekonsiliasi PPh 23**
Membandingkan PPh 23 di sistem dengan SPT yang dilaporkan

---

## 📋 REKOMENDASI IMPLEMENTASI

### **PRIORITAS TINGGI** 🔴

#### 1. **Laporan PPN** (Estimasi: 3-4 hari)

**File yang perlu dibuat:**
```
app/Http/Controllers/Accounting/TaxReportController.php
resources/views/reports/tax/ppn-keluaran.blade.php
resources/views/reports/tax/ppn-masukan.blade.php
resources/views/reports/tax/ppn-summary.blade.php
```

**Routes:**
```php
Route::prefix('reports/tax')->name('reports.tax.')->group(function () {
    Route::get('ppn-keluaran', [TaxReportController::class, 'ppnKeluaran'])->name('ppn-keluaran');
    Route::get('ppn-masukan', [TaxReportController::class, 'ppnMasukan'])->name('ppn-masukan');
    Route::get('ppn-summary', [TaxReportController::class, 'ppnSummary'])->name('ppn-summary');
});
```

**Query Logic:**
```php
// PPN Keluaran
$ppnKeluaran = DB::table('journal_lines as jl')
    ->join('journals as j', 'j.id', '=', 'jl.journal_id')
    ->join('chart_of_accounts as a', 'a.id', '=', 'jl.account_id')
    ->join('invoices as i', function($join) {
        $join->on('j.source_type', '=', DB::raw("'invoice'"))
             ->on('j.source_id', '=', 'i.id');
    })
    ->join('customers as c', 'c.id', '=', 'i.customer_id')
    ->where('a.code', '2100') // PPN Keluaran
    ->where('j.status', 'posted')
    ->whereBetween('j.journal_date', [$from, $to])
    ->select(
        'j.journal_date',
        'i.invoice_number',
        'c.name as customer_name',
        'c.npwp',
        'i.subtotal as dpp',
        'jl.credit as ppn',
        DB::raw('(i.subtotal + jl.credit) as total')
    )
    ->get();
```

---

#### 2. **Laporan PPh 23** (Estimasi: 3-4 hari)

**File yang perlu dibuat:**
```
resources/views/reports/tax/pph23-dipotong.blade.php
resources/views/reports/tax/pph23-dipungut.blade.php
resources/views/reports/tax/pph23-summary.blade.php
```

**Routes:**
```php
Route::get('pph23-dipotong', [TaxReportController::class, 'pph23Dipotong'])->name('pph23-dipotong');
Route::get('pph23-dipungut', [TaxReportController::class, 'pph23Dipungut'])->name('pph23-dipungut');
Route::get('pph23-summary', [TaxReportController::class, 'pph23Summary'])->name('pph23-summary');
```

**Query Logic:**
```php
// PPh 23 Dipotong (dari Vendor Bills)
$pph23Dipotong = DB::table('journal_lines as jl')
    ->join('journals as j', 'j.id', '=', 'jl.journal_id')
    ->join('chart_of_accounts as a', 'a.id', '=', 'jl.account_id')
    ->join('vendor_bills as vb', function($join) {
        $join->on('j.source_type', '=', DB::raw("'vendor_bill'"))
             ->on('j.source_id', '=', 'vb.id');
    })
    ->join('vendors as v', 'v.id', '=', 'vb.vendor_id')
    ->where('a.code', '2110') // Hutang PPh 23
    ->where('j.status', 'posted')
    ->whereBetween('j.journal_date', [$from, $to])
    ->select(
        'j.journal_date',
        'vb.vendor_bill_number',
        'v.name as vendor_name',
        'v.npwp',
        DB::raw('(jl.credit / 0.02) as dpp'), // DPP = PPh23 / 2%
        'jl.credit as pph23'
    )
    ->get();
```

---

### **PRIORITAS SEDANG** 🟡

#### 3. **Export to Excel** (Estimasi: 2 hari)
- Export semua laporan pajak ke Excel
- Gunakan Laravel Excel (maatwebsite/excel)
- Format sesuai template SPT

#### 4. **Dashboard Pajak** (Estimasi: 2 hari)
- Summary PPN & PPh 23 bulan berjalan
- Alert untuk jatuh tempo pelaporan
- Chart trend PPN & PPh 23

---

## 🎯 KESIMPULAN

### ✅ **Yang Sudah Lengkap:**
1. ✅ **Trial Balance** - Sudah lengkap dengan opening, debit, credit, closing
2. ✅ **General Ledger** - Sudah lengkap dengan running balance
3. ✅ **Profit & Loss** - **SUDAH MENAMPILKAN KUTIPAN LABA RUGI** dengan breakdown pendapatan & beban
4. ✅ **Balance Sheet** - **SUDAH MENAMPILKAN KUTIPAN NERACA** dengan breakdown aset, kewajiban, ekuitas
5. ✅ **Tax Handling** - PPN & PPh 23 sudah di-handle di journal posting

### ❌ **Yang Belum Ada:**
1. ❌ **Laporan PPN Keluaran** (Output VAT Report)
2. ❌ **Laporan PPN Masukan** (Input VAT Report)
3. ❌ **Rekapitulasi PPN** (VAT Summary)
4. ❌ **Laporan PPh 23 Dipotong** (Withholding Tax Report - as Withholder)
5. ❌ **Laporan PPh 23 Dipungut** (Withholding Tax Report - as Recipient)
6. ❌ **Rekonsiliasi Pajak** (Tax Reconciliation)
7. ❌ **Export to Excel** untuk laporan pajak

### 📊 **Status Laporan Keuangan:**
- **Neraca (Balance Sheet):** ✅ **SUDAH BISA MENAMPILKAN KUTIPAN NERACA**
- **Laba Rugi (P&L):** ✅ **SUDAH BISA MENAMPILKAN KUTIPAN LABA RUGI**
- **Trial Balance:** ✅ Lengkap
- **General Ledger:** ✅ Lengkap

### 📋 **Rekomendasi:**
**Fokus pada implementasi Laporan Pajak** karena:
1. Laporan keuangan dasar sudah lengkap ✅
2. Laporan pajak dibutuhkan untuk compliance (SPT Masa)
3. Data pajak sudah ada di sistem, tinggal buat report view
4. Estimasi total: **6-8 hari** untuk semua laporan pajak

---

**Dibuat oleh:** AI Assistant  
**Tanggal:** 21 November 2025  
**Versi:** 1.0
