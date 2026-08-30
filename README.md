# Warkop Madam ☕

Sistem Informasi Pemesanan & Kasir Digital **Warkop Madam** (Autentikasi Aman, Kelola Menu, Real-Time Antrean Dapur & Kasir, Laporan Bulanan, dan Pengamanan Siber).

## ✨ Fitur Utama
- **Katalog Menu Interaktif**: Pemesanan mandiri oleh pelanggan (Makanan, Cemilan, Minuman) dengan filter dan opsi topping/pilihan rasa.
- **Kasir & Manajemen Dapur**: Pemantauan antrean pesanan real-time, perubahan status hidangan, dan cetak struk barista.
- **Dashboard Analitik**: Grafik Best Seller, rekap omzet harian & bulanan, dan proporsi metode pembayaran (QRIS, Transfer Mandiri, Tunai).
- **Laporan Keuangan Bulanan**: Rekapitulasi transaksi, filter tanggal/status/metode, dan fitur cetak/print laporan resmi.
- **Security Hardening**:
  - Konfigurasi berbasis `.env`
  - Koneksi aman PDO Prepared Statements (`utf8mb4`)
  - Proteksi CSRF Token & Anti Brute-Force Rate Limiting
  - Validasi ketat upload berkas bukti bayar & foto menu (verifikasi MIME-type riil)
  - HTTP Security Headers & Server Hardening (.htaccess)

## 🚀 Panduan Instalasi Lokal (XAMPP)
1. Letakkan folder project di direktori `htdocs` (contoh: `C:\xampp\htdocs\Madam`).
2. Buat file `.env` dari `.env.example`:
   ```bash
   cp .env.example .env
   ```
3. Sesuaikan kredensial database di `.env`.
4. Jalankan script inisialisasi database:
   ```bash
   php db_init.php
   ```
5. Buka di browser:
   - **Menu Pelanggan**: `http://localhost/Madam/menu.php`
   - **Portal Admin**: `http://localhost/Madam/login_admin.php` (Default: `admin` / `admin123`)
