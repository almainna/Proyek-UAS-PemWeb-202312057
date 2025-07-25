<?php
// Start output buffering to prevent headers already sent errors
ob_start();

// Include environment configuration
require_once __DIR__ . '/environment.php';

session_start();

// Include database configuration
require_once 'database.php';

// Helper functions
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isPasien() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'pasien';
}

function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

function formatTanggal($tanggal) {
    return date('d/m/Y', strtotime($tanggal));
}

function formatWaktu($waktu) {
    return date('H:i', strtotime($waktu));
}

// Auto-load classes
spl_autoload_register(function ($class_name) {
    $directories = [
        'models/',
        'controllers/',
        'helpers/'
    ];
    
    foreach ($directories as $directory) {
        $file = $directory . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
?>