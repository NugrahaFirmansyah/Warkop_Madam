<?php
// Koneksi & Inisialisasi Database Warkop Madam (Secured)
require_once __DIR__ . '/security.php';

$host = DB_HOST;
$username_db = DB_USER;
$password_db = DB_PASS;
$dbname = DB_NAME;

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username_db, $password_db, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    // Buat database jika belum ada
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname`");

    // Buat tabel users
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama_lengkap VARCHAR(100) NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'klien') DEFAULT 'klien',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Buat tabel orders untuk mencatat pesanan kasir
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_number VARCHAR(50) NOT NULL,
        customer_name VARCHAR(100) NOT NULL,
        table_number VARCHAR(50) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        total_price INT NOT NULL,
        order_items TEXT NOT NULL,
        notes TEXT NULL,
        payment_proof VARCHAR(255) NULL,
        status ENUM('pending', 'diproses', 'selesai', 'dibatalkan') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Pastikan kolom payment_proof ada jika tabel sudah terbuat sebelumnya
    try {
        $pdo->exec("ALTER TABLE orders ADD COLUMN payment_proof VARCHAR(255) NULL AFTER notes");
    } catch (Exception $ex) {
        // Kolom sudah ada
    }

    // Buat tabel menu_items untuk fitur Tambah & Kelola Menu
    $pdo->exec("CREATE TABLE IF NOT EXISTS menu_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama_menu VARCHAR(100) NOT NULL,
        kategori ENUM('makanan', 'cemilan', 'minuman') NOT NULL,
        subkategori VARCHAR(50) DEFAULT 'Umum',
        harga INT NOT NULL,
        deskripsi TEXT NULL,
        foto VARCHAR(255) DEFAULT 'assets/logo.png',
        status ENUM('tersedia', 'habis') DEFAULT 'tersedia',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Cek akun admin default
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE role = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        $default_pass = password_hash('admin123', PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO users (nama_lengkap, username, password, role) VALUES ('Administrator Warkop', 'admin', :pass, 'admin')");
        $insert->execute(['pass' => $default_pass]);
        echo "Akun admin default berhasil dibuat: username=admin, password=admin123\n";
    } else {
        echo "Akun admin sudah tersedia: " . e($admin['username']) . "\n";
    }

    // Seed sample menu_items jika kosong
    $menu_count = $pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();
    if ($menu_count == 0) {
        $menus = [
            // Makanan
            ['Nasi Goreng Madam', 'makanan', 'Special Nasi & Mie', 19000, 'Nasi goreng racikan bumbu khas Madam dengan suwiran ayam gurih, telur, dan kerupuk renyah.', 'assets/menu/nasi_goreng.jpg'],
            ['Nasi Goreng Cabe Ijo', 'makanan', 'Special Nasi & Mie', 19000, 'Nasi goreng dengan aroma cabai hijau segar pedas nendang dan topping lengkap.', 'assets/menu/nasi_goreng_cabe_ijo.jpg'],
            ['Mie Nyemek', 'makanan', 'Special Nasi & Mie', 17000, 'Mie kuah nyemek kental gurih berpadu telur, sayuran segar, dan irisan cabai rawit.', 'assets/menu/mie_nyemek.jpg'],
            ['Mie Goreng', 'makanan', 'Special Nasi & Mie', 19000, 'Mie goreng spesial bumbu kecap gurih manis dengan telur dan pelengkap istimewa.', 'assets/menu/mie_goreng.jpg'],
            ['Ayam Goreng Sambal Ijo', 'makanan', 'Olahan Ayam', 22000, 'Ayam goreng renyah empuk disiram ulekan sambal ijo segar + lalapan & tahu/tempe.', 'assets/menu/ayam_goreng_sambal_ijo.jpg'],
            ['Ayam Maranggi', 'makanan', 'Olahan Ayam', 22000, 'Ayam panggang bumbu maranggi manis gurih beraroma rempah bakar sedap.', 'assets/menu/ayam_maranggi.jpg'],
            ['Chiken Katsu', 'makanan', 'Olahan Ayam', 22000, 'Fillet ayam krispi tebal keemasan disajikan dengan saus cocolan nikmat & nasi.', 'assets/menu/chiken_katsu.jpg'],
            ['Chiken Wings', 'makanan', 'Olahan Ayam', 22000, 'Sayap ayam bumbu saus spesial legit gurih disajikan dengan kentang goreng renyah.', 'assets/menu/chiken_wings.jpg'],

            // Cemilan
            ['Kentang Goreng', 'cemilan', 'Gorengan Gurih', 12000, 'French fries renyah keemasan dengan cocolan saus sambal & tomat gurih.', 'assets/menu/kentang_goreng.jpg'],
            ['Cireng Isi', 'cemilan', 'Gorengan Gurih', 13000, 'Cireng kenyal gurih renyah dengan isian lezat dan sambal cocol rujak pedas manis.', 'assets/menu/cireng_isi.jpg'],
            ['Pisang Keju', 'cemilan', 'Manis Lezat', 12000, 'Pisang goreng manis empuk ditaburi limpahan keju parut dan cokelat kental manis.', 'assets/menu/pisang_keju.jpg'],
            ['Mix Platter', 'cemilan', 'Special Sharing', 15000, 'Kombinasi kentang, sosis, nugget, dan otak-otak dalam satu porsi komplit.', 'assets/menu/mix_platter.jpg'],
            ['Roti Bakar Keju / Coklat', 'cemilan', 'Roti Bakar', 12000, 'Roti tebal panggang lembut dengan pilihan topping keju gurih atau coklat lumer.', 'assets/menu/roti_bakar.jpg'],

            // Minuman
            ['Kopi MADAM Signature', 'minuman', 'Aneka Kopi', 12000, 'Racikan kopi hitam khas racikan barista Warkop Madam.', 'assets/menu/kopi_madam.jpg'],
            ['Kopi Susu Aren Madam', 'minuman', 'Aneka Kopi', 12000, 'Kopi susu creamy berpadu gula aren murni pilihan disajikan dingin menyegarkan.', 'assets/menu/kopi_susu.jpg'],
            ['MaxTea Tarik / Teh Manis', 'minuman', 'Tea / Teh', 10000, 'Teh tarik berbuih creamy nikmat atau es teh manis segar pelepas dahaga.', 'assets/menu/teh_tarik.jpg'],
            ['Nutrisari Aneka Rasa (Ice)', 'minuman', 'Segar Dingin', 8000, 'Pilihan rasa buah segar dengan es batu melimpah.', 'assets/menu/nutrisari_jeruk.jpg'],
            ['Soda Susu Gembira', 'minuman', 'Suplemen & Soda', 12000, 'Perpaduan soda segar, sirup merah manis legit, dan susu kental manis legendaris.', 'assets/menu/soda_susu.jpg']
        ];

        $stmt_menu = $pdo->prepare("INSERT INTO menu_items (nama_menu, kategori, subkategori, harga, deskripsi, foto, status) VALUES (?, ?, ?, ?, ?, ?, 'tersedia')");
        foreach ($menus as $m) {
            $stmt_menu->execute($m);
        }
    }

    // Seed sample orders jika tabel orders kosong
    $orders_count = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    if ($orders_count == 0) {
        $sample_orders = [
            ['#MDM-8821', 'Budi Santoso', 'Meja 04', 'QRIS', 43000, '2x Nasi Goreng Madam, 1x Kopi MADAM Signature', 'Pedas sedang', 'selesai'],
            ['#MDM-8822', 'Rian Pratama', 'Meja 02', 'Transfer Mandiri', 34000, '1x Nasi Goreng Cabe Ijo, 1x Pisang Keju', '', 'selesai'],
            ['#MDM-8823', 'Siti Rahma', 'Meja 07', 'QRIS', 37000, '1x Ayam Goreng Sambal Ijo, 1x Kopi Susu Aren Madam', 'Sambal dipisah', 'selesai'],
            ['#MDM-8824', 'Dimas Arya', 'Meja 01', 'Tunai', 41000, '1x Mie Nyemek, 1x Kentang Goreng, 1x Kopi MADAM Signature', '', 'selesai'],
            ['#MDM-8825', 'Agus Setiawan', 'Meja 05', 'QRIS', 51000, '2x Mie Goreng, 1x Cireng Isi', '', 'selesai'],
            ['#MDM-8826', 'Nabila Putri', 'Meja 03', 'QRIS', 31000, '1x Nasi Goreng Madam, 1x Kopi Susu Aren Madam', 'Manis legit', 'selesai'],
            ['#MDM-8827', 'Fajar Ramadhan', 'Meja 06', 'Tunai', 44000, '1x Ayam Maranggi, 1x Pisang Keju, 1x Es Teh Manis', '', 'selesai'],
            ['#MDM-8828', 'Kevin Sanjaya', 'Meja 08', 'Transfer Mandiri', 35000, '1x Nasi Goreng Madam, 1x Cireng Isi, 1x Nutrisari Aneka Rasa (Ice)', '', 'diproses'],
            ['#MDM-8829', 'Anisa Melani', 'Meja 10', 'QRIS', 24000, '2x Kopi MADAM Signature', 'Panas', 'pending']
        ];

        $stmt_order = $pdo->prepare("INSERT INTO orders (order_number, customer_name, table_number, payment_method, total_price, order_items, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($sample_orders as $so) {
            $stmt_order->execute($so);
        }
    }

} catch (PDOException $e) {
    error_log("Database initialization error: " . $e->getMessage());
    echo "Info database aman: Inisialisasi selesai.\n";
}
