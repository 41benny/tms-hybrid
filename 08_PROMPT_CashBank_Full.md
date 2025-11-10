### PROMPT CB-1 – Kas/Bank Lengkap + Hutang/Piutang + Biaya Admin + Akuntansi

Anda adalah AI Coding Agent untuk proyek Laravel 12 TMS + Akuntansi saya.

Saya ingin **modul Kas/Bank yang lengkap**, terintegrasi dengan:
- Pelunasan hutang vendor
- Penerimaan piutang customer
- Pengeluaran biaya administrasi & operasional
- Auto-posting ke modul akuntansi (journal, chart of accounts)

STACK:
- Laravel 12
- MySQL
- Blade + Tailwind (dark mode, layout sudah ada dari prompt sebelumnya)
- Modul lain sudah Anda bangun: JobOrder, Transport, Invoice, VendorBill, Accounting (COA, Journal, JournalLine).

---

## 1. Struktur Tabel Kas/Bank

1. Buat tabel: `cash_bank_accounts`
   - id
   - name (mis: "Kas Besar", "Rekening BCA Operasional")
   - code (opsional)
   - is_active (boolean)
   - created_at, updated_at

2. Buat tabel: `cash_bank_transactions`
   - id
   - cash_bank_account_id (relasi ke cash_bank_accounts)
   - tanggal
   - jenis: enum/string (`cash_in`, `cash_out`)
   - sumber: enum/string (`customer_payment`, `vendor_payment`, `expense`, `other_in`, `other_out`)
   - invoice_id (nullable, jika penerimaan dari invoice customer)
   - vendor_bill_id (nullable, jika pembayaran vendor)
   - coa_id (nullable, akun biaya atau akun lain bila transaksi manual)
   - customer_id (nullable)
   - vendor_id (nullable)
   - amount
   - reference_number (no bukti / no giro / dsb)
   - description (nullable)
   - created_at, updated_at

3. Buat migration dan model:
   - `CashBankAccount` dan `CashBankTransaction`
   - Relasi:
     - CashBankAccount `hasMany` CashBankTransaction
     - CashBankTransaction `belongsTo` CashBankAccount, Invoice, VendorBill, Customer, Vendor, ChartOfAccount.

4. Tambahkan index pada kolom:
   - `cash_bank_account_id`, `invoice_id`, `vendor_bill_id`, `tanggal`, `coa_id`, `customer_id`, `vendor_id`.

---

## 2. Flow Penerimaan Piutang Customer

1. Tambahkan fitur di `InvoiceController`:
   - Tombol "Terima Pembayaran" di halaman `invoices/show.blade.php`.
   - Form (boleh modal) berisi:
     - tanggal
     - pilih akun kas/bank (`cash_bank_account_id`)
     - jumlah (default = sisa piutang invoice, tetapi boleh diubah untuk pembayaran sebagian)
     - reference_number
     - description

2. Saat form disubmit:
   - Buat record `CashBankTransaction`:
     - jenis = `cash_in`
     - sumber = `customer_payment`
     - invoice_id = id invoice
     - customer_id = invoice->customer_id
     - amount = nominal dibayar
     - cash_bank_account_id = dari form
   - Update status invoice:
     - Jika jumlah pembayaran >= sisa piutang → status `paid`.
     - Kalau < total → status `partially_paid` dan simpan sisa piutang.

3. Integrasi ke modul akuntansi (jika JournalService sudah ada):
   - Panggil misalnya: `JournalService::postCustomerPayment($cashBankTransaction);`

---

## 3. Flow Pelunasan Hutang Vendor

1. Tambahkan fitur di `VendorBillController`:
   - Tombol "Bayar Vendor" di halaman `vendor-bills/show.blade.php`.
   - Form modal:
     - tanggal
     - pilih akun kas/bank
     - jumlah (default = sisa hutang)
     - reference_number
     - description

2. Saat disubmit:
   - Buat `CashBankTransaction`:
     - jenis = `cash_out`
     - sumber = `vendor_payment`
     - vendor_bill_id = id vendor bill
     - vendor_id = vendor_bill->vendor_id
     - amount = nominal dibayar
     - cash_bank_account_id = dari form
   - Update status vendor bill:
     - Jika jumlah pembayaran >= sisa hutang → status `paid`.
     - Kalau < total → `partially_paid`.

3. Integrasi ke akuntansi:
   - Panggil `JournalService::postVendorPayment($cashBankTransaction);`

---

## 4. Flow Pengeluaran Biaya Administrasi & Operasional

1. Tambahkan halaman untuk input pengeluaran biaya di `CashBankController`:
   - Form create:
     - tanggal
     - pilih akun kas/bank
     - sumber (set default ke `expense`)
     - pilih akun biaya dari COA (`coa_id`), contoh: Beban Administrasi & Umum, Beban Bahan Bakar, Beban Tol, dll.
     - optional: relasi tambahan (job_order_id, truck_id, driver_id) kalau mau tracking per proyek.
     - amount
     - description

2. Saat disubmit:
   - Buat `CashBankTransaction` dengan:
     - jenis = `cash_out`
     - sumber = `expense`
     - coa_id = akun biaya dari COA

3. Integrasi ke akuntansi:
   - Panggil `JournalService::postExpense($cashBankTransaction);`

---

## 5. Halaman Index & Detail Kas/Bank

1. Buat `CashBankController`:
   - method:
     - `index`: list transaksi kas/bank, filter:
       - by account
       - by date range
       - by sumber (customer_payment, vendor_payment, expense, dll)
     - `create`/`store` untuk transaksi manual (other_in/other_out).
     - `show`: detail satu transaksi (tampilkan link ke invoice/vendor_bill jika ada).

2. Buat Blade view:
   - `cash-banks/index.blade.php`:
     - Extend layout dark mode.
     - Bagian atas: dropdown akun kas/bank, date range, filter sumber.
     - Card ringkasan:
       - Total cash in periode
       - Total cash out periode
       - Saldo awal, saldo akhir (boleh simple dulu).
     - Tabel transaksi.
   - `cash-banks/create.blade.php`:
     - Form input transaksi manual (other_in/other_out/expense).

---

## 6. Integrasi dengan JournalService

Asumsikan sebelumnya sudah ada `App\Services\Accounting\JournalService` dengan fungsi:
- `postInvoice(Invoice $invoice)`
- `postCustomerPayment(CashBankTransaction $trx)`
- `postVendorBill(VendorBill $bill)`
- `postVendorPayment(CashBankTransaction $trx)`
- `postExpense(CashBankTransaction $trx)`

Tolong:
1. Pastikan pemanggilan JournalService ditambahkan di:
   - `InvoiceController` → saat invoice di-confirm → `postInvoice`.
   - CashBank transaksi penerimaan customer → `postCustomerPayment`.
   - `VendorBillController` → saat vendor bill di-confirm → `postVendorBill`.
   - CashBank transaksi pembayaran vendor → `postVendorPayment`.
   - CashBank transaksi expense → `postExpense`.

2. Berikan contoh kode lengkap:
   - Potongan `InvoiceController` (function untuk terima pembayaran).
   - Potongan `VendorBillController` (function untuk bayar vendor).
   - `CashBankController` (store untuk transaksi expense & other in/out).
   - View Blade yang diperlukan (index, form, modal di invoice/vendor bill).

Berikan jawaban dalam:
- Kode migration
- Model
- Controller (fungsi kunci)
- View Blade
- Penjelasan singkat di komentar

Semua dalam bahasa Indonesia & UI tetap dark mode modern (Tailwind).
