# Database Reset - Transactional Data

Script ini digunakan untuk mereset semua data transaksi/inputan sambil mempertahankan data master dan user accounts.

## ⚠️ PERINGATAN PENTING

**BACKUP DATABASE ANDA SEBELUM MENJALANKAN SCRIPT INI!**

Script ini akan **MENGHAPUS PERMANEN** semua data transaksi. Operasi ini **TIDAK DAPAT DIBATALKAN** tanpa restore dari backup.

## Data yang AKAN DIHAPUS ❌

### Accounting & Finance
- ✗ Journals & Journal Lines
- ✗ Opening Balances
- ✗ Invoices & Invoice Items
- ✗ Invoice Transaction Payments
- ✗ Payment Receipts
- ✗ Vendor Bills & Vendor Bill Items
- ✗ Vendor Bill Payments
- ✗ Cash Bank Transactions
- ✗ Tax Invoice Requests

### System
- ✗ Notifications (all user notifications will be cleared)

### Operations
- ✗ Job Orders & Job Order Items
- ✗ Shipment Legs
- ✗ Leg Main Costs & Leg Additional Costs
- ✗ Transports & Transport Costs
- ✗ Driver Advances & Driver Advance Payments
- ✗ Payment Requests

### Inventory
- ✗ Part Purchases & Part Purchase Items
- ✗ Part Usages & Part Usage Items
- ✗ Part Stocks (inventory levels will be reset to 0)

### Fixed Assets
- ✗ Asset Depreciations
- ✗ Asset Disposals

## Data yang TIDAK AKAN DIHAPUS ✓

### Master Data
- ✓ Customers
- ✓ Vendors & Vendor Bank Accounts
- ✓ Drivers
- ✓ Trucks
- ✓ Routes
- ✓ Equipment
- ✓ Sales
- ✓ Parts (master data)
- ✓ Fixed Assets (master data)

### System Data
- ✓ Users & Roles
- ✓ Chart of Accounts
- ✓ Cash Bank Accounts
- ✓ Fiscal Periods
- ✓ Menus

## Cara Menggunakan

### 1. Backup Database

**WAJIB!** Backup database Anda terlebih dahulu:

```bash
# Menggunakan mysqldump
mysqldump -u root -p tms_hybrid > backup_tms_hybrid_$(date +%Y%m%d_%H%M%S).sql

# Atau menggunakan Laragon/phpMyAdmin
# Export database melalui interface
```

### 2. Jalankan Reset Script

```bash
php artisan db:seed --class=ResetTransactionalDataSeeder
```

### 3. Konfirmasi

Script akan meminta konfirmasi 2 kali:
1. Apakah Anda sudah backup database?
2. Apakah Anda yakin ingin melanjutkan?

Jawab **yes** untuk kedua pertanyaan jika Anda sudah yakin.

### 4. Verifikasi Hasil

Setelah selesai, script akan menampilkan:
- Jumlah tabel yang berhasil di-clear
- Daftar tabel yang sudah dihapus
- Konfirmasi data master yang dipertahankan

## Verifikasi Manual

Setelah menjalankan script, lakukan verifikasi berikut:

### ✓ Cek Master Data Masih Ada
```sql
SELECT COUNT(*) FROM customers;
SELECT COUNT(*) FROM vendors;
SELECT COUNT(*) FROM drivers;
SELECT COUNT(*) FROM trucks;
SELECT COUNT(*) FROM users;
SELECT COUNT(*) FROM chart_of_accounts;
```

### ✓ Cek Data Transaksi Sudah Kosong
```sql
SELECT COUNT(*) FROM job_orders;      -- Harus 0
SELECT COUNT(*) FROM invoices;        -- Harus 0
SELECT COUNT(*) FROM journals;        -- Harus 0
SELECT COUNT(*) FROM cash_bank_transactions; -- Harus 0
```

### ✓ Test Sistem
1. Login ke aplikasi
2. Buat Job Order baru
3. Pastikan tidak ada error
4. Pastikan nomor urut dimulai dari awal

## Membersihkan Notifikasi Orphaned

Jika Anda sudah terlanjur reset data tapi notifikasi masih muncul, jalankan command berikut:

```bash
php artisan notifications:clean-orphaned
```

Command ini akan:
- Scan semua notifikasi di database
- Cek apakah data yang direferensikan masih ada
- Hapus notifikasi yang datanya sudah tidak ada

> [!TIP]
> Setelah reset database dengan seeder, notifikasi akan otomatis terhapus. Command ini hanya diperlukan jika Anda reset data dengan cara lain.

## Troubleshooting

### Error: Foreign Key Constraint

Jika terjadi error foreign key constraint:
1. Pastikan tidak ada proses lain yang mengakses database
2. Coba jalankan ulang script
3. Jika masih error, restore dari backup dan hubungi developer

### Error: Table Does Not Exist

Script akan otomatis skip tabel yang tidak ada. Ini normal jika Anda belum menjalankan semua migration.

### Ingin Membatalkan

Jika Anda sudah terlanjur menjalankan script:
1. Restore database dari backup:
```bash
mysql -u root -p tms_hybrid < backup_tms_hybrid_YYYYMMDD_HHMMSS.sql
```

## Kapan Menggunakan Script Ini?

✓ **Gunakan script ini ketika:**
- Ingin memulai fresh dengan data master yang sama
- Testing atau development
- Setelah import data master baru
- Membersihkan data testing/dummy

✗ **JANGAN gunakan script ini ketika:**
- Di production server tanpa backup
- Masih ada data transaksi yang diperlukan
- Belum yakin 100%

## Kontak

Jika ada pertanyaan atau masalah, hubungi developer team.
