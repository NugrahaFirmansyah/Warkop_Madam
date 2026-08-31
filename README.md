# Warkop Madam ☕

Sistem Informasi Pemesanan & Kasir Digital **Warkop Madam** (Autentikasi Aman, Kelola Menu, Real-Time Antrean Dapur & Kasir, Laporan Bulanan, Modular, dan Siap Hosting).

---

## ✨ Fitur Utama
- **Katalog Menu Interaktif**: Pemesanan mandiri oleh pelanggan (Makanan, Cemilan, Minuman) dengan filter, varian rasa/topping, dan live search.
- **Kasir & Manajemen Dapur**: Pemantauan antrean pesanan real-time, update status hidangan, preview bukti transfer, dan cetak struk nota.
- **Dashboard Analitik**: Grafik Best Seller, rekap omzet harian & bulanan, dan proporsi metode pembayaran (QRIS, Transfer Mandiri, Tunai).
- **Laporan Keuangan Bulanan**: Rekapitulasi transaksi, filter tanggal/status/metode, dan fitur cetak/print laporan resmi siap arsip.
- **Modular & Clean Architecture**:
  - Komponen terpusat di folder `includes/` (`includes/security.php`, `includes/admin_sidebar.php`).
  - Berkas database dump `database/warkop_madam.sql` siap impor phpMyAdmin.
- **Security Hardening**:
  - Konfigurasi berbasis `.env`
  - Koneksi aman PDO Prepared Statements (`utf8mb4`)
  - Proteksi CSRF Token & Anti Brute-Force Rate Limiting
  - Validasi ketat upload berkas bukti bayar & foto menu (MIME-type & Magic Bytes check)
  - HTTP Security Headers & Server Hardening (.htaccess)

---

## 🚀 Panduan Instalasi Lokal (XAMPP / Laragon)

1. Letakkan folder project di direktori web server (contoh: `C:\xampp\htdocs\Madam`).
2. Buat file `.env` dari `.env.example`:
   ```bash
   cp .env.example .env
   ```
3. Sesuaikan kredensial database di `.env` jika diperlukan.
4. Jalankan script inisialisasi database:
   ```bash
   php db_init.php
   ```
5. Buka di browser:
   - **Selamat Datang**: `http://localhost/Madam/index.php`
   - **Menu Pelanggan**: `http://localhost/Madam/menu.php`
   - **Portal Admin**: `http://localhost/Madam/login_admin.php`
     - **Username**: `admin`
     - **Password**: `admin123`

---

## 🌐 Panduan Deployment & Hosting
Untuk panduan detail mengenai cara mengunggah ke **cPanel / Shared Hosting** (Hostinger, Niagahoster, DomaiNesia, dll) atau **VPS**, silakan baca panduan lengkap di:
👉 **[HOSTING_GUIDE.md](HOSTING_GUIDE.md)**
