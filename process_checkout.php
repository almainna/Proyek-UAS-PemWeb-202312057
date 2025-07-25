<?php
require_once 'config/init.php';

// Check if user is logged in and is a patient
if (!isLoggedIn() || !isPasien()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$cart = $input['cart'] ?? [];

if (empty($cart)) {
    echo json_encode(['success' => false, 'message' => 'Keranjang kosong']);
    exit;
}

$db = new Database();

try {
    // Get patient ID
    $db->query("SELECT id FROM pasien WHERE user_id = :user_id");
    $db->bind(':user_id', $_SESSION['user_id']);
    $pasien = $db->single();
    
    if (!$pasien) {
        echo json_encode(['success' => false, 'message' => 'Data pasien tidak ditemukan']);
        exit;
    }
    
    $pasien_id = $pasien['id'];
    
    // Calculate total
    $total_harga = 0;
    foreach ($cart as $item) {
        $total_harga += $item['harga'] * $item['qty'];
    }
    
    // Insert transaction
    $db->query("INSERT INTO transaksi (pasien_id, tanggal, total_harga, status) VALUES (:pasien_id, CURDATE(), :total_harga, 'diproses')");
    $db->bind(':pasien_id', $pasien_id);
    $db->bind(':total_harga', $total_harga);
    $db->execute();
    
    $transaksi_id = $db->lastInsertId();
    
    // Insert transaction details and update stock
    foreach ($cart as $item) {
        // Check stock
        $db->query("SELECT stok FROM obat WHERE id = :obat_id");
        $db->bind(':obat_id', $item['id']);
        $obat = $db->single();
        
        if (!$obat || $obat['stok'] < $item['qty']) {
            throw new Exception("Stok obat {$item['nama']} tidak mencukupi");
        }
        
        // Insert detail
        $subtotal = $item['harga'] * $item['qty'];
        $db->query("INSERT INTO detail_transaksi (transaksi_id, obat_id, qty, subtotal) VALUES (:transaksi_id, :obat_id, :qty, :subtotal)");
        $db->bind(':transaksi_id', $transaksi_id);
        $db->bind(':obat_id', $item['id']);
        $db->bind(':qty', $item['qty']);
        $db->bind(':subtotal', $subtotal);
        $db->execute();
        
        // Update stock
        $db->query("UPDATE obat SET stok = stok - :qty WHERE id = :obat_id");
        $db->bind(':qty', $item['qty']);
        $db->bind(':obat_id', $item['id']);
        $db->execute();
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Pembelian berhasil',
        'transaksi_id' => $transaksi_id
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>