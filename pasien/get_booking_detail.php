<?php
require_once '../config/init.php';

header('Content-Type: application/json');

// Cek apakah user sudah login dan merupakan pasien
if (!isLoggedIn() || !isPasien()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID booking tidak valid']);
    exit;
}

$booking_id = (int)$_GET['id'];
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
    
    // Ambil detail booking
    $stmt = $conn->prepare("
        SELECT b.*, d.nama_dokter, d.spesialisasi, u.nama as nama_user
        FROM booking b
        JOIN dokter d ON b.dokter_id = d.id
        JOIN users u ON d.user_id = u.id
        WHERE b.id = ? AND b.pasien_id = ?
    ");
    $stmt->execute([$booking_id, $pasien['id']]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        echo json_encode(['success' => false, 'message' => 'Booking tidak ditemukan']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $booking
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}