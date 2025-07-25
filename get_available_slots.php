<?php
require_once 'config/init.php';

header('Content-Type: application/json');

$dokter_id = $_GET['dokter_id'] ?? '';
$tanggal = $_GET['tanggal'] ?? '';
$hari = $_GET['hari'] ?? '';

if (!$dokter_id || !$tanggal || !$hari) {
    echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap']);
    exit;
}

try {
    $db = new Database();
    $slots = [];
    
    // Try to get schedule from database tables
    $schedule = null;
    
    // Try jadwal_dokter table first
    try {
        $db->query("SELECT jam_mulai, jam_selesai FROM jadwal_dokter WHERE dokter_id = :dokter_id AND hari = :hari");
        $db->bind(':dokter_id', $dokter_id);
        $db->bind(':hari', $hari);
        $schedule = $db->single();
    } catch (Exception $e) {
        // Try jadwal_praktik as fallback
        try {
            $db->query("SELECT jam_mulai, jam_selesai FROM jadwal_praktik WHERE dokter_id = :dokter_id AND hari = :hari");
            $db->bind(':dokter_id', $dokter_id);
            $db->bind(':hari', $hari);
            $schedule = $db->single();
        } catch (Exception $e2) {
            // Use default schedule based on day
            $defaultSchedules = [
                'Senin' => ['jam_mulai' => '08:00:00', 'jam_selesai' => '12:00:00'],
                'Selasa' => ['jam_mulai' => '08:00:00', 'jam_selesai' => '12:00:00'],
                'Rabu' => ['jam_mulai' => '14:00:00', 'jam_selesai' => '17:00:00'],
                'Kamis' => ['jam_mulai' => '08:00:00', 'jam_selesai' => '12:00:00'],
                'Jumat' => ['jam_mulai' => '08:00:00', 'jam_selesai' => '12:00:00'],
                'Sabtu' => ['jam_mulai' => '08:00:00', 'jam_selesai' => '11:00:00'],
            ];
            
            if (isset($defaultSchedules[$hari])) {
                $schedule = $defaultSchedules[$hari];
            }
        }
    }
    
    if ($schedule) {
        // Generate time slots (30-minute intervals)
        $start = strtotime($schedule['jam_mulai']);
        $end = strtotime($schedule['jam_selesai']);
        
        // Debug info
        $debug_info = [
            'jam_mulai' => $schedule['jam_mulai'],
            'jam_selesai' => $schedule['jam_selesai'],
            'start_timestamp' => $start,
            'end_timestamp' => $end,
            'generated_slots' => []
        ];
        
        for ($time = $start; $time < $end; $time += 30 * 60) {
            $slot_time = date('H:i', $time);
            $debug_info['generated_slots'][] = $slot_time;
            
            // Check if this slot is already booked (if booking table exists)
            $isBooked = false;
            try {
                $db->query("SELECT id FROM booking WHERE dokter_id = :dokter_id AND tanggal_kunjungan = :tanggal AND jam_kunjungan = :jam AND status != 'dibatalkan'");
                $db->bind(':dokter_id', $dokter_id);
                $db->bind(':tanggal', $tanggal);
                $db->bind(':jam', $slot_time . ':00'); // Add seconds for proper time comparison
                $booked = $db->single();
                $isBooked = (bool)$booked;
            } catch (Exception $e) {
                // Booking table doesn't exist, assume not booked
                $isBooked = false;
            }
            
            if (!$isBooked) {
                $slots[] = $slot_time;
            }
        }
        
        // Include debug info in response (only for debugging)
        if (isset($_GET['debug'])) {
            echo json_encode([
                'success' => true, 
                'slots' => $slots,
                'debug' => $debug_info
            ]);
            exit;
        }
        
    } else {
        // If no schedule found for this day, return empty slots
        $slots = [];
    }
    
    echo json_encode(['success' => true, 'slots' => $slots]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error loading slots: ' . $e->getMessage()]);
}
?>
