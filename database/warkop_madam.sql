-- ========================================================
-- WARKOP MADAM - DATABASE DUMP (SQL)
-- Siap diimpor langsung ke phpMyAdmin / MySQL Hosting
-- ========================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+07:00";

-- --------------------------------------------------------
-- 1. Struktur Tabel `users`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','klien') DEFAULT 'klien',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data untuk tabel `users` (Default Admin: username=admin, password=admin123)
INSERT INTO `users` (`id`, `nama_lengkap`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'Administrator Warkop', 'admin', '$2y$10$4w0NlBszQzG9P0h1eB4WpuAknJ9v1f1V6/2v4hP4e2J8e1K8m7l0y', 'admin', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE `username`=`username`;

-- --------------------------------------------------------
-- 2. Struktur Tabel `orders`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `table_number` varchar(50) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `total_price` int(11) NOT NULL,
  `order_items` text NOT NULL,
  `notes` text DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `status` enum('pending','diproses','selesai','dibatalkan') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Struktur Tabel `menu_items`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `menu_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_menu` varchar(100) NOT NULL,
  `kategori` enum('makanan','cemilan','minuman') NOT NULL,
  `subkategori` varchar(50) DEFAULT 'Umum',
  `harga` int(11) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT 'assets/logo.png',
  `status` enum('tersedia','habis') DEFAULT 'tersedia',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_kategori` (`kategori`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data awal katalog `menu_items`
INSERT INTO `menu_items` (`id`, `nama_menu`, `kategori`, `subkategori`, `harga`, `deskripsi`, `foto`, `status`) VALUES
(1, 'Nasi Goreng Madam', 'makanan', 'Special Nasi & Mie', 19000, 'Nasi goreng racikan bumbu khas Madam dengan suwiran ayam gurih, telur, dan kerupuk renyah.', 'assets/menu/nasi_goreng.jpg', 'tersedia'),
(2, 'Nasi Goreng Cabe Ijo', 'makanan', 'Special Nasi & Mie', 19000, 'Nasi goreng dengan aroma cabai hijau segar pedas nendang dan topping lengkap.', 'assets/menu/nasi_goreng_cabe_ijo.jpg', 'tersedia'),
(3, 'Mie Nyemek', 'makanan', 'Special Nasi & Mie', 17000, 'Mie kuah nyemek kental gurih berpadu telur, sayuran segar, dan irisan cabai rawit.', 'assets/menu/mie_nyemek.jpg', 'tersedia'),
(4, 'Mie Goreng', 'makanan', 'Special Nasi & Mie', 19000, 'Mie goreng spesial bumbu kecap gurih manis dengan telur dan pelengkap istimewa.', 'assets/menu/mie_goreng.jpg', 'tersedia'),
(5, 'Ayam Goreng Sambal Ijo', 'makanan', 'Olahan Ayam', 22000, 'Ayam goreng renyah empuk disiram ulekan sambal ijo segar + lalapan & tahu/tempe.', 'assets/menu/ayam_goreng_sambal_ijo.jpg', 'tersedia'),
(6, 'Ayam Maranggi', 'makanan', 'Olahan Ayam', 22000, 'Ayam panggang bumbu maranggi manis gurih beraroma rempah bakar sedap.', 'assets/menu/ayam_maranggi.jpg', 'tersedia'),
(7, 'Chiken Katsu', 'makanan', 'Olahan Ayam', 22000, 'Fillet ayam krispi tebal keemasan disajikan dengan saus cocolan nikmat & nasi.', 'assets/menu/chiken_katsu.jpg', 'tersedia'),
(8, 'Chiken Wings', 'makanan', 'Olahan Ayam', 22000, 'Sayap ayam bumbu saus spesial legit gurih disajikan dengan kentang goreng renyah.', 'assets/menu/chiken_wings.jpg', 'tersedia'),
(9, 'Kentang Goreng', 'cemilan', 'Gorengan Gurih', 12000, 'French fries renyah keemasan dengan cocolan saus sambal & tomat gurih.', 'assets/menu/kentang_goreng.jpg', 'tersedia'),
(10, 'Cireng Isi', 'cemilan', 'Gorengan Gurih', 13000, 'Cireng kenyal gurih renyah dengan isian lezat dan sambal cocol rujak pedas manis.', 'assets/menu/cireng_isi.jpg', 'tersedia'),
(11, 'Pisang Keju', 'cemilan', 'Manis Lezat', 12000, 'Pisang goreng manis empuk ditaburi limpahan keju parut dan cokelat kental manis.', 'assets/menu/pisang_keju.jpg', 'tersedia'),
(12, 'Mix Platter', 'cemilan', 'Special Sharing', 15000, 'Kombinasi kentang, sosis, nugget, dan otak-otak dalam satu porsi komplit.', 'assets/menu/mix_platter.jpg', 'tersedia'),
(13, 'Roti Bakar Keju / Coklat', 'cemilan', 'Roti Bakar', 12000, 'Roti tebal panggang lembut dengan pilihan topping keju gurih atau coklat lumer.', 'assets/menu/roti_bakar.jpg', 'tersedia'),
(14, 'Kopi Madam Special', 'minuman', 'Signature Coffee', 10000, 'Kopi hitam racikan legendaris khas Warkop Madam dengan aroma pekat harum.', 'assets/menu/kopi_madam.jpg', 'tersedia'),
(15, 'Kopi Susu Creamy', 'minuman', 'Signature Coffee', 12000, 'Paduan kopi mantap dengan kental manis legit gurih creamy.', 'assets/menu/kopi_susu.jpg', 'tersedia'),
(16, 'Teh Tarik', 'minuman', 'Non-Coffee & Milk', 10000, 'Teh pekat berbuih lembut ditarik berpadu susu manis segar.', 'assets/menu/teh_tarik.jpg', 'tersedia'),
(17, 'Nutrisari Jeruk Peras', 'minuman', 'Minuman Segar', 6000, 'Kesegaran rasa jeruk peras dingin dengan es batu segar pelepas dahaga.', 'assets/menu/nutrisari_jeruk.jpg', 'tersedia')
ON DUPLICATE KEY UPDATE `nama_menu`=`nama_menu`;

COMMIT;
