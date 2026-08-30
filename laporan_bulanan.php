<?php
require_once __DIR__ . '/security.php';
require_admin();

$admin_name = $_SESSION['nama_lengkap'] ?? 'Administrator';

$bulan_nama = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$current_month = intval(date('m'));
$current_year = intval(date('Y'));

$selected_month = isset($_GET['bulan']) ? max(1, min(12, intval($_GET['bulan']))) : $current_month;
$selected_year = isset($_GET['tahun']) ? max(2020, min(2050, intval($_GET['tahun']))) : $current_year;
$selected_status = isset($_GET['status']) ? trim($_GET['status']) : 'semua';
$selected_payment = isset($_GET['payment']) ? trim($_GET['payment']) : 'semua';

$allowed_statuses = ['semua', 'selesai', 'pending', 'dibatalkan'];
if (!in_array($selected_status, $allowed_statuses, true)) {
    $selected_status = 'semua';
}

$allowed_payments = ['semua', 'QRIS', 'Transfer Mandiri', 'Tunai'];
if (!in_array($selected_payment, $allowed_payments, true)) {
    $selected_payment = 'semua';
}

$orders = [];
$total_omzet = 0;
$total_transaksi = 0;
$total_pending = 0;
$total_selesai = 0;
$total_batal = 0;

$payment_stats = [
    'QRIS' => 0,
    'Tunai' => 0,
    'Transfer Mandiri' => 0
];

