# Import Master Vendor - Documentation

## 📋 Ringkasan Import

Import data vendor dari file `Mastervendor.txt` telah **berhasil dilakukan** dengan hasil sebagai berikut:

### Statistik Import:
- ✅ **Total diimport**: 179 vendor baru
- ⏭️ **Total dilewati**: 3 vendor (duplikat)
- 📊 **Total vendor di database**: 184 vendor
- 📍 **Vendor dengan alamat lengkap**: 182 vendor
- ❓ **Vendor tanpa alamat**: 2 vendor

## 🎯 Data yang Diimport

### Struktur Data:
Setiap vendor diimport dengan informasi berikut:
- **name**: Nama lengkap vendor (dari kolom 2 file txt)
- **address**: Alamat lengkap vendor (dari kolom terakhir yang tidak kosong)
- **phone**: Nomor telepon (jika ada di kolom 3-4)
- **email**: Email (jika ada di kolom 3-4)
- **pic_name**: Nama PIC/Contact Person (jika ada di kolom 3)
- **vendor_type**: Diset default sebagai 'subcon'
- **is_active**: Diset default sebagai true (aktif)

### Contoh Vendor yang Berhasil Diimport:

1. **PT. ANUGERAH BERKAT TRANSPORTASI**
   - Alamat: PADEMANGAN BARAT, PADEMANGAN, KOTA ADM. JAKARTA UTARA, DKI JAKARTA. 14420

2. **PT. ADHIMIX RMC INDONESIA**
   - Alamat: L'Avenue Office Building Lt.16, Jl. Raya Pasar Minggu Kav.16, Pancoran, Jakarta Selatan 12780. DKI Jakarta.

3. **PT. HEXINDO ADIPERKASA TBK.**
   - Alamat: Industrial Estate Pulo Gadung, Jl. Pulo Kambing II Kav. I - II No. 33, Jakarta, Indonesia 13930

4. **PT. MIKATA TRANSPORTASI LOGISTIK**
   - Alamat: Ruko Cleon Park No.18, Jl. Jkt Garden City Boulevard, RT.11/RW.8, Cakung Tim., Kec. Cakung, Kota Jakarta Timur, Daerah Khusus Ibukota Jakarta 13910
   - PIC: Alisha
   - Phone: 089508527835

5. **PT. Daya Kobelco Construction Machinery Indonesia**
   - Alamat: Kawasan Industri MM2100, Jl. Halmahera, Danau Indah, Kec. Cikarang Bar.
   - Email: raden.esugiarto@kobelco.com
   - Phone: +62 813-1425-4847

## 🛠️ Tools yang Dibuat

### 1. VendorMasterDataSeeder.php
**Lokasi**: `database/seeders/VendorMasterDataSeeder.php`

**Cara Penggunaan**:
```bash
php artisan db:seed --class=VendorMasterDataSeeder
```

**Fitur**:
- ✅ Import otomatis dari file `Mastervendor.txt`
- ✅ Deteksi duplikat berdasarkan nama vendor
- ✅ Update alamat untuk vendor yang sudah ada (jika sebelumnya kosong)
- ✅ Parse otomatis email, phone, dan nama PIC
- ✅ Transaksi database (rollback jika error)
- ✅ Laporan detail (imported, skipped, errors)

### 2. import_vendors.php
**Lokasi**: `import_vendors.php` (root directory)

**Cara Penggunaan**:
```bash
php import_vendors.php
```

**Fitur**:
- ✅ Script standalone untuk import manual
- ✅ Output console yang lebih detail dengan emoji
- ✅ Menampilkan progress real-time
- ✅ Summary lengkap setelah selesai

## 📊 Vendor yang Dilewati (Duplikat)

Berikut adalah vendor yang sudah ada di database sebelumnya:

1. **PT. NUSATAMA BERKAH, TBK** (muncul 2x di file)
2. **PT. XCMG GROUP INDONESIA** (muncul 2x di file)
3. **PT. Panca Mega Makmur** (muncul 2x di file)

## 🔄 Cara Menjalankan Import Ulang

Jika ingin menjalankan import ulang (misalnya setelah ada data baru):

### Opsi 1: Menggunakan Seeder (Recommended)
```bash
php artisan db:seed --class=VendorMasterDataSeeder
```

### Opsi 2: Menggunakan Script Standalone
```bash
php import_vendors.php
```

### Opsi 3: Menggunakan Tinker (untuk testing)
```bash
php artisan tinker
```
Kemudian jalankan:
```php
require 'import_vendors.php';
```

## 📝 Catatan Penting

### Format File Mastervendor.txt
File menggunakan format **Tab-separated values (TSV)** dengan struktur:
```
nomor [TAB] nama_lengkap [TAB] nama_pendek [TAB] email/pic [TAB] phone [TAB] ... [TAB] alamat
```

### Vendor dengan Alamat Tidak Lengkap
Beberapa vendor hanya memiliki nama PIC sebagai alamat (contoh: "Pak Frans", "Bu Tati", "Mba Tiara"). Ini terjadi karena format data di file txt yang tidak konsisten. Data ini tetap diimport dan bisa diupdate manual kemudian.

### Vendor Tanpa Alamat
2 vendor tidak memiliki alamat sama sekali:
- Vendor bisa diupdate manual melalui aplikasi
- Atau tambahkan alamat di file txt dan jalankan import ulang

## 🔍 Verifikasi Data

### Cek Total Vendor
```bash
php artisan tinker --execute="echo 'Total: ' . App\Models\Master\Vendor::count();"
```

### Cek Vendor Terbaru
```bash
php artisan tinker --execute="App\Models\Master\Vendor::latest()->take(10)->get(['name', 'address'])->each(function(\$v) { echo \$v->name . PHP_EOL; });"
```

### Cek Vendor Tanpa Alamat
```bash
php artisan tinker --execute="App\Models\Master\Vendor::whereNull('address')->orWhere('address', '')->get(['id', 'name'])->each(function(\$v) { echo \$v->id . ': ' . \$v->name . PHP_EOL; });"
```

## ✅ Status Import

**Status**: ✅ **SELESAI & BERHASIL**

Semua data dari file `Mastervendor.txt` telah berhasil diimport ke dalam database. Vendor dapat langsung digunakan untuk:
- Job Order
- Transport
- Invoice
- Vendor Bill
- Dan modul lainnya yang membutuhkan data vendor

## 🎯 Next Steps (Opsional)

1. **Validasi Manual**: Cek beberapa vendor secara manual untuk memastikan data akurat
2. **Update Data**: Update vendor yang alamatnya hanya berisi nama PIC
3. **Lengkapi Informasi**: Tambahkan email, phone, dan informasi kontak lainnya
4. **Setting Vendor Type**: Update vendor_type sesuai kategori (subcon, supplier, transport, dll)
5. **Aktifkan/Nonaktifkan**: Set is_active = false untuk vendor yang tidak aktif

---

**Tanggal Import**: 22 November 2025  
**Total Record**: 182 baris dari file  
**Berhasil Diimport**: 179 vendor baru  
**Duplikat**: 3 vendor
