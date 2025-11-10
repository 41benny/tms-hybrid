### PROMPT UI-2 – Halaman TMS + Finance dengan Dark Mode

Lanjutkan UI dark mode yang sudah Anda buat sebelumnya (layout & komponen dasar sudah ada).

**TUJUAN PROMPT INI:**
Menyempurnakan tampilan halaman utama untuk modul:
- Job Order
- Transport
- Keuangan (Invoice, Vendor Bill, Kas/Bank)
dengan gaya modern, dark mode, dan UX enak dipakai.

**TUGAS ANDA:**
1. Untuk setiap modul, buat tampilan index yang modern:
   - `resources/views/job-orders/index.blade.php`
   - `resources/views/transports/index.blade.php`
   - `resources/views/invoices/index.blade.php`
   - `resources/views/vendor-bills/index.blade.php`
   - `resources/views/cash-banks/index.blade.php`

2. Setiap index:
   - Menggunakan layout `layouts.app`.
   - Di bagian atas:
     - Judul & deskripsi singkat.
     - Filter (drop-down status, date range, customer/vendor).
     - Tombol "+ Job Order", "+ Transport", "+ Invoice", "+ Vendor Bill", "+ Transaksi Kas/Bank" sesuai modul.
   - Di bagian konten:
     - Tabel dengan style dark mode:
       - Background card: `bg-slate-900/80` dengan border `border-slate-800`.
       - Row hover: `hover:bg-slate-800/70`.
     - Badge status pakai `<x-app-badge>` yang sudah dibuat.

3. Tambahkan beberapa elemen interaktif sederhana:
   - Tooltip jika perlu (Tailwind + sedikit Alpine.js optional).
   - Di index Job Order, tambahkan quick action icon:
     - lihat detail
     - edit
     - buat transport dari job ini
     - buat invoice dari job ini (kalau belum difaktur).
   - Di index Invoice & Vendor Bill:
     - icon/tombol kecil untuk "Terima Pembayaran" / "Bayar Vendor" (bawa ke halaman atau modal).

4. Buat 1 halaman detail (show) contoh:
   - `resources/views/job-orders/show.blade.php`:
     - Header: info customer, nomor job, status dengan badge.
     - Section "Ringkasan":
       - Card ringkasan total unit, jenis layanan, tanggal, total rencana pendapatan (bila ada).
     - Section "Daftar Unit/Item":
       - Tabel item yang diangkut, termasuk Serial Number, origin, destination.
     - Section "Transport terkait":
       - List transport yang terhubung dengan job order ini (internal & vendor) dalam card/tabel kecil.

5. Pastikan:
   - Class Tailwind konsisten dengan layout utama.
   - Dark mode nyaman (kontras cukup, tidak menyilaukan).
   - Menggunakan komponen `x-app-card` dan `x-app-badge` jika memungkinkan.

Berikan kode Blade lengkap untuk masing-masing file, siap saya copy-paste.
