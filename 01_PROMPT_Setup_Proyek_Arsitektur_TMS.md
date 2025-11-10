### PROMPT 1 – Setup Proyek & Arsitektur Dasar TMS

Anda adalah AI Coding Agent yang akan membantu saya membangun aplikasi **Transportation Management System (TMS)** untuk perusahaan jasa pengurusan transportasi & multi moda.

**STACK WAJIB:**
- Backend: Laravel 12, PHP 8.2
- Database: MySQL
- Frontend: Blade + Tailwind CSS (tanpa SPA dulu)
- Environment: Laragon (Windows), deployment ke cPanel shared hosting

**KONTEKS BISNIS:**
- Perusahaan bergerak di bidang:
  1. Jasa Pengurusan Transportasi (JPT) / angkutan alat berat.
  2. Multi moda transportasi (kombinasi darat, laut, vendor, dll).
- Perusahaan punya armada sendiri (truk/mobil) **dan** juga menggunakan vendor (subkon).
- Sistem harus bisa bedakan:
  - **Internal Fleet**: biaya jalan, solar, tol, uang makan supir, dll.
  - **Vendor**: tidak pakai uang jalan, biasanya langsung tagihan vendor per job.

**TUGAS ANDA DI PROMPT INI:**
1. Buat desain arsitektur high-level untuk proyek Laravel ini.
2. Siapkan daftar nama folder dan namespace utama.
3. Buatkan daftar tabel utama (customers, vendors, trucks, drivers, routes, equipments, job_orders, transports, invoices, vendor_bills).
4. Buat roadmap coding (step-by-step modul yang akan dibangun).
5. Tulis dalam bahasa Indonesia yang rapi.
