<?php
require_once '../config/init.php';
require_once '../config/database.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
    exit;
}

// Set content type to JSON
header('Content-Type: application/json');

// Get transaction ID from parameter
$transaksi_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($transaksi_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID transaksi tidak valid']);
    exit;
}

try {
    $db = new Database();
    
    // Fetch transaction details
    $query = "SELECT t.*, u.nama as nama_pasien, u.email, p.no_hp
              FROM transaksi t
              LEFT JOIN pasien p ON t.pasien_id = p.id
              LEFT JOIN users u ON p.user_id = u.id
              WHERE t.id = :id";
    
    $db->query($query);
    $db->bind(':id', $transaksi_id);
    $transaksi = $db->single();
    
    if (!$transaksi) {
        echo json_encode(['success' => false, 'message' => 'Transaksi tidak ditemukan']);
        exit;
    }
    
    // Fetch transaction items
    $query = "SELECT dt.*, o.nama_obat, o.jenis
              FROM detail_transaksi dt
              LEFT JOIN obat o ON dt.obat_id = o.id
              WHERE dt.transaksi_id = :transaksi_id
              ORDER BY dt.id";
    
    $db->query($query);
    $db->bind(':transaksi_id', $transaksi_id);
    $items = $db->resultset();
    
    // Return response
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
?>
