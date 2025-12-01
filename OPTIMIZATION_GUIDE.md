# 🚀 TMS Hybrid - Panduan Optimasi Aplikasi

> **Catatan Penting:** Panduan ini berisi langkah-langkah optimasi yang AMAN dan TIDAK merusak kode yang sudah berjalan. Jalankan satu per satu dan test setelah setiap langkah.

---

## 📋 Daftar Isi

1. [Cache Optimization](#1-cache-optimization) ⭐ **PALING AMAN & EFEKTIF**
2. [Database Indexing](#2-database-indexing) ⭐ **SANGAT DIREKOMENDASIKAN**
3. [Database Cleanup](#3-database-cleanup)
4. [Eager Loading Optimization](#4-eager-loading-optimization)
5. [Asset Optimization](#5-asset-optimization)
6. [Queue System](#6-queue-system-advanced)
7. [Performance Monitoring](#7-performance-monitoring)

---

## 1. Cache Optimization ⭐

**Manfaat:** Aplikasi 2-3x lebih cepat  
**Risiko:** Sangat rendah  
**Waktu:** 5 menit  

### Cara Jalankan:

```bash
# Masuk ke folder aplikasi
cd /home/vintamal/apps/tms-hybrid

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# (Opsional) Cache events
php artisan event:cache
```

### Kapan Perlu Clear Cache?

**Setiap kali update kode**, jalankan:

```bash
# Clear semua cache
php artisan optimize:clear

# Atau clear satu-satu:
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

### Cara Test:

1. Buka halaman dashboard
2. Refresh beberapa kali
3. Harusnya terasa lebih cepat

---

## 2. Database Indexing ⭐

**Manfaat:** Query database 5-10x lebih cepat  
**Risiko:** Sangat rendah (hanya menambah index, tidak mengubah data)  
**Waktu:** 10-15 menit  

### Langkah 1: Buat Migration

```bash
php artisan make:migration add_performance_indexes_to_tables
```

### Langkah 2: Edit Migration

Buka file migration yang baru dibuat di `database/migrations/`, lalu isi dengan:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Job Orders - sering di-filter by status, customer, tanggal
        Schema::table('job_orders', function (Blueprint $table) {
            $table->index('status');
            $table->index('customer_id');
            $table->index('job_date');
            $table->index(['customer_id', 'status']);
        });

        // Invoices - sering di-filter by customer, status, tanggal
        Schema::table('invoices', function (Blueprint $table) {
            $table->index('customer_id');
            $table->index('status');
            $table->index('invoice_date');
            $table->index('due_date');
            $table->index(['customer_id', 'status']);
        });

        // Vendor Bills - sering di-filter by vendor, status
        Schema::table('vendor_bills', function (Blueprint $table) {
            $table->index('vendor_id');
            $table->index('status');
            $table->index('bill_date');
            $table->index(['vendor_id', 'status']);
        });

        // Cash Bank Transactions - sering di-filter by tanggal, tipe
        Schema::table('cash_bank_transactions', function (Blueprint $table) {
            $table->index('tanggal');
            $table->index('transaction_type');
            $table->index('bank_account_id');
            $table->index(['tanggal', 'transaction_type']);
        });

        // Journals - sering di-filter by tanggal, status, source
        Schema::table('journals', function (Blueprint $table) {
            $table->index('journal_date');
            $table->index('status');
            $table->index(['source_type', 'source_id']);
            $table->index(['journal_date', 'status']);
        });

        // Journal Lines - sering di-join dan di-filter
        Schema::table('journal_lines', function (Blueprint $table) {
            $table->index('journal_id');
            $table->index('account_id');
            $table->index('vendor_id');
            $table->index('customer_id');
            $table->index('job_order_id');
            $table->index(['journal_id', 'account_id']);
        });

        // Shipment Legs - sering di-filter by job_order, status
        Schema::table('shipment_legs', function (Blueprint $table) {
            $table->index('job_order_id');
            $table->index('status');
            $table->index('load_date');
            $table->index('driver_id');
            $table->index('truck_id');
        });

        // Driver Advances - sering di-filter by status, driver
        Schema::table('driver_advances', function (Blueprint $table) {
            $table->index('driver_id');
            $table->index('status');
            $table->index('journal_status');
            $table->index('advance_date');
        });
    }

    public function down(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['customer_id']);
            $table->dropIndex(['job_date']);
            $table->dropIndex(['customer_id', 'status']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['invoice_date']);
            $table->dropIndex(['due_date']);
            $table->dropIndex(['customer_id', 'status']);
        });

        Schema::table('vendor_bills', function (Blueprint $table) {
            $table->dropIndex(['vendor_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['bill_date']);
            $table->dropIndex(['vendor_id', 'status']);
        });

        Schema::table('cash_bank_transactions', function (Blueprint $table) {
            $table->dropIndex(['tanggal']);
            $table->dropIndex(['transaction_type']);
            $table->dropIndex(['bank_account_id']);
            $table->dropIndex(['tanggal', 'transaction_type']);
        });

        Schema::table('journals', function (Blueprint $table) {
            $table->dropIndex(['journal_date']);
            $table->dropIndex(['status']);
            $table->dropIndex(['source_type', 'source_id']);
            $table->dropIndex(['journal_date', 'status']);
        });

        Schema::table('journal_lines', function (Blueprint $table) {
            $table->dropIndex(['journal_id']);
            $table->dropIndex(['account_id']);
            $table->dropIndex(['vendor_id']);
            $table->dropIndex(['customer_id']);
            $table->dropIndex(['job_order_id']);
            $table->dropIndex(['journal_id', 'account_id']);
        });

        Schema::table('shipment_legs', function (Blueprint $table) {
            $table->dropIndex(['job_order_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['load_date']);
            $table->dropIndex(['driver_id']);
            $table->dropIndex(['truck_id']);
        });

        Schema::table('driver_advances', function (Blueprint $table) {
            $table->dropIndex(['driver_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['journal_status']);
            $table->dropIndex(['advance_date']);
        });
    }
};
```

### Langkah 3: Jalankan Migration

```bash
php artisan migrate
```

### Cara Test:

1. Buka halaman Job Orders
2. Filter by customer atau status
3. Harusnya lebih cepat dari sebelumnya

### Rollback (Jika Ada Masalah):

```bash
php artisan migrate:rollback
```

---

## 3. Database Cleanup

**Manfaat:** Database lebih kecil dan cepat  
**Risiko:** Rendah  
**Waktu:** 5 menit  
**Frekuensi:** Bulanan  

### Cara Jalankan:

```bash
# Bersihkan notifikasi orphan
php artisan notifications:clean-orphaned

# Bersihkan session lama (jika pakai database session)
php artisan session:gc
```

### Setup Cron untuk Auto Cleanup (Opsional):

Tambahkan di `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    // Cleanup notifikasi orphan setiap minggu
    $schedule->command('notifications:clean-orphaned')
             ->weekly()
             ->sundays()
             ->at('02:00');
    
    // Cleanup session setiap hari
    $schedule->command('session:gc')
             ->daily()
             ->at('03:00');
}
```

---

## 4. Eager Loading Optimization

**Manfaat:** Mengurangi query dari ratusan jadi puluhan  
**Risiko:** Rendah (hanya mengubah cara query)  
**Waktu:** 30-60 menit  

### Contoh Optimasi:

#### Before (N+1 Problem):
```php
// Di InvoiceController
$invoices = Invoice::all(); // 1 query
foreach ($invoices as $invoice) {
    echo $invoice->customer->name; // N queries (1 per invoice)
}
// Total: 1 + N queries
```

#### After (Eager Loading):
```php
// Di InvoiceController
$invoices = Invoice::with('customer')->get(); // 2 queries total
foreach ($invoices as $invoice) {
    echo $invoice->customer->name; // Tidak ada query tambahan
}
// Total: 2 queries
```

### File yang Perlu Dioptimasi:

1. **InvoiceController@index**
   ```php
   $invoices = Invoice::with(['customer', 'items.jobOrder'])
       ->latest()
       ->paginate(20);
   ```

2. **JobOrderController@index**
   ```php
   $jobOrders = JobOrder::with(['customer', 'shipmentLegs.driver', 'shipmentLegs.truck'])
       ->latest()
       ->paginate(20);
   ```

3. **VendorBillController@index**
   ```php
   $vendorBills = VendorBill::with(['vendor', 'items'])
       ->latest()
       ->paginate(20);
   ```

4. **CashBankController@index**
   ```php
   $transactions = CashBankTransaction::with(['bankAccount', 'invoicePayments.invoice', 'vendorBillPayments.vendorBill'])
       ->latest()
       ->paginate(20);
   ```

### Cara Test:

Install Laravel Debugbar untuk melihat jumlah query:

```bash
composer require barryvdh/laravel-debugbar --dev
```

Lihat di bagian bawah halaman, harusnya jumlah query berkurang drastis.

---

## 5. Asset Optimization

**Manfaat:** Halaman load 30-50% lebih cepat  
**Risiko:** Rendah  
**Waktu:** 10 menit  

### Cara Jalankan:

```bash
# Development
npm run dev

# Production (minify & compress)
npm run build
```

### Setup Auto-compile di Production:

Tambahkan di deployment script atau jalankan manual setiap deploy:

```bash
npm ci --production
npm run build
```

---

## 6. Queue System (Advanced)

**Manfaat:** User tidak perlu menunggu proses lama  
**Risiko:** Medium (perlu setup queue worker)  
**Waktu:** 1-2 jam  

### Proses yang Bisa Di-queue:

1. Generate PDF Invoice
2. Send Email Notifications
3. Export Laporan Besar
4. Posting Journal Batch

### Setup Queue:

#### 1. Update `.env`:
```env
QUEUE_CONNECTION=database
```

#### 2. Buat Queue Table:
```bash
php artisan queue:table
php artisan migrate
```

#### 3. Buat Job:
```bash
php artisan make:job GenerateInvoicePdf
```

#### 4. Setup Queue Worker di Supervisor (cPanel):

Tambahkan cron job baru:
```bash
* * * * * cd /home/vintamal/apps/tms-hybrid && php artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

### Contoh Penggunaan:

```php
// Sebelum
$pdf = $invoice->generatePdf(); // User menunggu 5-10 detik

// Sesudah
GenerateInvoicePdf::dispatch($invoice); // Instant, jalan di background
```

---

## 7. Performance Monitoring

### Install Laravel Telescope (Development Only):

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

Akses di: `http://yourdomain.com/telescope`

### Monitor Query Performance:

Lihat query yang lambat di Telescope → Queries, lalu tambahkan index untuk kolom yang sering di-query.

---

## 📊 Checklist Optimasi

### Priority 1 (Lakukan Sekarang):
- [ ] Cache Optimization
- [ ] Database Indexing
- [ ] Database Cleanup

### Priority 2 (Lakukan Minggu Depan):
- [ ] Eager Loading Optimization
- [ ] Asset Optimization

### Priority 3 (Lakukan Bulan Depan):
- [ ] Queue System
- [ ] Performance Monitoring

---

## 🆘 Troubleshooting

### Cache Tidak Berfungsi?
```bash
php artisan optimize:clear
php artisan config:cache
```

### Migration Gagal?
```bash
php artisan migrate:rollback
# Fix error
php artisan migrate
```

### Aplikasi Error Setelah Optimasi?
```bash
# Clear semua cache
php artisan optimize:clear

# Rollback migration terakhir
php artisan migrate:rollback

# Restart queue worker (jika pakai queue)
php artisan queue:restart
```

---

## 📝 Catatan Penting

1. **Selalu backup database** sebelum jalankan migration
2. **Test di local** dulu sebelum apply ke production
3. **Jalankan satu optimasi per waktu**, jangan sekaligus
4. **Monitor performa** setelah setiap optimasi
5. **Dokumentasikan** perubahan yang dilakukan

---

## 🎯 Target Performance

### Sebelum Optimasi:
- Page Load: 2-5 detik
- Database Queries: 50-200 per page
- Memory Usage: 50-100 MB

### Setelah Optimasi:
- Page Load: 0.5-1.5 detik ⚡
- Database Queries: 5-20 per page ⚡
- Memory Usage: 30-50 MB ⚡

---

**Dibuat:** 2025-12-01  
**Terakhir Update:** 2025-12-01  
**Status:** Ready to implement  

**Catatan:** Jalankan pelan-pelan, satu per satu, dan test setelah setiap langkah! 🚀
