<?php
require_once __DIR__ . '/security.php';
require_admin();

$admin_name = $_SESSION['nama_lengkap'] ?? 'Administrator';

$message = '';
$message_type = 'success';

try {
    $pdo = get_db_connection();

    // 1. Tambah Menu Baru
    if (isset($_POST['action_add_menu'])) {
        validate_csrf_or_die();

        $nama_menu   = strip_tags(trim($_POST['nama_menu'] ?? ''));
        $kategori    = trim($_POST['kategori'] ?? 'makanan');
        $subkategori = strip_tags(trim($_POST['subkategori'] ?? 'Umum'));
        $harga       = intval($_POST['harga'] ?? 0);
        $deskripsi   = strip_tags(trim($_POST['deskripsi'] ?? ''));
        $foto        = 'assets/logo.png';

        if (!in_array($kategori, ['makanan', 'cemilan', 'minuman'], true)) {
            $kategori = 'makanan';
        }

        // Handle upload foto jika ada
        if (isset($_FILES['foto_menu']) && $_FILES['foto_menu']['error'] === UPLOAD_ERR_OK) {
            $upload_res = validate_and_save_upload($_FILES['foto_menu'], 'assets/menu/', 4194304);
            if ($upload_res['success']) {
                $foto = $upload_res['file_path'];
            } else {
                $message = "Upload foto gagal: " . $upload_res['message'];
                $message_type = 'error';
            }
        }

        if (empty($message_type) || $message_type !== 'error') {
            if (!empty($nama_menu) && $harga > 0 && $harga <= 100000000) {
                $stmt = $pdo->prepare("INSERT INTO menu_items (nama_menu, kategori, subkategori, harga, deskripsi, foto, status) VALUES (:nama, :kat, :sub, :harga, :deskripsi, :foto, 'tersedia')");
                $stmt->execute([
                    'nama'      => $nama_menu,
                    'kat'       => $kategori,
                    'sub'       => $subkategori,
                    'harga'     => $harga,
                    'deskripsi' => $deskripsi,
                    'foto'      => $foto
                ]);
                $message = "Menu '$nama_menu' berhasil ditambahkan ke katalog!";
            } else {
                $message = "Nama menu dan harga valid wajib diisi!";
                $message_type = 'error';
            }
        }
    }

    // 2. Edit Menu
    if (isset($_POST['action_edit_menu'])) {
        validate_csrf_or_die();

        $id          = intval($_POST['menu_id']);
        $nama_menu   = strip_tags(trim($_POST['nama_menu'] ?? ''));
        $kategori    = trim($_POST['kategori'] ?? 'makanan');
        $subkategori = strip_tags(trim($_POST['subkategori'] ?? 'Umum'));
        $harga       = intval($_POST['harga'] ?? 0);
        $deskripsi   = strip_tags(trim($_POST['deskripsi'] ?? ''));
        $status      = trim($_POST['status'] ?? 'tersedia');

        if (!in_array($kategori, ['makanan', 'cemilan', 'minuman'], true)) {
            $kategori = 'makanan';
        }
        if (!in_array($status, ['tersedia', 'habis'], true)) {
            $status = 'tersedia';
        }

        $new_foto_path = null;
        if (isset($_FILES['foto_menu']) && $_FILES['foto_menu']['error'] === UPLOAD_ERR_OK) {
            $upload_res = validate_and_save_upload($_FILES['foto_menu'], 'assets/menu/', 4194304);
            if ($upload_res['success']) {
                $new_foto_path = $upload_res['file_path'];
            } else {
                $message = "Upload foto gagal: " . $upload_res['message'];
                $message_type = 'error';
            }
        }

        if (empty($message_type) || $message_type !== 'error') {
            if (!empty($nama_menu) && $harga > 0 && $id > 0) {
                if ($new_foto_path !== null) {
                    $sql_edit = "UPDATE menu_items SET nama_menu = :nama, kategori = :kat, subkategori = :sub, harga = :harga, deskripsi = :deskripsi, status = :status, foto = :foto WHERE id = :id";
                    $stmt = $pdo->prepare($sql_edit);
                    $stmt->execute([
                        'nama'      => $nama_menu,
                        'kat'       => $kategori,
                        'sub'       => $subkategori,
                        'harga'     => $harga,
                        'deskripsi' => $deskripsi,
                        'status'    => $status,
                        'foto'      => $new_foto_path,
                        'id'        => $id
                    ]);
                } else {
                    $sql_edit = "UPDATE menu_items SET nama_menu = :nama, kategori = :kat, subkategori = :sub, harga = :harga, deskripsi = :deskripsi, status = :status WHERE id = :id";
                    $stmt = $pdo->prepare($sql_edit);
                    $stmt->execute([
                        'nama'      => $nama_menu,
                        'kat'       => $kategori,
                        'sub'       => $subkategori,
                        'harga'     => $harga,
                        'deskripsi' => $deskripsi,
                        'status'    => $status,
                        'id'        => $id
                    ]);
                }
                $message = "Menu '$nama_menu' berhasil diperbarui!";
            } else {
                $message = "Data menu tidak lengkap atau ID tidak valid.";
                $message_type = 'error';
            }
        }
    }

    // 3. Toggle Status Stok
    if (isset($_GET['toggle_status']) && isset($_GET['csrf_token'])) {
        if (verify_csrf_token($_GET['csrf_token'])) {
            $id = intval($_GET['toggle_status']);
            $stmt_cur = $pdo->prepare("SELECT status FROM menu_items WHERE id = ?");
            $stmt_cur->execute([$id]);
            $cur = $stmt_cur->fetchColumn();
            if ($cur !== false) {
                $new_st = ($cur === 'tersedia') ? 'habis' : 'tersedia';
                $stmt_up = $pdo->prepare("UPDATE menu_items SET status = ? WHERE id = ?");
                $stmt_up->execute([$new_st, $id]);
            }
            header('Location: kelola_menu.php?msg=Status stok berhasil diubah');
            exit;
        }
    }

    // 4. Hapus Menu
    if (isset($_POST['action_delete_menu'])) {
        validate_csrf_or_die();
        $id = intval($_POST['menu_id']);
        if ($id > 0) {
            $stmt_del = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
            $stmt_del->execute([$id]);
            $message = "Menu berhasil dihapus dari daftar!";
        }
    }

    // Filter Kategori & Search Sanitized
    $filter_kat = $_GET['kategori'] ?? 'semua';
    $search = trim($_GET['q'] ?? '');

    $query = "SELECT * FROM menu_items WHERE 1=1";
    $params = [];

    if ($filter_kat !== 'semua' && in_array($filter_kat, ['makanan', 'cemilan', 'minuman'], true)) {
        $query .= " AND kategori = :kat";
        $params['kat'] = $filter_kat;
    }

    if (!empty($search)) {
        $query .= " AND (nama_menu LIKE :search OR subkategori LIKE :search)";
        $params['search'] = "%$search%";
    }

    $query .= " ORDER BY kategori ASC, id DESC";
    $stmt_menu = $pdo->prepare($query);
    $stmt_menu->execute($params);
    $menu_list = $stmt_menu->fetchAll();

    // Hitung total per kategori
    $count_all = $pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();
    $count_makanan = $pdo->query("SELECT COUNT(*) FROM menu_items WHERE kategori='makanan'")->fetchColumn();
    $count_cemilan = $pdo->query("SELECT COUNT(*) FROM menu_items WHERE kategori='cemilan'")->fetchColumn();
    $count_minuman = $pdo->query("SELECT COUNT(*) FROM menu_items WHERE kategori='minuman'")->fetchColumn();

} catch (Exception $e) {
    error_log("Kelola Menu Error: " . $e->getMessage());
    $message = "Terjadi kendala sistem database yang aman.";
    $message_type = 'error';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola & Tambah Menu - Warkop Madam (Secured)</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif-title { font-family: 'Playfair Display', serif; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0c0a09; }
        ::-webkit-scrollbar-thumb { background: #292524; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #d97706; }
    </style>
</head>
<body class="bg-[#09090b] text-stone-100 min-h-screen flex selection:bg-amber-500 selection:text-stone-950">

    <!-- ============================================== -->
    <!-- 1. LEFT SIDEBAR                                -->
    <!-- ============================================== -->
    <aside class="w-64 bg-[#121113] border-r border-stone-800/80 min-h-screen flex flex-col justify-between shrink-0 sticky top-0 h-screen z-30 overflow-y-auto hidden md:flex">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-11 h-11 rounded-2xl bg-black p-1 border border-amber-500/40 shadow-lg flex items-center justify-center shrink-0">
                    <img src="assets/logo.png" alt="Warkop Madam Logo" class="w-full h-full object-contain">
                </div>
                <div>
                    <h1 class="text-lg font-extrabold font-serif-title tracking-wider text-amber-300">
                        WARKOP MADAM
                    </h1>
                    <p class="text-[11px] text-stone-400 font-semibold tracking-wide">Admin Panel</p>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="space-y-1.5 text-xs font-semibold">
                <a href="dashboard_admin.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-stone-400 hover:text-white hover:bg-stone-900/90 transition group">
                    <i class="fa-solid fa-gauge-high text-base text-stone-400 group-hover:text-amber-400 transition"></i>
                    <span>Dashboard</span>
                </a>

                <a href="kelola_pesanan.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-stone-400 hover:text-white hover:bg-stone-900/90 transition group">
                    <i class="fa-solid fa-bell-concierge text-base text-stone-400 group-hover:text-amber-400 transition"></i>
                    <span>Kelola Pesanan</span>
                </a>

                <a href="kelola_menu.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-500 text-stone-950 font-bold shadow-lg shadow-amber-950/40 transition">
                    <i class="fa-solid fa-utensils text-base text-stone-950"></i>
                    <span class="text-sm font-extrabold">Tambah & Kelola Menu</span>
                </a>

                <a href="laporan_bulanan.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-stone-400 hover:text-white hover:bg-stone-900/90 transition group">
                    <i class="fa-solid fa-file-invoice-dollar text-base text-stone-400 group-hover:text-amber-400 transition"></i>
                    <span>Laporan Bulanan</span>
                </a>

                <div class="pt-4 pb-2">
                    <span class="px-4 text-[10px] uppercase font-bold tracking-widest text-stone-600">Akses Publik & Akun</span>
                </div>

                <a href="menu.php" target="_blank" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-stone-400 hover:text-amber-300 hover:bg-stone-900/90 transition group">
                    <i class="fa-solid fa-eye text-base text-stone-400 group-hover:text-amber-400 transition"></i>
                    <span>Buka Menu Tamu</span>
                </a>

                <a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-red-400/90 hover:text-red-300 hover:bg-red-950/40 transition">
                    <i class="fa-solid fa-right-from-bracket text-base"></i>
                    <span>Keluar / Logout</span>
                </a>
            </nav>
        </div>

        <div class="p-4 m-4 rounded-2xl bg-stone-900/70 border border-stone-800 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-sm">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <div class="overflow-hidden">
                <span class="text-xs font-bold text-stone-200 block truncate"><?php echo e($admin_name); ?></span>
                <span class="text-[10px] text-emerald-400 flex items-center gap-1 font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Online
                </span>
            </div>
        </div>
    </aside>

    <!-- ============================================== -->
    <!-- 2. MAIN CONTENT AREA                           -->
    <!-- ============================================== -->
    <div class="flex-1 flex flex-col min-w-0 bg-[#0c0a09]">
        
        <!-- Mobile Header -->
        <header class="md:hidden sticky top-0 z-40 bg-stone-950/95 backdrop-blur-xl border-b border-stone-800 p-4 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <img src="assets/logo.png" alt="Warkop Madam" class="w-8 h-8 object-contain">
                <span class="font-bold text-amber-300 font-serif-title text-sm">WARKOP MADAM</span>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <a href="dashboard_admin.php" class="px-2.5 py-1.5 rounded-lg bg-stone-900 text-stone-200">Dashboard</a>
                <a href="kelola_pesanan.php" class="px-2.5 py-1.5 rounded-lg bg-stone-900 text-stone-200">Pesanan</a>
                <a href="logout.php" class="px-2.5 py-1.5 rounded-lg bg-red-950 text-red-300">Keluar</a>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-7 max-w-7xl w-full mx-auto space-y-6">

            <!-- Notification Banner -->
            <?php if (!empty($message) || !empty($_GET['msg'])): ?>
                <?php $alertText = !empty($message) ? $message : strip_tags($_GET['msg']); ?>
                <div class="p-4 rounded-2xl <?php echo $message_type === 'error' ? 'bg-red-950/80 border border-red-500/40 text-red-200' : 'bg-emerald-950/80 border border-emerald-500/40 text-emerald-200'; ?> text-xs flex items-center justify-between shadow-xl">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid <?php echo $message_type === 'error' ? 'fa-triangle-exclamation text-red-400' : 'fa-circle-check text-emerald-400'; ?> text-sm"></i>
                        <span><?php echo e($alertText); ?></span>
                    </span>
                    <button onclick="this.parentElement.remove()" class="text-stone-400 hover:text-white">&times;</button>
                </div>
            <?php endif; ?>

            <!-- Hero Banner -->
            <div class="bg-gradient-to-r from-[#171412] via-[#1c1815] to-[#171412] rounded-3xl p-6 sm:p-8 border border-stone-800 shadow-2xl relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="text-xs text-amber-400 font-bold uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-kitchen-set"></i> Katalog Menu Warkop Madam
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-black font-serif-title text-stone-100 mt-1">
                        Tambah & Kelola Daftar Menu
                    </h2>
                    <p class="text-xs text-stone-400 mt-1">
                        Atur harga, foto hidangan, kategori, dan ketersediaan stok menu untuk pelanggan secara aman.
                    </p>
                </div>
                <button onclick="openAddMenuModal()" class="px-5 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-stone-950 font-black text-xs shadow-lg shadow-amber-500/20 transition flex items-center gap-2 self-start md:self-auto active:scale-95">
                    <i class="fa-solid fa-plus-circle text-sm"></i>
                    <span>Tambah Menu Baru</span>
                </button>
            </div>

            <!-- Filter Tabs & Search Bar -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
                    <a href="kelola_menu.php?kategori=semua" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 <?php echo $filter_kat === 'semua' ? 'bg-amber-500 text-stone-950 shadow-md' : 'bg-stone-900 text-stone-300 border border-stone-800 hover:border-amber-500/30'; ?>">
                        <span>Semua Menu</span>
                        <span class="px-1.5 py-0.5 rounded-md text-[10px] <?php echo $filter_kat === 'semua' ? 'bg-stone-950 text-amber-300' : 'bg-stone-800 text-stone-400'; ?>"><?php echo intval($count_all); ?></span>
                    </a>
                    <a href="kelola_menu.php?kategori=makanan" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 <?php echo $filter_kat === 'makanan' ? 'bg-amber-500 text-stone-950 shadow-md' : 'bg-stone-900 text-stone-300 border border-stone-800 hover:border-amber-500/30'; ?>">
                        <i class="fa-solid fa-utensils text-xs"></i>
                        <span>1. Makanan</span>
                        <span class="px-1.5 py-0.5 rounded-md text-[10px] <?php echo $filter_kat === 'makanan' ? 'bg-stone-950 text-amber-300' : 'bg-stone-800 text-stone-400'; ?>"><?php echo intval($count_makanan); ?></span>
                    </a>
                    <a href="kelola_menu.php?kategori=cemilan" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 <?php echo $filter_kat === 'cemilan' ? 'bg-amber-500 text-stone-950 shadow-md' : 'bg-stone-900 text-stone-300 border border-stone-800 hover:border-amber-500/30'; ?>">
                        <i class="fa-solid fa-cookie-bite text-xs"></i>
                        <span>2. Cemilan</span>
                        <span class="px-1.5 py-0.5 rounded-md text-[10px] <?php echo $filter_kat === 'cemilan' ? 'bg-stone-950 text-amber-300' : 'bg-stone-800 text-stone-400'; ?>"><?php echo intval($count_cemilan); ?></span>
                    </a>
                    <a href="kelola_menu.php?kategori=minuman" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 <?php echo $filter_kat === 'minuman' ? 'bg-amber-500 text-stone-950 shadow-md' : 'bg-stone-900 text-stone-300 border border-stone-800 hover:border-amber-500/30'; ?>">
                        <i class="fa-solid fa-mug-hot text-xs"></i>
                        <span>3. Minuman</span>
                        <span class="px-1.5 py-0.5 rounded-md text-[10px] <?php echo $filter_kat === 'minuman' ? 'bg-stone-950 text-amber-300' : 'bg-stone-800 text-stone-400'; ?>"><?php echo intval($count_minuman); ?></span>
                    </a>
                </div>

                <form action="" method="GET" class="relative min-w-[280px]">
                    <?php if ($filter_kat !== 'semua'): ?>
                        <input type="hidden" name="kategori" value="<?php echo e($filter_kat); ?>">
                    <?php endif; ?>
                    <input type="text" name="q" value="<?php echo e($search); ?>" placeholder="Cari nama menu..." maxlength="100" class="w-full pl-9 pr-4 py-2 bg-stone-900 border border-stone-800 rounded-xl text-xs text-stone-100 placeholder-stone-500 focus:outline-none focus:border-amber-500">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-stone-500 text-xs"></i>
                </form>
            </div>

            <!-- Menu Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
                <?php if (empty($menu_list)): ?>
                    <div class="col-span-full text-center py-16 bg-[#121113] rounded-3xl border border-stone-800 text-stone-500">
                        <i class="fa-solid fa-bowl-food text-4xl mb-3 text-stone-700 block"></i>
                        <p class="text-sm font-semibold">Tidak ada menu yang ditemukan pada kategori ini.</p>
                        <button onclick="openAddMenuModal()" class="mt-3 text-xs text-amber-400 underline font-semibold">Tambah Menu Sekarang</button>
                    </div>
                <?php else: ?>
                    <?php foreach ($menu_list as $item): ?>
                        <div class="bg-[#121113] rounded-2xl border border-stone-800 hover:border-amber-500/40 p-3 shadow-lg flex flex-col justify-between transition duration-200 group">
                            <div>
                                <div class="relative w-full h-44 rounded-xl bg-black overflow-hidden mb-3 border border-stone-800/80">
                                    <img src="<?php echo e($item['foto']); ?>" alt="<?php echo e($item['nama_menu']); ?>" onerror="this.onerror=null; this.src='assets/logo.png';" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                    
                                    <span class="absolute top-2 left-2 px-2.5 py-0.5 rounded-md bg-black/80 backdrop-blur-md text-[10px] font-bold uppercase tracking-wider <?php echo $item['kategori'] === 'makanan' ? 'text-amber-300' : ($item['kategori'] === 'cemilan' ? 'text-orange-300' : 'text-cyan-300'); ?>">
                                        <?php echo e($item['kategori']); ?>
                                    </span>

                                    <a href="kelola_menu.php?toggle_status=<?php echo intval($item['id']); ?>&csrf_token=<?php echo csrf_token(); ?>" title="Klik untuk ubah status stok" class="absolute top-2 right-2 px-2.5 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wider transition shadow-md <?php echo $item['status'] === 'tersedia' ? 'bg-emerald-500 text-stone-950 hover:bg-emerald-400' : 'bg-red-600 text-white hover:bg-red-500'; ?>">
                                        <?php echo $item['status'] === 'tersedia' ? 'Tersedia' : 'Habis'; ?>
                                    </a>
                                </div>

                                <span class="text-[10px] text-stone-500 block uppercase"><?php echo e($item['subkategori']); ?></span>
                                <h4 class="font-bold text-sm text-stone-100 leading-snug"><?php echo e($item['nama_menu']); ?></h4>

                                <?php if (!empty($item['deskripsi'])): ?>
                                    <p class="text-[11px] text-stone-400 line-clamp-2 mt-1"><?php echo e($item['deskripsi']); ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="pt-3 mt-3 border-t border-stone-800/80 flex items-center justify-between">
                                <span class="text-sm font-black font-serif-title text-amber-400">
                                    Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?>
                                </span>

                                <div class="flex items-center gap-1.5">
                                    <button onclick="openEditMenuModal(<?php echo htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8'); ?>)" class="w-7 h-7 rounded-lg bg-stone-900 hover:bg-amber-500/20 text-stone-300 hover:text-amber-300 border border-stone-800 flex items-center justify-center text-xs transition" title="Edit Menu">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <form action="" method="POST" onsubmit="return confirm('Hapus menu <?php echo addslashes($item['nama_menu']); ?>?');">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="menu_id" value="<?php echo intval($item['id']); ?>">
                                        <input type="hidden" name="action_delete_menu" value="1">
                                        <button type="submit" class="w-7 h-7 rounded-lg bg-stone-900 hover:bg-red-500/20 text-stone-300 hover:text-red-400 border border-stone-800 flex items-center justify-center text-xs transition" title="Hapus Menu">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </main>

        <footer class="text-center text-xs text-stone-500 py-6 border-t border-stone-900">
            &copy; 2026 <span class="text-amber-400 font-medium">Warkop Madam</span> &bull; Modul Kelola Menu Aman
        </footer>

    </div>

    <!-- Modals -->
    <div id="add-menu-modal" class="fixed inset-0 z-50 bg-black/90 backdrop-blur-md hidden flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-stone-950 border border-amber-500/40 w-full max-w-lg rounded-3xl p-6 sm:p-8 shadow-2xl relative my-auto">
            <button onclick="closeAddMenuModal()" class="absolute top-5 right-5 text-stone-400 hover:text-white text-lg">&times;</button>
            <div class="flex items-center gap-2.5 mb-5">
                <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-base">
                    <i class="fa-solid fa-plus"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold font-serif-title text-amber-200">Tambah Menu Baru</h3>
                    <p class="text-xs text-stone-400">Masukkan rincian makanan, cemilan, atau minuman</p>
                </div>
            </div>
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action_add_menu" value="1">
                <div>
                    <label class="block font-semibold text-stone-300 mb-1">Nama Menu <span class="text-red-400">*</span></label>
                    <input type="text" name="nama_menu" required maxlength="100" placeholder="Contoh: Nasi Goreng Spesial Madam" class="w-full px-3.5 py-2.5 bg-stone-900 border border-stone-800 rounded-xl text-stone-100 focus:outline-none focus:border-amber-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-stone-300 mb-1">Kategori <span class="text-red-400">*</span></label>
                        <select name="kategori" required class="w-full px-3.5 py-2.5 bg-stone-900 border border-stone-800 rounded-xl text-stone-100 focus:outline-none focus:border-amber-500">
                            <option value="makanan">1. Makanan (Atas)</option>
                            <option value="cemilan">2. Cemilan (Tengah)</option>
                            <option value="minuman">3. Minuman (Bawah)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-stone-300 mb-1">Subkategori</label>
                        <input type="text" name="subkategori" maxlength="50" placeholder="Contoh: Special Olahan Ayam" class="w-full px-3.5 py-2.5 bg-stone-900 border border-stone-800 rounded-xl text-stone-100 focus:outline-none focus:border-amber-500">
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-stone-300 mb-1">Harga (Rp) <span class="text-red-400">*</span></label>
                    <input type="number" name="harga" required min="500" max="10000000" step="500" placeholder="15000" class="w-full px-3.5 py-2.5 bg-stone-900 border border-stone-800 rounded-xl text-stone-100 focus:outline-none focus:border-amber-500">
                </div>
                <div>
                    <label class="block font-semibold text-stone-300 mb-1">Deskripsi Singkat</label>
                    <textarea name="deskripsi" rows="2" maxlength="500" placeholder="Keterangan hidangan..." class="w-full px-3.5 py-2 bg-stone-900 border border-stone-800 rounded-xl text-stone-100 focus:outline-none focus:border-amber-500"></textarea>
                </div>
                <div>
                    <label class="block font-semibold text-stone-300 mb-1">Upload Foto Menu (JPG/PNG/WebP, Maks 4MB)</label>
                    <input type="file" name="foto_menu" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="w-full px-3.5 py-2 bg-stone-900 border border-stone-800 rounded-xl text-stone-400 text-xs focus:outline-none file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-500 file:text-stone-950">
                </div>
                <div class="pt-3 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeAddMenuModal()" class="px-4 py-2.5 rounded-xl bg-stone-900 text-stone-300 font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-400 text-stone-950 font-bold shadow-md">Simpan Menu Baru</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="edit-menu-modal" class="fixed inset-0 z-50 bg-black/90 backdrop-blur-md hidden flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-stone-950 border border-amber-500/40 w-full max-w-lg rounded-3xl p-6 sm:p-8 shadow-2xl relative my-auto">
            <button onclick="closeEditMenuModal()" class="absolute top-5 right-5 text-stone-400 hover:text-white text-lg">&times;</button>
            <div class="flex items-center gap-2.5 mb-5">
                <div class="w-10 h-10 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center text-base">
                    <i class="fa-solid fa-pen"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold font-serif-title text-amber-200">Edit Data Menu</h3>
                    <p class="text-xs text-stone-400">Perbarui informasi harga atau stok</p>
                </div>
            </div>
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action_edit_menu" value="1">
                <input type="hidden" id="edit-id" name="menu_id" value="">
                <div>
                    <label class="block font-semibold text-stone-300 mb-1">Nama Menu <span class="text-red-400">*</span></label>
                    <input type="text" id="edit-nama" name="nama_menu" required maxlength="100" class="w-full px-3.5 py-2.5 bg-stone-900 border border-stone-800 rounded-xl text-stone-100 focus:outline-none focus:border-amber-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-stone-300 mb-1">Kategori</label>
                        <select id="edit-kategori" name="kategori" required class="w-full px-3.5 py-2.5 bg-stone-900 border border-stone-800 rounded-xl text-stone-100 focus:outline-none focus:border-amber-500">
                            <option value="makanan">Makanan</option>
                            <option value="cemilan">Cemilan</option>
                            <option value="minuman">Minuman</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-stone-300 mb-1">Subkategori</label>
                        <input type="text" id="edit-subkategori" name="subkategori" maxlength="50" class="w-full px-3.5 py-2.5 bg-stone-900 border border-stone-800 rounded-xl text-stone-100 focus:outline-none focus:border-amber-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-stone-300 mb-1">Harga (Rp) <span class="text-red-400">*</span></label>
                        <input type="number" id="edit-harga" name="harga" required min="500" max="10000000" step="500" class="w-full px-3.5 py-2.5 bg-stone-900 border border-stone-800 rounded-xl text-stone-100 focus:outline-none focus:border-amber-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-stone-300 mb-1">Status Ketersediaan</label>
                        <select id="edit-status" name="status" class="w-full px-3.5 py-2.5 bg-stone-900 border border-stone-800 rounded-xl text-stone-100 focus:outline-none focus:border-amber-500">
                            <option value="tersedia">Tersedia</option>
                            <option value="habis">Habis (Kosong)</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-stone-300 mb-1">Deskripsi Singkat</label>
                    <textarea id="edit-deskripsi" name="deskripsi" rows="2" maxlength="500" class="w-full px-3.5 py-2 bg-stone-900 border border-stone-800 rounded-xl text-stone-100 focus:outline-none focus:border-amber-500"></textarea>
                </div>
                <div>
                    <label class="block font-semibold text-stone-300 mb-1">Ganti Foto (Opsional, JPG/PNG/WebP, Maks 4MB)</label>
                    <input type="file" name="foto_menu" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="w-full px-3.5 py-2 bg-stone-900 border border-stone-800 rounded-xl text-stone-400 text-xs focus:outline-none file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-500 file:text-stone-950">
                </div>
                <div class="pt-3 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeEditMenuModal()" class="px-4 py-2.5 rounded-xl bg-stone-900 text-stone-300 font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-blue-500 text-white font-bold shadow-md">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddMenuModal() { document.getElementById('add-menu-modal').classList.remove('hidden'); }
        function closeAddMenuModal() { document.getElementById('add-menu-modal').classList.add('hidden'); }
        function openEditMenuModal(item) {
            document.getElementById('edit-id').value = item.id;
            document.getElementById('edit-nama').value = item.nama_menu;
            document.getElementById('edit-kategori').value = item.kategori;
            document.getElementById('edit-subkategori').value = item.subkategori;
            document.getElementById('edit-harga').value = item.harga;
            document.getElementById('edit-status').value = item.status;
            document.getElementById('edit-deskripsi').value = item.deskripsi || '';
            document.getElementById('edit-menu-modal').classList.remove('hidden');
        }
        function closeEditMenuModal() { document.getElementById('edit-menu-modal').classList.add('hidden'); }
    </script>

</body>
</html>
