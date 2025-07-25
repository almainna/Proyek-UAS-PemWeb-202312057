<?php
require_once 'config/init.php';

header('Content-Type: application/json');

$dokter_id = $_GET['dokter_id'] ?? 0;

if (!$dokter_id) {
    echo json_encode(['success' => false, 'message' => 'Dokter ID required']);
    exit;
}

try {
    $db = new Database();
    
    // Try jadwal_dokter table first
    try {
        $db->query("SELECT * FROM jadwal_dokter WHERE dokter_id = :dokter_id ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')");
        $db->bind(':dokter_id', $dokter_id);
        $schedule = $db->resultset();
    } catch (Exception $e) {
        // Try jadwal_praktik table as fallback
        try {
            $db->query("SELECT * FROM jadwal_praktik WHERE dokter_id = :dokter_id ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')");
            $db->bind(':dokter_id', $dokter_id);
            $schedule = $db->resultset();
        } catch (Exception $e2) {
            // Return default schedule if no table exists
            $schedule = [
                ['hari' => 'Senin', 'jam_mulai' => '08:00', 'jam_selesai' => '12:00'],
                ['hari' => 'Selasa', 'jam_mulai' => '08:00', 'jam_selesai' => '12:00'],
                ['hari' => 'Rabu', 'jam_mulai' => '14:00', 'jam_selesai' => '17:00'],
                ['hari' => 'Kamis', 'jam_mulai' => '08:00', 'jam_selesai' => '12:00'],
                ['hari' => 'Jumat', 'jam_mulai' => '08:00', 'jam_selesai' => '12:00'],
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'schedule' => $schedule
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error loading schedule: ' . $e->getMessage()
    ]);
}
?>
