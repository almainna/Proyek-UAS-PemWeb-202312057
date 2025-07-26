<?php
/**
 * Script untuk memperbaiki masalah CSS/JS tidak muncul di hosting
 * Jalankan script ini di hosting untuk diagnosa dan perbaikan
 */

echo "<!DOCTYPE html>
<html>
<head>
    <title>Fix Hosting Assets - Klinik Alma Sehat</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; }
        h1 { color: #007bff; }
        h2 { color: #28a745; border-bottom: 2px solid #28a745; padding-bottom: 5px; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-danger { background: #dc3545; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🔧 Fix Hosting Assets - Klinik Alma Sehat</h1>";

// 1. DIAGNOSA ENVIRONMENT
echo "<h2>1. 📊 Diagnosa Environment</h2>";

$currentEnv = defined('ENVIRONMENT') ? ENVIRONMENT : 'undefined';
$currentBaseUrl = defined('BASE_URL') ? BASE_URL : 'undefined';
$actualUrl = 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);

echo "<div class='info'>";
echo "<strong>Current Environment:</strong> $currentEnv<br>";
echo "<strong>Current BASE_URL:</strong> $currentBaseUrl<br>";
echo "<strong>Actual URL:</strong> $actualUrl<br>";
echo "<strong>Server Name:</strong> " . $_SERVER['HTTP_HOST'] . "<br>";
echo "<strong>Script Path:</strong> " . __DIR__ . "<br>";
echo "</div>";

// 2. CEK FILE ASSETS
echo "<h2>2. 📁 Cek File Assets</h2>";

$assetsPath = __DIR__ . '/assets';
$cssFiles = [
    'css/style.css',
    'css/admin-style.css',
    'css/user-style.css'
];
$jsFiles = [
    'js/script.js',
    'js/admin-script.js',
    'js/user-script.js'
];

echo "<h3>CSS Files:</h3>";
foreach ($cssFiles as $file) {
    $fullPath = $assetsPath . '/' . $file;
    if (file_exists($fullPath)) {
        $size = filesize($fullPath);
        $readable = is_readable($fullPath) ? 'Yes' : 'No';
        echo "<div class='success'>✅ $file (Size: " . number_format($size) . " bytes, Readable: $readable)</div>";
    } else {
        echo "<div class='error'>❌ $file - FILE NOT FOUND</div>";
    }
}

echo "<h3>JS Files:</h3>";
foreach ($jsFiles as $file) {
    $fullPath = $assetsPath . '/' . $file;
    if (file_exists($fullPath)) {
        $size = filesize($fullPath);
        $readable = is_readable($fullPath) ? 'Yes' : 'No';
        echo "<div class='success'>✅ $file (Size: " . number_format($size) . " bytes, Readable: $readable)</div>";
    } else {
        echo "<div class='error'>❌ $file - FILE NOT FOUND</div>";
    }
}

// 3. TEST URL ASSETS
echo "<h2>3. 🔗 Test URL Assets</h2>";

function testUrl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $httpCode;
}

$baseForTest = 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
$testUrls = [
    'CSS Style' => $baseForTest . '/assets/css/style.css',
    'JS Script' => $baseForTest . '/assets/js/script.js',
    'Admin CSS' => $baseForTest . '/assets/css/admin-style.css',
    'Admin JS' => $baseForTest . '/assets/js/admin-script.js'
];

foreach ($testUrls as $name => $url) {
    $httpCode = testUrl($url);
    if ($httpCode == 200) {
        echo "<div class='success'>✅ $name: $url (HTTP $httpCode)</div>";
    } else {
        echo "<div class='error'>❌ $name: $url (HTTP $httpCode)</div>";
    }
}

// 4. FIX ENVIRONMENT
echo "<h2>4. ⚙️ Fix Environment Configuration</h2>";

