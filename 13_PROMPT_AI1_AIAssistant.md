### PROMPT AI-1 – AI Assistant untuk Analisa Semua Data TMS + Keuangan

Sekarang saya ingin menambahkan **AI Assistant internal** di aplikasi TMS + Akuntansi ini.

**TUJUAN:**
- Membuat satu modul “AI Assistant” yang bisa:
  - Membaca semua data penting di database (tanpa dibatasi, kecuali saya batasi sendiri nanti).
  - Menjawab pertanyaan pengguna tentang:
    - Job Order (status, progres, pelanggan)
    - Transport (trip internal/vendor, biaya, performa)
    - Invoice & Piutang (mana yang belum dibayar, aging, customer mana yang sering telat)
    - Vendor Bill & Hutang (hutang berjalan, performa vendor)
    - Kas/Bank (arus kas, saldo per akun)
    - Laporan keuangan (laba rugi per periode, biaya terbesar, profit per customer/job, dsb)
  - Memberikan insight dalam bahasa Indonesia yang mudah dipahami.

**CATATAN PENTING:**
- Dalam sistem ini, **AI diperbolehkan mengakses semua data** di database (full read access), kecuali kalau nanti saya ubah sendiri.
- Implementasi koneksi ke LLM/OpenAI boleh dibuat dalam bentuk service yang memanggil HTTP API (saya akan isi API key sendiri).

---

## 1. Desain Arsitektur Modul AI Assistant

Buat desain sebagai berikut:

1. Controller:
   - `AiAssistantController`
     - method:
       - `index()` → halaman chat UI.
       - `ask(Request $request)` → endpoint AJAX untuk menerima pertanyaan dan mengembalikan jawaban.

2. Service:
   - `App\Services\Ai\AiAnalysisService`
     - Fungsi utama:
       - `analyze(string $question): string`
       - Di dalamnya:
         1. Analisa pertanyaan untuk menentukan kategori (rule-based sederhana pakai kata kunci, mis: "piutang", "hutang", "laba rugi", "job", "vendor", "kas", "arus kas", dll).
         2. Berdasarkan kategori, ambil data dari tabel terkait:
            - Job Order: `job_orders`, `job_order_items`
            - Transport: `transports`, `transport_costs`
            - Keuangan: `invoices`, `invoice_items`, `vendor_bills`, `vendor_bill_items`, `cash_bank_transactions`
            - Akuntansi: `chart_of_accounts`, `journals`, `journal_lines`
            - Master: `customers`, `vendors`, `trucks`, `drivers`
         3. Rangkai ringkasan/aggregasi data (misal: total piutang, daftar invoice terlambat, laba rugi periode tertentu).
         4. Kirim ringkasan/aggregasi + pertanyaan asli ke LLM API (misalnya OpenAI) untuk dibuatkan jawaban naratif.

   - Di jawaban, AI boleh mengakses dan mengolah semua data yang dikirim.

3. Konfigurasi:
   - File config misal: `config/ai_assistant.php`:
     - endpoint LLM
     - api_key (diambil dari env)
     - model (mis: "gpt-4.x" atau lain)
   - Saya akan isi `.env` sendiri, cukup buatkan skeleton-nya.

---

## 2. Implementasi Backend (Skeleton Lengkap)

