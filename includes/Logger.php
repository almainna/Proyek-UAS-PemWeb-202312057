<?php
/**
 * Logger Class
 * Kelas untuk mengelola logging sistem
 */

class Logger {
    private static $instance = null;
    private $logPath;
    
    private function __construct() {
        $this->logPath = LOG_PATH;
        $this->ensureLogDirectory();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function ensureLogDirectory() {
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }
    
    public function log($level, $message, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = [
            'timestamp' => $timestamp,
            'level' => strtoupper($level),
            'message' => $message,
            'context' => $context,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'user_id' => $_SESSION['user_id'] ?? null,
            'session_id' => session_id()
        ];
        
        $logLine = $this->formatLogEntry($logEntry);
        $filename = $this->getLogFilename($level);
        
        file_put_contents($filename, $logLine, FILE_APPEND | LOCK_EX);
        
        // Rotate log files if they get too large
        $this->rotateLogIfNeeded($filename);
    }
    
    private function formatLogEntry($entry) {
        $contextStr = !empty($entry['context']) ? json_encode($entry['context']) : '';
        return sprintf(
            "[%s] %s: %s | IP: %s | User: %s | Session: %s | Context: %s\n",
            $entry['timestamp'],
            $entry['level'],
            $entry['message'],
            $entry['ip'],
            $entry['user_id'] ?? 'guest',
            $entry['session_id'],
            $contextStr
        );
    }
    
    private function getLogFilename($level) {
        $date = date('Y-m-d');
        return $this->logPath . strtolower($level) . '_' . $date . '.log';
    }
    
    private function rotateLogIfNeeded($filename) {
        if (file_exists($filename) && filesize($filename) > 10 * 1024 * 1024) { // 10MB
            $rotatedName = $filename . '.' . time();
            rename($filename, $rotatedName);
            
            // Compress old log file
            if (function_exists('gzencode')) {
                $content = file_get_contents($rotatedName);
                file_put_contents($rotatedName . '.gz', gzencode($content));
                unlink($rotatedName);
            }
        }
    }
    
    public function debug($message, $context = []) {
        if (DEBUG_MODE) {
            $this->log('debug', $message, $context);
        }
    }
    
    public function info($message, $context = []) {
        $this->log('info', $message, $context);
    }
    
    public function warning($message, $context = []) {
        $this->log('warning', $message, $context);
    }
    
    public function error($message, $context = []) {
        $this->log('error', $message, $context);
    }
    
    public function critical($message, $context = []) {
        $this->log('critical', $message, $context);
    }
    
    // Security logging methods
    public function logLogin($userId, $success = true) {
        $message = $success ? "User login successful" : "User login failed";
        $this->info($message, ['user_id' => $userId, 'success' => $success]);
    }
    
    public function logLogout($userId) {
        $this->info("User logout", ['user_id' => $userId]);
    }
    
    public function logSecurityEvent($event, $details = []) {
        $this->warning("Security event: $event", $details);
    }
    
    public function logDatabaseQuery($query, $params = [], $executionTime = null) {
        if (SQL_DEBUG) {
            $context = [
                'query' => $query,
                'params' => $params,
                'execution_time' => $executionTime
            ];
            $this->debug("Database query executed", $context);
        }
    }
    
    public function logApiRequest($endpoint, $method, $params = [], $response = null) {
        $context = [
            'endpoint' => $endpoint,
            'method' => $method,
            'params' => $params,
            'response_code' => http_response_code()
        ];
        $this->info("API request", $context);
    }
    
    public function logFileUpload($filename, $size, $success = true) {
        $context = [
            'filename' => $filename,
            'size' => $size,
            'success' => $success
        ];
        $message = $success ? "File uploaded successfully" : "File upload failed";
        $this->info($message, $context);
    }
    
    public function logBooking($bookingId, $action, $details = []) {
        $context = array_merge(['booking_id' => $bookingId, 'action' => $action], $details);
        $this->info("Booking $action", $context);
    }
    
    public function logTransaction($transactionId, $action, $amount = null, $details = []) {
        $context = array_merge([
            'transaction_id' => $transactionId,
            'action' => $action,
            'amount' => $amount
        ], $details);
        $this->info("Transaction $action", $context);
    }
    
    // Clean up old log files
    public function cleanupOldLogs($days = 30) {
        $files = glob($this->logPath . '*.log*');
        $cutoff = time() - ($days * 24 * 60 * 60);
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }
    
    // Get log statistics
    public function getLogStats($days = 7) {
        $stats = [
            'total_entries' => 0,
            'by_level' => [],
            'by_date' => [],
            'top_users' => [],
            'top_ips' => []
        ];
        
        $files = glob($this->logPath . '*.log');
        $cutoff = time() - ($days * 24 * 60 * 60);
        
        foreach ($files as $file) {
            if (filemtime($file) >= $cutoff) {
                $content = file_get_contents($file);
                $lines = explode("\n", $content);
                
                foreach ($lines as $line) {
                    if (empty(trim($line))) continue;
                    
                    $stats['total_entries']++;
                    
                    // Parse log entry
                    if (preg_match('/\[(.*?)\] (.*?): .*? \| IP: (.*?) \| User: (.*?) \|/', $line, $matches)) {
                        $date = substr($matches[1], 0, 10);
                        $level = $matches[2];
                        $ip = $matches[3];
                        $user = $matches[4];
                        
                        $stats['by_level'][$level] = ($stats['by_level'][$level] ?? 0) + 1;
                        $stats['by_date'][$date] = ($stats['by_date'][$date] ?? 0) + 1;
                        
                        if ($user !== 'guest') {
                            $stats['top_users'][$user] = ($stats['top_users'][$user] ?? 0) + 1;
                        }
                        
                        $stats['top_ips'][$ip] = ($stats['top_ips'][$ip] ?? 0) + 1;
                    }
                }
            }
        }
        
        // Sort arrays
        arsort($stats['top_users']);
        arsort($stats['top_ips']);
        ksort($stats['by_date']);
        
        return $stats;
    }
}

// Global logging functions
function logDebug($message, $context = []) {
    Logger::getInstance()->debug($message, $context);
}

function logInfo($message, $context = []) {
    Logger::getInstance()->info($message, $context);
}

function logWarning($message, $context = []) {
    Logger::getInstance()->warning($message, $context);
}

function logError($message, $context = []) {
    Logger::getInstance()->error($message, $context);
}

function logCritical($message, $context = []) {
    Logger::getInstance()->critical($message, $context);
}
?>