if (isset($_POST['fix_environment'])) {
    $newBaseUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/';
    $envContent = file_get_contents(__DIR__ . '/config/environment.php');
    
    // Update environment to production
    $envContent = preg_replace(
        "/define\('ENVIRONMENT', '[^']*'\);/", 
        "define('ENVIRONMENT', 'production');", 
        $envContent
    );
    
    // Update base URL for production
    $envContent = preg_replace(
        "/(case 'production':.*?define\('BASE_URL', ')[^']*('\);)/s",
        "\\1$newBaseUrl\\2",
        $envContent
    );
    
    if (file_put_contents(__DIR__ . '/config/environment.php', $envContent)) {
        echo "<div class='success'>✅ Environment berhasil diupdate ke production dengan BASE_URL: $newBaseUrl</div>";
    } else {
        echo "<div class='error'>❌ Gagal mengupdate environment.php</div>";
    }
}

echo "<form method='post'>";
echo "<button type='submit' name='fix_environment' class='btn btn-success'>🔧 Fix Environment untuk Production</button>";
echo "</form>";

// 5. GENERATE ALTERNATIVE LINKS
echo "<h2>5. 🔄 Generate Alternative Asset Links</h2>";

if (isset($_POST['generate_alternative'])) {
    $headerContent = file_get_contents(__DIR__ . '/includes/header.php');
    $footerContent = file_get_contents(__DIR__ . '/includes/footer.php');
    
    // Backup original files
    file_put_contents(__DIR__ . '/includes/header.php.backup', $headerContent);
    file_put_contents(__DIR__ . '/includes/footer.php.backup', $footerContent);
    
    // Replace dengan absolute URL
    $hostUrl = 'https://' . $_SERVER['HTTP_HOST'];
    $newCssLink = $hostUrl . dirname($_SERVER['REQUEST_URI']) . '/assets/css/style.css';
    $newJsLink = $hostUrl . dirname($_SERVER['REQUEST_URI']) . '/assets/js/script.js';
    
    // Update header
    $headerContent = preg_replace(
        '/href="[^"]*assets\/css\/style\.css"/',
        'href="' . $newCssLink . '"',
        $headerContent
    );
    
    // Update footer
    $footerContent = preg_replace(
        '/src="[^"]*assets\/js\/script\.js"/',
        'src="' . $newJsLink . '"',
        $footerContent
    );
    
    if (file_put_contents(__DIR__ . '/includes/header.php', $headerContent) && 
        file_put_contents(__DIR__ . '/includes/footer.php', $footerContent)) {
        echo "<div class='success'>✅ Asset links berhasil diupdate ke absolute URL</div>";
        echo "<div class='info'>
        <strong>New CSS URL:</strong> $newCssLink<br>
        <strong>New JS URL:</strong> $newJsLink
        </div>";
    } else {
        echo "<div class='error'>❌ Gagal mengupdate asset links</div>";
    }
}

echo "<form method='post'>";
echo "<button type='submit' name='generate_alternative' class='btn btn-warning'>🔄 Generate Absolute Asset URLs</button>";
echo "</form>";

// 6. BUAT CDN FALLBACK
echo "<h2>6. 🌐 Setup CDN Fallback</h2>";

