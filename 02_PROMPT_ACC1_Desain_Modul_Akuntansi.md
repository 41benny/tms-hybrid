### PROMPT ACC-1 – Desain Modul Akuntansi & Struktur Tabel

Sekarang saya ingin modul **Akuntansi** yang lengkap terintegrasi dengan TMS ini.

**TUJUAN:**
Membangun pondasi akuntansi sehingga sistem bisa menghasilkan:
- Neraca (Balance Sheet)
- Laba Rugi (Profit & Loss)
- Trial Balance (Neraca Saldo)
- Buku Besar (General Ledger)
- Laporan arus kas (minimal ringkasan)

**KONTEKS:**
- Event dari modul operasional:
  - Penerbitan Invoice ke customer (penjualan/jasa angkut).
  - Penerimaan pembayaran invoice.
  - Penerbitan Vendor Bill (tagihan vendor).
  - Pembayaran vendor.
  - Biaya-biaya lain di Kas/Bank.
- Semua ini harus menghasilkan **jurnal otomatis**.

**TUGAS ANDA:**
1. Rancang struktur tabel utama akuntansi:
   - `chart_of_accounts`
   - `journals`
   - `journal_lines`
   - `fiscal_periods` (opsional)
   - `opening_balances` (opsional)
2. Untuk masing-masing tabel, jelaskan kolom-kolom penting, misalnya:
   - `chart_of_accounts`:
     - code, name, type (asset, liability, equity, revenue, expense), parent_id, is_active, level.
   - `journals`:
     - tanggal, nomor_jurnal, sumber (invoice, vendor_bill, cashbank, manual), reference_id, deskripsi.
   - `journal_lines`:
     - journal_id, account_id, debit, credit, keterangan, atribut tambahan (customer_id, vendor_id, job_order_id, truck_id, dll bila perlu).
3. Jelaskan skema relasi:
   - ChartOfAccount hasMany JournalLines.
   - Journal hasMany JournalLines.
   - JournalLine belongsTo ChartOfAccount & Journal.
4. Buat daftar contoh akun COA minimal yang relevan:
   - Kas, Bank
   - Piutang Usaha
   - Hutang Usaha
   - Pendapatan Jasa Angkut
   - Beban Bahan Bakar
   - Beban Tol
   - Beban Gaji/Upah Supir
   - Beban Jasa Vendor
   - Beban Administrasi & Umum
   - Modal / Ekuitas
5. Buat **daftar aturan posting** (tanpa coding dulu) untuk event:
   - Invoice ke customer (penjualan jasa).
   - Penerimaan pembayaran invoice.
   - Vendor bill (tagihan vendor).
   - Pembayaran vendor.
   - Biaya operasional lain di kas/bank.

Jawab dalam bentuk desain dan spesifikasi yang jelas, JANGAN langsung tulis migration di prompt ini.
