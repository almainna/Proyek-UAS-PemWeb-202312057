<?php
require_once '../config/init.php';

header('Content-Type: application/json');

// Cek apakah user sudah login dan merupakan pasien
if (!isLoggedIn() || !isPasien()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID transaksi tidak valid']);
    exit;
}

$transaksi_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Ambil data pasien
    $stmt = $conn->prepare("SELECT id FROM pasien WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $pasien = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pasien) {
        echo json_encode(['success' => false, 'message' => 'Data pasien tidak ditemukan']);
        exit;
    }
    
    // Ambil detail transaksi
    $stmt = $conn->prepare("
        SELECT * FROM transaksi 
        WHERE id = ? AND pasien_id = ?
    ");
    $stmt->execute([$transaksi_id, $pasien['id']]);
    $transaksi = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$transaksi) {
        echo json_encode(['success' => false, 'message' => 'Transaksi tidak ditemukan']);
        exit;
    }
    
    // Ambil detail item transaksi
    $stmt = $conn->prepare("
        SELECT dt.*, o.nama_obat, o.jenis
        FROM detail_transaksi dt
        JOIN obat o ON dt.obat_id = o.id
        WHERE dt.transaksi_id = ?
        ORDER BY o.nama_obat
    ");
    $stmt->execute([$transaksi_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'transaksi' => $transaksi,
            'items' => $items
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}