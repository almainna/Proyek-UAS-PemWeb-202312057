<?php
require_once '../config/init.php';

// Cek apakah user sudah login dan merupakan pasien
if (!isLoggedIn() || !isPasien()) {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Ambil data user dan pasien
    $stmt = $conn->prepare("
        SELECT u.*, p.* 
        FROM users u 
        JOIN pasien p ON u.id = p.user_id 
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user_data) {
        redirect('../login.php');
    }
    
    // Proses form jika ada submit
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nama = trim($_POST['nama']);
        $email = trim($_POST['email']);
        $alamat = trim($_POST['alamat']);
        $no_hp = trim($_POST['no_hp']);
        $tanggal_lahir = $_POST['tanggal_lahir'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $password_baru = trim($_POST['password_baru']);
        $konfirmasi_password = trim($_POST['konfirmasi_password']);
        
        // Validasi
        if (empty($nama) || empty($email) || empty($alamat) || empty($no_hp) || empty($tanggal_lahir) || empty($jenis_kelamin)) {
            $error = "Semua field wajib diisi!";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Format email tidak valid!";
        } elseif (!empty($password_baru) && $password_baru !== $konfirmasi_password) {
            $error = "Konfirmasi password tidak cocok!";
        } elseif (!empty($password_baru) && strlen($password_baru) < 6) {
            $error = "Password minimal 6 karakter!";
        } else {
            // Cek apakah email sudah digunakan user lain
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user_id]);
            if ($stmt->fetch()) {
                $error = "Email sudah digunakan!";
            } else {
                try {
                    $conn->beginTransaction();
                    
                    // Update data user
                    if (!empty($password_baru)) {
                        $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("
                            UPDATE users 
                            SET nama = ?, email = ?, password = ? 
                            WHERE id = ?
                        ");
                        $stmt->execute([$nama, $email, $hashed_password, $user_id]);
                    } else {
                        $stmt = $conn->prepare("
                            UPDATE users 
                            SET nama = ?, email = ? 
                            WHERE id = ?
                        ");
                        $stmt->execute([$nama, $email, $user_id]);
                    }
                    
                    // Update data pasien
                    $stmt = $conn->prepare("
                        UPDATE pasien 
                        SET alamat = ?, no_hp = ?, tanggal_lahir = ?, jenis_kelamin = ? 
                        WHERE user_id = ?
                    ");
                    $stmt->execute([$alamat, $no_hp, $tanggal_lahir, $jenis_kelamin, $user_id]);
                    
                    $conn->commit();
                    
                    // Update session
                    $_SESSION['user_nama'] = $nama;
                    $_SESSION['user_email'] = $email;
                    
                    $success = "Profil berhasil diperbarui!";
                    
                    // Refresh data
                    $stmt = $conn->prepare("
                        SELECT u.*, p.* 
                        FROM users u 
                        JOIN pasien p ON u.id = p.user_id 
                        WHERE u.id = ?
                    ");
                    $stmt->execute([$user_id]);
                    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                } catch (Exception $e) {
                    $conn->rollBack();
                    $error = "Terjadi kesalahan: " . $e->getMessage();
                }
            }
        }
    }
    
} catch (Exception $e) {
    $error = "Terjadi kesalahan: " . $e->getMessage();
}

$page_title = "Edit Profil";
include '../includes/user-header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-user-edit me-2"></i>Edit Profil</h2>
                <a href="index.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?= $success ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i><?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informasi Profil</h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="profileForm">
                        <div class="row">
                            <!-- Data Akun -->
                            <div class="col-12">
                                <h6 class="text-primary mb-3"><i class="fas fa-user-circle me-2"></i>Data Akun</h6>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama" name="nama" 
                                       value="<?= htmlspecialchars($user_data['nama']) ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($user_data['email']) ?>" required>
                            </div>
                            
                            <!-- Data Pribadi -->
                            <div class="col-12 mt-3">
                                <h6 class="text-primary mb-3"><i class="fas fa-id-card me-2"></i>Data Pribadi</h6>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="alamat" class="form-label">Alamat <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?= htmlspecialchars($user_data['alamat']) ?></textarea>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="no_hp" class="form-label">Nomor HP <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="no_hp" name="no_hp" 
                                       value="<?= htmlspecialchars($user_data['no_hp']) ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_lahir" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" 
                                       value="<?= $user_data['tanggal_lahir'] ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="jenis_kelamin" class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="L" <?= $user_data['jenis_kelamin'] == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                    <option value="P" <?= $user_data['jenis_kelamin'] == 'P' ? 'selected' : '' ?>>Perempuan</option>
                                </select>
                            </div>
                            
                            <!-- Ubah Password -->
                            <div class="col-12 mt-3">
                                <h6 class="text-primary mb-3"><i class="fas fa-lock me-2"></i>Ubah Password (Opsional)</h6>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Kosongkan jika tidak ingin mengubah password
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="password_baru" class="form-label">Password Baru</label>
                                <input type="password" class="form-control" id="password_baru" name="password_baru" 
                                       minlength="6" placeholder="Minimal 6 karakter">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="konfirmasi_password" class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" 
                                       placeholder="Ulangi password baru">
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const passwordBaru = document.getElementById('password_baru').value;
    const konfirmasiPassword = document.getElementById('konfirmasi_password').value;
    
    if (passwordBaru && passwordBaru !== konfirmasiPassword) {
        e.preventDefault();
        showAlert('danger', 'Konfirmasi password tidak cocok!');
        return false;
    }
    
    if (passwordBaru && passwordBaru.length < 6) {
        e.preventDefault();
        showAlert('danger', 'Password minimal 6 karakter!');
        return false;
    }
});

// Real-time password validation
document.getElementById('konfirmasi_password').addEventListener('input', function() {
    const passwordBaru = document.getElementById('password_baru').value;
    const konfirmasiPassword = this.value;
    
    if (passwordBaru && konfirmasiPassword) {
        if (passwordBaru === konfirmasiPassword) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
        }
    } else {
        this.classList.remove('is-valid', 'is-invalid');
    }
});
</script>

<?php include '../includes/user-footer.php'; ?>