try {
    $pdo = get_db_connection();

    $sql = "SELECT * FROM orders WHERE MONTH(created_at) = :bulan AND YEAR(created_at) = :tahun";
    $params = [
        'bulan' => $selected_month,
        'tahun' => $selected_year
    ];

    if ($selected_status !== 'semua') {
        $sql .= " AND status = :status";
        $params['status'] = $selected_status;
    }

    if ($selected_payment !== 'semua') {
        $sql .= " AND payment_method = :payment";
        $params['payment'] = $selected_payment;
    }

    $sql .= " ORDER BY created_at ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();

    foreach ($orders as $row) {
        $total_transaksi++;
        if ($row['status'] === 'selesai') {
            $total_selesai++;
            $total_omzet += $row['total_price'];
            
            if (isset($payment_stats[$row['payment_method']])) {
                $payment_stats[$row['payment_method']] += $row['total_price'];
            }
        } elseif ($row['status'] === 'pending' || $row['status'] === 'diproses') {
            $total_pending++;
        } elseif ($row['status'] === 'dibatalkan') {
            $total_batal++;
        }
    }
} catch (Exception $e) {
    error_log("Laporan Bulanan Error: " . $e->getMessage());
    $error = "Terjadi kendala sistem database saat memuat laporan.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Bulanan - Warkop Madam</title>
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

        @media print {
            aside, header, footer, .no-print { display: none !important; }
            body { background: #ffffff !important; color: #000000 !important; }
            .print-only { display: block !important; }
            .print-card { background: #ffffff !important; border: 1px solid #d1d5db !important; box-shadow: none !important; color: #000000 !important; }
            .print-table { width: 100% !important; border-collapse: collapse !important; color: #000000 !important; }
            .print-table th, .print-table td { border: 1px solid #9ca3af !important; padding: 6px 8px !important; color: #000000 !important; font-size: 10px !important; }
            .print-table th { background-color: #f3f4f6 !important; }
            .print-header { border-bottom: 2px solid #000000 !important; padding-bottom: 12px !important; margin-bottom: 16px !important; }
        }

        .print-only { display: none; }
    </style>
</head>
<body class="bg-[#09090b] text-stone-100 min-h-screen flex selection:bg-amber-500 selection:text-stone-950">

    <!-- ============================================== -->
    <!-- 1. LEFT SIDEBAR                                -->
    <!-- ============================================== -->
    <aside class="no-print w-64 bg-[#121113] border-r border-stone-800/80 min-h-screen flex flex-col justify-between shrink-0 sticky top-0 h-screen z-30 overflow-y-auto hidden md:flex">
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

                <a href="kelola_menu.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-stone-400 hover:text-white hover:bg-stone-900/90 transition group">
                    <i class="fa-solid fa-utensils text-base text-stone-400 group-hover:text-amber-400 transition"></i>
                    <span>Tambah & Kelola Menu</span>
                </a>

                <a href="laporan_bulanan.php" class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-gradient-to-r from-amber-600 to-amber-500 text-stone-950 font-bold shadow-lg shadow-amber-950/40 transition">
                    <i class="fa-solid fa-file-invoice-dollar text-base text-stone-950"></i>
                    <span class="text-sm font-extrabold">Laporan Bulanan</span>
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
                <span class="text-xs font-bold text-stone-200 block truncate"><?php echo htmlspecialchars($admin_name); ?></span>
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
        <header class="no-print md:hidden sticky top-0 z-40 bg-stone-950/95 backdrop-blur-xl border-b border-stone-800 p-4 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <img src="assets/logo.png" alt="Warkop Madam" class="w-8 h-8 object-contain">
                <span class="font-bold text-amber-300 font-serif-title text-sm">WARKOP MADAM</span>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <a href="dashboard_admin.php" class="px-2.5 py-1.5 rounded-lg bg-stone-900 text-stone-200">Dashboard</a>
                <button onclick="window.print()" class="px-2.5 py-1.5 rounded-lg bg-amber-500 text-stone-950 font-bold">Print</button>
                <a href="logout.php" class="px-2.5 py-1.5 rounded-lg bg-red-950 text-red-300">Keluar</a>
            </div>
        </header>

        <!-- PRINT HEADER (Hanya saat dicetak) -->
        <div class="print-only max-w-4xl mx-auto p-4 print-header text-center">
            <div class="flex items-center justify-center gap-4 mb-2">
                <img src="assets/logo.png" alt="Warkop Madam" style="width: 55px; height: 55px; object-fit: contain;">
                <div>
                    <h1 style="font-size: 20px; font-weight: bold; margin: 0; text-transform: uppercase;">WARKOP MADAM</h1>
                    <p style="font-size: 11px; margin: 2px 0; color: #4b5563;">Sensasi Kopi Nusantara & Aneka Hidangan Kuliner</p>
                    <p style="font-size: 10px; margin: 0; color: #6b7280;">Jl. Warkop Madam No. 01 &bull; Telp / WA: 0812-XXXX-XXXX</p>
                </div>
            </div>
            <div style="border-top: 2px solid #000; margin-top: 8px; padding-top: 8px;">
                <h2 style="font-size: 14px; font-weight: bold; margin: 0;">LAPORAN REKAPITULASI PENJUALAN BULANAN</h2>
                <p style="font-size: 11px; margin: 2px 0;">Periode: <strong><?php echo $bulan_nama[$selected_month] . ' ' . $selected_year; ?></strong></p>
                <p style="font-size: 9px; color: #4b5563;">Dicetak pada: <?php echo date('d-m-Y H:i:s'); ?> WIB | Oleh: <?php echo htmlspecialchars($admin_name); ?></p>
            </div>
        </div>

        <main class="flex-1 p-4 sm:p-7 max-w-7xl w-full mx-auto space-y-6">

            <!-- Hero Banner -->
            <div class="no-print bg-gradient-to-r from-[#171412] via-[#1c1815] to-[#171412] rounded-3xl p-6 sm:p-8 border border-stone-800 shadow-2xl relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="text-xs text-amber-400 font-bold uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-file-invoice-dollar"></i> Financial & Sales Reporting
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-black font-serif-title text-stone-100 mt-1">
                        Laporan Rekapitulasi Bulanan
                    </h2>
                    <p class="text-xs text-stone-400 mt-1">
                        Filter periode transaksi, pantau rincian omzet, dan cetak dokumen resmi A4 / PDF.
                    </p>
                </div>
                <button onclick="window.print()" class="px-5 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-stone-950 font-black text-xs shadow-lg shadow-amber-500/20 transition flex items-center gap-2 self-start md:self-auto active:scale-95">
                    <i class="fa-solid fa-print text-sm"></i>
                    <span>Cetak / Print Laporan</span>
                </button>
            </div>

            <!-- Filter Controls -->
            <div class="no-print bg-[#121113] p-5 rounded-3xl border border-stone-800 shadow-xl">
                <form action="" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-stone-300 mb-1.5">Pilih Bulan</label>
                        <select name="bulan" class="w-full px-3 py-2.5 bg-stone-900 border border-stone-700 rounded-xl text-stone-100 text-xs focus:outline-none focus:border-amber-500">
                            <?php foreach ($bulan_nama as $num => $nama): ?>
                                <option value="<?php echo $num; ?>" <?php echo $num === $selected_month ? 'selected' : ''; ?>><?php echo $nama; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-300 mb-1.5">Pilih Tahun</label>
                        <select name="tahun" class="w-full px-3 py-2.5 bg-stone-900 border border-stone-700 rounded-xl text-stone-100 text-xs focus:outline-none focus:border-amber-500">
                            <?php for ($y = $current_year; $y >= $current_year - 3; $y--): ?>
                                <option value="<?php echo $y; ?>" <?php echo $y === $selected_year ? 'selected' : ''; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-300 mb-1.5">Status Pesanan</label>
                        <select name="status" class="w-full px-3 py-2.5 bg-stone-900 border border-stone-700 rounded-xl text-stone-100 text-xs focus:outline-none focus:border-amber-500">
                            <option value="semua" <?php echo $selected_status === 'semua' ? 'selected' : ''; ?>>Semua Status</option>
                            <option value="selesai" <?php echo $selected_status === 'selesai' ? 'selected' : ''; ?>>Selesai (Sukses)</option>
                            <option value="pending" <?php echo $selected_status === 'pending' ? 'selected' : ''; ?>>Pending / Proses</option>
                            <option value="dibatalkan" <?php echo $selected_status === 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-300 mb-1.5">Metode Bayar</label>
                        <select name="payment" class="w-full px-3 py-2.5 bg-stone-900 border border-stone-700 rounded-xl text-stone-100 text-xs focus:outline-none focus:border-amber-500">
                            <option value="semua" <?php echo $selected_payment === 'semua' ? 'selected' : ''; ?>>Semua Metode</option>
                            <option value="QRIS" <?php echo $selected_payment === 'QRIS' ? 'selected' : ''; ?>>QRIS</option>
                            <option value="Transfer Mandiri" <?php echo $selected_payment === 'Transfer Mandiri' ? 'selected' : ''; ?>>Transfer Mandiri</option>
                            <option value="Tunai" <?php echo $selected_payment === 'Tunai' ? 'selected' : ''; ?>>Tunai / Kasir</option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl bg-amber-500 hover:bg-amber-400 text-stone-950 font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-md">
                            <i class="fa-solid fa-filter"></i>
                            <span>Filter Data</span>
                        </button>
                        <a href="laporan_bulanan.php" class="px-3 py-2.5 rounded-xl bg-stone-900 hover:bg-stone-800 text-stone-300 text-xs font-semibold border border-stone-800 flex items-center justify-center" title="Reset">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Financial Summary Cards -->
            <div class="print-card bg-[#121113] rounded-3xl p-6 border border-amber-500/30 shadow-xl">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-stone-800/80 mb-6">
                    <div>
                        <span class="text-xs text-amber-400 font-bold uppercase tracking-wider">Rekapitulasi Keuangan</span>
                        <h2 class="text-2xl sm:text-3xl font-black font-serif-title text-amber-100">
                            Periode: <?php echo $bulan_nama[$selected_month] . ' ' . $selected_year; ?>
                        </h2>
                    </div>
                    <div class="text-right">
                        <span class="text-[11px] text-stone-400 block">Total Omzet Bulan Ini:</span>
                        <span class="text-2xl sm:text-3xl font-black text-amber-400 font-serif-title">
                            Rp <?php echo number_format($total_omzet, 0, ',', '.'); ?>
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="p-4 rounded-2xl bg-stone-900/70 border border-stone-800">
                        <span class="text-[11px] text-stone-400 block mb-1">Total Transaksi</span>
                        <span class="text-xl font-bold text-stone-100"><?php echo $total_transaksi; ?> Pesanan</span>
                    </div>
                    <div class="p-4 rounded-2xl bg-stone-900/70 border border-stone-800">
                        <span class="text-[11px] text-stone-400 block mb-1">Transaksi Selesai</span>
                        <span class="text-xl font-bold text-emerald-400"><?php echo $total_selesai; ?> Sukses</span>
                    </div>
                    <div class="p-4 rounded-2xl bg-stone-900/70 border border-stone-800">
                        <span class="text-[11px] text-stone-400 block mb-1">Via QRIS</span>
                        <span class="text-sm font-bold text-amber-300">Rp <?php echo number_format($payment_stats['QRIS'], 0, ',', '.'); ?></span>
                    </div>
                    <div class="p-4 rounded-2xl bg-stone-900/70 border border-stone-800">
                        <span class="text-[11px] text-stone-400 block mb-1">Via Mandiri & Tunai</span>
                        <span class="text-sm font-bold text-blue-300">Rp <?php echo number_format($payment_stats['Transfer Mandiri'] + $payment_stats['Tunai'], 0, ',', '.'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="print-card bg-[#121113] rounded-3xl border border-stone-800 shadow-2xl overflow-hidden mb-8">
                <div class="overflow-x-auto">
                    <table class="print-table w-full text-left text-xs text-stone-300">
                        <thead class="bg-stone-900 text-stone-400 uppercase font-semibold text-[10px] tracking-wider border-b border-stone-800">
                            <tr>
                                <th class="py-3 px-3 text-center w-10">No</th>
                                <th class="py-3 px-3">Tgl & Jam</th>
                                <th class="py-3 px-3">No. Pesanan</th>
                                <th class="py-3 px-3">Nama Pemesan</th>
                                <th class="py-3 px-3">Meja</th>
                                <th class="py-3 px-4">Menu Dipesan</th>
                                <th class="py-3 px-3">Metode Bayar</th>
                                <th class="py-3 px-3">Status</th>
                                <th class="py-3 px-4 text-right">Total (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-900">
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-10 text-stone-500">
                                        Tidak ada data transaksi pada periode bulan dan tahun yang dipilih.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($orders as $row): ?>
                                    <tr class="hover:bg-stone-900/50 transition">
                                        <td class="py-3 px-3 text-center font-medium text-stone-500"><?php echo $no++; ?></td>
                                        <td class="py-3 px-3 font-mono text-[11px] text-stone-400">
                                            <?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?>
                                        </td>
                                        <td class="py-3 px-3 font-mono font-bold text-amber-400">
                                            <?php echo htmlspecialchars($row['order_number'] ?? '#MDM-' . $row['id']); ?>
                                        </td>
                                        <td class="py-3 px-3 font-semibold text-stone-100">
                                            <?php echo htmlspecialchars($row['customer_name']); ?>
                                        </td>
                                        <td class="py-3 px-3">
                                            <span class="px-2 py-0.5 rounded bg-stone-900 border border-stone-800 text-[10px]">
                                                <?php echo htmlspecialchars($row['table_number']); ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-[11px] text-stone-300">
                                            <?php echo htmlspecialchars($row['order_items']); ?>
                                            <?php if (!empty($row['notes'])): ?>
                                                <span class="text-stone-500 block italic text-[10px]">Ket: <?php echo htmlspecialchars($row['notes']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-3 font-medium">
                                            <?php echo htmlspecialchars($row['payment_method']); ?>
                                        </td>
                                        <td class="py-3 px-3">
                                            <?php if ($row['status'] === 'selesai'): ?>
                                                <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-[9px] font-bold uppercase">Selesai</span>
                                            <?php elseif ($row['status'] === 'pending' || $row['status'] === 'diproses'): ?>
                                                <span class="px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 text-[9px] font-bold uppercase">Proses</span>
                                            <?php else: ?>
                                                <span class="px-2 py-0.5 rounded-full bg-red-500/20 text-red-300 text-[9px] font-bold uppercase">Batal</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-4 text-right font-bold font-mono text-amber-400">
                                            Rp <?php echo number_format($row['total_price'], 0, ',', '.'); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                
                                <tr class="bg-stone-900/90 font-bold border-t-2 border-amber-500/40">
                                    <td colspan="8" class="py-3.5 px-4 text-right uppercase tracking-wider text-amber-200">
                                        Total Omzet Penjualan (Selesai):
                                    </td>
                                    <td class="py-3.5 px-4 text-right text-sm font-mono text-amber-400 font-black">
                                        Rp <?php echo number_format($total_omzet, 0, ',', '.'); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PRINT SIGNATURE SECTION -->
            <div class="print-only mt-8 pt-4">
                <table style="width: 100%; border: none; font-size: 11px;">
                    <tr>
                        <td style="width: 50%; text-align: center; border: none;">
                            <p style="margin-bottom: 50px;">Dibuat Oleh,<br><strong>Kasir / Admin</strong></p>
                            <p style="text-decoration: underline; font-weight: bold;"><?php echo htmlspecialchars($admin_name); ?></p>
                        </td>
                        <td style="width: 50%; text-align: center; border: none;">
                            <p style="margin-bottom: 50px;">Mengetahui,<br><strong>Pemilik Warkop Madam</strong></p>
                            <p style="text-decoration: underline; font-weight: bold;">( .................................... )</p>
                        </td>
                    </tr>
                </table>
            </div>

        </main>

        <footer class="no-print text-center text-xs text-stone-500 py-6 border-t border-stone-900">
            &copy; 2026 <span class="text-amber-400 font-medium">Warkop Madam</span> &bull; Modul Laporan Penjualan
        </footer>

    </div>

</body>
</html>
