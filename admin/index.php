<?php
require_once '../config/init.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    $_SESSION['error'] = 'Anda harus login sebagai admin untuk mengakses halaman ini!';
    redirect('../login.php');
}

$title = "Dashboard Admin";
// Use user-header instead of admin-header for now
include '../includes/user-header.php';

$db = new Database();

// Get basic statistics
$db->query("SELECT COUNT(*) as total FROM users WHERE role = 'pasien'");
$total_pasien = $db->single()['total'] ?? 0;

$db->query("SELECT COUNT(*) as total FROM users WHERE role = 'dokter'");
$total_dokter = $db->single()['total'] ?? 0;

$db->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
$total_admin = $db->single()['total'] ?? 0;

$db->query("SELECT COUNT(*) as total FROM users");
$total_users = $db->single()['total'] ?? 0;
?>

<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard Admin</h2>
        <p class="text-muted">Selamat datang, <?php echo $_SESSION['nama']; ?>!</p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo $total_pasien; ?></h4>
                        <p class="mb-0">Total Pasien</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
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
                        <h4><?php echo $total_dokter; ?></h4>
                        <p class="mb-0">Total Dokter</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-user-md fa-2x"></i>
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
                            <h4><?php echo $total_admin; ?></h4>
                            <p class="mb-0">Total Admin</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-shield fa-2x"></i>
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
                        <h4><?php echo $total_users; ?></h4>
                        <p class="mb-0">Total Users</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
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
                    <div class="col-md-2">
                        <a href="users.php" class="btn btn-primary w-100">
                            <i class="fas fa-users d-block mb-2"></i>
                            Kelola User
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="dokter.php" class="btn btn-success w-100">
                            <i class="fas fa-user-md d-block mb-2"></i>
                            Kelola Dokter
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="obat.php" class="btn btn-info w-100">
                            <i class="fas fa-pills d-block mb-2"></i>
                            Kelola Obat
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="booking.php" class="btn btn-warning w-100">
                            <i class="fas fa-calendar-check d-block mb-2"></i>
                            Kelola Booking
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="transaksi.php" class="btn btn-secondary w-100">
                            <i class="fas fa-shopping-cart d-block mb-2"></i>
                            Kelola Transaksi
                        </a>
                    </div>
                    <div class="col-md-2">
                        <a href="laporan.php" class="btn btn-dark w-100">
                            <i class="fas fa-chart-bar d-block mb-2"></i>
                            Laporan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-info-circle me-2"></i>Informasi Akun Admin</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Nama:</strong></td>
                        <td><?php echo $_SESSION['nama']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td><?php echo $_SESSION['email']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Role:</strong></td>
                        <td><span class="badge bg-danger"><?php echo ucfirst($_SESSION['role']); ?></span></td>
                    </tr>
                    <tr>
                        <td><strong>Session ID:</strong></td>
                        <td><?php echo session_id(); ?></td>
                    </tr>
                </table>
                
                <a href="../logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/user-footer.php'; ?>
