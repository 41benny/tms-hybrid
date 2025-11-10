### PROMPT 3 – Modul Job Order (JPT & Multi Moda)

Sekarang kita bangun modul **Job Order** sebagai inti TMS.

**KONTEKS:**
- Job Order = pesanan dari customer.
- Satu Job Order bisa berisi beberapa alat berat / unit muatan (job_order_items).
- Untuk alat berat, sering ada **Serial Number (SN)** per unit yang wajib dicantumkan saat penagihan.
- Job Order bisa menggunakan:
  - Armada internal (truck & driver sendiri).
  - Vendor (trucking/pelayaran).
  - Kombinasi multi moda (contoh: trucking ke pelabuhan, lanjut kapal, dst).

**TUGAS ANDA:**
1. Buat migration + model untuk:
   - `job_orders`
   - `job_order_items`

2. Struktur minimal:
   - `job_orders`:
     - `id`
     - `customer_id` (relasi ke customers)
     - `job_number` (unik, bisa auto-generate nanti, contoh format: `JO-YYYYMMDD-XXXX`)
     - `order_date`
     - `service_type`: enum/string (`jpt`, `multi_moda`, `sewa_truk`)
     - `status`: `draft`, `confirmed`, `in_progress`, `completed`, `cancelled`
     - `notes` (nullable)
     - `created_at`, `updated_at`
   - `job_order_items`:
     - `id`
     - `job_order_id`
     - `equipment_id` (relasi ke equipments, nullable jika deskripsi bebas)
     - `equipment_name` (untuk jaga-jaga jika tidak pakai master)
     - `serial_number` (bila wajib per unit)
     - `qty`
     - `origin_route_id` (nullable, relasi ke routes)
     - `destination_route_id` (nullable, relasi ke routes)
     - `origin_text` (nullable, jika tidak pakai routes)
     - `destination_text` (nullable)
     - `remark` (nullable)
     - `created_at`, `updated_at`

3. Buatkan juga:
   - Model Eloquent beserta relasi:
     - JobOrder:
       - `belongsTo Customer`
       - `hasMany JobOrderItem`
     - JobOrderItem:
       - `belongsTo JobOrder`
       - `belongsTo Equipment` (nullable)
       - `belongsTo Route` (origin, destination) bila perlu.

4. Lalu buat:
   - Controller dasar `JobOrderController` dengan method:
     - `index` (list & filter by customer, status, tanggal)
     - `create`, `store`
     - `show`
     - `edit`, `update`
   - Route resource di `routes/web.php`.

5. Buatkan Blade view sederhana:
   - `job-orders/index.blade.php`:
     - tabel daftar job order (job_number, customer, service_type, status, action).
     - filter sederhana di atas tabel (status, customer, date range).
   - `job-orders/create.blade.php` & `edit.blade.php`:
     - form header job order.
     - input multiple item sederhana (boleh pakai row dinamis tetapi minimal bisa input beberapa item).
   - `job-orders/show.blade.php`:
     - tampilan detail job order + list item.

6. Tulis kodenya lengkap dan rapi, siap saya copy-paste ke project Laravel.
   - Gunakan Tailwind CSS dasar sesuai konvensi layout dark mode yang sudah dibuat sebelumnya.

Jawab dalam bahasa Indonesia.
