### PROMPT ACC-3 – Engine Posting Otomatis dari TMS ke Jurnal

Sekarang saya ingin setiap event penting di TMS **otomatis membuat jurnal**.

**EVENT YANG HARUS DIPOSTING:**
1. Penerbitan Invoice Customer (status invoice = sent/confirmed).
   - Dr Piutang Usaha
   - Cr Pendapatan Jasa Angkut

2. Penerimaan Pembayaran Invoice:
   - Dr Kas/Bank
   - Cr Piutang Usaha

3. Penerbitan Vendor Bill:
   - Dr Beban Jasa Vendor (atau akun biaya lain sesuai mapping)
   - Cr Hutang Usaha

4. Pembayaran Vendor:
   - Dr Hutang Usaha
   - Cr Kas/Bank

5. Biaya Operasional via modul Kas/Bank:
   - Dr akun biaya (mis: Beban Bahan Bakar, Beban Tol, Beban Administrasi & Umum)
   - Cr Kas/Bank

**TUGAS ANDA:**
1. Buat service class, misalnya:
   - `App\Services\Accounting\JournalService`
   dengan fungsi:
   - `postInvoice(Invoice $invoice)`
   - `postCustomerPayment(CashBankTransaction $trx)`
   - `postVendorBill(VendorBill $bill)`
   - `postVendorPayment(CashBankTransaction $trx)`
   - `postExpense(CashBankTransaction $trx)`

2. Di setiap fungsi:
   - Ambil akun-akun yang terkait dari Chart of Accounts:
     - Bisa pakai konfigurasi mapping sementara di config (misal `config/account_mapping.php`) atau tabel mapping khusus.
   - Buat `Journal` baru:
     - nomor_jurnal (bisa auto-generate, mis: `JNL-YYYYMMDD-XXXX`),
     - tanggal (pakai tanggal invoice/transaksi),
     - sumber (mis: `invoice`, `vendor_bill`, `cashbank`),
     - reference_id (id invoice/bill/transaksi),
     - deskripsi singkat.
   - Buat beberapa `JournalLine` sesuai aturan debit/kredit.

3. Integrasikan ke event/flow:
   - Saat Invoice diubah status menjadi `sent` atau `confirmed`, panggil `JournalService::postInvoice($invoice)`.
   - Saat `CashBankTransaction` dengan sumber `customer_payment` dibuat → `postCustomerPayment`.
   - Saat VendorBill di-confirm → `postVendorBill`.
   - Saat `CashBankTransaction` dengan sumber `vendor_payment` → `postVendorPayment`.
   - Saat `CashBankTransaction` dengan sumber `expense` → `postExpense`.

4. Jelaskan di mana sebaiknya saya panggil service ini:
   - Apakah di Observer (model events), di Controller, atau di Service lain.
   - Berikan contoh implementasi menggunakan Model Observer atau pemanggilan langsung di Controller.

5. Berikan kode:
   - Isi `JournalService` lengkap (class + method).
   - Contoh penggunaan di:
     - `Invoice` Observer atau `InvoiceController`.
     - `VendorBill` Observer atau `VendorBillController`.
     - `CashBankTransaction` Observer atau `CashBankController`.

Gunakan Laravel 12 style dan buat kode yang rapi, bisa dibaca, dan mudah dikembangkan.
