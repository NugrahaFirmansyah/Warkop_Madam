<?php
require_once __DIR__ . '/security.php';
require_admin();

$admin_name = $_SESSION['nama_lengkap'] ?? 'Administrator';

$message = '';
$message_type = 'success';

try {
    $pdo = get_db_connection();

    // 1. Update Status Pesanan
    if (isset($_POST['update_status'])) {
        validate_csrf_or_die();
        $order_id = intval($_POST['order_id']);
        $new_status = trim($_POST['status'] ?? '');
        $allowed_statuses = ['pending', 'diproses', 'selesai', 'dibatalkan'];

        if ($order_id > 0 && in_array($new_status, $allowed_statuses, true)) {
            $stmt_up = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
            $stmt_up->execute(['status' => $new_status, 'id' => $order_id]);
            $message = "Status pesanan #$order_id berhasil diperbarui menjadi '$new_status'!";
        } else {
            $message = "Status pesanan tidak valid!";
            $message_type = 'error';
        }
    }

    // 2. Hapus Pesanan
    if (isset($_POST['delete_order'])) {
        validate_csrf_or_die();
        $order_id = intval($_POST['order_id']);
        if ($order_id > 0) {
            $stmt_del = $pdo->prepare("DELETE FROM orders WHERE id = :id");
            $stmt_del->execute(['id' => $order_id]);
            $message = "Pesanan #$order_id berhasil dihapus!";
        }
    }

    // Filter Status & Search
    $filter_status = $_GET['status'] ?? 'semua';
    $search = trim($_GET['q'] ?? '');

    $sql = "SELECT * FROM orders WHERE 1=1";
    $params = [];

    if ($filter_status !== 'semua' && in_array($filter_status, ['pending', 'diproses', 'selesai', 'dibatalkan'], true)) {
        $sql .= " AND status = :status";
        $params['status'] = $filter_status;
    }

    if (!empty($search)) {
        $sql .= " AND (order_number LIKE :q OR customer_name LIKE :q OR table_number LIKE :q)";
        $params['q'] = "%$search%";
    }

    $sql .= " ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();

    // Counts for tabs
    $count_all = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $count_pending = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
    $count_diproses = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='diproses'")->fetchColumn();
    $count_selesai = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='selesai'")->fetchColumn();
    $count_batal = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='dibatalkan'")->fetchColumn();

} catch (Exception $e) {
    error_log("Kelola Pesanan Error: " . $e->getMessage());
    $message = "Terjadi kendala sistem saat memuat antrean pesanan.";
    $message_type = 'error';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Warkop Madam (Secured)</title>
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
    <!-- 1. LEFT SIDEBAR COMPONENT                      -->
    <!-- ============================================== -->
    <?php 
    $active_tab = 'pesanan';
    $pending_badge = $count_pending ?? 0;
    require_once __DIR__ . '/includes/admin_sidebar.php'; 
    ?>

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
                <a href="kelola_menu.php" class="px-2.5 py-1.5 rounded-lg bg-stone-900 text-stone-200">Menu</a>
                <a href="logout.php" class="px-2.5 py-1.5 rounded-lg bg-red-950 text-red-300">Keluar</a>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-7 max-w-7xl w-full mx-auto space-y-6">

            <!-- Notification Banner -->
            <?php if (!empty($message)): ?>
                <div class="p-4 rounded-2xl <?php echo $message_type === 'error' ? 'bg-red-950/80 border border-red-500/40 text-red-200' : 'bg-emerald-950/80 border border-emerald-500/40 text-emerald-200'; ?> text-xs flex items-center justify-between shadow-xl">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid <?php echo $message_type === 'error' ? 'fa-triangle-exclamation text-red-400' : 'fa-circle-check text-emerald-400'; ?> text-sm"></i>
                        <span><?php echo e($message); ?></span>
                    </span>
                    <button onclick="this.parentElement.remove()" class="text-stone-400 hover:text-white">&times;</button>
                </div>
            <?php endif; ?>

            <!-- Hero Banner -->
            <div class="bg-gradient-to-r from-[#171412] via-[#1c1815] to-[#171412] rounded-3xl p-6 sm:p-8 border border-stone-800 shadow-2xl relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="text-xs text-amber-400 font-bold uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-bell-concierge"></i> Kitchen & Cashier Management
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-black font-serif-title text-stone-100 mt-1">
                        Kelola Antrean & Pesanan Meja
                    </h2>
                    <p class="text-xs text-stone-400 mt-1">
                        Pantau pesanan masuk secara real-time, perbarui status aman dengan verifikasi CSRF, dan cetak struk.
                    </p>
                </div>
                <button onclick="location.reload()" class="px-4 py-2.5 rounded-xl bg-stone-900 hover:bg-stone-800 text-stone-300 text-xs font-semibold border border-stone-800 transition flex items-center gap-2 self-start md:self-auto">
                    <i class="fa-solid fa-arrows-rotate text-amber-400"></i>
                    <span>Segarkan Antrean</span>
                </button>
            </div>

            <!-- Filter Tabs & Search Bar -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                
                <!-- Status Tabs -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
                    <a href="kelola_pesanan.php?status=semua" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 <?php echo $filter_status === 'semua' ? 'bg-amber-500 text-stone-950 shadow-md' : 'bg-stone-900 text-stone-300 border border-stone-800 hover:border-amber-500/30'; ?>">
                        <span>Semua Pesanan</span>
                        <span class="px-1.5 py-0.5 rounded-md text-[10px] <?php echo $filter_status === 'semua' ? 'bg-stone-950 text-amber-300' : 'bg-stone-800 text-stone-400'; ?>"><?php echo intval($count_all); ?></span>
                    </a>
                    <a href="kelola_pesanan.php?status=pending" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 <?php echo $filter_status === 'pending' ? 'bg-amber-500 text-stone-950 shadow-md' : 'bg-stone-900 text-amber-300 border border-amber-500/30 hover:bg-amber-500/10'; ?>">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                        <span>Pending</span>
                        <span class="px-1.5 py-0.5 rounded-md text-[10px] <?php echo $filter_status === 'pending' ? 'bg-stone-950 text-amber-300' : 'bg-stone-800 text-stone-400'; ?>"><?php echo intval($count_pending); ?></span>
                    </a>
                    <a href="kelola_pesanan.php?status=diproses" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 <?php echo $filter_status === 'diproses' ? 'bg-blue-600 text-white shadow-md' : 'bg-stone-900 text-blue-300 border border-blue-500/30 hover:bg-blue-500/10'; ?>">
                        <i class="fa-solid fa-fire text-xs"></i>
                        <span>Diproses Dapur</span>
                        <span class="px-1.5 py-0.5 rounded-md text-[10px] <?php echo $filter_status === 'diproses' ? 'bg-stone-950 text-blue-300' : 'bg-stone-800 text-stone-400'; ?>"><?php echo intval($count_diproses); ?></span>
                    </a>
                    <a href="kelola_pesanan.php?status=selesai" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 <?php echo $filter_status === 'selesai' ? 'bg-emerald-600 text-white shadow-md' : 'bg-stone-900 text-emerald-300 border border-emerald-500/30 hover:bg-emerald-500/10'; ?>">
                        <i class="fa-solid fa-circle-check text-xs"></i>
                        <span>Selesai (Lunas)</span>
                        <span class="px-1.5 py-0.5 rounded-md text-[10px] <?php echo $filter_status === 'selesai' ? 'bg-stone-950 text-emerald-300' : 'bg-stone-800 text-stone-400'; ?>"><?php echo intval($count_selesai); ?></span>
                    </a>
                    <a href="kelola_pesanan.php?status=dibatalkan" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 <?php echo $filter_status === 'dibatalkan' ? 'bg-red-600 text-white shadow-md' : 'bg-stone-900 text-red-300 border border-stone-800 hover:border-red-500/30'; ?>">
                        <i class="fa-solid fa-circle-xmark text-xs"></i>
                        <span>Batal</span>
                        <span class="px-1.5 py-0.5 rounded-md text-[10px] <?php echo $filter_status === 'dibatalkan' ? 'bg-stone-950 text-red-300' : 'bg-stone-800 text-stone-400'; ?>"><?php echo intval($count_batal); ?></span>
                    </a>
                </div>

                <!-- Search Form -->
                <form action="" method="GET" class="relative min-w-[280px]">
                    <?php if ($filter_status !== 'semua'): ?>
                        <input type="hidden" name="status" value="<?php echo e($filter_status); ?>">
                    <?php endif; ?>
                    <input type="text" name="q" value="<?php echo e($search); ?>" maxlength="100" placeholder="Cari pemesan / meja / no order..." class="w-full pl-9 pr-4 py-2 bg-stone-900 border border-stone-800 rounded-xl text-xs text-stone-100 placeholder-stone-500 focus:outline-none focus:border-amber-500">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-stone-500 text-xs"></i>
                </form>

            </div>

            <!-- Orders Table Card -->
            <div class="bg-[#121113] rounded-3xl border border-stone-800 shadow-2xl overflow-hidden mb-8">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-stone-300">
                        <thead class="bg-stone-900/60 text-stone-400 uppercase font-semibold text-[10px] tracking-wider border-b border-stone-800">
                            <tr>
                                <th class="py-3.5 px-4">Waktu</th>
                                <th class="py-3.5 px-4">No. Order</th>
                                <th class="py-3.5 px-4">Pemesan & Meja</th>
                                <th class="py-3.5 px-4">Rincian Menu & Catatan</th>
                                <th class="py-3.5 px-4">Metode Bayar</th>
                                <th class="py-3.5 px-4">Bukti Transfer</th>
                                <th class="py-3.5 px-4">Total</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4 text-right">Aksi & Struk</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-900">
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-16 text-stone-500">
                                        <i class="fa-solid fa-mug-hot text-4xl mb-3 text-stone-700 block"></i>
                                        Tidak ada antrean pesanan pada filter ini.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $row): ?>
                                    <tr class="hover:bg-stone-900/40 transition">
                                        <td class="py-3.5 px-4 font-mono text-[11px] text-stone-400 whitespace-nowrap">
                                            <?php echo date('H:i', strtotime($row['created_at'])); ?>
                                            <span class="block text-[9px] text-stone-600"><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></span>
                                        </td>
                                        
                                        <td class="py-3.5 px-4 font-mono font-bold text-amber-400 whitespace-nowrap">
                                            <?php echo e($row['order_number'] ?? '#MDM-' . $row['id']); ?>
                                        </td>

                                        <td class="py-3.5 px-4">
                                            <span class="font-bold text-stone-100 block text-xs"><?php echo e($row['customer_name']); ?></span>
                                            <span class="inline-block mt-0.5 px-2 py-0.5 rounded bg-stone-900 border border-stone-800 text-[10px] text-amber-300 font-semibold">
                                                <?php echo e($row['table_number']); ?>
                                            </span>
                                        </td>

                                        <td class="py-3.5 px-4 max-w-sm">
                                            <div class="font-medium text-amber-200 leading-relaxed"><?php echo e($row['order_items']); ?></div>
                                            <?php if (!empty($row['notes'])): ?>
                                                <div class="text-[10px] text-stone-400 italic mt-1 bg-stone-900/60 px-2 py-0.5 rounded border border-stone-800/80">
                                                    <i class="fa-solid fa-pen text-[9px] text-amber-400"></i> Ket: <?php echo e($row['notes']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <span class="px-2.5 py-1 rounded-lg bg-stone-900 border border-stone-800 text-[11px] font-medium text-stone-300">
                                                <?php echo e($row['payment_method']); ?>
                                            </span>
                                        </td>

                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <?php if (!empty($row['payment_proof'])): ?>
                                                <div class="flex flex-col gap-1 items-start">
                                                    <button onclick="viewPaymentProofModal(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>)" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-500/15 hover:bg-amber-500/25 border border-amber-500/30 text-amber-300 text-[10px] font-bold transition">
                                                        <i class="fa-solid fa-receipt"></i>
                                                        <span>Lihat Struk</span>
                                                    </button>
                                                    
                                                    <!-- AI Security Status Badge -->
                                                    <?php if ($row['ai_status'] === 'valid'): ?>
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[9px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                                            <i class="fa-solid fa-shield-check text-[9px]"></i> AI Lolos (<?php echo intval($row['ai_confidence']); ?>%)
                                                        </span>
                                                    <?php elseif ($row['ai_status'] === 'review'): ?>
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[9px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 animate-pulse">
                                                            <i class="fa-solid fa-triangle-exclamation text-[9px]"></i> AI Perlu Cek
                                                        </span>
                                                    <?php elseif ($row['ai_status'] === 'invalid'): ?>
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[9px] font-bold bg-red-500/20 text-red-300 border border-red-500/30">
                                                            <i class="fa-solid fa-circle-xmark text-[9px]"></i> AI Ditolak
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-[10px] text-stone-500 italic">
                                                    <?php echo $row['payment_method'] === 'Tunai' ? 'Tunai di Meja' : 'Belum Ada Struk'; ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="py-3.5 px-4 font-bold text-amber-400 font-mono text-xs whitespace-nowrap">
                                            Rp <?php echo number_format($row['total_price'], 0, ',', '.'); ?>
                                        </td>

                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <?php if ($row['status'] === 'pending'): ?>
                                                <span class="px-2.5 py-1 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[10px] font-bold uppercase animate-pulse">Pending</span>
                                            <?php elseif ($row['status'] === 'diproses'): ?>
                                                <span class="px-2.5 py-1 rounded-full bg-blue-500/20 text-blue-300 border border-blue-500/30 text-[10px] font-bold uppercase">Diproses</span>
                                            <?php elseif ($row['status'] === 'selesai'): ?>
                                                <span class="px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[10px] font-bold uppercase">Selesai</span>
                                            <?php else: ?>
                                                <span class="px-2.5 py-1 rounded-full bg-red-500/20 text-red-300 border border-red-500/30 text-[10px] font-bold uppercase">Batal</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                            <div class="inline-flex items-center gap-1.5">
                                                <!-- Ubah Status Dropdown with CSRF -->
                                                <form action="" method="POST" class="inline">
                                                    <?php echo csrf_field(); ?>
                                                    <input type="hidden" name="order_id" value="<?php echo intval($row['id']); ?>">
                                                    <select name="status" onchange="this.form.submit()" class="px-2 py-1 bg-stone-900 border border-stone-700 rounded-lg text-[11px] text-stone-200 focus:outline-none focus:border-amber-500 cursor-pointer">
                                                        <option value="pending" <?php echo $row['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="diproses" <?php echo $row['status'] == 'diproses' ? 'selected' : ''; ?>>Diproses</option>
                                                        <option value="selesai" <?php echo $row['status'] == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                                        <option value="dibatalkan" <?php echo $row['status'] == 'dibatalkan' ? 'selected' : ''; ?>>Batal</option>
                                                    </select>
                                                    <input type="hidden" name="update_status" value="1">
                                                </form>

                                                <!-- Cetak Struk -->
                                                <button onclick="printOrderReceipt(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>)" class="w-7 h-7 rounded-lg bg-stone-900 hover:bg-amber-500/20 text-stone-300 hover:text-amber-300 border border-stone-800 flex items-center justify-center text-xs transition" title="Cetak Struk Pesanan">
                                                    <i class="fa-solid fa-print"></i>
                                                </button>

                                                <!-- Hapus Pesanan with CSRF -->
                                                <form action="" method="POST" onsubmit="return confirm('Hapus pesanan <?php echo addslashes($row['order_number'] ?? '#MDM-' . $row['id']); ?>?');" class="inline">
                                                    <?php echo csrf_field(); ?>
                                                    <input type="hidden" name="order_id" value="<?php echo intval($row['id']); ?>">
                                                    <input type="hidden" name="delete_order" value="1">
                                                    <button type="submit" class="w-7 h-7 rounded-lg bg-stone-900 hover:bg-red-500/20 text-stone-400 hover:text-red-400 border border-stone-800 flex items-center justify-center text-xs transition" title="Hapus Data">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>

        <footer class="text-center text-xs text-stone-500 py-6 border-t border-stone-900 flex items-center justify-center gap-3">
            <span>&copy; 2026 <span class="text-amber-400 font-medium">Warkop Madam</span> &bull; Modul Kelola Pesanan Aman</span>
            <span>&bull;</span>
            <span class="inline-flex items-center gap-1.5 text-emerald-400 text-[11px]">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                Auto-refresh aktif (15s)
            </span>
        </footer>

    </div>

    <!-- Printable Receipt Window Handler -->
    <script>
        function printOrderReceipt(order) {
            const printWin = window.open('', '', 'width=400,height=600');
            printWin.document.write(`
                <html>
                <head>
                    <title>Struk Pesanan - ${order.order_number || ('#MDM-' + order.id)}</title>
                    <style>
                        body { font-family: 'Courier New', monospace; font-size: 12px; padding: 15px; color: #000; }
                        .text-center { text-align: center; }
                        .text-right { text-align: right; }
                        .border-b { border-bottom: 1px dashed #000; padding-bottom: 8px; margin-bottom: 8px; }
                        .border-t { border-top: 1px dashed #000; padding-top: 8px; margin-top: 8px; }
                        .bold { font-weight: bold; }
                    </style>
                </head>
                <body onload="window.print(); window.close();">
                    <div class="text-center border-b">
                        <h2 style="margin: 0;">WARKOP MADAM</h2>
                        <p style="margin: 2px 0;">Struk Pesanan Dapur & Kasir</p>
                        <p style="margin: 0; font-size: 10px;">${order.created_at}</p>
                    </div>
                    <div style="margin-bottom: 8px;">
                        <div>No. Order : <strong>${order.order_number || ('#MDM-' + order.id)}</strong></div>
                        <div>Pemesan   : ${order.customer_name}</div>
                        <div>Meja      : <strong>${order.table_number}</strong></div>
                        <div>Metode    : ${order.payment_method}</div>
                        ${order.notes ? `<div>Catatan   : <em>${order.notes}</em></div>` : ''}
                    </div>
                    <div class="border-t border-b">
                        <div class="bold">Rincian Menu:</div>
                        <div style="margin-top: 4px; line-height: 1.4;">${order.order_items}</div>
                    </div>
                    <div class="border-b" style="display: flex; justify-content: space-between;">
                        <span class="bold">TOTAL TAGIHAN:</span>
                        <span class="bold">Rp ${Number(order.total_price).toLocaleString('id-ID')}</span>
                    </div>
                    <div class="text-center" style="margin-top: 15px; font-size: 10px;">
                        Terima kasih atas kunjungan Anda di Warkop Madam!
                    </div>
                </body>
                </html>
            `);
            printWin.document.close();
        }

        // Proof Modal Preview with AI Audit Panel
        function viewPaymentProofModal(orderData) {
            const imgSrc = orderData.payment_proof || '';
            const orderNo = orderData.order_number || ('#MDM-' + orderData.id);
            const customer = orderData.customer_name || '-';
            const method = orderData.payment_method || '-';
            const aiStatus = orderData.ai_status || 'skipped';
            const aiConfidence = orderData.ai_confidence || 0;
            const aiAmount = orderData.ai_detected_amount ? Number(orderData.ai_detected_amount) : null;
            const totalPrice = Number(orderData.total_price) || 0;
            const refNo = orderData.ai_reference_no || '-';

            let analysis = {};
            try {
                if (orderData.ai_analysis_json) {
                    analysis = JSON.parse(orderData.ai_analysis_json);
                }
            } catch (e) {}

            document.getElementById('proof-modal-img').src = imgSrc;
            document.getElementById('proof-modal-download').href = imgSrc;
            document.getElementById('proof-modal-title').innerText = 'Struk: ' + orderNo;
            document.getElementById('proof-modal-customer').innerText = 'Pemesan: ' + customer + ' (Meja: ' + (orderData.table_number || '-') + ')';
            document.getElementById('proof-modal-method').innerText = 'METODE: ' + method.toUpperCase();

            // Populate AI Audit Card
            const badgeEl = document.getElementById('modal-ai-badge');
            const amountDiffEl = document.getElementById('modal-ai-amount-diff');
            const refNoEl = document.getElementById('modal-ai-ref');
            const statusTransEl = document.getElementById('modal-ai-trans-status');
            const reasonsEl = document.getElementById('modal-ai-reasons');
            const bankWalletEl = document.getElementById('modal-ai-bank');

            document.getElementById('modal-ai-bill').innerText = 'Rp ' + totalPrice.toLocaleString('id-ID');
            document.getElementById('modal-ai-detected').innerText = aiAmount ? ('Rp ' + aiAmount.toLocaleString('id-ID')) : 'Tidak Terbaca';

            if (aiStatus === 'valid') {
                badgeEl.className = 'px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 inline-flex items-center gap-1.5';
                badgeEl.innerHTML = `<i class="fa-solid fa-shield-check"></i> Lolos Validasi AI (${aiConfidence}%)`;
            } else if (aiStatus === 'review') {
                badgeEl.className = 'px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40 inline-flex items-center gap-1.5';
                badgeEl.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> Perlu Verifikasi Kasir (${aiConfidence}%)`;
            } else if (aiStatus === 'invalid') {
                badgeEl.className = 'px-2.5 py-1 rounded-full text-xs font-bold bg-red-500/20 text-red-300 border border-red-500/40 inline-flex items-center gap-1.5';
                badgeEl.innerHTML = `<i class="fa-solid fa-circle-xmark"></i> Ditolak AI / Meragukan`;
            } else {
                badgeEl.className = 'px-2.5 py-1 rounded-full text-xs font-bold bg-stone-800 text-stone-300 border border-stone-700 inline-flex items-center gap-1.5';
                badgeEl.innerHTML = `<i class="fa-solid fa-clock"></i> Belum Dipindai AI`;
            }

            if (aiAmount && aiAmount === totalPrice) {
                amountDiffEl.className = 'text-emerald-400 font-bold';
                amountDiffEl.innerText = 'Sesuai (100% Cocok)';
            } else if (aiAmount && aiAmount < totalPrice) {
                amountDiffEl.className = 'text-red-400 font-bold';
                amountDiffEl.innerText = 'Kurang Rp ' + (totalPrice - aiAmount).toLocaleString('id-ID');
            } else if (aiAmount && aiAmount > totalPrice) {
                amountDiffEl.className = 'text-amber-400 font-bold';
                amountDiffEl.innerText = 'Lebih Rp ' + (aiAmount - totalPrice).toLocaleString('id-ID');
            } else {
                amountDiffEl.className = 'text-stone-400';
                amountDiffEl.innerText = '-';
            }

            refNoEl.innerText = refNo;
            statusTransEl.innerText = analysis.payment_status || 'BERHASIL';
            bankWalletEl.innerText = analysis.bank_or_wallet || method;

            if (analysis.reasons && Array.isArray(analysis.reasons) && analysis.reasons.length > 0) {
                reasonsEl.innerHTML = analysis.reasons.map(r => `<li class="text-[10px] text-stone-300 flex items-start gap-1.5"><i class="fa-solid fa-angle-right text-amber-400 mt-0.5"></i> <span>${r}</span></li>`).join('');
            } else {
                reasonsEl.innerHTML = `<li class="text-[10px] text-stone-400 italic">Analisis AI selesai dan terekam di database.</li>`;
            }

            document.getElementById('proof-modal').classList.remove('hidden');
        }

        function closeProofModal() {
            document.getElementById('proof-modal').classList.add('hidden');
        }

        // Auto Refresh
        setInterval(() => {
            const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
            if (activeTag !== 'select' && activeTag !== 'input') {
                location.reload();
            }
        }, 15000);
    </script>

    <!-- Proof Modal with AI Security Audit Panel -->
    <div id="proof-modal" class="fixed inset-0 z-50 bg-black/90 backdrop-blur-md hidden flex items-center justify-center p-4 overflow-y-auto" onclick="closeProofModal()">
        <div class="max-w-2xl w-full bg-stone-950 border border-amber-500/40 rounded-3xl overflow-hidden shadow-2xl p-6 relative my-auto" onclick="event.stopPropagation()">
            <button onclick="closeProofModal()" class="absolute top-5 right-5 z-10 w-9 h-9 rounded-full bg-black/70 text-stone-300 hover:text-white flex items-center justify-center border border-stone-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
            
            <div class="mb-4 pb-3 border-b border-stone-800">
                <span class="text-[10px] text-amber-400 font-bold uppercase tracking-wider block" id="proof-modal-method">BUKTI PEMBAYARAN</span>
                <h4 class="font-bold text-lg text-stone-100 font-serif-title" id="proof-modal-title">Struk Transfer</h4>
                <p class="text-xs text-stone-400" id="proof-modal-customer">-</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Left: Struk Image -->
                <div class="flex flex-col gap-2">
                    <div class="w-full h-72 rounded-2xl bg-black overflow-hidden flex items-center justify-center border border-stone-800 p-1">
                        <img id="proof-modal-img" src="" alt="Bukti Pembayaran" class="max-w-full max-h-full object-contain rounded-xl">
                    </div>
                    <a id="proof-modal-download" href="" target="_blank" download class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-stone-900 hover:bg-stone-800 text-amber-300 text-xs font-bold border border-stone-800 transition">
                        <i class="fa-solid fa-download text-xs"></i>
                        <span>Buka / Unduh Gambar Asli</span>
                    </a>
                </div>

                <!-- Right: AI Security Audit Card -->
                <div class="bg-stone-900/70 border border-stone-800 rounded-2xl p-4 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex items-center justify-between pb-2 border-b border-stone-800">
                            <span class="text-xs font-bold text-stone-200 flex items-center gap-1.5">
                                <i class="fa-solid fa-robot text-emerald-400"></i>
                                Audit AI Anti-Fraud
                            </span>
                            <span id="modal-ai-badge"></span>
                        </div>

                        <div class="space-y-2 mt-3 text-xs">
                            <div class="flex justify-between">
                                <span class="text-stone-400">Penyedia / Bank:</span>
                                <span class="font-semibold text-stone-200" id="modal-ai-bank">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-stone-400">Total Tagihan:</span>
                                <span class="font-bold text-amber-400 font-mono" id="modal-ai-bill">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-stone-400">Nominal AI Terbaca:</span>
                                <span class="font-bold text-emerald-400 font-mono" id="modal-ai-detected">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-stone-400">Status Nominal:</span>
                                <span class="font-semibold" id="modal-ai-amount-diff">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-stone-400">Status Struk:</span>
                                <span class="font-bold text-emerald-400" id="modal-ai-trans-status">BERHASIL</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-stone-400">No. Referensi / RRN:</span>
                                <span class="font-mono text-[11px] text-stone-300" id="modal-ai-ref">-</span>
                            </div>
                        </div>

                        <div class="mt-3 pt-2.5 border-t border-stone-800">
                            <span class="text-[11px] font-bold text-stone-400 block mb-1">Catatan Analisis AI:</span>
                            <ul id="modal-ai-reasons" class="space-y-1"></ul>
                        </div>
                    </div>

                    <div class="text-[10px] text-stone-500 pt-2 border-t border-stone-800 italic flex items-center gap-1">
                        <i class="fa-solid fa-shield-halved text-emerald-400 text-[10px]"></i>
                        <span>Dilindungi oleh Warkop Madam AI Security Engine</span>
                    </div>
                </div>
            </div>

        </div>
    </div>


</body>
</html>
