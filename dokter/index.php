<?php
require_once '../config/init.php';

// Check if user is logged in and is dokter
if (!isLoggedIn() || !isDokter()) {
    $_SESSION['error'] = 'Anda harus login sebagai dokter untuk mengakses halaman ini!';
    redirect('../login.php');
}

$title = "Dashboard Dokter";
include '../includes/user-header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="alert alert-success">
            <h4><i class="fas fa-stethoscope me-2"></i>Selamat Datang, Dr. <?php echo $_SESSION['nama']; ?>!</h4>
            <p>Anda berhasil login sebagai <strong>Dokter</strong>. Dashboard dokter masih dalam pengembangan.</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-calendar-alt fa-3x text-primary mb-3"></i>
                <h5>Jadwal Praktek</h5>
                <p class="text-muted">Kelola jadwal praktek Anda</p>
                <button class="btn btn-primary" disabled>Coming Soon</button>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-users fa-3x text-success mb-3"></i>
                <h5>Pasien</h5>
                <p class="text-muted">Daftar pasien dan riwayat</p>
                <button class="btn btn-success" disabled>Coming Soon</button>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-chart-bar fa-3x text-info mb-3"></i>
                <h5>Statistik</h5>
                <p class="text-muted">Statistik praktek</p>
                <button class="btn btn-info" disabled>Coming Soon</button>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-info-circle me-2"></i>Informasi Akun</h5>
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
                        <td><span class="badge bg-success"><?php echo ucfirst($_SESSION['role']); ?></span></td>
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
