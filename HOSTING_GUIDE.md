# 🌐 Panduan Lengkap Hosting & Deployment - Warkop Madam

Panduan ini memandu Anda mengunggah dan menjalankan aplikasi **Warkop Madam** di berbagai jenis web hosting:
- **Shared Hosting / cPanel** (Hostinger, Niagahoster, DomaiNesia, Rumahweb, InfinityFree, dll)
- **VPS / Cloud Server** (Ubuntu / Debian dengan Apache atau Nginx)

---

## 📁 Struktur Direktori Proyek

```text
Warkop_Madam/
├── .env                  # Konfigurasi kredensial (Rahasia, jangan disebar)
├── .env.example          # Template environment untuk hosting
├── .htaccess             # Proteksi server, Gzip kompresi, dan HTTP headers
├── index.php             # Halaman pembuka / welcome screen
├── menu.php              # Katalog menu & antarmuka pemesanan pelanggan
├── api_order.php         # Endpoint API penerima pesanan pelanggan
├── login_admin.php       # Portal masuk otentikasi administrator
├── register.php          # Halaman pendaftaran klien/pelanggan baru
├── dashboard_admin.php   # Dashboard analitik & ringkasan omzet
├── kelola_pesanan.php    # Manajemen antrean pesanan & dapur kasir
├── kelola_menu.php       # Tambah & edit hidangan katalog
├── laporan_bulanan.php   # Rekapitulasi omzet & cetak laporan bulanan
├── logout.php            # Destruksi sesi admin aman
├── db_init.php           # Inisialisasi & pemeriksa database
├── database/
│   └── warkop_madam.sql  # Berkas SQL siap impor ke phpMyAdmin
├── includes/
│   ├── security.php      # Modul sentral keamanan, PDO, CSRF & sesi
│   └── admin_sidebar.php # Komponen terpusat navigasi admin
├── assets/
│   ├── logo.png          # Logo resmi Warkop Madam
│   ├── qris.png          # Barcode QRIS pembayaran
│   └── menu/             # Foto hidangan katalog (.htaccess protected)
└── uploads/
    └── proofs/           # Foto bukti bayar transfer (.htaccess protected)
```

---

## 🚀 OPSI 1: Deploy di Shared Hosting / cPanel

### Langkah 1: Siapkan File ZIP
1. Di komputer lokal Anda, pilih semua file dan folder di dalam proyek `Madam` (pastikan file tersembunyi seperti `.htaccess` dan `.env.example` ikut terpilih).
2. Kompres menjadi berkas `warkop_madam.zip`.

### Langkah 2: Upload ke cPanel File Manager
1. Masuk ke **cPanel** akun hosting Anda.
2. Buka menu **File Manager** -> masuk ke direktori **`public_html`** (atau subfolder addon domain/subdomain).
3. Klik tombol **Upload** dan pilih file `warkop_madam.zip`.
4. Setelah selesai, klik kanan pada file ZIP lalu pilih **Extract**.
5. *(Penting)* Aktifkan opsi **Show Hidden Files (dotfiles)** di pengaturan pojok kanan atas File Manager agar file `.htaccess` dan `.env` terlihat.

### Langkah 3: Buat Database MySQL & User
1. Di cPanel, buka menu **MySQL® Databases** (atau **MySQL Database Wizard**).
2. Buat database baru (contoh nama: `u1234567_madam`).
3. Buat user database baru (contoh: `u1234567_user`) dan tentukan kata sandi yang kuat.
4. Hubungkan User ke Database dengan mencentang **ALL PRIVILEGES** (Semua Hak Akses).

### Langkah 4: Impor Database via phpMyAdmin
1. Di cPanel, buka menu **phpMyAdmin**.
2. Klik nama database Anda yang baru dibuat di panel sebelah kiri.
3. Klik tab **Import** pada menu atas.
4. Klik **Choose File** / Telusuri, lalu pilih berkas `database/warkop_madam.sql` dari folder proyek.
5. Klik tombol **Import / Kirim** di bagian bawah. Semua tabel dan menu awal akan terpasang otomatis.

### Langkah 5: Konfigurasi File `.env`
1. Di File Manager cPanel, cari file `.env.example`.
2. Klik kanan -> pilih **Rename** -> ubah namanya menjadi **`.env`**.
3. Klik kanan pada file `.env` -> pilih **Edit**.
4. Sesuaikan nilai-nilai berikut dengan data hosting Anda:
   ```ini
   APP_NAME="Warkop Madam"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://namadomainanda.com

   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=u1234567_madam
   DB_USERNAME=u1234567_user
   DB_PASSWORD=password_db_anda_disini
   ```
5. Simpan perubahan file.

### Langkah 6: Atur Izin Folder (Permissions)
Pastikan folder upload memiliki izin tulis bagi web server:
- Folder **`uploads/`** & **`uploads/proofs/`**: Izin **`755`**
- Folder **`assets/menu/`**: Izin **`755`**
*(Jika gagal upload gambar, ubah sementara menjadi `775` melalui File Manager).*

### Langkah 7: Pengujian Sistem
Buka web browser dan akses domain Anda:
- **Halaman Utama**: `https://namadomainanda.com/index.php`
- **Menu Pelanggan**: `https://namadomainanda.com/menu.php`
- **Login Admin**: `https://namadomainanda.com/login_admin.php`
  - **Username Default**: `admin`
  - **Password Default**: `admin123`

---

## 🖥️ OPSI 2: Deploy di VPS (Ubuntu / Debian + Nginx / Apache)

### 1. Kebutuhan Server
- PHP 8.1 / 8.2 / 8.3 dengan ekstensi: `php-pdo`, `php-mysql`, `php-mbstring`, `php-gd`, `php-fileinfo`, `php-curl`.
- MariaDB / MySQL Server 8.0+.
- Web Server: Nginx atau Apache2.

### 2. Konfigurasi Nginx (Jika Menggunakan Nginx)
Jika menggunakan Nginx, tambahkan blok berikut ke `/etc/nginx/sites-available/warkop_madam`:

```nginx
server {
    listen 80;
    server_name namadomainanda.com;
    root /var/www/warkop_madam;
    index index.php index.html;

    # Blokir akses ke file tersembunyi & file sensitif
    location ~ /\.(env|git|htaccess) {
        deny all;
    }
    location ~* \.(sql|log|bak|md)$ {
        deny all;
    }

    # Blokir eksekusi PHP di dalam folder uploads & assets/menu
    location ~* ^/(uploads|assets/menu)/.*\.php$ {
        deny all;
    }

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 3. Setup Database via Terminal
```bash
mysql -u root -p
CREATE DATABASE warkop_madam CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'madam_user'@'localhost' IDENTIFIED BY 'PasswordKuat123!';
GRANT ALL PRIVILEGES ON warkop_madam.* TO 'madam_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import skema database
mysql -u madam_user -p warkop_madam < database/warkop_madam.sql
```

---

## 🔒 Checklist Keamanan Pasca-Hosting

- [x] **Ganti Password Default Admin**: Segera ubah password admin default dari `admin123` melalui menu database atau buat user admin baru.
- [x] **Aktifkan SSL / HTTPS**: Gunakan Let's Encrypt SSL gratis di hosting agar seluruh transaksi dan data login terenkripsi.
- [x] **Pastikan `APP_DEBUG=false`** di file `.env` untuk mencegah kebocoran pesan error internal ke pengguna luar.
- [x] **Cek Proteksi File `.env`**: Coba akses `https://namadomainanda.com/.env` dari browser. Pastikan server menampilkan **403 Forbidden** atau **404 Not Found**.
