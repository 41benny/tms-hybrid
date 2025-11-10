### PROMPT ACC-4 – Laporan Keuangan (Trial Balance, GL, Laba Rugi, Neraca)

Sekarang saya ingin laporan keuangan yang bisa dijalankan dari modul akuntansi ini.

**LAPORAN YANG DIBUTUHKAN:**
1. Trial Balance (Neraca Saldo)
2. Buku Besar (General Ledger) per akun
3. Laba Rugi (Profit & Loss) per periode
4. Neraca (Balance Sheet) per tanggal

**TUGAS ANDA:**
1. Buat controller `ReportAccountingController` dengan method:
   - `trialBalance(Request $request)`
   - `generalLedger(Request $request)`
   - `profitLoss(Request $request)`
   - `balanceSheet(Request $request)`

2. Filtering:
   - Semua laporan minimal bisa filter:
     - Periode (tanggal_awal, tanggal_akhir).
   - GL:
     - Pilih akun (account_id).

3. Logika per laporan:
   - Trial Balance:
     - Sum debit & credit per account dalam periode.
     - Tampilkan saldo awal (jika ada opening balance), total debit, total credit, saldo akhir.
   - General Ledger:
     - List jurnal per akun (tanggal, nomor jurnal, deskripsi, debit, credit, saldo berjalan).
   - Profit & Loss:
     - Ambil akun type `revenue` dan `expense`, hitung total dan selisih (laba/rugi) dalam periode.
   - Balance Sheet:
     - Ambil akun type `asset`, `liability`, `equity`, dan hitung saldo per tanggal tertentu.

4. Buat Blade view:
   - `reports/trial-balance.blade.php`
   - `reports/general-ledger.blade.php`
   - `reports/profit-loss.blade.php`
   - `reports/balance-sheet.blade.php`
   Semua:
   - Extend layout dark mode utama.
   - Tabel rapi, pakai format angka (dipisah ribuan).
   - Ringkasan di header (periode, total, dsb).

5. Jelaskan secara singkat (boleh di komentar) bagaimana cara optimasi query, misalnya:
   - Memakai `groupBy` di query builder untuk trial balance.
   - Menghindari N+1 query dengan eager loading bila perlu.
   - Bisa gunakan view database atau query builder khusus untuk performa.

Berikan kode lengkap:
- Controller
- Route yang diperlukan
- Blade view contoh (struktur tabel + penggunaan Tailwind dark mode)
Jawab dalam bahasa Indonesia.
)