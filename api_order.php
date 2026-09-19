<?php
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/includes/ai_proof_verifier.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode permintaan tidak diizinkan!']);
    exit;
}

// 0. Endpoint Pre-Scan AI Cepat untuk Umpan Balik Real-Time di Antarmuka Pelanggan
if (isset($_GET['action']) && $_GET['action'] === 'scan_proof') {
    if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Tidak ada berkas bukti pembayaran yang dikirim.']);
        exit;
    }

    $upload_res = validate_and_save_upload($_FILES['payment_proof'], 'uploads/proofs/', 5242880);
    if (!$upload_res['success']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $upload_res['message']]);
        exit;
    }

    $temp_path = $upload_res['file_path'];
    $expected_amount = intval($_POST['expected_amount'] ?? 0);
    $payment_method = trim($_POST['payment_method'] ?? 'QRIS');

    try {
        $pdo = get_db_connection();
        $ai_result = verify_payment_proof($temp_path, $expected_amount, $payment_method, $pdo);
        
        echo json_encode([
            'success' => true,
            'file_path' => $temp_path,
            'ai_result' => $ai_result
        ]);
        exit;
    } catch (Exception $e) {
        error_log("AI Scan Proof Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal memindai bukti transfer dengan AI.']);
        exit;
    }
}

// Ambil parameter POST
$order_number = trim($_POST['order_number'] ?? '');
$customer_name = trim($_POST['customer_name'] ?? '');
$table_number = trim($_POST['table_number'] ?? '');
$payment_method = trim($_POST['payment_method'] ?? 'QRIS');
$total_price = intval($_POST['total_price'] ?? 0);
$order_items = trim($_POST['order_items'] ?? '');
$notes = trim($_POST['notes'] ?? '');
$existing_proof_path = trim($_POST['existing_proof_path'] ?? '');

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
        $existing_proof_path = trim($data['existing_proof_path'] ?? '');
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
} elseif (!empty($existing_proof_path) && file_exists(__DIR__ . '/' . ltrim($existing_proof_path, '/'))) {
    // Gunakan file yang telah dipindai dari pre-scan sebelumnya
    $payment_proof_path = $existing_proof_path;
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

// Inisialisasi data AI Verification
$ai_status = 'skipped';
$ai_confidence = 0;
$ai_detected_amount = null;
$ai_reference_no = null;
$ai_analysis_json = null;
$proof_hash = null;
$ai_verification_details = null;

try {
    $pdo = get_db_connection();

    // Jalankan Verifikasi AI jika bukti pembayaran diunggah
    if (!empty($payment_proof_path)) {
        $ai_result = verify_payment_proof($payment_proof_path, $total_price, $payment_method, $pdo);
        $ai_verification_details = $ai_result;

        $ai_status = $ai_result['status']; // 'valid' | 'review' | 'invalid'
        $ai_confidence = intval($ai_result['confidence']);
        $ai_detected_amount = $ai_result['detected_amount'] > 0 ? intval($ai_result['detected_amount']) : null;
        $ai_reference_no = !empty($ai_result['reference_no']) ? $ai_result['reference_no'] : null;
        $ai_analysis_json = json_encode($ai_result['analysis'], JSON_UNESCAPED_UNICODE);
        $proof_hash = $ai_result['proof_hash'] ?? null;

        // Pengecekan Strict Mode AI Anti-Fraud (Tolak foto sembarangan & non-struk)
        $strict_mode = env('AI_VERIFICATION_STRICT_MODE', true);
        if ($strict_mode && ($ai_status === 'invalid' || !$ai_result['is_valid'])) {
            // Hapus file palsu/tidak valid agar tidak membebani server
            $project_root = __DIR__;
            $full_file = $project_root . '/' . ltrim($payment_proof_path, '/');
            if (file_exists($full_file)) {
                @unlink($full_file);
            }

            http_response_code(400);
            echo json_encode([
                'success' => false,
                'ai_rejected' => true,
                'ai_status' => 'invalid',
                'message' => '🛡️ Verifikasi AI Menolak Bukti Transfer: ' . $ai_result['message'],
                'ai_details' => $ai_result
            ]);
            exit;
        }
    }

    // Generate nomor pesanan jika belum ada atau tidak valid formatnya
    if (empty($order_number) || !preg_match('/^#MDM-[A-Za-z0-9_-]{3,15}$/', $order_number)) {
        $order_number = '#MDM-' . random_int(1000, 9999);
    }

    $stmt = $pdo->prepare("INSERT INTO orders (
        order_number, customer_name, table_number, payment_method, total_price, order_items, notes, payment_proof, 
        ai_status, ai_confidence, ai_detected_amount, ai_reference_no, ai_analysis_json, proof_hash, status
    ) VALUES (
        :order_number, :customer_name, :table_number, :payment_method, :total_price, :order_items, :notes, :payment_proof,
        :ai_status, :ai_confidence, :ai_detected_amount, :ai_reference_no, :ai_analysis_json, :proof_hash, 'pending'
    )");
    
    $stmt->execute([
        'order_number'       => $order_number,
        'customer_name'      => $customer_name,
        'table_number'       => $table_number,
        'payment_method'     => $payment_method,
        'total_price'        => $total_price,
        'order_items'        => $order_items,
        'notes'              => $notes,
        'payment_proof'      => $payment_proof_path,
        'ai_status'          => $ai_status,
        'ai_confidence'      => $ai_confidence,
        'ai_detected_amount' => $ai_detected_amount,
        'ai_reference_no'    => $ai_reference_no,
        'ai_analysis_json'   => $ai_analysis_json,
        'proof_hash'         => $proof_hash
    ]);

    $inserted_id = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Pesanan berhasil diverifikasi dan disimpan dengan aman!',
        'order_id' => intval($inserted_id),
        'order_number' => $order_number,
        'payment_proof' => $payment_proof_path,
        'ai_status' => $ai_status,
        'ai_confidence' => $ai_confidence,
        'ai_message' => $ai_verification_details['message'] ?? null
    ]);
} catch (Exception $e) {
    error_log("API Order Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kendala sistem saat menyimpan pesanan. Silakan coba kembali.'
    ]);
}
