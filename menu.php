<?php
require_once __DIR__ . '/security.php';

// Koneksi Database untuk memuat menu tambahan dari admin jika ada
$db_custom_items = ['makanan' => [], 'cemilan' => [], 'minuman' => []];
try {
    $pdo_menu = get_db_connection();
    $stmt_custom = $pdo_menu->query("SELECT * FROM menu_items WHERE status = 'tersedia' ORDER BY id DESC");
    while ($row = $stmt_custom->fetch()) {
        $kat = strtolower($row['kategori']);
        if (isset($db_custom_items[$kat])) {
            $db_custom_items[$kat][] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Menu load fallback: " . $e->getMessage());
    // Fallback gracefully jika database belum siap
}

// 1. DATA MENU MAKANAN (PALING ATAS)
$menu_makanan = [
    'special_nasi_mie' => [
        'title' => 'Special Madam - Nasi & Mie',
        'icon' => 'fa-bowl-rice',
        'items' => [
            [
                'id' => 'm1',
                'nama' => 'Nasi Goreng',
                'kategori' => 'Makanan',
                'harga' => 19000,
                'foto' => 'assets/menu/nasi_goreng.jpg',
                'badge' => 'Favorit',
                'deskripsi' => 'Nasi goreng racikan bumbu khas Madam dengan suwiran ayam gurih, telur, dan kerupuk renyah.'
            ],
            [
                'id' => 'm2',
                'nama' => 'Nasi Goreng Cabe Ijo',
                'kategori' => 'Makanan',
                'harga' => 19000,
                'foto' => 'assets/menu/nasi_goreng_cabe_ijo.jpg',
                'badge' => 'Pedas Gurih',
                'deskripsi' => 'Nasi goreng dengan aroma cabai hijau segar pedas nendang dan topping lengkap.'
            ],
            [
                'id' => 'm3',
                'nama' => 'Mie Nyemek',
                'kategori' => 'Makanan',
                'harga' => 17000,
                'foto' => 'assets/menu/mie_nyemek.jpg',
                'badge' => 'Best Seller',
                'deskripsi' => 'Mie kuah nyemek kental gurih berpadu telur, sayuran segar, dan irisan cabai rawit.'
            ],
            [
                'id' => 'm4',
                'nama' => 'Mie Goreng',
                'kategori' => 'Makanan',
                'harga' => 19000,
                'foto' => 'assets/menu/mie_goreng.jpg',
                'badge' => 'Favorit',
                'deskripsi' => 'Mie goreng spesial bumbu kecap gurih manis dengan telur dan pelengkap istimewa.'
            ],
            [
                'id' => 'm5',
                'nama' => 'Mie Chili Oil',
                'kategori' => 'Makanan',
                'harga' => 17000,
                'foto' => 'assets/menu/mie_chili_oil.jpg',
                'badge' => 'Spicy Kick',
                'deskripsi' => 'Mie kenyal diaduk dengan homemade chili oil pedas gurih wangi menggugah selera.'
            ],
            [
                'id' => 'm6',
                'nama' => 'Kuetiau Madam',
                'kategori' => 'Makanan',
                'harga' => 19000,
                'foto' => 'assets/menu/kuetiau_madam.jpg',
                'badge' => 'Special',
                'deskripsi' => 'Kwetiau goreng gurih kenyal dengan campuran telur, bakso/sosis, dan sayuran segar.'
            ],
            [
                'id' => 'm7',
                'nama' => 'Nasi Orak-Arik',
                'kategori' => 'Makanan',
                'harga' => 13000,
                'foto' => 'assets/menu/nasi_orak_arik.jpg',
                'badge' => '',
                'deskripsi' => 'Nasi hangat disajikan dengan tumisan orak-arik telur gurih nikmat.'
            ],
            [
                'id' => 'm8',
                'nama' => 'Nasi Telur Dadar',
                'kategori' => 'Makanan',
                'harga' => 10000,
                'foto' => 'assets/menu/nasi_telur_dadar.jpg',
                'badge' => 'Hemat',
                'deskripsi' => 'Nasi hangat dengan telur dadar tebal garing di luar lembut di dalam.'
            ]
        ]
    ],
    'special_ayam' => [
        'title' => 'Special Madam - Olahan Ayam & Nasi',
        'icon' => 'fa-drumstick-bite',
        'items' => [
            [
                'id' => 'm9',
                'nama' => 'Ayam Goreng Sambal Ijo',
                'kategori' => 'Makanan',
                'harga' => 22000,
                'foto' => 'assets/menu/ayam_goreng_sambal_ijo.jpg',
                'badge' => 'Best Seller',
                'deskripsi' => 'Ayam goreng renyah empuk disiram ulekan sambal ijo segar + lalapan & tahu/tempe.'
            ],
            [
                'id' => 'm10',
                'nama' => 'Ayam Maranggi',
                'kategori' => 'Makanan',
                'harga' => 22000,
                'foto' => 'assets/menu/ayam_maranggi.jpg',
                'badge' => 'Rekomendasi',
                'deskripsi' => 'Ayam panggang bumbu maranggi manis gurih beraroma rempah bakar sedap.'
            ],
            [
                'id' => 'm11',
                'nama' => 'Chiken Katsu',
                'kategori' => 'Makanan',
                'harga' => 22000,
                'foto' => 'assets/menu/chiken_katsu.jpg',
                'badge' => 'Favorit',
                'deskripsi' => 'Fillet ayam krispi tebal keemasan disajikan dengan saus cocolan nikmat & nasi.'
            ],
            [
                'id' => 'm12',
                'nama' => 'Chiken Wings',
                'kategori' => 'Makanan',
                'harga' => 22000,
                'foto' => 'assets/menu/chiken_wings.jpg',
                'badge' => 'Porsi Puas',
                'deskripsi' => 'Sayap ayam bumbu saus spesial legit gurih disajikan dengan kentang goreng renyah.'
            ],
            [
                'id' => 'm13',
                'nama' => 'Nasi Telur Bakso',
                'kategori' => 'Makanan',
                'harga' => 13000,
                'foto' => 'assets/menu/nasi_orak_arik.jpg',
                'badge' => '',
                'deskripsi' => 'Nasi hangat dengan kombinasi telur goreng gurih dan potongan bakso lezat.'
            ],
            [
                'id' => 'm14',
                'nama' => 'Nasi Telur Sosis',
                'kategori' => 'Makanan',
                'harga' => 13000,
                'foto' => 'assets/menu/nasi_telur_dadar.jpg',
                'badge' => '',
                'deskripsi' => 'Nasi hangat disajikan dengan telur dadar/mata sapi dan sosis goreng gurih.'
            ],
            [
                'id' => 'm15',
                'nama' => 'Ayam Goreng Sayap',
                'kategori' => 'Makanan',
                'harga' => 19000,
                'foto' => 'assets/menu/ayam_maranggi.jpg',
                'badge' => '',
                'deskripsi' => 'Sayap ayam goreng bumbu kuning gurih meresap sampai ke tulang.'
            ]
        ]
    ],
    'indomie' => [
        'title' => 'Aneka Indomie Madam',
        'icon' => 'fa-fire-burner',
        'base_price' => 9000,
        'variants' => [
            'Indomie Goreng Original',
            'Indomie Goreng Rendang',
            'Indomie Goreng Cabe Ijo',
            'Indomie Goreng Aceh',
            'Indomie Goreng Hype Abis',
            'Indomie Kari',
            'Indomie Ayam Bawang',
            'Indomie Soto',
            'Indomie Ayam Spesial',
            'Indomie Empal Gentong'
        ],
        'additionals' => [
            ['id' => 'top1', 'nama' => 'Topping Telur', 'harga' => 5000, 'icon' => 'fa-egg'],
            ['id' => 'top2', 'nama' => 'Topping Bakso', 'harga' => 3000, 'icon' => 'fa-circle'],
            ['id' => 'top3', 'nama' => 'Topping Sosis', 'harga' => 4000, 'icon' => 'fa-hotdog'],
            ['id' => 'top4', 'nama' => 'Topping Nasi', 'harga' => 5000, 'icon' => 'fa-bowl-rice']
        ]
    ]
];

// 2. DATA MENU CEMILAN (DI TENGAH / DI BAWAH MAKANAN)
$menu_cemilan = [
    [
        'id' => 'c1',
        'nama' => 'Kentang Goreng',
        'kategori' => 'Cemilan',
        'harga' => 12000,
        'foto' => 'assets/menu/kentang_goreng.jpg',
        'badge' => 'Favorit',
        'deskripsi' => 'French fries renyah keemasan dengan cocolan saus sambal & tomat gurih.'
    ],
    [
        'id' => 'c2',
        'nama' => 'Sosis Goreng',
        'kategori' => 'Cemilan',
        'harga' => 13000,
        'foto' => 'assets/menu/sosis_goreng.jpg',
        'badge' => '',
        'deskripsi' => 'Sosis mekar digoreng garing gurih disajikan dengan saus cocolan nikmat.'
    ],
    [
        'id' => 'c3',
        'nama' => 'Bakso Goreng',
        'kategori' => 'Cemilan',
        'harga' => 13000,
        'foto' => 'assets/menu/bakso_goreng.jpg',
        'badge' => 'Gurih Renyah',
        'deskripsi' => 'Bakso goreng kenyal di dalam garing di luar dengan sambal cocol nikmat.'
    ],
    [
        'id' => 'c4',
        'nama' => 'Otak-Otak',
        'kategori' => 'Cemilan',
        'harga' => 12000,
        'foto' => 'assets/menu/otak_otak.jpg',
        'badge' => '',
        'deskripsi' => 'Otak-otak ikan goreng gurih lembut dengan saus sambal pedas sedap.'
    ],
    [
        'id' => 'c5',
        'nama' => 'Cireng Isi',
        'kategori' => 'Cemilan',
        'harga' => 13000,
        'foto' => 'assets/menu/cireng_isi.jpg',
        'badge' => 'Best Seller',
        'deskripsi' => 'Cireng kenyal gurih renyah dengan isian lezat dan sambal cocol rujak pedas manis.'
    ],
    [
        'id' => 'c6',
        'nama' => 'Tahu Walik',
        'kategori' => 'Cemilan',
        'harga' => 12000,
        'foto' => 'assets/menu/tahu_walik.jpg',
        'badge' => 'Kriuk Renyah',
        'deskripsi' => 'Tahu walik khas dengan isian adonan gurih garing di luar lembut di dalam.'
    ],
    [
        'id' => 'c7',
        'nama' => 'Risol',
        'kategori' => 'Cemilan',
        'harga' => 12000,
        'foto' => 'assets/menu/risol.jpg',
        'badge' => '',
        'deskripsi' => 'Risol kulit renyah tepung panir dengan isian lezat gurih dan cabai rawit.'
    ],
    [
        'id' => 'c8',
        'nama' => 'Nuget',
        'kategori' => 'Cemilan',
        'harga' => 12000,
        'foto' => 'assets/menu/nuget.jpg',
        'badge' => '',
        'deskripsi' => 'Nugget ayam goreng renyah keemasan cocok untuk teman ngobrol santai.'
    ],
    [
        'id' => 'c9',
        'nama' => 'DimSum',
        'kategori' => 'Cemilan',
        'harga' => 13000,
        'foto' => 'assets/menu/dimsum.jpg',
        'badge' => 'Special',
        'deskripsi' => 'Dimsum kukus empuk lembut berdaging padat disajikan dengan chili oil gurih.'
    ],
    [
        'id' => 'c10',
        'nama' => 'Omelette Mie',
        'kategori' => 'Cemilan',
        'harga' => 14000,
        'foto' => 'assets/menu/omelette_mie.jpg',
        'badge' => 'Favorit',
        'deskripsi' => 'Martabak mie telur gurih tebal garing dengan saus cocolan nikmat.'
    ],
    [
        'id' => 'c11',
        'nama' => 'Mix Platter',
        'kategori' => 'Cemilan',
        'harga' => 15000,
        'foto' => 'assets/menu/mix_platter.jpg',
        'badge' => 'Lengkap',
        'deskripsi' => 'Kombinasi kentang, sosis, nugget, dan otak-otak dalam satu porsi komplit.'
    ],
    [
        'id' => 'c12',
        'nama' => 'Ceker Jeletot',
        'kategori' => 'Cemilan',
        'harga' => 15000,
        'foto' => 'assets/menu/ceker_jeletot.jpg',
        'badge' => 'Super Pedas',
        'deskripsi' => 'Ceker empuk bumbu jeletot pedas meledak dengan perasan jeruk limau segar.'
    ],
    [
        'id' => 'c13',
        'nama' => 'Pisang Keju',
        'kategori' => 'Cemilan',
        'harga' => 12000,
        'foto' => 'assets/menu/pisang_keju.jpg',
        'badge' => 'Manis Legit',
        'deskripsi' => 'Pisang goreng manis empuk ditaburi limpahan keju parut dan cokelat kental manis.'
    ],
    [
        'id' => 'c14',
        'nama' => 'Roti Bakar Keju / Coklat',
        'kategori' => 'Cemilan',
        'harga' => 12000,
        'foto' => 'assets/menu/roti_bakar.jpg',
        'badge' => 'Best Seller',
        'deskripsi' => 'Roti tebal panggang lembut dengan pilihan topping keju gurih atau coklat lumer.'
    ],
    [
        'id' => 'c15',
        'nama' => 'Rolling Cheese',
        'kategori' => 'Cemilan',
        'harga' => 12000,
        'foto' => 'assets/menu/rolling_cheese.jpg',
        'badge' => 'Crunchy',
        'deskripsi' => 'Stick gulung renyah manis gurih dengan taburan keju dan meses cokelat.'
    ],
    [
        'id' => 'c16',
        'nama' => 'Banana Nuget',
        'kategori' => 'Cemilan',
        'harga' => 12000,
        'foto' => 'assets/menu/banana_nuget.jpg',
        'badge' => 'Favorit',
        'deskripsi' => 'Nugget pisang crispy tebal diselimuti parutan keju melimpah dan taburan coklat.'
    ]
];

// 3. DATA MENU MINUMAN (PALING BAWAH)
$menu_minuman = [
    'kopi' => [
        'title' => 'Aneka Kopi',
        'icon' => 'fa-mug-hot',
        'items' => [
            ['id' => 'dr1', 'nama' => 'Kopi MADAM', 'hot' => 10000, 'ice' => 12000, 'badge' => 'Signature'],
            ['id' => 'dr2', 'nama' => 'Kapal Api Special Mix', 'hot' => 8000, 'ice' => 10000, 'badge' => ''],
            ['id' => 'dr3', 'nama' => 'Good day Cappuccino', 'hot' => 8000, 'ice' => 10000, 'badge' => 'Favorit'],
            ['id' => 'dr4', 'nama' => 'Good day Cooling', 'hot' => 8000, 'ice' => 10000, 'badge' => ''],
            ['id' => 'dr5', 'nama' => 'Good day Chococino', 'hot' => 8000, 'ice' => 10000, 'badge' => ''],
            ['id' => 'dr6', 'nama' => 'Good day Vanilla', 'hot' => 8000, 'ice' => 10000, 'badge' => ''],
            ['id' => 'dr7', 'nama' => 'Good day Mocacinno', 'hot' => 8000, 'ice' => 10000, 'badge' => ''],
            ['id' => 'dr8', 'nama' => 'Good day Freeze', 'hot' => 8000, 'ice' => 10000, 'badge' => ''],
            ['id' => 'dr9', 'nama' => 'ABC Susu', 'hot' => 8000, 'ice' => 10000, 'badge' => ''],
            ['id' => 'dr10', 'nama' => 'ABC Klepon', 'hot' => 8000, 'ice' => 10000, 'badge' => 'Unik'],
            ['id' => 'dr11', 'nama' => 'Indocafe', 'hot' => 8000, 'ice' => 10000, 'badge' => ''],
            ['id' => 'dr12', 'nama' => 'Luwak White Coffee', 'hot' => 8000, 'ice' => 10000, 'badge' => ''],
            ['id' => 'dr13', 'nama' => 'Torabika Creamy Latte', 'hot' => 8000, 'ice' => 10000, 'badge' => ''],
            ['id' => 'dr14', 'nama' => 'Torabika Cappuccino', 'hot' => 8000, 'ice' => 10000, 'badge' => ''],
            ['id' => 'dr15', 'nama' => 'Kopi Liong', 'hot' => 8000, 'ice' => 10000, 'badge' => 'Otentik']
        ]
    ],
    'tea' => [
        'title' => 'Tea / Teh',
        'icon' => 'fa-leaf',
        'items' => [
            ['id' => 'dr16', 'nama' => 'MaxTea Tarik', 'hot' => 8000, 'ice' => 10000, 'badge' => 'Favorit'],
            ['id' => 'dr17', 'nama' => 'MaxTea Lemontea', 'hot' => 10000, 'ice' => 12000, 'badge' => 'Segar'],
            ['id' => 'dr18', 'nama' => 'Teh Manis / Tawar', 'hot' => 5000, 'ice' => 7000, 'badge' => ''],
            ['id' => 'dr19', 'nama' => 'Tea Jus Apel', 'hot' => null, 'ice' => 6000, 'badge' => ''],
            ['id' => 'dr20', 'nama' => 'Tea Jus Melati', 'hot' => null, 'ice' => 6000, 'badge' => ''],
            ['id' => 'dr21', 'nama' => 'Tea Jus Gula Batu', 'hot' => null, 'ice' => 6000, 'badge' => ''],
            ['id' => 'dr22', 'nama' => 'Tea Jus Lemon', 'hot' => null, 'ice' => 6000, 'badge' => '']
        ]
    ],
    'susu_coklat' => [
        'title' => 'Susu & Cokelat',
        'icon' => 'fa-whiskey-glass',
        'items' => [
            ['id' => 'dr23', 'nama' => 'Dancow Putih', 'hot' => 9000, 'ice' => 11000, 'badge' => ''],
            ['id' => 'dr24', 'nama' => 'Dancow Coklat', 'hot' => 9000, 'ice' => 11000, 'badge' => ''],
            ['id' => 'dr25', 'nama' => 'Susu Putih', 'hot' => 8000, 'ice' => 10000, 'badge' => ''],
            ['id' => 'dr26', 'nama' => 'Susu Coklat', 'hot' => 8000, 'ice' => 10000, 'badge' => ''],
            ['id' => 'dr27', 'nama' => 'Chocolatos Coklat', 'hot' => 8000, 'ice' => 10000, 'badge' => 'Best Seller'],
            ['id' => 'dr28', 'nama' => 'Chocolatos Matcha', 'hot' => 8000, 'ice' => 10000, 'badge' => 'Matcha'],
            ['id' => 'dr29', 'nama' => 'Drink Beng Beng', 'hot' => 8000, 'ice' => 10000, 'badge' => 'Favorit'],
            ['id' => 'dr30', 'nama' => 'Milo', 'hot' => 8000, 'ice' => 10000, 'badge' => ''],
            ['id' => 'dr31', 'nama' => 'Susu Jahe', 'hot' => 8000, 'ice' => null, 'badge' => 'Hangat'],
            ['id' => 'dr32', 'nama' => 'Energen', 'hot' => 8000, 'ice' => null, 'badge' => 'Kenyang']
        ]
    ],
    'nutrisari' => [
        'title' => 'Nutrisari Segar (Ice)',
        'icon' => 'fa-lemon',
        'price' => 8000,
        'flavors' => [
            'Sweet Orange', 'Jeruk Peras', 'Jeruk Nipis', 'Mango', 
            'Blewah', 'Milk Orange', 'Cincau', 'Anggur', 
            'Anggur Hijau', 'Leci', 'Kelapa', 'Sirsak'
        ]
    ],
    'suplemen_soda' => [
        'title' => 'Suplemen & Minuman Segar',
        'icon' => 'fa-bolt',
        'items' => [
            ['id' => 'dr33', 'nama' => 'Suplemen X-tra Joss', 'harga' => 7000, 'badge' => 'Energy'],
            ['id' => 'dr34', 'nama' => 'Kuku Bima', 'harga' => 7000, 'badge' => 'Energy'],
            ['id' => 'dr35', 'nama' => 'Soda Susu', 'harga' => 12000, 'badge' => 'Favorit']
        ]
    ],
    'additional' => [
        ['id' => 'ad1', 'nama' => 'Mineral 220ml', 'harga' => 3000, 'icon' => 'fa-bottle-water'],
        ['id' => 'ad2', 'nama' => 'Mineral 600ml', 'harga' => 5000, 'icon' => 'fa-bottle-water'],
        ['id' => 'ad3', 'nama' => 'Ekstra Es Batu (+ Ice)', 'harga' => 2000, 'icon' => 'fa-snowflake'],
        ['id' => 'ad4', 'nama' => 'Ekstra Susu (+ Susu)', 'harga' => 3000, 'icon' => 'fa-droplet']
    ]
];
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Menu & Pemesanan - Warkop Madam</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;0,800;1,600&family=Caveat:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
        }
        .font-serif-title {
            font-family: 'Playfair Display', serif;
        }
        .font-handwriting {
            font-family: 'Caveat', cursive;
        }

        /* High-Definition Image Rendering */
        img {
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #0c0a09;
        }
        ::-webkit-scrollbar-thumb {
            background: #78350f;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #b45309;
        }

        .gold-border-glow {
            box-shadow: 0 4px 20px -2px rgba(245, 158, 11, 0.12);
        }
        .gold-border-glow:hover {
            box-shadow: 0 8px 30px -2px rgba(245, 158, 11, 0.3);
        }

        /* Scroll Reveal Effect for Menu Cards & Sections */
        .menu-card, .menu-section-header, .friendly-chip {
            opacity: 0;
            transform: translateY(22px);
            transition: opacity 0.55s cubic-bezier(0.2, 0.8, 0.2, 1), transform 0.55s cubic-bezier(0.2, 0.8, 0.2, 1), border-color 0.3s ease, box-shadow 0.3s ease;
            will-change: opacity, transform;
        }
        .menu-card.is-visible, .menu-section-header.is-visible, .friendly-chip.is-visible {
            opacity: 1;
            transform: translateY(0);
        }
        .menu-card:hover {
            transform: translateY(-4px);
        }

        /* Category Pill Active State with Glowing Accent */
        .category-pill {
            transition: all 0.25s ease-in-out;
        }
        .category-pill.active {
            background: linear-gradient(135deg, #f59e0b, #d97706) !important;
            color: #0c0a09 !important;
            box-shadow: 0 4px 20px -2px rgba(245, 158, 11, 0.45);
            border-color: #fbbf24 !important;
            transform: scale(1.03);
        }
        .category-pill.active i, .category-pill.active span {
            color: #0c0a09 !important;
            font-weight: 800;
        }

        /* Toast Feedback Notification */
        #cart-toast {
            transition: transform 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.25s ease;
        }
        #cart-toast.toast-hidden {
            transform: translate(-50%, 40px) scale(0.92);
            opacity: 0;
            pointer-events: none;
        }
        #cart-toast.toast-visible {
            transform: translate(-50%, 0) scale(1);
            opacity: 1;
            pointer-events: auto;
        }

        /* Badge Pop Animation */
        @keyframes qtyBadgePop {
            0% { transform: scale(1); }
            50% { transform: scale(1.4); color: #fbbf24; }
            100% { transform: scale(1); }
        }
        .qty-pop {
            animation: qtyBadgePop 0.25s ease-out;
        }

        /* Friendly Pulse Badge */
        @keyframes subtlePulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.75; }
        }
        .subtle-pulse {
            animation: subtlePulse 2.5s infinite;
        }
    </style>
