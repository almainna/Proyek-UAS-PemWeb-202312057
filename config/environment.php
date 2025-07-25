<?php
/**
 * Environment Configuration
 * Konfigurasi environment untuk deployment
 */

// Environment settings
define('ENVIRONMENT', 'development'); // development, staging, production

// Error reporting berdasarkan environment
switch (ENVIRONMENT) {
    case 'development':
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
        ini_set('display_errors', 1);
        ini_set('log_errors', 1);
        break;
        
    case 'staging':
        error_reporting(E_ALL & ~E_NOTICE);
        ini_set('display_errors', 0);
        ini_set('log_errors', 1);
        break;
        
    case 'production':
        error_reporting(0);
        ini_set('display_errors', 0);
        ini_set('log_errors', 1);
        break;
}

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS

// Upload configuration
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');
ini_set('max_execution_time', 300);

// Memory limit
ini_set('memory_limit', '256M');

// Database configuration berdasarkan environment
switch (ENVIRONMENT) {
    case 'development':
        define('DB_HOST', 'localhost');
        define('DB_USER', 'root');
        define('DB_PASS', '');
        define('DB_NAME', 'klinik_alma');
        define('BASE_URL', 'http://localhost/alma/');
        break;
        
    case 'staging':
        define('DB_HOST', 'localhost');
        define('DB_USER', 'staging_user');
        define('DB_PASS', 'staging_password');
        define('DB_NAME', 'klinik_alma_staging');
        define('BASE_URL', 'https://staging.klinikalmasehat.com/');
        break;
        
    case 'production':
        define('DB_HOST', 'localhost');
        define('DB_USER', 'prod_user');
        define('DB_PASS', 'secure_password');
        define('DB_NAME', 'klinik_alma_prod');
        define('BASE_URL', 'https://klinikalmasehat.com/');
        break;
}

// Application settings
define('APP_NAME', 'Klinik Alma Sehat');
define('APP_VERSION', '1.0.0');
define('APP_DESCRIPTION', 'Sistem Informasi Klinik dengan Apotek Online');

// Security settings
define('HASH_ALGORITHM', PASSWORD_DEFAULT);
define('SESSION_LIFETIME', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// File upload settings
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

// Email settings (untuk fitur masa depan)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_ENCRYPTION', 'tls');

// Pagination settings
define('ITEMS_PER_PAGE', 10);
define('MAX_PAGINATION_LINKS', 5);

// Cache settings
define('CACHE_ENABLED', false);
define('CACHE_LIFETIME', 3600);

// Logging
define('LOG_PATH', __DIR__ . '/../logs/');
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR

// API settings
define('API_VERSION', 'v1');
define('API_RATE_LIMIT', 100); // requests per hour

// Feature flags
define('FEATURE_EMAIL_NOTIFICATIONS', false);
define('FEATURE_SMS_NOTIFICATIONS', false);
define('FEATURE_PAYMENT_GATEWAY', false);
define('FEATURE_TELEMEDICINE', false);

// Maintenance mode
define('MAINTENANCE_MODE', false);
define('MAINTENANCE_MESSAGE', 'Sistem sedang dalam pemeliharaan. Silakan coba lagi nanti.');

// Debug settings
define('DEBUG_MODE', ENVIRONMENT === 'development');
define('SQL_DEBUG', false);

// Backup settings
define('BACKUP_PATH', __DIR__ . '/../backups/');
define('AUTO_BACKUP', false);
define('BACKUP_RETENTION_DAYS', 30);

// Performance settings
define('ENABLE_GZIP', true);
define('ENABLE_CACHING', false);
define('MINIFY_HTML', ENVIRONMENT === 'production');

// Security headers - only set if headers haven't been sent yet
if (ENVIRONMENT === 'production' && !headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// Custom error handler
if (ENVIRONMENT !== 'development') {
    set_error_handler(function($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $log_message = date('Y-m-d H:i:s') . " - Error: $message in $file on line $line\n";
        error_log($log_message, 3, LOG_PATH . 'error.log');
        
        return true;
    });
}

// Exception handler
set_exception_handler(function($exception) {
    $log_message = date('Y-m-d H:i:s') . " - Exception: " . $exception->getMessage() . 
                   " in " . $exception->getFile() . " on line " . $exception->getLine() . "\n";
    error_log($log_message, 3, LOG_PATH . 'error.log');
    
    if (ENVIRONMENT === 'development') {
        echo "<pre>$log_message</pre>";
    } else {
        echo "Terjadi kesalahan sistem. Silakan coba lagi nanti.";
    }
});

// Create necessary directories
$directories = [
    UPLOAD_PATH,
    UPLOAD_PATH . 'doctors/',
    UPLOAD_PATH . 'patients/',
    LOG_PATH,
    BACKUP_PATH
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Maintenance mode check
if (MAINTENANCE_MODE && !isset($_SESSION['admin_override'])) {
    http_response_code(503);
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Maintenance - " . APP_NAME . "</title>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    </head>
    <body class='bg-light d-flex align-items-center justify-content-center' style='min-height: 100vh;'>
        <div class='text-center'>
            <div class='card shadow'>
                <div class='card-body p-5'>
                    <i class='fas fa-tools fa-3x text-warning mb-3'></i>
                    <h3>Sistem Dalam Pemeliharaan</h3>
                    <p class='text-muted'>" . MAINTENANCE_MESSAGE . "</p>
                    <small class='text-muted'>Terima kasih atas pengertian Anda.</small>
                </div>
            </div>
        </div>
    </body>
    </html>";
    exit;
}
?>