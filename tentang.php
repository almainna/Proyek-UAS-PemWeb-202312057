<?php
require_once 'config/init.php';

// Ambil data pengaturan klinik
try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("SELECT nama_pengaturan, nilai FROM pengaturan");
    $stmt->execute();
    $pengaturan_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert to associative array
    $pengaturan = [];
    foreach ($pengaturan_data as $row) {
        $pengaturan[$row['nama_pengaturan']] = $row['nilai'];
    }
    
    // Set default values if not found
    $pengaturan['nama_klinik'] = $pengaturan['nama_klinik'] ?? 'Klinik Alma Sehat';
    $pengaturan['alamat'] = $pengaturan['alamat_klinik'] ?? 'Jl. Kesehatan No. 123, Jakarta';
    $pengaturan['telepon'] = $pengaturan['telepon_klinik'] ?? '(021) 1234-5678';
    $pengaturan['email'] = $pengaturan['email_klinik'] ?? 'info@klinikalmasehat.com';
    $pengaturan['jam_buka'] = $pengaturan['jam_buka'] ?? '08:00 - 20:00';
    $pengaturan['visi'] = $pengaturan['visi'] ?? 'Menjadi klinik terdepan dalam pelayanan kesehatan yang berkualitas dan terjangkau.';
    $pengaturan['misi'] = $pengaturan['misi'] ?? 'Memberikan pelayanan kesehatan terbaik dengan teknologi modern dan tenaga medis profesional.';
    
} catch (Exception $e) {
    $pengaturan = [
        'nama_klinik' => 'Klinik Alma Sehat',
        'alamat' => 'Jl. Kesehatan No. 123, Jakarta',
        'telepon' => '(021) 1234-5678',
        'email' => 'info@klinikalmasehat.com',
        'jam_buka' => '08:00 - 20:00',
        'visi' => 'Menjadi klinik terdepan dalam pelayanan kesehatan yang berkualitas dan terjangkau.',
        'misi' => 'Memberikan pelayanan kesehatan terbaik dengan teknologi modern dan tenaga medis profesional.'
    ];
}

$page_title = "Tentang Kami - " . $pengaturan['nama_klinik'];
include 'includes/user-header.php';
?>

