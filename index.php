<?php
require_once 'config/init.php';

$title = "Beranda";
include 'includes/user-header.php';

// Get data for homepage
$db = new Database();

// Get doctors
$db->query("SELECT d.*, u.nama FROM dokter d JOIN users u ON d.user_id = u.id LIMIT 3");
$doctors = $db->resultset();

// Get featured medicines
$db->query("SELECT * FROM obat WHERE stok > 0 ORDER BY id DESC LIMIT 6");
$medicines = $db->resultset();

// Get clinic info
$db->query("SELECT * FROM pengaturan");
$settings = $db->resultset();
$clinic_info = [];
foreach ($settings as $setting) {
    $clinic_info[$setting['nama_pengaturan']] = $setting['nilai'];
}
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="fade-in-up">Selamat Datang di <?php echo $clinic_info['nama_klinik'] ?? 'Klinik Alma Sehat'; ?></h1>
                <p class="fade-in-up">Melayani kesehatan Anda dengan sepenuh hati. Klinik terpercaya dengan dokter berpengalaman dan fasilitas modern.</p>
                <div class="fade-in-up">
                    <?php if (!isLoggedIn()): ?>
                    <a href="register.php" class="btn btn-light btn-lg me-3">
                        <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                    </a>
                    <?php endif; ?>
                    <a href="dokter.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-user-md me-2"></i>Lihat Dokter
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <svg width="400" height="300" viewBox="0 0 400 300" class="fade-in-up">
                    <!-- Hospital Building -->
                    <rect x="50" y="100" width="300" height="150" fill="#ffffff" stroke="#0d6efd" stroke-width="2"/>
                    <rect x="70" y="120" width="40" height="40" fill="#0d6efd"/>
                    <rect x="130" y="120" width="40" height="40" fill="#0d6efd"/>
                    <rect x="190" y="120" width="40" height="40" fill="#0d6efd"/>
                    <rect x="250" y="120" width="40" height="40" fill="#0d6efd"/>
                    <rect x="310" y="120" width="40" height="40" fill="#0d6efd"/>
                    
                    <!-- Cross Symbol -->
                    <rect x="180" y="50" width="40" height="10" fill="#dc3545"/>
                    <rect x="195" y="35" width="10" height="40" fill="#dc3545"/>
                    
                    <!-- Entrance -->
                    <rect x="170" y="200" width="60" height="50" fill="#198754"/>
                    
                    <!-- Text -->
                    <text x="200" y="280" text-anchor="middle" fill="#0d6efd" font-size="16" font-weight="bold">Klinik Alma Sehat</text>
                </svg>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="py-5">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <h2 class="mb-3">Layanan Kami</h2>
                <p class="text-muted">Berbagai layanan kesehatan terbaik untuk Anda dan keluarga</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <div class="mb-3">
                            <i class="fas fa-user-md fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title">Konsultasi Dokter</h5>
                        <p class="card-text">Konsultasi dengan dokter berpengalaman untuk berbagai keluhan kesehatan Anda.</p>
                        <a href="dokter.php" class="btn btn-primary">Lihat Dokter</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <div class="mb-3">
                            <i class="fas fa-pills fa-3x text-success"></i>
                        </div>
                        <h5 class="card-title">Apotek Online</h5>
                        <p class="card-text">Beli obat-obatan berkualitas dengan mudah melalui sistem online kami.</p>
                        <a href="obat.php" class="btn btn-success">Beli Obat</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <div class="mb-3">
                            <i class="fas fa-calendar-check fa-3x text-info"></i>
                        </div>
                        <h5 class="card-title">Booking Online</h5>
                        <p class="card-text">Reservasi jadwal konsultasi dengan mudah melalui sistem booking online.</p>
                        <?php if (isLoggedIn() && isPasien()): ?>
                        <a href="booking.php" class="btn btn-info">Booking Sekarang</a>
                        <?php else: ?>
                        <a href="login.php" class="btn btn-info">Login untuk Booking</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Doctors Section -->