</head>
<body class="bg-black text-stone-100 min-h-screen selection:bg-amber-500 selection:text-stone-950 pb-32">

    <!-- Top Scroll Progress Indicator Bar -->
    <div id="scroll-progress-bar" class="fixed top-0 left-0 h-1 bg-gradient-to-r from-amber-500 via-orange-400 to-amber-300 z-[60] transition-all duration-75 ease-out shadow-[0_0_12px_rgba(245,158,11,0.8)]" style="width: 0%;"></div>

    <!-- Ambient Glowing Background Elements -->
    <div class="fixed -top-40 -left-40 w-[500px] h-[500px] bg-amber-600/10 rounded-full blur-[140px] pointer-events-none"></div>
    <div class="fixed top-1/3 -right-40 w-[500px] h-[500px] bg-red-950/15 rounded-full blur-[140px] pointer-events-none"></div>
    <div class="fixed -bottom-40 left-1/3 w-[500px] h-[500px] bg-amber-500/10 rounded-full blur-[140px] pointer-events-none"></div>

    <!-- Sticky Navigation Header -->
    <header id="main-header" class="sticky top-0 z-50 bg-stone-950/90 backdrop-blur-xl border-b border-stone-800/80 px-4 py-3 sm:px-8 shadow-2xl transition-all duration-300">
        <div class="max-w-6xl mx-auto flex items-center justify-between gap-3">
            
            <!-- Logo & Brand Link -->
            <a href="index.php" class="flex items-center gap-3 group">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-black p-0.5 border border-amber-500/40 shadow-lg group-hover:scale-105 transition duration-300 overflow-hidden flex items-center justify-center">
                    <img src="assets/logo.png" alt="Warkop Madam Logo" class="w-full h-full object-contain">
                </div>
                <div>
                    <h1 class="text-base sm:text-lg font-bold font-serif-title tracking-wider text-amber-300 group-hover:text-amber-200 transition">
                        WARKOP MADAM
                    </h1>
                    <p class="text-[10px] sm:text-[11px] text-stone-400 font-medium flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block animate-ping"></span>
                        <span>Buka &bull; Order Mudah & Cepat</span>
                    </p>
                </div>
            </a>

            <!-- Action / Cart Trigger -->
            <div class="flex items-center gap-2">
                <button onclick="openPaymentModal()" class="relative inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-stone-950 text-xs font-bold transition shadow-lg shadow-amber-500/20 active:scale-95">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span>Pesanan (<span id="header-order-count">0</span>)</span>
                </button>
                <a href="index.php" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-stone-900 hover:bg-stone-800 text-stone-300 text-xs font-semibold border border-stone-800 transition">
                    <i class="fa-solid fa-house text-amber-400"></i>
                    <span class="hidden sm:inline">Beranda</span>
                </a>
            </div>

        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-6xl mx-auto px-4 sm:px-6 pt-6 relative z-10">

        <!-- Friendly Top Welcome & Banner Header -->
        <div class="text-center mb-6 relative">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-amber-950/60 border border-amber-600/40 text-amber-300 text-xs font-semibold mb-2.5 shadow-inner">
                <span class="text-sm">☕</span>
                <span class="tracking-wide">Warkop Madam &bull; Suasana Santai & Rasa Istimewa</span>
                <span class="text-sm">✨</span>
            </div>
            
            <h2 class="text-3xl sm:text-5xl font-black font-serif-title bg-gradient-to-r from-amber-100 via-amber-400 to-amber-200 bg-clip-text text-transparent tracking-wide">
                DAFTAR MENU
            </h2>
        </div>

        <!-- Search Bar with Live Filter -->
        <div class="max-w-md mx-auto mb-4">
            <div class="relative">
                <input type="text" 
                       id="menu-search-input" 
                       oninput="searchMenuItems()" 
                       placeholder="Cari menu (misal: Nasi Goreng, Katsu, Cireng, Kopi...)" 
                       class="w-full pl-11 pr-10 py-3 bg-stone-900/90 border border-stone-800 focus:border-amber-500 rounded-2xl text-sm text-stone-100 placeholder-stone-500 focus:outline-none focus:ring-1 focus:ring-amber-500 shadow-xl transition">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-stone-500 text-sm"></i>
                <button id="clear-search-btn" onclick="clearSearch()" class="hidden absolute right-3.5 top-1/2 -translate-y-1/2 text-stone-500 hover:text-amber-400 text-xs w-6 h-6 rounded-full bg-stone-800 flex items-center justify-center">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>

        <!-- Quick Filter Tags / Friendly Chips -->
        <div class="flex items-center justify-center flex-wrap gap-2 mb-6 max-w-xl mx-auto">
            <button onclick="filterByTag('')" class="friendly-chip text-[11px] px-3 py-1 rounded-full bg-stone-900 hover:bg-stone-800 text-stone-300 border border-stone-800 hover:border-amber-500/40 transition flex items-center gap-1.5">
                <i class="fa-solid fa-border-all text-amber-400 text-[10px]"></i>
                <span>Semua</span>
            </button>
            <button onclick="filterByTag('nasi')" class="friendly-chip text-[11px] px-3 py-1 rounded-full bg-stone-900 hover:bg-stone-800 text-stone-300 border border-stone-800 hover:border-amber-500/40 transition flex items-center gap-1.5">
                <i class="fa-solid fa-bowl-rice text-amber-400 text-[10px]"></i>
                <span>Nasi & Mie</span>
            </button>
            <button onclick="filterByTag('ayam')" class="friendly-chip text-[11px] px-3 py-1 rounded-full bg-stone-900 hover:bg-stone-800 text-stone-300 border border-stone-800 hover:border-amber-500/40 transition flex items-center gap-1.5">
                <i class="fa-solid fa-drumstick-bite text-amber-400 text-[10px]"></i>
                <span>Ayam & Lauk</span>
            </button>
            <button onclick="filterByTag('cireng')" class="friendly-chip text-[11px] px-3 py-1 rounded-full bg-stone-900 hover:bg-stone-800 text-stone-300 border border-stone-800 hover:border-amber-500/40 transition flex items-center gap-1.5">
                <i class="fa-solid fa-cookie-bite text-amber-400 text-[10px]"></i>
                <span>Cemilan</span>
            </button>
            <button onclick="filterByTag('kopi')" class="friendly-chip text-[11px] px-3 py-1 rounded-full bg-stone-900 hover:bg-stone-800 text-stone-300 border border-stone-800 hover:border-amber-500/40 transition flex items-center gap-1.5">
                <i class="fa-solid fa-mug-hot text-amber-400 text-[10px]"></i>
                <span>Kopi Khas</span>
            </button>
            <button onclick="filterByTag('es')" class="friendly-chip text-[11px] px-3 py-1 rounded-full bg-stone-900 hover:bg-stone-800 text-stone-300 border border-stone-800 hover:border-amber-500/40 transition flex items-center gap-1.5">
                <i class="fa-solid fa-snowflake text-cyan-400 text-[10px]"></i>
                <span>Minuman Dingin</span>
            </button>
        </div>

        <!-- Sticky Quick Category Tabs with Scrollspy -->
        <div id="category-tabs-bar" class="flex items-center justify-center gap-2 sm:gap-3 mb-10 sticky top-[62px] z-40 py-2.5 bg-black/90 backdrop-blur-md border-y border-stone-800/80 overflow-x-auto no-scrollbar shadow-lg">
            <a href="#menu-makanan" id="tab-menu-makanan" class="category-pill active shrink-0 px-4 sm:px-5 py-2 rounded-xl bg-amber-500 text-stone-950 font-bold text-xs sm:text-sm shadow-md transition flex items-center gap-2">
                <i class="fa-solid fa-utensils"></i>
                <span>1. Makanan</span>
            </a>
            <a href="#menu-cemilan" id="tab-menu-cemilan" class="category-pill shrink-0 px-4 sm:px-5 py-2 rounded-xl bg-stone-900 hover:bg-stone-800 text-amber-300 hover:text-amber-200 font-semibold text-xs sm:text-sm border border-stone-800 hover:border-amber-500/30 transition flex items-center gap-2">
                <i class="fa-solid fa-cookie-bite text-amber-400"></i>
                <span>2. Cemilan</span>
            </a>
            <a href="#menu-minuman" id="tab-menu-minuman" class="category-pill shrink-0 px-4 sm:px-5 py-2 rounded-xl bg-stone-900 hover:bg-stone-800 text-amber-300 hover:text-amber-200 font-semibold text-xs sm:text-sm border border-stone-800 hover:border-amber-500/30 transition flex items-center gap-2">
                <i class="fa-solid fa-mug-hot text-amber-400"></i>
                <span>3. Minuman</span>
            </a>
        </div>

        <!-- Live Search Status Indicator -->
        <div id="search-results-info" class="hidden mb-6 p-3 rounded-xl bg-amber-950/40 border border-amber-500/30 text-amber-300 text-xs flex items-center justify-between">
            <span id="search-results-text">Menampilkan hasil pencarian...</span>
            <button onclick="clearSearch()" class="text-amber-400 underline font-semibold">Tampilkan Semua</button>
        </div>


        <!-- ========================================== -->
        <!-- SECTION 1: MENU MAKANAN (PALING ATAS)     -->
        <!-- ========================================== -->
        <section id="menu-makanan" class="scroll-mt-36 mb-20 menu-section">
            
            <!-- Section Title Banner -->
            <div class="flex items-center justify-between gap-4 mb-8 pb-3 border-b-2 border-amber-500/30">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-600 to-amber-400 p-0.5 shadow-lg">
                        <div class="w-full h-full bg-stone-950 rounded-2xl flex items-center justify-center text-amber-400">
                            <i class="fa-solid fa-utensils text-xl"></i>
                        </div>
                    </div>
                    <div>
                        <span class="text-xs uppercase tracking-widest text-amber-400 font-bold">Menu Bagian 1 (Paling Atas)</span>
                        <h3 class="text-2xl sm:text-3xl font-extrabold font-serif-title text-amber-100">
                            DAFTAR MENU MAKANAN
                        </h3>
                    </div>
                </div>
                <span class="hidden md:inline-block px-3.5 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-semibold">
                    Makanan Berat & Spesial
                </span>
            </div>

            <!-- Sub Category 1: Special Nasi & Mie -->
            <div class="mb-12">
                <div class="flex items-center gap-2.5 mb-5 text-amber-300">
                    <i class="fa-solid fa-bowl-rice text-lg text-amber-400"></i>
                    <h4 class="text-xl font-bold font-serif-title"><?php echo $menu_makanan['special_nasi_mie']['title']; ?></h4>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                    <?php foreach ($menu_makanan['special_nasi_mie']['items'] as $item): ?>
                        <div class="menu-card bg-stone-950 rounded-2xl border border-stone-800 hover:border-amber-500/50 transition-all duration-300 overflow-hidden group flex flex-col justify-between gold-border-glow"
                             data-name="<?php echo strtolower($item['nama']); ?>" 
                             data-desc="<?php echo strtolower($item['deskripsi']); ?>">
                            
                            <!-- Crisp Image with Zoom Trigger -->
                            <div class="relative h-48 w-full bg-stone-900 overflow-hidden cursor-pointer" onclick="openImageModal('<?php echo $item['foto']; ?>', '<?php echo addslashes($item['nama']); ?>', 'Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?>')">
                                <img src="<?php echo $item['foto']; ?>" 
                                     alt="<?php echo htmlspecialchars($item['nama']); ?>" 
                                     loading="lazy"
                                     class="w-full h-full object-cover group-hover:scale-110 transition duration-500 filter contrast-[1.05] brightness-[1.02]">
                                
                                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent"></div>

                                <?php if (!empty($item['badge'])): ?>
                                    <div class="absolute top-3 right-3 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-500 text-stone-950 shadow-md">
                                        <?php echo htmlspecialchars($item['badge']); ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Quick Zoom Icon on Hover -->
                                <div class="absolute top-3 left-3 w-8 h-8 rounded-full bg-black/60 backdrop-blur-md text-stone-300 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                    <i class="fa-solid fa-expand text-xs"></i>
                                </div>

                                <!-- Price Tag Overlay -->
                                <div class="absolute bottom-3 left-3 px-3 py-1 rounded-xl bg-black/85 backdrop-blur-md border border-amber-500/40 text-amber-400 font-extrabold text-sm sm:text-base font-serif-title shadow-lg">
                                    Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?>
                                </div>
                            </div>

                            <!-- Details & Plus Minus Counter Buttons -->
                            <div class="p-4 flex flex-col justify-between flex-1">
                                <div>
                                    <h5 class="font-bold text-base text-stone-100 group-hover:text-amber-300 transition">
                                        <?php echo htmlspecialchars($item['nama']); ?>
                                    </h5>
                                    <p class="text-stone-400 text-xs mt-1.5 line-clamp-2 leading-relaxed">
                                        <?php echo htmlspecialchars($item['deskripsi']); ?>
                                    </p>
                                </div>
                                <div class="mt-4 pt-3 border-t border-stone-800/80 flex items-center justify-between">
                                    <span class="text-amber-400/90 font-medium text-xs flex items-center gap-1">
                                        <i class="fa-solid fa-check text-[10px]"></i> Siap Saji
                                    </span>
                                    
                                    <!-- Interactive + and - Quantity Selector -->
                                    <div class="flex items-center gap-1.5 bg-stone-900 border border-stone-800 rounded-xl p-1">
                                        <button onclick="changeItemQty('<?php echo addslashes($item['nama']); ?>', <?php echo $item['harga']; ?>, -1, 'Makanan')" 
                                                class="w-7 h-7 rounded-lg bg-stone-800 hover:bg-red-900/60 text-stone-300 hover:text-red-300 text-sm font-bold flex items-center justify-center transition active:scale-95">
                                            <i class="fa-solid fa-minus text-[11px]"></i>
                                        </button>
                                        <span id="qty-<?php echo md5($item['nama']); ?>" class="item-qty-badge w-6 text-center text-xs font-bold text-amber-300">0</span>
                                        <button onclick="changeItemQty('<?php echo addslashes($item['nama']); ?>', <?php echo $item['harga']; ?>, 1, 'Makanan')" 
                                                class="w-7 h-7 rounded-lg bg-amber-500 hover:bg-amber-400 text-stone-950 text-sm font-bold flex items-center justify-center transition active:scale-95 shadow-md">
                                            <i class="fa-solid fa-plus text-[11px]"></i>
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Sub Category 2: Special Olahan Ayam & Nasi -->
            <div class="mb-12">
                <div class="flex items-center gap-2.5 mb-5 text-amber-300">
                    <i class="fa-solid fa-drumstick-bite text-lg text-amber-400"></i>
                    <h4 class="text-xl font-bold font-serif-title"><?php echo $menu_makanan['special_ayam']['title']; ?></h4>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                    <?php foreach ($menu_makanan['special_ayam']['items'] as $item): ?>
                        <div class="menu-card bg-stone-950 rounded-2xl border border-stone-800 hover:border-amber-500/50 transition-all duration-300 overflow-hidden group flex flex-col justify-between gold-border-glow"
                             data-name="<?php echo strtolower($item['nama']); ?>" 
                             data-desc="<?php echo strtolower($item['deskripsi']); ?>">
                            
                            <!-- Crisp Image with Zoom Trigger -->
                            <div class="relative h-48 w-full bg-stone-900 overflow-hidden cursor-pointer" onclick="openImageModal('<?php echo $item['foto']; ?>', '<?php echo addslashes($item['nama']); ?>', 'Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?>')">
                                <img src="<?php echo $item['foto']; ?>" 
                                     alt="<?php echo htmlspecialchars($item['nama']); ?>" 
                                     loading="lazy"
                                     class="w-full h-full object-cover group-hover:scale-110 transition duration-500 filter contrast-[1.05] brightness-[1.02]">
                                
                                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent"></div>

                                <?php if (!empty($item['badge'])): ?>
                                    <div class="absolute top-3 right-3 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-500 text-stone-950 shadow-md">
                                        <?php echo htmlspecialchars($item['badge']); ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Quick Zoom Icon on Hover -->
                                <div class="absolute top-3 left-3 w-8 h-8 rounded-full bg-black/60 backdrop-blur-md text-stone-300 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                    <i class="fa-solid fa-expand text-xs"></i>
                                </div>

                                <!-- Price Tag Overlay -->
                                <div class="absolute bottom-3 left-3 px-3 py-1 rounded-xl bg-black/85 backdrop-blur-md border border-amber-500/40 text-amber-400 font-extrabold text-sm sm:text-base font-serif-title shadow-lg">
                                    Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?>
                                </div>
                            </div>

                            <!-- Details & Plus Minus Counter Buttons -->
                            <div class="p-4 flex flex-col justify-between flex-1">
                                <div>
                                    <h5 class="font-bold text-base text-stone-100 group-hover:text-amber-300 transition">
                                        <?php echo htmlspecialchars($item['nama']); ?>
                                    </h5>
                                    <p class="text-stone-400 text-xs mt-1.5 line-clamp-2 leading-relaxed">
                                        <?php echo htmlspecialchars($item['deskripsi']); ?>
                                    </p>
                                </div>
                                <div class="mt-4 pt-3 border-t border-stone-800/80 flex items-center justify-between">
                                    <span class="text-amber-400/90 font-medium text-xs flex items-center gap-1">
                                        <i class="fa-solid fa-check text-[10px]"></i> Siap Saji
                                    </span>
                                    
                                    <!-- Interactive + and - Quantity Selector -->
                                    <div class="flex items-center gap-1.5 bg-stone-900 border border-stone-800 rounded-xl p-1">
                                        <button onclick="changeItemQty('<?php echo addslashes($item['nama']); ?>', <?php echo $item['harga']; ?>, -1, 'Makanan')" 
                                                class="w-7 h-7 rounded-lg bg-stone-800 hover:bg-red-900/60 text-stone-300 hover:text-red-300 text-sm font-bold flex items-center justify-center transition active:scale-95">
                                            <i class="fa-solid fa-minus text-[11px]"></i>
                                        </button>
                                        <span id="qty-<?php echo md5($item['nama']); ?>" class="item-qty-badge w-6 text-center text-xs font-bold text-amber-300">0</span>
                                        <button onclick="changeItemQty('<?php echo addslashes($item['nama']); ?>', <?php echo $item['harga']; ?>, 1, 'Makanan')" 
                                                class="w-7 h-7 rounded-lg bg-amber-500 hover:bg-amber-400 text-stone-950 text-sm font-bold flex items-center justify-center transition active:scale-95 shadow-md">
                                            <i class="fa-solid fa-plus text-[11px]"></i>
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Sub Category 3: Aneka Indomie & Toppings -->
            <div class="bg-gradient-to-br from-stone-950 via-stone-900 to-stone-950 p-6 sm:p-8 rounded-3xl border border-amber-500/30 shadow-2xl relative overflow-hidden">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 pb-4 border-b border-stone-800">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 text-xs font-bold mb-2">
                            <i class="fa-solid fa-fire"></i> VARIAN RASA LENGKAP
                        </div>
                        <h4 class="text-2xl sm:text-3xl font-extrabold font-serif-title text-amber-200">
                            Indomie Warkop Madam
                        </h4>
                    </div>
                    <div class="px-5 py-2.5 rounded-2xl bg-amber-500/15 border border-amber-500/40 text-amber-400 font-serif-title font-black text-xl">
                        Rp 9.000 <span class="text-xs font-sans text-stone-300 font-normal">/ porsi</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Indomie Flavors List with + - counters -->
                    <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <?php foreach ($menu_makanan['indomie']['variants'] as $variant): ?>
                            <div class="menu-card p-3.5 rounded-xl bg-black/60 border border-stone-800/80 hover:border-amber-500/40 flex items-center justify-between gap-3 group transition"
                                 data-name="<?php echo strtolower($variant); ?>" data-desc="indomie">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-400 text-xs group-hover:scale-110 transition">
                                        <i class="fa-solid fa-bowl-food"></i>
                                    </div>
                                    <div>
                                        <span class="text-sm font-semibold text-stone-200 group-hover:text-amber-300 transition block">
                                            <?php echo htmlspecialchars($variant); ?>
                                        </span>
                                        <span class="text-[11px] text-amber-400 font-bold">Rp 9.000</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 bg-stone-900 border border-stone-800 rounded-xl p-1">
                                    <button onclick="changeItemQty('<?php echo addslashes($variant); ?>', 9000, -1, 'Indomie')" 
                                            class="w-6 h-6 rounded-lg bg-stone-800 hover:bg-red-900/50 text-stone-300 text-xs font-bold flex items-center justify-center transition">
                                        -
                                    </button>
                                    <span id="qty-<?php echo md5($variant); ?>" class="item-qty-badge w-5 text-center text-xs font-bold text-amber-300">0</span>
                                    <button onclick="changeItemQty('<?php echo addslashes($variant); ?>', 9000, 1, 'Indomie')" 
                                            class="w-6 h-6 rounded-lg bg-amber-500 hover:bg-amber-400 text-stone-950 text-xs font-bold flex items-center justify-center transition">
                                        +
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Indomie Additional / Toppings Box -->
                    <div class="bg-black/80 rounded-2xl p-5 border border-amber-500/30 flex flex-col justify-between shadow-xl">
                        <div>
                            <h5 class="text-base font-bold text-amber-300 flex items-center gap-2 mb-1">
                                <i class="fa-solid fa-circle-plus text-amber-400"></i>
                                Topping Tambahan (Additional)
                            </h5>
                            <p class="text-xs text-stone-400 mb-4">Tambahkan topping favorit untuk porsi yang lebih mantap:</p>
                            
                            <div class="space-y-2.5">
                                <?php foreach ($menu_makanan['indomie']['additionals'] as $add): ?>
                                    <div class="p-2.5 rounded-xl bg-stone-900/80 border border-stone-800 flex items-center justify-between">
                                        <span class="text-xs font-medium text-stone-200 flex items-center gap-2">
                                            <i class="fa-solid <?php echo $add['icon']; ?> text-amber-400 text-xs w-4"></i>
                                            + <?php echo htmlspecialchars($add['nama']); ?>
                                            <span class="text-amber-400 font-bold text-[11px]">(+<?php echo number_format($add['harga'], 0, ',', '.'); ?>)</span>
                                        </span>
                                        <div class="flex items-center gap-1 bg-stone-950 border border-stone-800 rounded-lg p-0.5">
                                            <button onclick="changeItemQty('<?php echo addslashes($add['nama']); ?>', <?php echo $add['harga']; ?>, -1, 'Topping')" 
                                                    class="w-5 h-5 rounded bg-stone-800 text-stone-300 text-xs font-bold flex items-center justify-center">-</button>
                                            <span id="qty-<?php echo md5($add['nama']); ?>" class="item-qty-badge w-4 text-center text-xs font-bold text-amber-300">0</span>
                                            <button onclick="changeItemQty('<?php echo addslashes($add['nama']); ?>', <?php echo $add['harga']; ?>, 1, 'Topping')" 
                                                    class="w-5 h-5 rounded bg-amber-500 text-stone-950 text-xs font-bold flex items-center justify-center">+</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="mt-5 p-3 rounded-xl bg-amber-950/40 border border-amber-600/30 text-center">
                            <span class="text-[11px] text-amber-200">
                                <i class="fa-solid fa-circle-info mr-1"></i> Bisa request tingkat kematangan & cabai rawit
                            </span>
                        </div>
                    </div>
                </div>

            </div>

        </section>


        <!-- ========================================== -->
        <!-- SECTION 2: MENU CEMILAN (DI TENGAH)       -->
        <!-- ========================================== -->
        <section id="menu-cemilan" class="scroll-mt-36 mb-20 menu-section">
            
            <!-- Section Title Banner -->
            <div class="flex items-center justify-between gap-4 mb-8 pb-3 border-b-2 border-amber-500/30">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-600 to-amber-400 p-0.5 shadow-lg">
                        <div class="w-full h-full bg-stone-950 rounded-2xl flex items-center justify-center text-amber-400">
                            <i class="fa-solid fa-cookie-bite text-xl"></i>
                        </div>
                    </div>
                    <div>
                        <span class="text-xs uppercase tracking-widest text-amber-400 font-bold">Menu Bagian 2 (Di Bawah Makanan)</span>
                        <h3 class="text-2xl sm:text-3xl font-extrabold font-serif-title text-amber-100">
                            DAFTAR MENU CEMILAN
                        </h3>
                    </div>
                </div>
                <span class="hidden md:inline-block px-3.5 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-semibold">
                    Aneka Gorengan, Snack & Manis
                </span>
            </div>

            <!-- Cemilan Grid Cards with Sharp High-Definition Photos & Counters -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                <?php foreach ($menu_cemilan as $item): ?>
                    <div class="menu-card bg-stone-950 rounded-2xl border border-stone-800 hover:border-amber-500/50 transition-all duration-300 overflow-hidden group flex flex-col justify-between gold-border-glow"
                         data-name="<?php echo strtolower($item['nama']); ?>" 
                         data-desc="<?php echo strtolower($item['deskripsi']); ?>">
                        
                        <!-- Crisp Image with Zoom Trigger -->
                        <div class="relative h-48 w-full bg-stone-900 overflow-hidden cursor-pointer" onclick="openImageModal('<?php echo $item['foto']; ?>', '<?php echo addslashes($item['nama']); ?>', 'Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?>')">
                            <img src="<?php echo $item['foto']; ?>" 
                                 alt="<?php echo htmlspecialchars($item['nama']); ?>" 
                                 loading="lazy"
                                 class="w-full h-full object-cover group-hover:scale-110 transition duration-500 filter contrast-[1.05] brightness-[1.02]">
                            
                            <div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent"></div>

                            <?php if (!empty($item['badge'])): ?>
                                <div class="absolute top-3 right-3 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-500 text-stone-950 shadow-md">
                                    <?php echo htmlspecialchars($item['badge']); ?>
                                </div>
                            <?php endif; ?>

                            <!-- Quick Zoom Icon on Hover -->
                            <div class="absolute top-3 left-3 w-8 h-8 rounded-full bg-black/60 backdrop-blur-md text-stone-300 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                <i class="fa-solid fa-expand text-xs"></i>
                            </div>

                            <!-- Price Tag Overlay -->
                            <div class="absolute bottom-3 left-3 px-3 py-1 rounded-xl bg-black/85 backdrop-blur-md border border-amber-500/40 text-amber-400 font-extrabold text-sm sm:text-base font-serif-title shadow-lg">
                                Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?>
                            </div>
                        </div>

                        <!-- Details & Plus Minus Counter Buttons -->
                        <div class="p-4 flex flex-col justify-between flex-1">
                            <div>
                                <h5 class="font-bold text-base text-stone-100 group-hover:text-amber-300 transition">
                                    <?php echo htmlspecialchars($item['nama']); ?>
                                </h5>
                                <p class="text-stone-400 text-xs mt-1.5 line-clamp-2 leading-relaxed">
                                    <?php echo htmlspecialchars($item['deskripsi']); ?>
                                </p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-stone-800/80 flex items-center justify-between">
                                <span class="text-amber-400/90 font-medium text-xs flex items-center gap-1">
                                    <i class="fa-solid fa-check text-[10px]"></i> Siap Saji
                                </span>
                                
                                <!-- Interactive + and - Quantity Selector -->
                                <div class="flex items-center gap-1.5 bg-stone-900 border border-stone-800 rounded-xl p-1">
                                    <button onclick="changeItemQty('<?php echo addslashes($item['nama']); ?>', <?php echo $item['harga']; ?>, -1, 'Cemilan')" 
                                            class="w-7 h-7 rounded-lg bg-stone-800 hover:bg-red-900/60 text-stone-300 hover:text-red-300 text-sm font-bold flex items-center justify-center transition active:scale-95">
                                        <i class="fa-solid fa-minus text-[11px]"></i>
                                    </button>
                                    <span id="qty-<?php echo md5($item['nama']); ?>" class="item-qty-badge w-6 text-center text-xs font-bold text-amber-300">0</span>
                                    <button onclick="changeItemQty('<?php echo addslashes($item['nama']); ?>', <?php echo $item['harga']; ?>, 1, 'Cemilan')" 
                                            class="w-7 h-7 rounded-lg bg-amber-500 hover:bg-amber-400 text-stone-950 text-sm font-bold flex items-center justify-center transition active:scale-95 shadow-md">
                                        <i class="fa-solid fa-plus text-[11px]"></i>
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </section>


        <!-- ========================================== -->
        <!-- SECTION 3: MENU MINUMAN (PALING BAWAH)    -->
        <!-- ========================================== -->
        <section id="menu-minuman" class="scroll-mt-36 mb-16 menu-section">
            
            <!-- Section Title Banner -->
            <div class="flex items-center justify-between gap-4 mb-8 pb-3 border-b-2 border-red-800/40">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-red-700 via-amber-600 to-amber-400 p-0.5 shadow-lg">
                        <div class="w-full h-full bg-stone-950 rounded-2xl flex items-center justify-center text-amber-400">
                            <i class="fa-solid fa-mug-hot text-xl"></i>
                        </div>
                    </div>
                    <div>
                        <span class="text-xs uppercase tracking-widest text-amber-400 font-bold">Menu Bagian 3 (Paling Bawah)</span>
                        <h3 class="text-2xl sm:text-3xl font-extrabold font-serif-title text-amber-100">
                            DAFTAR MENU MINUMAN
                        </h3>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full bg-red-950/80 border border-red-700/40 text-amber-300 text-xs font-semibold flex items-center gap-1.5">
                        <i class="fa-solid fa-fire text-amber-400 text-[10px]"></i> Hot & Ice
                    </span>
                    <span class="hidden md:inline-block px-3.5 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-semibold">
                        Coffee Break
                    </span>
                </div>
            </div>

            <!-- Drinks Layout Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Left Column: Kopi & Tea -->
                <div class="space-y-8">
                    
                    <!-- 1. Kopi Madam & Kopi Pilihan -->
                    <div class="bg-gradient-to-br from-stone-950 to-stone-900 rounded-3xl p-6 border border-stone-800 hover:border-amber-500/30 transition shadow-xl">
                        <div class="flex items-center justify-between mb-5 pb-3 border-b border-stone-800">
                            <div class="flex items-center gap-2.5">
                                <i class="fa-solid fa-mug-hot text-amber-400 text-lg"></i>
                                <h4 class="text-xl font-bold font-serif-title text-amber-200">KOPI</h4>
                            </div>
                            <div class="flex items-center gap-3 text-xs font-bold">
                                <span class="px-2.5 py-1 rounded-lg bg-red-950/70 border border-red-700/50 text-red-200">Hot (Panas)</span>
                                <span class="px-2.5 py-1 rounded-lg bg-blue-950/70 border border-blue-700/50 text-blue-200">Ice (Dingin)</span>
                            </div>
                        </div>

                        <div class="divide-y divide-stone-900">
                            <?php foreach ($menu_minuman['kopi']['items'] as $item): ?>
                                <div class="menu-card py-3 flex items-center justify-between gap-3 group hover:bg-stone-900/50 px-2 rounded-xl transition"
                                     data-name="<?php echo strtolower($item['nama']); ?>" data-desc="kopi">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        <div>
                                            <span class="text-sm font-semibold text-stone-200 group-hover:text-amber-300 transition block">
                                                <?php echo htmlspecialchars($item['nama']); ?>
                                            </span>
                                            <?php if (!empty($item['badge'])): ?>
                                                <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30 inline-block mt-0.5">
                                                    <?php echo $item['badge']; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs font-bold shrink-0">
                                        <!-- Hot Button -->
                                        <div class="flex items-center gap-1 bg-stone-900 border border-red-900/40 rounded-lg p-0.5">
                                            <button onclick="changeItemQty('<?php echo addslashes($item['nama']); ?> (Hot)', <?php echo $item['hot']; ?>, -1, 'Minuman')" class="w-5 h-5 rounded bg-stone-800 text-stone-300 text-[10px]">-</button>
                                            <span id="qty-<?php echo md5($item['nama'] . ' (Hot)'); ?>" class="item-qty-badge w-4 text-center text-[11px] text-amber-300">0</span>
                                            <button onclick="changeItemQty('<?php echo addslashes($item['nama']); ?> (Hot)', <?php echo $item['hot']; ?>, 1, 'Minuman')" class="w-5 h-5 rounded bg-red-900 hover:bg-red-800 text-white text-[10px]">+</button>
                                            <span class="text-[10px] text-stone-400 ml-1"><?php echo number_format($item['hot'], 0, ',', '.'); ?></span>
                                        </div>

                                        <!-- Ice Button -->
                                        <div class="flex items-center gap-1 bg-stone-900 border border-blue-900/40 rounded-lg p-0.5">
                                            <button onclick="changeItemQty('<?php echo addslashes($item['nama']); ?> (Ice)', <?php echo $item['ice']; ?>, -1, 'Minuman')" class="w-5 h-5 rounded bg-stone-800 text-stone-300 text-[10px]">-</button>
                                            <span id="qty-<?php echo md5($item['nama'] . ' (Ice)'); ?>" class="item-qty-badge w-4 text-center text-[11px] text-amber-300">0</span>
                                            <button onclick="changeItemQty('<?php echo addslashes($item['nama']); ?> (Ice)', <?php echo $item['ice']; ?>, 1, 'Minuman')" class="w-5 h-5 rounded bg-blue-900 hover:bg-blue-800 text-white text-[10px]">+</button>
                                            <span class="text-[10px] text-amber-400 ml-1"><?php echo number_format($item['ice'], 0, ',', '.'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- 2. Tea / Teh -->
                    <div class="bg-gradient-to-br from-stone-950 to-stone-900 rounded-3xl p-6 border border-stone-800 hover:border-amber-500/30 transition shadow-xl">
                        <div class="flex items-center justify-between mb-5 pb-3 border-b border-stone-800">
                            <div class="flex items-center gap-2.5">
                                <i class="fa-solid fa-leaf text-amber-400 text-lg"></i>
                                <h4 class="text-xl font-bold font-serif-title text-amber-200">TEA / TEH</h4>
                            </div>
                            <div class="flex items-center gap-3 text-xs font-bold">
                                <span class="px-2.5 py-1 rounded-lg bg-red-950/70 border border-red-700/50 text-red-200">Hot</span>
                                <span class="px-2.5 py-1 rounded-lg bg-blue-950/70 border border-blue-700/50 text-blue-200">Ice</span>
                            </div>
                        </div>

                        <div class="divide-y divide-stone-900">
                            <?php foreach ($menu_minuman['tea']['items'] as $item): ?>
                                <div class="menu-card py-3 flex items-center justify-between gap-3 group hover:bg-stone-900/50 px-2 rounded-xl transition"
                                     data-name="<?php echo strtolower($item['nama']); ?>" data-desc="teh tea">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <div>
                                            <span class="text-sm font-semibold text-stone-200 group-hover:text-amber-300 transition block">
                                                <?php echo htmlspecialchars($item['nama']); ?>
                                            </span>
                                            <?php if (!empty($item['badge'])): ?>
                                                <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 inline-block mt-0.5">
                                                    <?php echo $item['badge']; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs font-bold shrink-0">
                                        <?php if ($item['hot'] !== null): ?>
                                            <div class="flex items-center gap-1 bg-stone-900 border border-red-900/40 rounded-lg p-0.5">
                                                <button onclick="changeItemQty('<?php echo addslashes($item['nama']); ?> (Hot)', <?php echo $item['hot']; ?>, -1, 'Minuman')" class="w-5 h-5 rounded bg-stone-800 text-stone-300 text-[10px]">-</button>
                                                <span id="qty-<?php echo md5($item['nama'] . ' (Hot)'); ?>" class="item-qty-badge w-4 text-center text-[11px] text-amber-300">0</span>
                                                <button onclick="changeItemQty('<?php echo addslashes($item['nama']); ?> (Hot)', <?php echo $item['hot']; ?>, 1, 'Minuman')" class="w-5 h-5 rounded bg-red-900 text-white text-[10px]">+</button>
                                                <span class="text-[10px] text-stone-400 ml-1"><?php echo number_format($item['hot'], 0, ',', '.'); ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($item['ice'] !== null): ?>
                                            <div class="flex items-center gap-1 bg-stone-900 border border-blue-900/40 rounded-lg p-0.5">
                                                <button onclick="changeItemQty('<?php echo addslashes($item['nama']); ?> (Ice)', <?php echo $item['ice']; ?>, -1, 'Minuman')" class="w-5 h-5 rounded bg-stone-800 text-stone-300 text-[10px]">-</button>
                                                <span id="qty-<?php echo md5($item['nama'] . ' (Ice)'); ?>" class="item-qty-badge w-4 text-center text-[11px] text-amber-300">0</span>
                                                <button onclick="changeItemQty('<?php echo addslashes($item['nama']); ?> (Ice)', <?php echo $item['ice']; ?>, 1, 'Minuman')" class="w-5 h-5 rounded bg-blue-900 text-white text-[10px]">+</button>
                                                <span class="text-[10px] text-amber-400 ml-1"><?php echo number_format($item['ice'], 0, ',', '.'); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Susu, Nutrisari, Suplemen, Additional -->
                <div class="space-y-8">
                    
                    <!-- 3. Susu & Cokelat -->
                    <div class="bg-gradient-to-br from-stone-950 to-stone-900 rounded-3xl p-6 border border-stone-800 hover:border-amber-500/30 transition shadow-xl">
                        <div class="flex items-center justify-between mb-5 pb-3 border-b border-stone-800">
                            <div class="flex items-center gap-2.5">
                                <i class="fa-solid fa-whiskey-glass text-amber-400 text-lg"></i>
                                <h4 class="text-xl font-bold font-serif-title text-amber-200">SUSU & COKELAT</h4>
                            </div>
                            <div class="flex items-center gap-3 text-xs font-bold">
                                <span class="px-2.5 py-1 rounded-lg bg-red-950/70 border border-red-700/50 text-red-200">Hot</span>
                                <span class="px-2.5 py-1 rounded-lg bg-blue-950/70 border border-blue-700/50 text-blue-200">Ice</span>
                            </div>
                        </div>

                        <div class="divide-y divide-stone-900">
                            <?php foreach ($menu_minuman['susu_coklat']['items'] as $item): ?>
                                <div class="menu-card py-3 flex items-center justify-between gap-3 group hover:bg-stone-900/50 px-2 rounded-xl transition"
                                     data-name="<?php echo strtolower($item['nama']); ?>" data-desc="susu cokelat coklat matcha">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                        <div>
                                            <span class="text-sm font-semibold text-stone-200 group-hover:text-amber-300 transition block">
                                                <?php echo htmlspecialchars($item['nama']); ?>
                                            </span>
                                            <?php if (!empty($item['badge'])): ?>
                                                <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30 inline-block mt-0.5">
                                                    <?php echo $item['badge']; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs font-bold shrink-0">
                                        <?php if ($item['hot'] !== null): ?>
                                            <div class="flex items-center gap-1 bg-stone-900 border border-red-900/40 rounded-lg p-0.5">
                                                <button onclick="changeItemQty('<?php echo addslashes($item['nama']); ?> (Hot)', <?php echo $item['hot']; ?>, -1, 'Minuman')" class="w-5 h-5 rounded bg-stone-800 text-stone-300 text-[10px]">-</button>
                                                <span id="qty-<?php echo md5($item['nama'] . ' (Hot)'); ?>" class="item-qty-badge w-4 text-center text-[11px] text-amber-300">0</span>
                                                <button onclick="changeItemQty('<?php echo addslashes($item['nama']); ?> (Hot)', <?php echo $item['hot']; ?>, 1, 'Minuman')" class="w-5 h-5 rounded bg-red-900 text-white text-[10px]">+</button>
                                                <span class="text-[10px] text-stone-400 ml-1"><?php echo number_format($item['hot'], 0, ',', '.'); ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($item['ice'] !== null): ?>
                                            <div class="flex items-center gap-1 bg-stone-900 border border-blue-900/40 rounded-lg p-0.5">
                                                <button onclick="changeItemQty('<?php echo addslashes($item['nama']); ?> (Ice)', <?php echo $item['ice']; ?>, -1, 'Minuman')" class="w-5 h-5 rounded bg-stone-800 text-stone-300 text-[10px]">-</button>
                                                <span id="qty-<?php echo md5($item['nama'] . ' (Ice)'); ?>" class="item-qty-badge w-4 text-center text-[11px] text-amber-300">0</span>
                                                <button onclick="changeItemQty('<?php echo addslashes($item['nama']); ?> (Ice)', <?php echo $item['ice']; ?>, 1, 'Minuman')" class="w-5 h-5 rounded bg-blue-900 text-white text-[10px]">+</button>
                                                <span class="text-[10px] text-amber-400 ml-1"><?php echo number_format($item['ice'], 0, ',', '.'); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- 4. Nutrisari Segar (Ice) with + - buttons -->
                    <div class="bg-gradient-to-br from-stone-950 via-stone-900 to-stone-950 rounded-3xl p-6 border border-amber-500/30 shadow-xl">
                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-stone-800">
                            <div class="flex items-center gap-2.5">
                                <i class="fa-solid fa-lemon text-amber-400 text-lg"></i>
                                <h4 class="text-xl font-bold font-serif-title text-amber-200">NUTRISARI</h4>
                            </div>
                            <span class="px-3 py-1 rounded-full bg-amber-500/20 border border-amber-500/40 text-amber-300 font-bold text-xs">
                                Rp 8.000 (Ice)
                            </span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                            <?php foreach ($menu_minuman['nutrisari']['flavors'] as $flavor): ?>
                                <div class="menu-card p-2.5 rounded-xl bg-black/60 border border-stone-800 flex flex-col justify-between hover:border-amber-500/40 transition"
                                     data-name="nutrisari <?php echo strtolower($flavor); ?>" data-desc="nutrisari buah">
                                    <span class="text-xs font-semibold text-stone-200 block mb-2">
                                        <?php echo htmlspecialchars($flavor); ?>
                                    </span>
                                    <div class="flex items-center justify-between gap-1 bg-stone-900 rounded-lg p-1 border border-stone-800">
                                        <button onclick="changeItemQty('Nutrisari <?php echo addslashes($flavor); ?> (Ice)', 8000, -1, 'Nutrisari')" class="w-5 h-5 rounded bg-stone-800 text-stone-300 text-xs font-bold flex items-center justify-center">-</button>
                                        <span id="qty-<?php echo md5('Nutrisari ' . $flavor . ' (Ice)'); ?>" class="item-qty-badge w-4 text-center text-xs font-bold text-amber-300">0</span>
                                        <button onclick="changeItemQty('Nutrisari <?php echo addslashes($flavor); ?> (Ice)', 8000, 1, 'Nutrisari')" class="w-5 h-5 rounded bg-amber-500 text-stone-950 text-xs font-bold flex items-center justify-center">+</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- 5. Suplemen, Soda & Mineral Additional -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        
                        <!-- Suplemen & Soda -->
                        <div class="bg-stone-950 rounded-2xl p-5 border border-stone-800">
                            <h5 class="text-sm font-bold text-amber-300 flex items-center gap-2 mb-3">
                                <i class="fa-solid fa-bolt text-amber-400"></i> SUPLEMEN
                            </h5>
                            <div class="space-y-2.5">
                                <?php foreach ($menu_minuman['suplemen_soda']['items'] as $sup): ?>
                                    <div class="menu-card p-2 rounded-xl bg-stone-900/80 flex items-center justify-between"
                                         data-name="<?php echo strtolower($sup['nama']); ?>" data-desc="suplemen soda">
                                        <div>
                                            <span class="text-xs text-stone-300 block font-medium"><?php echo htmlspecialchars($sup['nama']); ?></span>
                                            <span class="text-[11px] text-amber-400 font-bold">Rp <?php echo number_format($sup['harga'], 0, ',', '.'); ?></span>
                                        </div>
                                        <div class="flex items-center gap-1 bg-stone-950 border border-stone-800 rounded-lg p-0.5">
                                            <button onclick="changeItemQty('<?php echo addslashes($sup['nama']); ?>', <?php echo $sup['harga']; ?>, -1, 'Minuman')" class="w-5 h-5 rounded bg-stone-800 text-stone-300 text-xs font-bold flex items-center justify-center">-</button>
                                            <span id="qty-<?php echo md5($sup['nama']); ?>" class="item-qty-badge w-4 text-center text-xs font-bold text-amber-300">0</span>
                                            <button onclick="changeItemQty('<?php echo addslashes($sup['nama']); ?>', <?php echo $sup['harga']; ?>, 1, 'Minuman')" class="w-5 h-5 rounded bg-amber-500 text-stone-950 text-xs font-bold flex items-center justify-center">+</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Additional & Mineral -->
                        <div class="bg-stone-950 rounded-2xl p-5 border border-stone-800">
                            <h5 class="text-sm font-bold text-amber-300 flex items-center gap-2 mb-3">
                                <i class="fa-solid fa-circle-plus text-amber-400"></i> ADDITIONAL
                            </h5>
                            <div class="space-y-2.5">
                                <?php foreach ($menu_minuman['additional'] as $add): ?>
                                    <div class="menu-card p-2 rounded-xl bg-stone-900/80 flex items-center justify-between"
                                         data-name="<?php echo strtolower($add['nama']); ?>" data-desc="mineral es susu">
                                        <div>
                                            <span class="text-xs text-stone-300 block font-medium"><?php echo htmlspecialchars($add['nama']); ?></span>
                                            <span class="text-[11px] text-amber-400 font-bold">Rp <?php echo number_format($add['harga'], 0, ',', '.'); ?></span>
                                        </div>
                                        <div class="flex items-center gap-1 bg-stone-950 border border-stone-800 rounded-lg p-0.5">
                                            <button onclick="changeItemQty('<?php echo addslashes($add['nama']); ?>', <?php echo $add['harga']; ?>, -1, 'Additional')" class="w-5 h-5 rounded bg-stone-800 text-stone-300 text-xs font-bold flex items-center justify-center">-</button>
                                            <span id="qty-<?php echo md5($add['nama']); ?>" class="item-qty-badge w-4 text-center text-xs font-bold text-amber-300">0</span>
                                            <button onclick="changeItemQty('<?php echo addslashes($add['nama']); ?>', <?php echo $add['harga']; ?>, 1, 'Additional')" class="w-5 h-5 rounded bg-amber-500 text-stone-950 text-xs font-bold flex items-center justify-center">+</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </section>

    </main>

    <!-- FLOATING CHECKOUT & PESAN BUTTON (Muncul saat memilih menu) -->
    <div id="floating-cart-bar" class="hidden fixed bottom-4 left-1/2 -translate-x-1/2 z-40 w-[94%] max-w-lg bg-stone-900/95 backdrop-blur-2xl border border-amber-500/50 rounded-2xl p-4 shadow-[0_10px_40px_rgba(0,0,0,0.9)] flex items-center justify-between gap-3 animate-bounce-short">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-amber-600 to-amber-400 text-stone-950 flex flex-col items-center justify-center font-black text-sm shadow-md">
                <span id="floating-count">0</span>
                <span class="text-[8px] font-bold tracking-tighter uppercase -mt-1">ITEM</span>
            </div>
            <div>
                <p class="text-[11px] text-stone-400 font-medium">Total Tagihan:</p>
                <p class="text-lg font-black text-amber-400 font-serif-title" id="floating-total">Rp 0</p>
            </div>
        </div>
        
        <!-- Tombol Pesan & Lanjut ke Pembayaran -->
        <button onclick="openPaymentModal()" 
                class="px-5 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-stone-950 font-extrabold text-sm shadow-lg shadow-amber-500/20 hover:shadow-amber-500/40 transform hover:-translate-y-0.5 active:translate-y-0 transition flex items-center gap-2">
            <span>Pesan Sekarang</span>
            <i class="fa-solid fa-arrow-right text-xs"></i>
        </button>
    </div>


    <!-- ==================================================== -->
    <!-- MODAL PROSES PEMESANAN & PEMBAYARAN (CHECKOUT MODAL) -->
    <!-- ==================================================== -->
    <div id="payment-modal" class="fixed inset-0 z-50 bg-black/85 backdrop-blur-md hidden flex items-end sm:items-center justify-center p-0 sm:p-4 overflow-y-auto">
        <div class="bg-stone-950 border border-amber-500/40 w-full max-w-lg rounded-t-3xl sm:rounded-3xl flex flex-col max-h-[92vh] overflow-hidden shadow-2xl relative my-auto">
            
            <!-- Modal Header -->
            <div class="p-5 border-b border-stone-800 flex items-center justify-between bg-stone-900/80">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-base border border-amber-500/30">
                        <i class="fa-solid fa-cash-register"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-lg text-amber-100 font-serif-title">Proses Pemesanan & Pembayaran</h4>
                        <p class="text-[11px] text-stone-400">Lengkapi data meja & pilih metode bayar</p>
                    </div>
                </div>
                <button onclick="closePaymentModal()" class="w-8 h-8 rounded-full bg-stone-800 text-stone-400 hover:text-white flex items-center justify-center">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Modal Content (Scrollable) -->
            <div class="p-5 overflow-y-auto space-y-6 flex-1 divide-y divide-stone-800/80">
                
                <!-- 1. Ringkasan Pesanan -->
                <div>
                    <h5 class="text-xs font-bold tracking-wider text-amber-400 uppercase mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-list-check"></i>
                        <span>1. Rincian Menu Dipilih</span>
                    </h5>
                    
                    <div id="checkout-items-list" class="space-y-2 bg-stone-900/60 rounded-2xl p-3.5 border border-stone-800">
                        <!-- Dynamic items generated by JS -->
                    </div>
                </div>

                <!-- 2. Informasi Meja & Pemesan -->
                <div class="pt-5 space-y-4">
                    <h5 class="text-xs font-bold tracking-wider text-amber-400 uppercase flex items-center gap-2">
                        <i class="fa-solid fa-user-tag"></i>
                        <span>2. Informasi Pemesan & Meja</span>
                    </h5>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-stone-300 mb-1">Nomor Meja <span class="text-red-400">*</span></label>
                            <select id="order-table-number" class="w-full px-3 py-2.5 bg-stone-900 border border-stone-700 rounded-xl text-white text-xs focus:outline-none focus:border-amber-500">
                                <option value="Meja 01">Meja 01</option>
                                <option value="Meja 02">Meja 02</option>
                                <option value="Meja 03">Meja 03</option>
                                <option value="Meja 04">Meja 04</option>
                                <option value="Meja 05">Meja 05</option>
                                <option value="Meja 06">Meja 06</option>
                                <option value="Meja 07">Meja 07</option>
                                <option value="Meja 08">Meja 08</option>
                                <option value="Meja 09">Meja 09</option>
                                <option value="Meja 10">Meja 10</option>
                                <option value="Take Away (Bungkus)">Take Away (Bungkus)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-stone-300 mb-1">Nama Pemesan <span class="text-red-400">*</span></label>
                            <input type="text" id="order-customer-name" placeholder="Nama Anda..." class="w-full px-3 py-2.5 bg-stone-900 border border-stone-700 rounded-xl text-white text-xs focus:outline-none focus:border-amber-500" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-stone-300 mb-1">Catatan Tambahan (Opsional)</label>
                        <input type="text" id="order-notes" placeholder="Misal: pedas sedang, es batu dipisah..." class="w-full px-3 py-2.5 bg-stone-900 border border-stone-700 rounded-xl text-white text-xs focus:outline-none focus:border-amber-500">
                    </div>
                </div>

                <!-- 3. Metode Pembayaran -->
                <div class="pt-5 space-y-3">
                    <h5 class="text-xs font-bold tracking-wider text-amber-400 uppercase flex items-center gap-2">
                        <i class="fa-solid fa-wallet"></i>
                        <span>3. Pilih Metode Pembayaran</span>
                    </h5>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        
                        <!-- QRIS -->
                        <label class="payment-method-card cursor-pointer p-3 rounded-2xl bg-stone-900/80 border-2 border-amber-500 flex flex-col items-center justify-center text-center transition" onclick="selectPaymentMethod('QRIS')">
                            <input type="radio" name="payment_method" value="QRIS" checked class="hidden">
                            <i class="fa-solid fa-qrcode text-amber-400 text-xl mb-1"></i>
                            <span class="text-xs font-bold text-stone-100">QRIS Instant</span>
                            <span class="text-[10px] text-stone-400">Gopay, DANA, OVO, BCA</span>
                        </label>

                        <!-- Transfer Bank Mandiri -->
                        <label class="payment-method-card cursor-pointer p-3 rounded-2xl bg-stone-900/80 border-2 border-stone-800 flex flex-col items-center justify-center text-center transition hover:border-amber-500/40" onclick="selectPaymentMethod('Transfer Mandiri')">
                            <input type="radio" name="payment_method" value="Transfer Mandiri" class="hidden">
                            <i class="fa-solid fa-building-columns text-blue-400 text-xl mb-1"></i>
                            <span class="text-xs font-bold text-stone-100">Bank Mandiri</span>
                            <span class="text-[10px] text-stone-400">Transfer No. Rekening</span>
                        </label>

                        <!-- Tunai / Kasir -->
                        <label class="payment-method-card cursor-pointer p-3 rounded-2xl bg-stone-900/80 border-2 border-stone-800 flex flex-col items-center justify-center text-center transition hover:border-amber-500/40" onclick="selectPaymentMethod('Tunai')">
                            <input type="radio" name="payment_method" value="Tunai" class="hidden">
                            <i class="fa-solid fa-money-bill-wave text-emerald-400 text-xl mb-1"></i>
                            <span class="text-xs font-bold text-stone-100">Tunai / Kasir</span>
                            <span class="text-[10px] text-stone-400">Bayar di meja/kasir</span>
                        </label>

                    </div>

                    <!-- QRIS Official Display Area -->
                    <div id="qris-display-box" class="p-4 rounded-2xl bg-stone-900 border border-amber-500/30 text-center">
                        <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-amber-500/20 text-amber-300 text-[11px] font-bold mb-2">
                            <i class="fa-solid fa-qrcode"></i>
                            <span>QRIS STANDAR NASIONAL</span>
                        </div>
                        <p class="text-xs font-bold text-stone-200 mb-2">Warkop Madam</p>
                        <div class="max-w-[220px] mx-auto bg-white p-2 rounded-2xl shadow-xl border border-stone-700 overflow-hidden cursor-pointer group" onclick="openImageModal('assets/qris.jpg', 'QRIS Pembayaran Warkop Madam', 'Semua E-Wallet & M-Banking')">
                            <img src="assets/qris.jpg" alt="QRIS Warkop Madam" class="w-full h-auto object-contain rounded-xl group-hover:scale-105 transition duration-300">
                        </div>
                        <p class="text-[11px] text-stone-400 mt-2">Klik gambar untuk memperbesar QRIS</p>
                        <p class="text-[10px] text-amber-400/90 font-medium mt-0.5">Dapat discan melalui BCA Mobile, Mandiri Livin, BRImo, DANA, GoPay, OVO, ShopeePay, LinkAja</p>
                    </div>

                    <!-- Transfer Mandiri Display Area -->
                    <div id="transfer-display-box" class="hidden p-4 rounded-2xl bg-stone-900 border border-blue-500/30 text-left">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold text-blue-400 flex items-center gap-1.5">
                                <i class="fa-solid fa-building-columns"></i>
                                TRANSFER BANK MANDIRI
                            </span>
                            <span class="text-[10px] bg-blue-500/20 text-blue-300 px-2 py-0.5 rounded-full font-semibold">Online</span>
                        </div>

                        <div class="bg-black/60 p-3 rounded-xl border border-stone-800 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-stone-400 block uppercase tracking-wider">Nomor Rekening Mandiri</span>
                                <span class="text-base sm:text-lg font-mono font-black text-amber-300 tracking-wider" id="mandiri-rek">1340032583824</span>
                                <span class="text-[11px] text-stone-300 block font-semibold">a.n. Warkop Madam</span>
                            </div>
                            <button type="button" onclick="copyRekening('1340032583824')" class="px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs flex items-center gap-1.5 transition shadow-md active:scale-95">
                                <i class="fa-regular fa-copy"></i>
                                <span id="copy-text">Salin</span>
                            </button>
                        </div>
                        <p class="text-[10px] text-stone-400 mt-2">Silakan transfer sesuai total tagihan dan tunjukkan bukti transfer ke pelayan/barista.</p>
                    </div>

                    <!-- Tunai Display Area -->
                    <div id="tunai-display-box" class="hidden p-4 rounded-2xl bg-stone-900 border border-emerald-500/30 text-left">
                        <div class="flex items-center gap-2 text-emerald-400 text-xs font-bold mb-2">
                            <i class="fa-solid fa-money-bill-wave"></i>
                            <span>PEMBAYARAN TUNAI DI MEJA / KASIR</span>
                        </div>
                        <p class="text-xs text-stone-300 leading-relaxed">
                            Pesanan Anda akan langsung diproses. Pembayaran dapat dilakukan langsung ke kasir atau kepada pelayan yang mengantarkan hidangan ke meja Anda.
                        </p>
                    </div>

                    <!-- UPLOAD BUKTI PEMBAYARAN AREA (Wajib untuk QRIS & Transfer Mandiri) -->
                    <div id="payment-proof-container" class="pt-2">
                        <label class="block text-xs font-bold text-amber-300 mb-1.5 flex items-center justify-between">
                            <span class="flex items-center gap-1.5">
                                <i class="fa-solid fa-receipt text-amber-400"></i>
                                Upload Bukti Pembayaran <span class="text-red-400">*</span>
                            </span>
                            <span class="text-[10px] text-stone-400 font-normal">Screenshot QRIS / Struk Transfer</span>
                        </label>

                        <div class="border-2 border-dashed border-stone-700 hover:border-amber-500 rounded-2xl p-4 bg-stone-900/60 text-center transition cursor-pointer relative group" onclick="document.getElementById('payment-proof-input').click()">
                            <input type="file" id="payment-proof-input" name="payment_proof" accept="image/*" class="hidden" onchange="previewPaymentProof(this)">
                            
                            <!-- State 1: Belum Ada File -->
                            <div id="proof-placeholder" class="py-2">
                                <div class="w-10 h-10 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition">
                                    <i class="fa-solid fa-cloud-arrow-up text-base"></i>
                                </div>
                                <p class="text-xs font-bold text-stone-200">Klik di sini untuk upload bukti transfer / QRIS</p>
                                <p class="text-[10px] text-stone-400 mt-0.5">Format: JPG, PNG, WEBP, atau screenshot m-banking</p>
                            </div>

                            <!-- State 2: Preview File yang dipilih -->
                            <div id="proof-preview-wrapper" class="hidden flex items-center gap-3 p-2 bg-black/60 rounded-xl border border-stone-800 text-left">
                                <img id="proof-image-thumbnail" src="" alt="Bukti Transfer" class="w-14 h-14 rounded-lg object-cover border border-amber-500/40 shrink-0">
                                <div class="overflow-hidden flex-1">
                                    <span class="text-xs font-bold text-amber-300 block truncate" id="proof-file-name">bukti_transfer.jpg</span>
                                    <span class="text-[10px] text-emerald-400 flex items-center gap-1 mt-0.5">
                                        <i class="fa-solid fa-circle-check"></i> Bukti siap dilampirkan
                                    </span>
                                </div>
                                <button type="button" onclick="event.stopPropagation(); removePaymentProof();" class="text-stone-400 hover:text-red-400 text-xs px-2 py-1 bg-stone-900 rounded-lg border border-stone-800">
                                    Ganti
                                </button>
                            </div>

                        </div>
                        <p class="text-[10px] text-amber-400/80 mt-1 italic">* Harap lampirkan bukti transaksi yang sah agar pesanan dapat diverifikasi oleh kasir & barista.</p>
                    </div>

                </div>

            </div>

            <!-- Modal Footer (Total & Submit) -->
            <div class="p-5 border-t border-stone-800 bg-stone-900/90 flex flex-col gap-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-stone-400">Total Pembayaran:</span>
                    <span class="text-xl font-black text-amber-400 font-serif-title" id="checkout-modal-total">Rp 0</span>
                </div>
                
                <button onclick="submitOrderProcess()" 
                        class="w-full py-3.5 rounded-xl bg-gradient-to-r from-amber-500 via-amber-400 to-amber-500 text-stone-950 font-black text-sm tracking-wide shadow-lg shadow-amber-500/25 hover:from-amber-400 hover:to-amber-300 transition duration-200 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Konfirmasi & Bayar Sekarang</span>
                </button>
            </div>

        </div>
    </div>


    <!-- ==================================================== -->
    <!-- MODAL STRUK PESANAN BERHASIL (ORDER SUCCESS RECEIPT) -->
    <!-- ==================================================== -->
    <div id="receipt-modal" class="fixed inset-0 z-50 bg-black/90 backdrop-blur-md hidden flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-stone-950 border border-amber-500/40 w-full max-w-md rounded-3xl p-6 shadow-2xl text-center relative my-auto">
            
            <!-- Success Icon -->
            <div class="w-16 h-16 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 flex items-center justify-center text-2xl mx-auto mb-3 animate-pulse">
                <i class="fa-solid fa-check-double"></i>
            </div>

            <h4 class="text-2xl font-black font-serif-title text-amber-200">Pesanan Berhasil Diterima!</h4>
            <p class="text-xs text-stone-400 mt-1">Barista & Dapur Warkop Madam sedang menyiapkan pesanan Anda.</p>

            <!-- Digital Receipt Box -->
            <div class="mt-5 p-4 rounded-2xl bg-stone-900 border border-stone-800 text-left text-xs font-mono text-stone-300 space-y-3">
                <div class="text-center pb-2 border-b border-stone-800">
                    <span class="font-bold text-sm text-amber-300 font-serif-title block">WARKOP MADAM</span>
                    <span class="text-[10px] text-stone-500" id="receipt-order-id">#MDM-8891</span>
                </div>

                <div class="flex justify-between text-[11px]">
                    <span class="text-stone-400">Nama:</span>
                    <span class="font-bold text-stone-200" id="receipt-name">-</span>
                </div>
                <div class="flex justify-between text-[11px]">
                    <span class="text-stone-400">Meja:</span>
                    <span class="font-bold text-amber-400" id="receipt-table">-</span>
                </div>
                <div class="flex justify-between text-[11px]">
                    <span class="text-stone-400">Metode Bayar:</span>
                    <span class="font-bold text-emerald-400" id="receipt-payment">-</span>
                </div>
                <div class="flex justify-between text-[11px]" id="receipt-notes-row">
                    <span class="text-stone-400">Catatan:</span>
                    <span class="font-normal text-stone-300 italic" id="receipt-notes">-</span>
                </div>

                <div class="pt-2 border-t border-stone-800">
                    <span class="font-bold text-[11px] text-stone-400 block mb-1">Rincian Menu:</span>
                    <div id="receipt-items-container" class="space-y-1 text-[11px]"></div>
                </div>

                <div class="pt-2 border-t border-stone-800 flex justify-between font-bold text-sm text-amber-400">
                    <span>Total Pembayaran:</span>
                    <span id="receipt-total-price">Rp 0</span>
                </div>
            </div>

            <!-- Receipt Actions -->
            <div class="mt-6 space-y-2.5">
                <button onclick="sendOrderToWhatsApp()" 
                        class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center justify-center gap-2 transition shadow-lg">
                    <i class="fa-brands fa-whatsapp text-base"></i>
                    <span>Kirim Pesanan ke WhatsApp Barista</span>
                </button>
                <button onclick="resetAndCloseAll()" 
                        class="w-full py-2.5 rounded-xl bg-stone-900 hover:bg-stone-800 text-stone-300 font-semibold text-xs border border-stone-800 transition">
                    Pesan Menu Lainnya / Tutup
                </button>
            </div>

        </div>
    </div>


    <!-- HIGH-DEFINITION IMAGE PREVIEW MODAL (Sharp & Clean Zoom) -->
    <div id="image-modal" class="fixed inset-0 z-50 bg-black/90 backdrop-blur-md hidden flex items-center justify-center p-4" onclick="closeImageModal()">
        <div class="max-w-md w-full bg-stone-950 border border-amber-500/40 rounded-3xl overflow-hidden shadow-2xl p-3 relative" onclick="event.stopPropagation()">
            <button onclick="closeImageModal()" class="absolute top-5 right-5 z-10 w-9 h-9 rounded-full bg-black/70 text-stone-300 hover:text-white flex items-center justify-center border border-stone-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div class="w-full h-72 sm:h-80 rounded-2xl bg-black overflow-hidden flex items-center justify-center">
                <img id="modal-image-preview" src="" alt="Menu Preview" class="w-full h-full object-cover">
            </div>
            <div class="p-4 text-center">
                <h4 id="modal-image-title" class="text-xl font-bold font-serif-title text-amber-200"></h4>
                <p id="modal-image-price" class="text-base font-extrabold text-amber-400 font-serif-title mt-1"></p>
            </div>
        </div>
    </div>

    <!-- Friendly Toast Notification Popup -->
    <div id="cart-toast" class="toast-hidden fixed bottom-24 left-1/2 -translate-x-1/2 z-50 px-4 py-2.5 rounded-2xl bg-stone-900/95 border border-amber-500/50 text-amber-300 text-xs font-semibold shadow-2xl backdrop-blur-xl flex items-center gap-2.5">
        <span id="toast-icon" class="w-6 h-6 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center text-xs">✨</span>
        <span id="toast-msg">Item berhasil ditambahkan!</span>
    </div>

    <!-- Enhanced Back to Top Button with Smooth Scroll -->
    <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" id="back-to-top" class="fixed bottom-24 right-5 z-30 w-11 h-11 rounded-full bg-stone-900/90 text-amber-400 border border-amber-500/40 shadow-xl flex items-center justify-center hover:bg-amber-500 hover:text-stone-950 transition-all duration-300 opacity-0 pointer-events-none translate-y-4 hover:scale-110 active:scale-95">
        <i class="fa-solid fa-arrow-up text-sm"></i>
    </button>

    <!-- Footer -->
    <footer class="mt-16 text-center text-xs text-stone-500 border-t border-stone-900 pt-8 pb-4 flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-4">
        <p>&copy; 2026 <span class="text-amber-400 font-medium">Warkop Madam</span>. All rights reserved.</p>
        <span class="hidden sm:inline">&bull;</span>
        <a href="login_admin.php" class="text-stone-400 hover:text-amber-400 transition inline-flex items-center gap-1">
            <i class="fa-solid fa-lock text-[10px]"></i>
            <span>Akses Admin</span>
        </a>
    </footer>

    <!-- Interactive Client JavaScript (Order Logic, Plus/Minus, Payment & Checkout) -->
    <script>
        // Global Order State
        let myOrder = [];
        let selectedPayment = 'QRIS';
        let latestOrderReceipt = null;
        let toastTimeout = null;

        // Change Quantity directly with + and - buttons
        function changeItemQty(name, price, delta, category) {
            let item = myOrder.find(i => i.name === name);
            if (!item && delta > 0) {
                item = { name, price, category, qty: 0 };
                myOrder.push(item);
            }

            if (item) {
                item.qty += delta;
                if (delta > 0) {
                    showToast(`+1 ${name} (${item.qty} di pesanan)`, '✨');
                } else if (item.qty > 0) {
                    showToast(`-1 ${name} (${item.qty} tersisa)`, '🗑️');
                } else {
                    showToast(`${name} dihapus dari pesanan`, 'ℹ️');
                }

                if (item.qty <= 0) {
                    myOrder = myOrder.filter(i => i.name !== name);
                }
            }

            updateAllQtyBadges(name);
            renderFloatingBar();
        }

        // Update all badge numbers in the cards in real-time with pop animation
        function updateAllQtyBadges(activeName = null) {
            document.querySelectorAll('.item-qty-badge').forEach(badge => {
                badge.innerText = '0';
            });

            // Update badge by md5 name
            document.querySelectorAll('[id^="qty-"]').forEach(el => {
                const id = el.id.replace('qty-', '');
                myOrder.forEach(item => {
                    if (md5(item.name) === id) {
                        el.innerText = item.qty;
                        if (activeName && item.name === activeName) {
                            el.classList.remove('qty-pop');
                            void el.offsetWidth; // Trigger reflow
                            el.classList.add('qty-pop');
                        }
                    }
                });
            });
        }

        // MD5 helper for JavaScript element ID matching
        function md5(string) {
            function RotateLeft(lValue, iShiftBits) {
                return (lValue<<iShiftBits) | (lValue>>>(32-iShiftBits));
            }
            function AddUnsigned(lX,lY) {
                var lX4,lY4,lX8,lY8,lResult;
                lX8 = (lX & 0x80000000);
                lY8 = (lY & 0x80000000);
                lX4 = (lX & 0x40000000);
                lY4 = (lY & 0x40000000);
                lResult = (lX & 0x3FFFFFFF)+(lY & 0x3FFFFFFF);
                if (lX4 & lY4) {
                    return (lResult ^ 0x80000000 ^ lX8 ^ lY8);
                }
                if (lX4 | lY4) {
                    if (lResult & 0x40000000) {
                        return (lResult ^ 0xC0000000 ^ lX8 ^ lY8);
                    } else {
                        return (lResult ^ 0x40000000 ^ lX8 ^ lY8);
                    }
                } else {
                    return (lResult ^ lX8 ^ lY8);
                }
            }
            function F(x,y,z) { return (x & y) | ((~x) & z); }
            function G(x,y,z) { return (x & z) | (y & (~z)); }
            function H(x,y,z) { return (x ^ y ^ z); }
            function I(x,y,z) { return (y ^ (x | (~z))); }
            function FF(a,b,c,d,x,s,ac) {
                a = AddUnsigned(a, AddUnsigned(AddUnsigned(F(b, c, d), x), ac));
                return AddUnsigned(RotateLeft(a, s), b);
            };
            function GG(a,b,c,d,x,s,ac) {
                a = AddUnsigned(a, AddUnsigned(AddUnsigned(G(b, c, d), x), ac));
                return AddUnsigned(RotateLeft(a, s), b);
            };
            function HH(a,b,c,d,x,s,ac) {
                a = AddUnsigned(a, AddUnsigned(AddUnsigned(H(b, c, d), x), ac));
                return AddUnsigned(RotateLeft(a, s), b);
            };
            function II(a,b,c,d,x,s,ac) {
                a = AddUnsigned(a, AddUnsigned(AddUnsigned(I(b, c, d), x), ac));
                return AddUnsigned(RotateLeft(a, s), b);
            };
            function ConvertToWordArray(string) {
                var lWordCount;
                var lMessageLength = string.length;
                var lNumberOfWords_temp1=lMessageLength + 8;
                var lNumberOfWords_temp2=(lNumberOfWords_temp1-(lNumberOfWords_temp1 % 64))/64;
                var lNumberOfWords = (lNumberOfWords_temp2+1)*16;
                var lWordArray=Array(lNumberOfWords-1);
                var lBytePosition = 0;
                var lByteCount = 0;
                while ( lByteCount < lMessageLength ) {
                    lWordCount = (lByteCount-(lByteCount % 4))/4;
                    lBytePosition = (lByteCount % 4)*8;
                    lWordArray[lWordCount] = (lWordArray[lWordCount] | (string.charCodeAt(lByteCount)<<lBytePosition));
                    lByteCount++;
                }
                lWordCount = (lByteCount-(lByteCount % 4))/4;
                lBytePosition = (lByteCount % 4)*8;
                lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80<<lBytePosition);
                lWordArray[lNumberOfWords-2] = lMessageLength<<3;
                lWordArray[lNumberOfWords-1] = lMessageLength>>>29;
                return lWordArray;
            };
            function WordToHex(lValue) {
                var WordToHexValue="",WordToHexValue_temp="",lByte,lCount;
                for (lCount = 0;lCount<=3;lCount++) {
                    lByte = (lValue>>>(lCount*8)) & 255;
                    WordToHexValue_temp = "0" + lByte.toString(16);
                    WordToHexValue = WordToHexValue + WordToHexValue_temp.substr(WordToHexValue_temp.length-2,2);
                }
                return WordToHexValue;
            };
            var x=Array();
            var k,AA,BB,CC,DD,a,b,c,d;
            var S11=7, S12=12, S13=17, S14=22;
            var S21=5, S22=9 , S23=14, S24=20;
            var S31=4, S32=11, S33=16, S34=23;
            var S41=6, S42=10, S43=15, S44=21;
            x = ConvertToWordArray(string);
            a = 0x67452301; b = 0xEFCDAB89; c = 0x98BADCFE; d = 0x10325476;
            for (k=0;k<x.length;k+=16) {
                AA=a; BB=b; CC=c; DD=d;
                a=FF(a,b,c,d,x[k+0], S11,0xD76AA478);
                d=FF(d,a,b,c,x[k+1], S12,0xE8C7B756);
                c=FF(c,d,a,b,x[k+2], S13,0x242070DB);
                b=FF(b,c,d,a,x[k+3], S14,0xC1BDCEEE);
                a=FF(a,b,c,d,x[k+4], S11,0xF57C0FAF);
                d=FF(d,a,b,c,x[k+5], S12,0x4787C62A);
                c=FF(c,d,a,b,x[k+6], S13,0xA8304613);
                b=FF(b,c,d,a,x[k+7], S14,0xFD469501);
                a=FF(a,b,c,d,x[k+8], S11,0x698098D8);
                d=FF(d,a,b,c,x[k+9], S12,0x8B44F7AF);
                c=FF(c,d,a,b,x[k+10],S13,0xFFFF5BB1);
                b=FF(b,c,d,a,x[k+11],S14,0x895CD7BE);
                a=FF(a,b,c,d,x[k+12],S11,0x6B901122);
                d=FF(d,a,b,c,x[k+13],S12,0xFD987193);
                c=FF(c,d,a,b,x[k+14],S13,0xA679438E);
                b=FF(b,c,d,a,x[k+15],S14,0x49B40821);
                a=GG(a,b,c,d,x[k+1], S21,0xF61E2562);
                d=GG(d,a,b,c,x[k+6], S22,0xC040B340);
                c=GG(c,d,a,b,x[k+11],S23,0x265E5A51);
                b=GG(b,c,d,a,x[k+0], S24,0xE9B6C7AA);
                a=GG(a,b,c,d,x[k+5], S21,0xD62F105D);
                d=GG(d,a,b,c,x[k+10],S22,0x2441453);
                c=GG(c,d,a,b,x[k+15],S23,0xD8A1E681);
                b=GG(b,c,d,a,x[k+4], S24,0xE7D3FBC8);
                a=GG(a,b,c,d,x[k+9], S21,0x21E1CDE6);
                d=GG(d,a,b,c,x[k+14],S22,0xC33707D6);
                c=GG(c,d,a,b,x[k+3], S23,0xF4D50D87);
                b=GG(b,c,d,a,x[k+8], S24,0x455A14ED);
                a=GG(a,b,c,d,x[k+13],S21,0xA9E3E905);
                d=GG(d,a,b,c,x[k+2], S22,0xFCEFA3F8);
                c=GG(c,d,a,b,x[k+7], S23,0x676F02D9);
                b=GG(b,c,d,a,x[k+12],S24,0x8D2A4C8A);
                a=HH(a,b,c,d,x[k+5], S31,0xFFFA3942);
                d=HH(d,a,b,c,x[k+8], S32,0x8771F681);
                c=HH(c,d,a,b,x[k+11],S33,0x6D9D6122);
                b=HH(b,c,d,a,x[k+14],S34,0xFDE5380C);
                a=HH(a,b,c,d,x[k+1], S31,0xA4BEEA44);
                d=HH(d,a,b,c,x[k+4], S32,0x4BDECFA9);
                c=HH(c,d,a,b,x[k+7], S33,0xF6BB4B60);
                b=HH(b,c,d,a,x[k+10],S34,0xBEBFBC70);
                a=HH(a,b,c,d,x[k+13],S31,0x289B7EC6);
                d=HH(d,a,b,c,x[k+0], S32,0xEAA127FA);
                c=HH(c,d,a,b,x[k+3], S33,0xD4EF3085);
                b=HH(b,c,d,a,x[k+6], S34,0x4881D05);
                a=HH(a,b,c,d,x[k+9], S31,0xD9D4D039);
                d=HH(d,a,b,c,x[k+12],S32,0xE6DB99E5);
                c=HH(c,d,a,b,x[k+15],S33,0x1FA27CF8);
                b=HH(b,c,d,a,x[k+2], S34,0xC4AC5665);
                a=II(a,b,c,d,x[k+0], S41,0xF4292244);
                d=II(d,a,b,c,x[k+7], S42,0x432AFF97);
                c=II(c,d,a,b,x[k+14],S43,0xAB9423A7);
                b=II(b,c,d,a,x[k+5], S44,0xFC93A039);
                a=II(a,b,c,d,x[k+12],S41,0x655B59C3);
                d=II(d,a,b,c,x[k+3], S42,0x8F0CCC92);
                c=II(c,d,a,b,x[k+10],S43,0xFFEFF47D);
                b=II(b,c,d,a,x[k+1], S44,0x85845DD1);
                a=II(a,b,c,d,x[k+8], S41,0x6FA87E4F);
                d=II(d,a,b,c,x[k+15],S42,0xFE2CE6E0);
                c=II(c,d,a,b,x[k+6], S43,0xA3014314);
                b=II(b,c,d,a,x[k+13],S44,0x4E0811A1);
                a=II(a,b,c,d,x[k+4], S41,0xF7537E82);
                d=II(d,a,b,c,x[k+11],S42,0xBD3AF235);
                c=II(c,d,a,b,x[k+2], S43,0x2AD7D2BB);
                b=II(b,c,d,a,x[k+9], S44,0xEB86D391);
                a=AddUnsigned(a,AA);
                b=AddUnsigned(b,BB);
                c=AddUnsigned(c,CC);
                d=AddUnsigned(d,DD);
            }
            var temp = WordToHex(a)+WordToHex(b)+WordToHex(c)+WordToHex(d);
            return temp.toLowerCase();
        }

        // Render Floating Order Bar
        function renderFloatingBar() {
            const headerCount = document.getElementById('header-order-count');
            const floatingCount = document.getElementById('floating-count');
            const floatingTotal = document.getElementById('floating-total');
            const floatingBar = document.getElementById('floating-cart-bar');

            let totalQty = 0;
            let totalPrice = 0;

            myOrder.forEach(item => {
                totalQty += item.qty;
                totalPrice += (item.price * item.qty);
            });

            headerCount.innerText = totalQty;
            floatingCount.innerText = totalQty;
            floatingTotal.innerText = 'Rp ' + totalPrice.toLocaleString('id-ID');

            if (totalQty > 0) {
                floatingBar.classList.remove('hidden');
            } else {
                floatingBar.classList.add('hidden');
            }
        }

        // Open Payment Modal & Render Checkout List
        function openPaymentModal() {
            if (myOrder.length === 0) {
                alert('Silakan pilih minimal 1 menu dengan tombol + terlebih dahulu!');
                return;
            }

            const listEl = document.getElementById('checkout-items-list');
            const checkoutTotal = document.getElementById('checkout-modal-total');

            let totalPrice = 0;
            let html = '';

            myOrder.forEach(item => {
                const subtotal = item.price * item.qty;
                totalPrice += subtotal;
                html += `
                    <div class="flex items-center justify-between py-2 border-b border-stone-800/60 last:border-none">
                        <div>
                            <h6 class="text-xs sm:text-sm font-bold text-stone-100">${item.name}</h6>
                            <p class="text-[10px] text-stone-400">@ Rp ${item.price.toLocaleString('id-ID')} &bull; Subtotal: <span class="text-amber-400 font-bold">Rp ${subtotal.toLocaleString('id-ID')}</span></p>
                        </div>
                        <div class="flex items-center gap-1.5 bg-stone-950 p-1 rounded-lg border border-stone-800">
                            <button onclick="changeItemQty('${item.name.replace(/'/g, "\\'")}', ${item.price}, -1, '${item.category}'); openPaymentModal();" class="w-6 h-6 rounded bg-stone-800 hover:bg-stone-700 text-stone-300 text-xs font-bold flex items-center justify-center">-</button>
                            <span class="text-xs font-bold text-amber-300 w-4 text-center">${item.qty}</span>
                            <button onclick="changeItemQty('${item.name.replace(/'/g, "\\'")}', ${item.price}, 1, '${item.category}'); openPaymentModal();" class="w-6 h-6 rounded bg-amber-500 hover:bg-amber-400 text-stone-950 text-xs font-bold flex items-center justify-center">+</button>
                        </div>
                    </div>
                `;
            });

            listEl.innerHTML = html;
            checkoutTotal.innerText = 'Rp ' + totalPrice.toLocaleString('id-ID');
            document.getElementById('payment-modal').classList.remove('hidden');
        }

        function closePaymentModal() {
            document.getElementById('payment-modal').classList.add('hidden');
        }

        // Select Payment Method Tab
        function selectPaymentMethod(method) {
            selectedPayment = method;
            const cards = document.querySelectorAll('.payment-method-card');
            cards.forEach(c => {
                c.classList.remove('border-amber-500', 'bg-stone-900');
                c.classList.add('border-stone-800');
            });

            const activeInput = document.querySelector(`input[name="payment_method"][value="${method}"]`);
            if (activeInput) {
                activeInput.checked = true;
                const activeCard = activeInput.closest('.payment-method-card');
                activeCard.classList.remove('border-stone-800');
                activeCard.classList.add('border-amber-500', 'bg-stone-900');
            }

            const qrisBox = document.getElementById('qris-display-box');
            const transferBox = document.getElementById('transfer-display-box');
            const tunaiBox = document.getElementById('tunai-display-box');
            const proofContainer = document.getElementById('payment-proof-container');

            if (qrisBox) qrisBox.classList.add('hidden');
            if (transferBox) transferBox.classList.add('hidden');
            if (tunaiBox) tunaiBox.classList.add('hidden');

            if (method === 'QRIS' && qrisBox) {
                qrisBox.classList.remove('hidden');
                if (proofContainer) proofContainer.classList.remove('hidden');
            } else if (method === 'Transfer Mandiri' && transferBox) {
                transferBox.classList.remove('hidden');
                if (proofContainer) proofContainer.classList.remove('hidden');
            } else if (method === 'Tunai' && tunaiBox) {
                tunaiBox.classList.remove('hidden');
                if (proofContainer) proofContainer.classList.add('hidden');
            }
        }

        // Preview Payment Proof File
        function previewPaymentProof(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('proof-image-thumbnail').src = e.target.result;
                    document.getElementById('proof-file-name').innerText = file.name;
                    document.getElementById('proof-placeholder').classList.add('hidden');
                    document.getElementById('proof-preview-wrapper').classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        }

        function removePaymentProof() {
            const input = document.getElementById('payment-proof-input');
            input.value = '';
            document.getElementById('proof-placeholder').classList.remove('hidden');
            document.getElementById('proof-preview-wrapper').classList.add('hidden');
        }

        // Copy Account Number Helper
        function copyRekening(rekNumber) {
            navigator.clipboard.writeText(rekNumber).then(() => {
                const btnText = document.getElementById('copy-text');
                if (btnText) {
                    btnText.innerText = 'Tersalin!';
                    setTimeout(() => {
                        btnText.innerText = 'Salin';
                    }, 2000);
                }
            }).catch(() => {
                prompt('Salin nomor rekening:', rekNumber);
            });
        }

        // Submit Order & Process Receipt
        async function submitOrderProcess() {
            const table = document.getElementById('order-table-number').value;
            const name = document.getElementById('order-customer-name').value.trim();
            const notes = document.getElementById('order-notes').value.trim();
            const proofInput = document.getElementById('payment-proof-input');

            if (!name) {
                alert('Mohon masukkan Nama Pemesan!');
                document.getElementById('order-customer-name').focus();
                return;
            }

            if (myOrder.length === 0) {
                alert('Pesanan kosong!');
                return;
            }

            // Validasi WAJIB Upload Bukti Transfer untuk QRIS & Transfer Mandiri
            if (selectedPayment === 'QRIS' || selectedPayment === 'Transfer Mandiri') {
                if (!proofInput.files || proofInput.files.length === 0) {
                    alert('PERHATIAN: Untuk pembayaran menggunakan ' + selectedPayment + ', Anda WAJIB mengunggah bukti pembayaran (screenshot QRIS atau struk transfer) sebelum melanjutkan pemesanan!');
                    document.getElementById('payment-proof-container').scrollIntoView({ behavior: 'smooth' });
                    return;
                }
            }

            const orderId = '#MDM-' + Math.floor(1000 + Math.random() * 9000);
            let totalPrice = 0;
            let orderItemsSummary = [];
            
            myOrder.forEach(i => {
                totalPrice += (i.price * i.qty);
                orderItemsSummary.push(`${i.qty}x ${i.name}`);
            });

            // Tampilkan status proses pada tombol
            const submitBtn = event.currentTarget || document.querySelector('#payment-modal button[onclick*="submitOrderProcess"]');
            let originalBtnHtml = '';
            if (submitBtn) {
                originalBtnHtml = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span>Memproses & Mengunggah Bukti...</span>';
            }

            try {
                // Gunakan FormData untuk mengirim data teks dan file bukti transfer
                const formData = new FormData();
                formData.append('order_number', orderId);
                formData.append('customer_name', name);
                formData.append('table_number', table);
                formData.append('payment_method', selectedPayment);
                formData.append('total_price', totalPrice);
                formData.append('order_items', orderItemsSummary.join(', '));
                formData.append('notes', notes);

                if (proofInput.files && proofInput.files[0]) {
                    formData.append('payment_proof', proofInput.files[0]);
                }

                // Kirim ke Database MySQL melalui API
                const response = await fetch('api_order.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (!result.success) {
                    alert('Catatan Server: ' + result.message);
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnHtml;
                    }
                    return;
                }
            } catch (err) {
                console.error('Gagal mengirim ke server:', err);
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
            }

            latestOrderReceipt = {
                orderId,
                name,
                table,
                notes,
                payment: selectedPayment,
                items: [...myOrder],
                total: totalPrice
            };

            // Render Receipt Modal
            document.getElementById('receipt-order-id').innerText = orderId;
            document.getElementById('receipt-name').innerText = name;
            document.getElementById('receipt-table').innerText = table;
            document.getElementById('receipt-payment').innerText = selectedPayment;
            
            const notesRow = document.getElementById('receipt-notes-row');
            if (notes) {
                notesRow.classList.remove('hidden');
                document.getElementById('receipt-notes').innerText = notes;
            } else {
                notesRow.classList.add('hidden');
            }

            let receiptHtml = '';
            myOrder.forEach(item => {
                receiptHtml += `
                    <div class="flex justify-between">
                        <span>${item.qty}x ${item.name}</span>
                        <span class="text-amber-400">Rp ${(item.price * item.qty).toLocaleString('id-ID')}</span>
                    </div>
                `;
            });
            document.getElementById('receipt-items-container').innerHTML = receiptHtml;
            document.getElementById('receipt-total-price').innerText = 'Rp ' + totalPrice.toLocaleString('id-ID');

            // Switch Modals
            closePaymentModal();
            document.getElementById('receipt-modal').classList.remove('hidden');
        }

        // Send Order to WhatsApp Barista
        function sendOrderToWhatsApp() {
            if (!latestOrderReceipt) return;

            let text = `*PESANAN BARU - WARKOP MADAM*\n`;
            text += `No. Pesanan: ${latestOrderReceipt.orderId}\n`;
            text += `Nama: ${latestOrderReceipt.name}\n`;
            text += `Meja: ${latestOrderReceipt.table}\n`;
            text += `Metode Bayar: ${latestOrderReceipt.payment}\n`;
            if (latestOrderReceipt.notes) {
                text += `Catatan: ${latestOrderReceipt.notes}\n`;
            }
            text += `--------------------------\n`;
            latestOrderReceipt.items.forEach(i => {
                text += `- ${i.qty}x ${i.name} (Rp ${(i.price * i.qty).toLocaleString('id-ID')})\n`;
            });
            text += `--------------------------\n`;
            text += `*TOTAL: Rp ${latestOrderReceipt.total.toLocaleString('id-ID')}*\n\nMohon segera diproses, terima kasih!`;

            const waUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(text)}`;
            window.open(waUrl, '_blank');
        }

        function resetAndCloseAll() {
            myOrder = [];
            updateAllQtyBadges();
            renderFloatingBar();
            document.getElementById('receipt-modal').classList.add('hidden');
        }

        // Friendly Toast Notification Helper
        function showToast(msg, icon = '✨') {
            const toast = document.getElementById('cart-toast');
            const toastMsg = document.getElementById('toast-msg');
            const toastIcon = document.getElementById('toast-icon');
            if (!toast || !toastMsg) return;

            toastMsg.innerText = msg;
            toastIcon.innerText = icon;

            toast.classList.remove('toast-hidden');
            toast.classList.add('toast-visible');

            if (toastTimeout) clearTimeout(toastTimeout);
            toastTimeout = setTimeout(() => {
                toast.classList.remove('toast-visible');
                toast.classList.add('toast-hidden');
            }, 2200);
        }

        // Quick Tag Filter (Friendly Chips)
        function filterByTag(keyword) {
            const input = document.getElementById('menu-search-input');
            if (input) {
                input.value = keyword;
                searchMenuItems();
            }
        }

        // Live Search System
        function searchMenuItems() {
            const query = document.getElementById('menu-search-input').value.trim().toLowerCase();
            const cards = document.querySelectorAll('.menu-card');
            const clearBtn = document.getElementById('clear-search-btn');
            const searchInfo = document.getElementById('search-results-info');
            const searchText = document.getElementById('search-results-text');

            if (query.length > 0) {
                clearBtn.classList.remove('hidden');
                searchInfo.classList.remove('hidden');
                let matchCount = 0;

                cards.forEach(card => {
                    const name = card.getAttribute('data-name') || '';
                    const desc = card.getAttribute('data-desc') || '';
                    if (name.includes(query) || desc.includes(query)) {
                        card.style.display = '';
                        card.classList.add('is-visible');
                        matchCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                if (matchCount === 0) {
                    searchText.innerHTML = `<span>Menu tidak ditemukan untuk <b>"${query}"</b>. Coba kata kunci lain ya! 😊</span>`;
                } else {
                    searchText.innerHTML = `<span>Menemukan <b>${matchCount}</b> menu untuk kata kunci "${query}"</span>`;
                }
            } else {
                clearBtn.classList.add('hidden');
                searchInfo.classList.add('hidden');
                cards.forEach(card => {
                    card.style.display = '';
                });
            }
        }

        function clearSearch() {
            document.getElementById('menu-search-input').value = '';
            searchMenuItems();
        }

        // Image Zoom Modal
        function openImageModal(imgSrc, title, price) {
            document.getElementById('modal-image-preview').src = imgSrc;
            document.getElementById('modal-image-title').innerText = title;
            document.getElementById('modal-image-price').innerText = price;
            document.getElementById('image-modal').classList.remove('hidden');
        }

        function closeImageModal() {
            document.getElementById('image-modal').classList.add('hidden');
        }

        // Keyboard ESC handler
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeImageModal();
                closePaymentModal();
                document.getElementById('receipt-modal').classList.add('hidden');
            }
        });

        // ==============================================================
        // SCROLL EFFECTS & INTERACTIVE SCROLLSPY
        // ==============================================================
        function initScrollAnimations() {
            // 1. Scroll Reveal Observer for Menu Cards and Friendly Elements
            const observerOptions = {
                root: null,
                rootMargin: '0px 0px -40px 0px',
                threshold: 0.1
            };

            const revealObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        // Stagger effect when scrolling
                        setTimeout(() => {
                            entry.target.classList.add('is-visible');
                        }, 50);
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.menu-card, .friendly-chip, .menu-section-header').forEach(el => {
                revealObserver.observe(el);
            });

            // 2. Window Scroll Event: Progress Bar, Header Elevation, Back to Top, and Scrollspy
            const progressBar = document.getElementById('scroll-progress-bar');
            const backToTopBtn = document.getElementById('back-to-top');
            const mainHeader = document.getElementById('main-header');
            const sections = document.querySelectorAll('section.menu-section');
            const tabLinks = {
                'menu-makanan': document.getElementById('tab-menu-makanan'),
                'menu-cemilan': document.getElementById('tab-menu-cemilan'),
                'menu-minuman': document.getElementById('tab-menu-minuman')
            };

            window.addEventListener('scroll', () => {
                const scrollTop = window.scrollY || document.documentElement.scrollTop;
                const docHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                
                // A. Update Top Scroll Progress Bar
                if (progressBar && docHeight > 0) {
                    const scrollPercent = (scrollTop / docHeight) * 100;
                    progressBar.style.width = Math.min(100, Math.max(0, scrollPercent)) + '%';
                }

                // B. Back to Top Button Visibility
                if (backToTopBtn) {
                    if (scrollTop > 280) {
                        backToTopBtn.classList.remove('opacity-0', 'pointer-events-none', 'translate-y-4');
                        backToTopBtn.classList.add('opacity-100', 'pointer-events-auto', 'translate-y-0');
                    } else {
                        backToTopBtn.classList.add('opacity-0', 'pointer-events-none', 'translate-y-4');
                        backToTopBtn.classList.remove('opacity-100', 'pointer-events-auto', 'translate-y-0');
                    }
                }

                // C. Header Elevation on Scroll
                if (mainHeader) {
                    if (scrollTop > 40) {
                        mainHeader.classList.add('bg-stone-950/98', 'border-amber-500/30', 'shadow-[0_10px_30px_rgba(0,0,0,0.8)]');
                        mainHeader.classList.remove('bg-stone-950/90', 'border-stone-800/80');
                    } else {
                        mainHeader.classList.remove('bg-stone-950/98', 'border-amber-500/30', 'shadow-[0_10px_30px_rgba(0,0,0,0.8)]');
                        mainHeader.classList.add('bg-stone-950/90', 'border-stone-800/80');
                    }
                }

                // D. Active Category Tab Scrollspy
                let currentSectionId = '';
                sections.forEach(sec => {
                    const rect = sec.getBoundingClientRect();
                    if (rect.top <= 180 && rect.bottom >= 180) {
                        currentSectionId = sec.getAttribute('id');
                    }
                });

                if (currentSectionId && tabLinks[currentSectionId]) {
                    Object.values(tabLinks).forEach(tab => {
                        if (tab) {
                            tab.classList.remove('active', 'bg-amber-500', 'text-stone-950');
                            tab.classList.add('bg-stone-900', 'text-amber-300');
                        }
                    });
                    const activeTab = tabLinks[currentSectionId];
                    if (activeTab) {
                        activeTab.classList.add('active');
                        activeTab.classList.remove('bg-stone-900', 'text-amber-300');
                    }
                }
            }, { passive: true });
        }

        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initScrollAnimations);
        } else {
            initScrollAnimations();
        }
    </script>

</body>
</html>
