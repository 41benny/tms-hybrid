### PROMPT ACC-2 – Migration & Model Akuntansi

Lanjutkan dari desain modul akuntansi (COA, jurnal, dll) yang sudah Anda buat di ACC-1.

**TUGAS ANDA:**
1. Buat migration + model untuk:
   - `chart_of_accounts`
   - `journals`
   - `journal_lines`
   - (opsional) `fiscal_periods`
   - (opsional) `opening_balances`

2. Untuk setiap tabel, gunakan struktur kolom yang sudah Anda jelaskan sebelumnya. Pastikan:
   - Tipe data sesuai (decimal untuk debit/credit).
   - Index pada kolom yang sering di-filter (account_id, tanggal, type).
   - Ada kolom `created_at`, `updated_at`.

3. Buat model Eloquent:
   - `ChartOfAccount.php`
   - `Journal.php`
   - `JournalLine.php`
   beserta relasi:
   - ChartOfAccount `hasMany` JournalLine.
   - Journal `hasMany` JournalLine.
   - JournalLine `belongsTo` ChartOfAccount dan `belongsTo` Journal.

4. Buat seeder sederhana:
   - `ChartOfAccountsSeeder` berisi set minimal akun:
     - Kas, Bank, Piutang Usaha, Hutang Usaha,
     - Pendapatan Jasa Angkut,
     - Beban Bahan Bakar,
     - Beban Tol,
     - Beban Jasa Vendor,
     - Beban Administrasi & Umum,
     - Modal / Ekuitas, dll.
   - Berikan contoh kode seeder dengan beberapa data awal (code + name + type).

5. Tulis kode:
   - file migration lengkap,
   - file model lengkap dengan `$fillable` dan relasi,
   - file seeder, serta cara mendaftarkannya di `DatabaseSeeder`.

Tulis dengan format siap copy-paste ke project Laravel saya.
