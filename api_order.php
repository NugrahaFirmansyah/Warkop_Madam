<?php
require_once __DIR__ . '/security.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode permintaan tidak diizinkan!']);
    exit;
}

// Ambil parameter POST
$order_number = trim($_POST['order_number'] ?? '');
$customer_name = trim($_POST['customer_name'] ?? '');
$table_number = trim($_POST['table_number'] ?? '');
$payment_method = trim($_POST['payment_method'] ?? 'QRIS');
$total_price = intval($_POST['total_price'] ?? 0);
$order_items = trim($_POST['order_items'] ?? '');
$notes = trim($_POST['notes'] ?? '');

// Handle JSON input fallback
if (empty($customer_name) && empty($order_items)) {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (is_array($data)) {
        $order_number = trim($data['order_number'] ?? '');
        $customer_name = trim($data['customer_name'] ?? '');
        $table_number = trim($data['table_number'] ?? '');
        $payment_method = trim($data['payment_method'] ?? 'QRIS');
        $total_price = intval($data['total_price'] ?? 0);
        $order_items = trim($data['order_items'] ?? '');
        $notes = trim($data['notes'] ?? '');
    }
}

// Sanitasi & Validasi Input
$customer_name = strip_tags(mb_substr($customer_name, 0, 100));
$table_number = strip_tags(mb_substr($table_number, 0, 50));
$notes = strip_tags(mb_substr($notes, 0, 500));
$order_items = strip_tags($order_items);

// Validasi Whitelist Metode Pembayaran
$allowed_payment_methods = ['QRIS', 'Transfer Mandiri', 'Tunai'];
if (!in_array($payment_method, $allowed_payment_methods, true)) {
    $payment_method = 'QRIS';
}

if (empty($customer_name) || empty($order_items) || $total_price <= 0 || $total_price > 100000000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Data pesanan tidak lengkap atau jumlah harga tidak valid!']);
    exit;
}

// Handle upload bukti pembayaran dengan validasi keamanan ketat
$payment_proof_path = null;

if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
    $upload_res = validate_and_save_upload($_FILES['payment_proof'], 'uploads/proofs/', 5242880); // max 5MB
    if ($upload_res['success']) {
        $payment_proof_path = $upload_res['file_path'];
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Upload bukti gagal: ' . $upload_res['message']]);
        exit;
    }
}

// Validasi wajib bukti transfer jika QRIS / Transfer Mandiri
if (in_array($payment_method, ['QRIS', 'Transfer Mandiri'], true) && empty($payment_proof_path)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Wajib melampirkan foto/screenshot bukti transfer untuk pembayaran ' . $payment_method . '!'
    ]);
    exit;
}

// Generate nomor pesanan jika belum ada atau tidak valid formatnya
if (empty($order_number) || !preg_match('/^#MDM-[A-Za-z0-9_-]{3,15}$/', $order_number)) {
    $order_number = '#MDM-' . random_int(1000, 9999);
}

try {
    $pdo = get_db_connection();

    $stmt = $pdo->prepare("INSERT INTO orders (order_number, customer_name, table_number, payment_method, total_price, order_items, notes, payment_proof, status) VALUES (:order_number, :customer_name, :table_number, :payment_method, :total_price, :order_items, :notes, :payment_proof, 'pending')");
    
    $stmt->execute([
        'order_number'   => $order_number,
        'customer_name'  => $customer_name,
        'table_number'   => $table_number,
        'payment_method' => $payment_method,
        'total_price'    => $total_price,
        'order_items'    => $order_items,
        'notes'          => $notes,
        'payment_proof'  => $payment_proof_path
    ]);

    $inserted_id = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Pesanan berhasil disimpan ke sistem dengan aman!',
        'order_id' => intval($inserted_id),
        'order_number' => $order_number,
        'payment_proof' => $payment_proof_path
    ]);
} catch (Exception $e) {
    error_log("API Order Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kendala sistem saat menyimpan pesanan. Silakan coba kembali.'
    ]);
}