<div class="container mt-4">
    <!-- Hero Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body text-center py-5">
                    <h1 class="display-4 mb-3"><?= htmlspecialchars($pengaturan['nama_klinik']) ?></h1>
                    <p class="lead">Melayani kesehatan Anda dengan sepenuh hati</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Visi Misi -->
    <div class="row mb-5">
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-eye text-primary me-3" style="font-size: 2rem;"></i>
                        <h3 class="card-title mb-0">Visi</h3>
                    </div>
                    <p class="card-text"><?= htmlspecialchars($pengaturan['visi']) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-bullseye text-primary me-3" style="font-size: 2rem;"></i>
                        <h3 class="card-title mb-0">Misi</h3>
                    </div>
                    <p class="card-text"><?= htmlspecialchars($pengaturan['misi']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Layanan Unggulan -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-4">Layanan Unggulan</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card text-center h-100 shadow-sm">
                        <div class="card-body">
                            <i class="fas fa-user-md text-primary mb-3" style="font-size: 3rem;"></i>
                            <h5 class="card-title">Konsultasi Dokter</h5>
                            <p class="card-text">Konsultasi dengan dokter spesialis berpengalaman dan terpercaya.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card text-center h-100 shadow-sm">
                        <div class="card-body">
                            <i class="fas fa-pills text-primary mb-3" style="font-size: 3rem;"></i>
                            <h5 class="card-title">Apotek Online</h5>
                            <p class="card-text">Beli obat secara online dengan mudah dan aman, diantar ke rumah.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card text-center h-100 shadow-sm">
                        <div class="card-body">
                            <i class="fas fa-calendar-check text-primary mb-3" style="font-size: 3rem;"></i>
                            <h5 class="card-title">Booking Online</h5>
                            <p class="card-text">Reservasi jadwal konsultasi secara online, praktis dan efisien.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informasi Kontak -->
    <div class="row mb-5">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi Kontak</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-map-marker-alt text-primary me-3"></i>
                                <div>
                                    <strong>Alamat:</strong><br>
                                    <?= htmlspecialchars($pengaturan['alamat']) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-phone text-primary me-3"></i>
                                <div>
                                    <strong>Telepon:</strong><br>
                                    <a href="tel:<?= htmlspecialchars($pengaturan['telepon']) ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($pengaturan['telepon']) ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-envelope text-primary me-3"></i>
                                <div>
                                    <strong>Email:</strong><br>
                                    <a href="mailto:<?= htmlspecialchars($pengaturan['email']) ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($pengaturan['email']) ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-clock text-primary me-3"></i>
                                <div>
                                    <strong>Jam Operasional:</strong><br>
                                    Senin - Minggu: <?= htmlspecialchars($pengaturan['jam_buka']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Google Maps -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-map me-2"></i>Lokasi Kami</h4>
                </div>
                <div class="card-body p-0">
                    <div class="ratio ratio-16x9" id="map-container">
                        <!-- Fallback image jika maps tidak load -->
                        <div class="d-flex align-items-center justify-content-center h-100 bg-light" id="map-fallback">
                            <div class="text-center">
                                <i class="fas fa-map-marked-alt text-primary mb-3" style="font-size: 3rem;"></i>
                                <h5 class="text-primary">Lokasi Klinik Alma Sehat</h5>
                                <p class="text-muted mb-3"><?= htmlspecialchars($pengaturan['alamat']) ?></p>
                                <a href="https://maps.google.com/?q=Jl.+Kesehatan+No.+123,+Jakarta" 
                                   target="_blank" 
                                   class="btn btn-primary btn-sm">
                                    <i class="fas fa-external-link-alt me-2"></i>Buka di Google Maps
                                </a>
                            </div>
                        </div>
                        <!-- Google Maps iframe dengan error handling -->
                        <iframe 
                            id="google-maps-iframe"
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.521260322283!2d106.8195613!3d-6.1944491!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f5390917b759%3A0x6b45e67356080477!2sJl.%20Kesehatan%2C%20Jakarta!5e0!3m2!1sen!2sid!4v1635123456789!5m2!1sen!2sid"
                            style="border:0; display: none;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade"
                            onload="showMap()"
                            onerror="showFallback()">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fasilitas -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-4">Fasilitas Klinik</h2>
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card text-center border-0">
                        <div class="card-body">
                            <i class="fas fa-stethoscope text-primary mb-2" style="font-size: 2.5rem;"></i>
                            <h6>Ruang Pemeriksaan</h6>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card text-center border-0">
                        <div class="card-body">
                            <i class="fas fa-x-ray text-primary mb-2" style="font-size: 2.5rem;"></i>
                            <h6>Laboratorium</h6>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card text-center border-0">
                        <div class="card-body">
                            <i class="fas fa-prescription-bottle-alt text-primary mb-2" style="font-size: 2.5rem;"></i>
                            <h6>Apotek</h6>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="card text-center border-0">
                        <div class="card-body">
                            <i class="fas fa-wifi text-primary mb-2" style="font-size: 2.5rem;"></i>
                            <h6>WiFi Gratis</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body text-center py-5">
                    <h3 class="mb-3">Siap untuk konsultasi?</h3>
                    <p class="lead mb-4">Booking jadwal konsultasi Anda sekarang juga!</p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="booking.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-calendar-plus me-2"></i>Booking Sekarang
                        </a>
                        <a href="dokter.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-user-md me-2"></i>Lihat Dokter
                        </a>
                        <a href="obat.php" class="btn btn-outline-success btn-lg">
                            <i class="fas fa-pills me-2"></i>Beli Obat
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript untuk handling Google Maps dengan fallback
function showMap() {
    console.log('Google Maps loaded successfully');
    const iframe = document.getElementById('google-maps-iframe');
    const fallback = document.getElementById('map-fallback');
    
    if (iframe && fallback) {
        iframe.style.display = 'block';
        fallback.style.display = 'none';
    }
}

function showFallback() {
    console.log('Google Maps failed to load, showing fallback');
    const iframe = document.getElementById('google-maps-iframe');
    const fallback = document.getElementById('map-fallback');
    
    if (iframe && fallback) {
        iframe.style.display = 'none';
        fallback.style.display = 'flex';
    }
}

// Fallback jika iframe tidak load dalam 5 detik
document.addEventListener('DOMContentLoaded', function() {
    const iframe = document.getElementById('google-maps-iframe');
    const fallback = document.getElementById('map-fallback');
    
    if (iframe && fallback) {
        // Set timeout untuk fallback
        const timeoutId = setTimeout(() => {
            console.log('Google Maps timeout, showing fallback');
            showFallback();
        }, 5000);
        
        // Clear timeout jika berhasil load
        iframe.onload = function() {
            clearTimeout(timeoutId);
            showMap();
        };
        
        // Handle error
        iframe.onerror = function() {
            clearTimeout(timeoutId);
            showFallback();
        };
        
        // Cek apakah sudah ada error dari awal
        try {
            if (iframe.contentDocument === null) {
                // Iframe mungkin diblokir
                setTimeout(() => {
                    if (iframe.style.display === 'none') {
                        showFallback();
                    }
                }, 2000);
            }
        } catch (e) {
            // Cross-origin error, normal untuk iframe eksternal
            console.log('Cross-origin restriction (normal for external iframe)');
        }
    }
});

// Handle network errors atau ad blocker
window.addEventListener('error', function(e) {
    if (e.target && e.target.tagName === 'IFRAME' && e.target.id === 'google-maps-iframe') {
        console.log('Iframe error detected:', e);
        showFallback();
    }
}, true);

// Detect jika request ke Google Maps diblokir
if (typeof window.google === 'undefined') {
    // Jika Google Maps API tidak tersedia, langsung show fallback
    document.addEventListener('DOMContentLoaded', function() {
        // Delay untuk memastikan elements sudah ready
        setTimeout(() => {
            const iframe = document.getElementById('google-maps-iframe');
            if (iframe && iframe.style.display !== 'block') {
                showFallback();
            }
        }, 1000);
    });
}
</script>

<?php include 'includes/user-footer.php'; ?>
