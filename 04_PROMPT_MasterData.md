### PROMPT 2 – Migration & Model untuk Master Data

Lanjutkan dari desain arsitektur dan daftar tabel yang sudah Anda buat sebelumnya.

**TUGAS ANDA SEKARANG:**
Bantu saya membuat **migration + model** untuk modul **Master Data**, dengan ketentuan:

1. Buat migration dan model untuk tabel berikut (silakan sesuaikan nama kolom dari desain Anda sebelumnya, tambahkan bila perlu):
   - `customers`
   - `vendors`
   - `trucks`
   - `drivers`
   - `routes`
   - `equipments` (alat berat)

2. Pastikan ada kolom sebagai berikut (boleh disesuaikan dengan best practice Laravel):
   - Semua tabel: `id`, `created_at`, `updated_at`.
   - `customers`:
     - name, address, phone, email (nullable), npwp (nullable),
     - payment_term (mis: COD, 14 hari, 30 hari).
   - `vendors`:
     - name, address, phone, email (nullable),
     - vendor_type (mis: `trucking`, `pelayaran`, `lainnya`),
     - is_active.
   - `trucks`:
     - plate_number, vehicle_type, capacity_tonase, is_active,
     - is_own_fleet (boolean, milik sendiri atau bukan),
     - vendor_id (nullable, jika ini armada vendor).
   - `drivers`:
     - name, phone, is_active,
     - vendor_id (nullable, jika supir vendor).
   - `routes`:
     - origin, destination, distance_km (nullable), description (nullable).
   - `equipments`:
     - name, category (excavator, bulldozer, dll),
     - brand, model, serial_number, capacity (nullable), description (nullable).

3. Buat juga relasi dasar di model:
   - Truck:
     - `belongsTo Vendor` (nullable).
   - Driver:
     - `belongsTo Vendor` (nullable).
   - Vendor:
     - `hasMany Truck`, `hasMany Driver`.

4. Berikan kode:
   - Migration lengkap berbasis Laravel 12.
   - Model Eloquent dengan `$fillable` dan relasi dasar.

5. Tulis semuanya dalam format yang siap saya copy ke file migration dan model Laravel.
   - Sertakan nama file yang disarankan, misal:
     - `database/migrations/xxxx_xx_xx_create_customers_table.php`
     - `app/Models/Customer.php`, dst.

Jawab dalam bahasa Indonesia.
