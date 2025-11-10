### PROMPT 4 – Eksekusi Transport (Internal Fleet vs Vendor)

Sekarang kita bangun modul **Eksekusi Transport** yang menghubungkan Job Order dengan aktivitas pengiriman.

**KONTEKS:**
- Dari satu Job Order, bisa dibuat satu atau beberapa "Transport" (trip).
- Per Transport:
  - Bisa menggunakan **armada internal** (truck & driver milik sendiri).
  - Atau menggunakan **vendor** (trucking/pelayaran).
- Jika internal:
  - Ada uang jalan, biaya solar, tol, makan supir, dll.
- Jika vendor:
  - Biasanya hanya ada tagihan vendor (biaya lump sum per trip atau per unit).

**TUGAS ANDA:**
1. Buat migration + model untuk:
   - `transports`
   - `transport_costs`

2. Struktur minimal:
   - `transports`:
     - `id`
     - `job_order_id`
     - `job_order_item_id` (nullable, jika 1 transport = 1 item; atau null jika gabungan)
     - `executor_type`: enum/string (`internal`, `vendor`)
     - `truck_id` (nullable, jika internal)
     - `driver_id` (nullable, jika internal)
     - `vendor_id` (nullable, jika vendor)
     - `departure_date` (nullable)
     - `arrival_date` (nullable)
     - `status`: `planned`, `on_route`, `delivered`, `closed`, `cancelled`
     - `spj_number` / `sj_number` (nullable, untuk internal)
     - `notes` (nullable)
     - timestamps
   - `transport_costs`:
     - `id`
     - `transport_id`
     - `cost_category`: contoh (`uang_jalan`, `solar`, `tol`, `makan_supir`, `lainnya`, `vendor_charge`)
     - `description` (nullable)
     - `amount`
     - `is_vendor_cost` (boolean, untuk bedakan antara biaya internal vs tagihan vendor)
     - timestamps

3. Buatkan:
   - Model beserta relasi (Transport belongsTo JobOrder, JobOrderItem, Truck, Driver, Vendor; hasMany TransportCost).

4. Buat `TransportController` dengan fitur:
   - `index`: filter by status, job order, executor_type.
   - `create`/`store`: buat transport baru dari Job Order yang sudah ada.
   - `show`: detail transport + breakdown biaya.
   - `edit`/`update`: ubah informasi dan biaya.
   - Fungsi untuk update status (misal: tombol ubah status dari `planned` → `on_route` → `delivered` → `closed`).

5. Buat Blade view sederhana:
   - `transports/index.blade.php`:
     - daftar transport (no, job_order, executor_type, truck/vendor, status, action).
   - `transports/create.blade.php` & `edit.blade.php`:
     - pilih Job Order + executor_type + armada/vendor.
   - `transports/show.blade.php`:
     - tampilkan informasi lengkap + tabel biaya (`transport_costs`).

6. Tulis kodenya lengkap dan rapi, siap saya copy-paste.

Tetap gunakan Tailwind CSS dan layout dark mode yang sudah dibuat sebelumnya.
