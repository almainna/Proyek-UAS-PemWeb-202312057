<?php
require_once '../config/init.php';

// Check if user is logged in and is a patient
if (!isLoggedIn() || !isPasien()) {
    redirect('../login.php');
}

$title = "Dashboard Pasien";
include '../includes/user-header.php';

$db = new Database();

// Simplified patient data - use session data directly
$patient = [
    'nama' => $_SESSION['nama'],
    'email' => $_SESSION['email'],
    'id' => $_SESSION['user_id']
];

// Set default statistics (will be enhanced when tables are ready)
$total_booking = 0;
$booking_pending = 0;
$total_transaksi = 0;
$total_pembelian = 0;

// Try to get statistics if tables exist
try {
    // This will be enhanced when booking and transaksi tables are implemented
    $recent_bookings = [];
    $recent_transactions = [];
} catch (Exception $e) {
    // Tables don't exist yet, use defaults
    $recent_bookings = [];
    $recent_transactions = [];
}
?>

<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-user me-2"></i>Dashboard Pasien</h2>
        <p class="text-muted">Selamat datang, <?php echo $patient['nama']; ?>!</p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo $total_booking; ?></h4>
                        <p class="mb-0">Total Booking</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-calendar-check fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo $booking_pending; ?></h4>
                        <p class="mb-0">Booking Pending</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo $total_transaksi; ?></h4>
                        <p class="mb-0">Total Transaksi</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-shopping-cart fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo formatRupiah($total_pembelian); ?></h4>
                        <p class="mb-0">Total Pembelian</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-money-bill-wave fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-bolt me-2"></i>Aksi Cepat</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="../booking.php" class="btn btn-primary w-100">
                            <i class="fas fa-calendar-plus d-block mb-2"></i>
                            Booking Konsultasi
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="../obat.php" class="btn btn-success w-100">
                            <i class="fas fa-pills d-block mb-2"></i>
                            Beli Obat
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="booking.php" class="btn btn-info w-100">
                            <i class="fas fa-history d-block mb-2"></i>
                            Riwayat Booking
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="transaksi.php" class="btn btn-secondary w-100">
                            <i class="fas fa-receipt d-block mb-2"></i>
                            Riwayat Transaksi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Bookings -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-calendar-check me-2"></i>Booking Terbaru</h5>
                <a href="booking.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body">
                <?php if (empty($recent_bookings)): ?>
                <div class="text-center text-muted py-3">
                    <i class="fas fa-calendar-times fa-2x mb-2"></i>
                    <p>Belum ada booking.</p>
                    <a href="../booking.php" class="btn btn-primary btn-sm">Booking Sekarang</a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Dokter</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_bookings as $booking): ?>
                            <tr>
                                <td>
                                    <strong><?php echo $booking['nama_dokter']; ?></strong><br>
                                    <small class="text-muted"><?php echo $booking['spesialis']; ?></small>
                                </td>
                                <td>
                                    <?php echo formatTanggal($booking['tanggal_kunjungan']); ?><br>
                                    <small class="text-muted"><?php echo formatWaktu($booking['jam_kunjungan']); ?></small>
                                </td>
                                <td>
                                    <span class="badge status-<?php echo $booking['status']; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Transactions -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-shopping-cart me-2"></i>Transaksi Terbaru</h5>
                <a href="transaksi.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
            </div>
            <div class="card-body">
                <?php if (empty($recent_transactions)): ?>
                <div class="text-center text-muted py-3">
                    <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                    <p>Belum ada transaksi.</p>
                    <a href="../obat.php" class="btn btn-success btn-sm">Beli Obat</a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_transactions as $transaction): ?>
                            <tr>
                                <td><?php echo formatTanggal($transaction['tanggal']); ?></td>
                                <td><?php echo formatRupiah($transaction['total_harga']); ?></td>
                                <td>
                                    <span class="badge status-<?php echo $transaction['status']; ?>">
                                        <?php echo ucfirst($transaction['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Profile Card -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-user me-2"></i>Profil Saya</h5>
                <a href="profil.php" class="btn btn-sm btn-outline-primary">Edit Profil</a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Nama:</strong></td>
                                <td><?php echo $patient['nama']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td><?php echo $patient['email']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>No. HP:</strong></td>
                                <td><?php echo $patient['no_hp'] ?: '-'; ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Tanggal Lahir:</strong></td>
                                <td><?php echo $patient['tanggal_lahir'] ? formatTanggal($patient['tanggal_lahir']) : '-'; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Jenis Kelamin:</strong></td>
                                <td><?php echo $patient['jenis_kelamin'] == 'L' ? 'Laki-laki' : ($patient['jenis_kelamin'] == 'P' ? 'Perempuan' : '-'); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Alamat:</strong></td>
                                <td><?php echo $patient['alamat'] ?: '-'; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/user-footer.php'; ?>