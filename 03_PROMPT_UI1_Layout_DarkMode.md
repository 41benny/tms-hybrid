### PROMPT UI-1 – Layout Utama + Dark Mode Global

Anda adalah AI Coding Agent untuk proyek Laravel 12 + Blade + Tailwind CSS.

**TUJUAN PROMPT INI:**
Membangun kerangka UI utama aplikasi TMS saya dengan:
- **Full dark mode** sebagai default.
- Layout dashboard modern:
  - Sidebar kiri (icon + label).
  - Header atas (judul halaman, search, user menu).
  - Konten di tengah dengan card-card.
- Responsif (desktop & mobile).
- Style modern: glassmorphism ringan, border radius besar, drop shadow lembut.

**STACK:**
- Laravel 12
- Blade
- Tailwind CSS (bukan CDN di production, pakai build tool)
- Opsional: DaisyUI atau plugin Tailwind lain jika membantu dark mode.

**TUGAS ANDA:**
1. Siapkan konfigurasi Tailwind untuk project ini:
   - `tailwind.config.js` dengan:
     - `darkMode: 'class'`.
     - Palette warna khusus untuk TMS, contoh:
       - Background: `slate-950` atau setara.
       - Surface: kombinasi `slate-900`, `slate-800` dengan efek glass (bg-opacity).
       - Primary: biru/ungu (misal `indigo` / `violet`).
   - Jika perlu plugin (forms, typography) jelaskan juga.

2. Buat layout Blade utama:
   - File: `resources/views/layouts/app.blade.php`
   - Struktur:
     - `<html lang="id" class="dark">` (dark default).
     - `<body class="bg-slate-950 text-slate-100">`
     - Sidebar kiri:
       - Logo mini & nama app (mis: "TMS360" atau sejenis).
       - Menu:
         - Dashboard
         - Job Order
         - Transport
         - Master Data
         - Keuangan
         - Laporan
         - AI Assistant
       - Gunakan icon (Heroicons / FontAwesome) jika memungkinkan.
     - Topbar:
       - Judul halaman (yield dari child).
       - Search bar kecil.
       - User avatar + dropdown.
       - Tombol toggle dark/light mode (JS kecil: hanya menambahkan/menghapus class `dark` pada `<html>` dan simpan preferensi di localStorage).

3. Buat komponen Blade sederhana:
   - `resources/views/components/app-card.blade.php`:
     - Card dengan class Tailwind yang cocok di dark mode (bg-slate-900/80, border-slate-700, hover efek).
   - `resources/views/components/app-badge.blade.php`:
     - Badge status (success, warning, danger, info) dengan warna yang cocok di dark theme.

4. Berikan juga contoh:
   - Contoh view `resources/views/dashboard.blade.php` yang extend layout dan menampilkan beberapa card statistik dummy:
     - Total Job Order
     - Job sedang berjalan
     - Outstanding Invoice
     - Outstanding Vendor Bill

5. Berikan kode lengkap:
   - `tailwind.config.js`
   - `layouts/app.blade.php`
   - Komponen card & badge
   - `dashboard.blade.php`
   - Script JS singkat untuk toggle dark/light.

Jawab dalam bahasa Indonesia, kode harus siap saya copy-paste ke file terkait.