1. Buat class `AiAnalysisService`:
   - Fungsi `analyze(string $question): string`:
     - Panggil helper `detectIntent($question)` → return kategori, misal: `"piutang"`, `"hutang"`, `"profit"`, `"job_customer"`, `"vendor_performance"`, `"cash_flow"`, `"general_financial"`.
     - Switch by kategori:
       - Untuk `"piutang"`:
         - Query invoices dan payments terkait.
         - Hitung total outstanding, aging, top 5 customer yang paling besar piutangnya.
       - Untuk `"hutang"`:
         - Query vendor_bills dan payments terkait.
       - Untuk `"profit"` atau `"laba rugi"`:
         - Query journal_lines (revenue & expense) dalam periode tertentu (gunakan default bulan berjalan jika user tidak sebut).
       - Untuk `"cash_flow"`:
         - Query cash_bank_transactions (cash_in vs cash_out).
       - dst.
     - Buat data ringkasan dalam bentuk array/JSON yang berisi angka-angka penting dan list top item.
     - Panggil fungsi `callLlmApi($question, $summaryData)`:
       - Buat HTTP request ke API LLM (gunakan Guzzle atau Http client Laravel).
       - Prompt ke LLM berisi:
         - Pertanyaan user
         - Ringkasan data (summaryData)
       - Minta LLM menjawab dalam bahasa Indonesia yang jelas, dengan angka-angka yang sudah diringkas.
     - Return teks jawaban yang sudah dihasilkan LLM.

   - Sertakan:
     - `detectIntent($question)` (rule-based kata kunci).
     - fungsi-fungsi query data (piutang, hutang, laba rugi, cash flow, dsb).
     - `callLlmApi($question, $summaryData)` yang membaca API key dari config/env.

2. Buat `AiAssistantController`:
   - `index()` → return view `ai-assistant/index.blade.php`.
   - `ask(Request $request)`:
     - Validasi input `question`.
     - Panggil `AiAnalysisService::analyze($question)`.
     - Return JSON response `{ "answer": "...", "debug_summary": ...optional }`.

3. Tambahkan route:
   - GET `/ai-assistant` → `AiAssistantController@index` (name: `ai-assistant.index`)
   - POST `/ai-assistant/ask` → `AiAssistantController@ask` (name: `ai-assistant.ask`)

---

## 3. Implementasi UI Chat (Dark Mode)

1. Buat view: `resources/views/ai-assistant/index.blade.php`:
   - Extend layout dark mode utama.
   - Layout:
     - Bagian atas: card "AI Assistant – Analisa TMS & Keuangan".
     - Area chat:
       - List bubble chat (pertanyaan user & jawaban AI).
       - Input textarea + tombol "Kirim" di bawah.
     - Tambahkan beberapa quick suggestion button, contoh:
       - "Tunjukkan daftar invoice yang sudah jatuh tempo minggu ini"
       - "Berapa laba rugi bulan ini?"
       - "Customer mana yang paling besar piutangnya?"
       - "Vendor mana yang paling sering terlambat?"
   - Style:
     - Chat bubble user: align kanan, warna primary.
     - Chat bubble AI: align kiri, warna surface gelap.

2. Tambahkan JS (bisa pakai Alpine atau vanilla):
   - Submit pertanyaan via fetch/AJAX ke route `ai-assistant.ask`.
   - Tampilkan loading indicator saat menunggu jawaban.
   - Append jawaban ke area chat tanpa reload halaman.

---

## 4. Akses Data Tanpa Pembatasan Internal

- Pastikan:
  - Service `AiAnalysisService` diizinkan untuk mengakses semua model:
    - Customer, Vendor, Truck, Driver
    - JobOrder, JobOrderItem
    - Transport, TransportCost
    - Invoice, InvoiceItem
    - VendorBill, VendorBillItem
    - CashBankAccount, CashBankTransaction
    - ChartOfAccount, Journal, JournalLine
  - Tidak perlu menambah rule yang memfilter data berdasarkan user login **di level service** (kecuali saya tambahkan sendiri).

- Tetap jaga performa:
  - Query efisien (aggregate, groupBy).
  - Ringkas data sebelum dikirim ke LLM (misalnya top 10 saja, summary per kategori).

---

## 5. Output yang Saya Harapkan

Berikan:
1. Kode lengkap:
   - `app/Services/Ai/AiAnalysisService.php`
   - `app/Http/Controllers/AiAssistantController.php`
   - `resources/views/ai-assistant/index.blade.php`
   - `config/ai_assistant.php`
2. Contoh isi `.env` yang perlu saya tambah untuk API LLM (tanpa value sensitif).
3. Penjelasan singkat di komentar bagaimana cara nanti saya mengaktifkan AI (mengisi API key dan memilih model).

Jawab dalam bahasa Indonesia, dengan kode siap copy-paste ke project Laravel.