<?php if (!empty($doctors)): ?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <h2 class="mb-3">Dokter Kami</h2>
                <p class="text-muted">Tim dokter berpengalaman dan profesional</p>
            </div>
        </div>
        
        <div class="row g-4">
            <?php foreach ($doctors as $doctor): ?>
            <div class="col-md-4">
                <div class="card doctor-card">
                    <?php if ($doctor['foto']): ?>
                    <img src="uploads/doctors/<?php echo $doctor['foto']; ?>" alt="<?php echo $doctor['nama']; ?>">
                    <?php else: ?>
                    <div class="bg-primary text-white d-flex align-items-center justify-content-center" style="width: 150px; height: 150px; border-radius: 50%; margin: 0 auto 1rem;">
                        <i class="fas fa-user-md fa-3x"></i>
                    </div>
                    <?php endif; ?>
                    <h5><?php echo $doctor['nama']; ?></h5>
                    <p class="text-muted"><?php echo $doctor['spesialis']; ?></p>
                    <a href="dokter.php" class="btn btn-outline-primary">Lihat Jadwal</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="dokter.php" class="btn btn-primary">Lihat Semua Dokter</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Featured Medicines -->
<?php if (!empty($medicines)): ?>
<section class="py-5">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <h2 class="mb-3">Obat Terpopuler</h2>
                <p class="text-muted">Obat-obatan berkualitas dengan harga terjangkau</p>
            </div>
        </div>
        
        <div class="row g-4">
            <?php foreach ($medicines as $medicine): ?>
            <div class="col-md-4">
                <div class="card medicine-card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $medicine['nama_obat']; ?></h5>
                        <p class="card-text text-muted"><?php echo $medicine['jenis']; ?></p>
                        <p class="card-text"><?php echo substr($medicine['deskripsi'], 0, 100); ?>...</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h5 text-primary mb-0"><?php echo formatRupiah($medicine['harga']); ?></span>
                            <small class="text-muted">Stok: <?php echo $medicine['stok']; ?></small>
                        </div>
                        <?php if (isLoggedIn() && isPasien()): ?>
                        <button class="btn btn-success mt-2 w-100" onclick="addToCart(<?php echo $medicine['id']; ?>, '<?php echo $medicine['nama_obat']; ?>', <?php echo $medicine['harga']; ?>)">
                            <i class="fas fa-cart-plus me-2"></i>Tambah ke Keranjang
                        </button>
                        <?php else: ?>
                        <a href="login.php" class="btn btn-success mt-2 w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>Login untuk Beli
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="obat.php" class="btn btn-success">Lihat Semua Obat</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Contact Info -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3">
                <div class="mb-3">
                    <i class="fas fa-map-marker-alt fa-2x"></i>
                </div>
                <h5>Alamat</h5>
                <p><?php echo $clinic_info['alamat_klinik'] ?? 'Jl. Kesehatan No. 123, Jakarta'; ?></p>
            </div>
            
            <div class="col-md-3">
                <div class="mb-3">
                    <i class="fas fa-phone fa-2x"></i>
                </div>
                <h5>Telepon</h5>
                <p><?php echo $clinic_info['telepon_klinik'] ?? '021-12345678'; ?></p>
            </div>
            
            <div class="col-md-3">
                <div class="mb-3">
                    <i class="fas fa-envelope fa-2x"></i>
                </div>
                <h5>Email</h5>
                <p><?php echo $clinic_info['email_klinik'] ?? 'info@klinikalmasehat.com'; ?></p>
            </div>
            
            <div class="col-md-3">
                <div class="mb-3">
                    <i class="fas fa-clock fa-2x"></i>
                </div>
                <h5>Jam Buka</h5>
                <p><?php echo $clinic_info['jam_buka'] ?? '08:00 - 20:00'; ?></p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/user-footer.php'; ?>