if (isset($_POST['setup_cdn'])) {
    $cdnFooter = '
    <!-- CDN Fallback Script -->
    <script>
    // Check if CSS loaded
    function checkCSS() {
        var sheets = document.styleSheets;
        var localCSSLoaded = false;
        for (var i = 0; i < sheets.length; i++) {
            if (sheets[i].href && sheets[i].href.indexOf("style.css") > -1) {
                localCSSLoaded = true;
                break;
            }
        }
        
        if (!localCSSLoaded) {
            // Load Bootstrap CSS as fallback
            var link = document.createElement("link");
            link.rel = "stylesheet";
            link.href = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css";
            document.head.appendChild(link);
            
            // Add basic styling
            var style = document.createElement("style");
            style.textContent = `
                body { padding-top: 70px; }
                .navbar-brand { font-weight: bold; }
                .hero-section { background: #007bff; color: white; padding: 60px 0; }
                .card { box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
            `;
            document.head.appendChild(style);
        }
    }
    
    // Check when page loads
    document.addEventListener("DOMContentLoaded", checkCSS);
    </script>';
    
    $footerContent = file_get_contents(__DIR__ . '/includes/footer.php');
    
    // Add CDN fallback before closing body tag
    $footerContent = str_replace('</body>', $cdnFooter . '</body>', $footerContent);
    
    if (file_put_contents(__DIR__ . '/includes/footer.php', $footerContent)) {
        echo "<div class='success'>✅ CDN Fallback berhasil ditambahkan</div>";
    } else {
        echo "<div class='error'>❌ Gagal menambahkan CDN Fallback</div>";
    }
}

echo "<form method='post'>";
echo "<button type='submit' name='setup_cdn' class='btn btn-info'>🌐 Setup CDN Fallback</button>";
echo "</form>";

// 7. RESTORE BACKUP
echo "<h2>7. 🔙 Restore Backup</h2>";

if (isset($_POST['restore_backup'])) {
    $headerBackup = __DIR__ . '/includes/header.php.backup';
    $footerBackup = __DIR__ . '/includes/footer.php.backup';
    
    $restored = 0;
    if (file_exists($headerBackup)) {
        copy($headerBackup, __DIR__ . '/includes/header.php');
        $restored++;
    }
    if (file_exists($footerBackup)) {
        copy($footerBackup, __DIR__ . '/includes/footer.php');
        $restored++;
    }
    
    if ($restored > 0) {
        echo "<div class='success'>✅ $restored file(s) berhasil direstore dari backup</div>";
    } else {
        echo "<div class='warning'>⚠️ Tidak ada backup file yang ditemukan</div>";
    }
}

echo "<form method='post'>";
echo "<button type='submit' name='restore_backup' class='btn btn-danger'>🔙 Restore dari Backup</button>";
echo "</form>";

// 8. INSTRUKSI MANUAL
echo "<h2>8. 📝 Instruksi Manual</h2>";
echo "<div class='info'>";
echo "<strong>Jika masalah masih berlanjut, lakukan langkah manual berikut:</strong><br><br>";
echo "1. <strong>Upload ulang folder assets/</strong> ke hosting<br>";
echo "2. <strong>Pastikan permission folder assets/</strong> adalah 755<br>";
echo "3. <strong>Pastikan permission file CSS/JS</strong> adalah 644<br>";
echo "4. <strong>Update BASE_URL</strong> di config/environment.php sesuai domain hosting<br>";
echo "5. <strong>Ubah ENVIRONMENT</strong> dari 'development' ke 'production'<br>";
echo "6. <strong>Clear cache browser</strong> dan test ulang<br>";
echo "</div>";

echo "<div class='code'>";
echo "// File: config/environment.php<br>";
echo "define('ENVIRONMENT', 'production');<br>";
echo "define('BASE_URL', 'https://yourdomain.com/');<br>";
echo "</div>";

// 9. TEST FINAL
echo "<h2>9. 🧪 Test Final</h2>";
echo "<div class='warning'>";
echo "Setelah melakukan perbaikan, silakan:<br>";
echo "1. Refresh halaman ini<br>";
echo "2. Buka halaman utama website<br>";
echo "3. Periksa apakah styling sudah muncul<br>";
echo "4. Periksa console browser untuk error JavaScript<br>";
echo "</div>";

echo "<div style='text-align: center; margin: 30px 0;'>";
echo "<a href='index.php' class='btn btn-success'>🏠 Kembali ke Beranda</a>";
echo "<a href='javascript:location.reload()' class='btn'>🔄 Refresh Test</a>";
echo "</div>";

echo "</div></body></html>";
?>
