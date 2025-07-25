<?php
require_once '../config/init.php';

header('Content-Type: application/json');

// Cek apakah user sudah login dan merupakan pasien
if (!isLoggedIn() || !isPasien()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Cek method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
    exit;
}

// Ambil data JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['booking_id']) || !is_numeric($input['booking_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID booking tidak valid']);
    exit;
}

$booking_id = (int)$input['booking_id'];
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
    
    // Cek apakah booking ada dan milik pasien ini
    $stmt = $conn->prepare("
        SELECT * FROM booking 
        WHERE id = ? AND pasien_id = ? AND status = 'pending'
    ");
    $stmt->execute([$booking_id, $pasien['id']]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        echo json_encode(['success' => false, 'message' => 'Booking tidak ditemukan atau tidak dapat dibatalkan']);
        exit;
    }
    
    // Update status booking menjadi dibatalkan
    $stmt = $conn->prepare("
        UPDATE booking 
        SET status = 'dibatalkan' 
        WHERE id = ?
    ");
    $stmt->execute([$booking_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Booking berhasil dibatalkan'
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}