### PROMPT 5 – Invoicing ke Customer & Tagihan Vendor

Sekarang kita bangun modul **Keuangan dasar**: invoicing ke customer dan tagihan vendor.

**KONTEKS:**
- Customer:
  - Ditagih berdasarkan Job Order (bisa per Job Order atau per unit/alat).
- Vendor:
  - Mengirim tagihan ke kita (per transport atau per job) jika kita pakai vendor.

**TUGAS ANDA:**
1. Buat migration + model untuk:
   - `invoices` (tagihan ke customer)
   - `invoice_items`
   - `vendor_bills` (tagihan dari vendor)
   - `vendor_bill_items`

2. Struktur minimal:
   - `invoices`:
     - `id`
     - `customer_id`
     - `invoice_number` (unik, contoh: `INV-YYYYMM-XXXX`)
     - `invoice_date`
     - `due_date`
     - `total_amount`
     - `status`: `draft`, `sent`, `partially_paid`, `paid`, `cancelled`
     - `notes` (nullable)
     - timestamps
   - `invoice_items`:
     - `id`
     - `invoice_id`
     - `job_order_id` (nullable)
     - `transport_id` (nullable)
     - `description`
     - `qty`
     - `unit_price`
     - `subtotal`
     - timestamps
   - `vendor_bills`:
     - `id`
     - `vendor_id`
     - `vendor_bill_number`
     - `bill_date`
     - `due_date`
     - `total_amount`
     - `status`: `draft`, `received`, `partially_paid`, `paid`, `cancelled`
     - `notes` (nullable)
     - timestamps
   - `vendor_bill_items`:
     - `id`
     - `vendor_bill_id`
     - `transport_id` (relasi ke transport yang dikerjakan vendor)
     - `description`
     - `qty`
     - `unit_price`
     - `subtotal`
     - timestamps

3. Buat controller:
   - `InvoiceController`
   - `VendorBillController`
   dengan method standar CRUD + perhitungan total otomatis dari items (total_amount = sum(subtotal)).

4. Buat Blade view:
   - `invoices/index.blade.php`:
     - filter by customer, status, periode.
   - `invoices/create.blade.php` & `edit.blade.php`:
     - bisa pilih Job Order/Transport yang belum pernah difaktur, lalu auto-isi item (boleh sederhana dulu).
   - `invoices/show.blade.php`:
     - detail invoice + status + tombol aksi (kirim, terima pembayaran).
   - `vendor-bills/index.blade.php`:
     - filter by vendor, status, periode.
   - `vendor-bills/create.blade.php` & `edit.blade.php`:
     - pilih transport vendor yang belum pernah ditagihkan.
   - `vendor-bills/show.blade.php`.

5. Tambahkan fungsi sederhana untuk:
   - Hitung `total_amount` berdasarkan penjumlahan `subtotal` item.
   - Tombol "Mark as Paid" / "Mark as Sent" untuk invoice.
   - Tombol "Mark as Paid" / "Mark as Received" untuk vendor bill.

Tulis kodenya lengkap, termasuk route & relasi model tambahan yang dibutuhkan. Gunakan Tailwind + dark mode layout.
