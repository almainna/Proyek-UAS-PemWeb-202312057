<?php
require_once 'config/init.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $alamat = $_POST['alamat'];
    $no_hp = $_POST['no_hp'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    
    $db = new Database();
    
    // Check if email already exists
    $db->query("SELECT id FROM users WHERE email = :email");
    $db->bind(':email', $email);
    $existing_user = $db->single();
    
    if ($existing_user) {
        $_SESSION['error'] = 'Email sudah terdaftar!';
    } else {
        try {
            // Insert user
            $db->query("INSERT INTO users (nama, email, password, role) VALUES (:nama, :email, :password, 'pasien')");
            $db->bind(':nama', $nama);
            $db->bind(':email', $email);
            $db->bind(':password', $password);
            $db->execute();
            
            $user_id = $db->lastInsertId();
            
            // Insert pasien data
            $db->query("INSERT INTO pasien (user_id, alamat, no_hp, tanggal_lahir, jenis_kelamin) VALUES (:user_id, :alamat, :no_hp, :tanggal_lahir, :jenis_kelamin)");
            $db->bind(':user_id', $user_id);
            $db->bind(':alamat', $alamat);
            $db->bind(':no_hp', $no_hp);
            $db->bind(':tanggal_lahir', $tanggal_lahir);
            $db->bind(':jenis_kelamin', $jenis_kelamin);
            $db->execute();
            
            $_SESSION['success'] = 'Registrasi berhasil! Silakan login.';
            redirect('login.php');
        } catch (Exception $e) {
            $_SESSION['error'] = 'Terjadi kesalahan saat registrasi!';
        }
    }
}

$title = "Register";
include 'includes/user-header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow">
            <div class="card-header text-center">
                <h4><i class="fas fa-user-plus me-2"></i>Registrasi Pasien</h4>
            </div>
            <div class="card-body">
                <form method="POST" id="registerForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama" name="nama" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required minlength="6">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="no_hp" class="form-label">No. HP</label>
                                <input type="tel" class="form-control" id="no_hp" name="no_hp" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                        <select class="form-control" id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>Daftar
                        </button>
                    </div>
                </form>
                
                <hr>
                
                <div class="text-center">
                    <p class="mb-0">Sudah punya akun? <a href="login.php">Login di sini</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Get form elements
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            const nama = document.getElementById('nama');
            const email = document.getElementById('email');
            const alamat = document.getElementById('alamat');
            const noHp = document.getElementById('no_hp');
            const tanggalLahir = document.getElementById('tanggal_lahir');
            const jenisKelamin = document.getElementById('jenis_kelamin');
            
            // Clear previous validation states
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            
            // Validate required fields
            const requiredFields = [nama, email, password, confirmPassword, alamat, noHp, tanggalLahir];
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                }
            });
            
            // Validate jenis kelamin
            if (!jenisKelamin.value) {
                jenisKelamin.classList.add('is-invalid');
                isValid = false;
            }
            
            // Validate email format
            if (email.value && !email.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                email.classList.add('is-invalid');
                isValid = false;
            }
            
            // Validate password match
            if (password.value !== confirmPassword.value) {
                confirmPassword.classList.add('is-invalid');
                isValid = false;
                
                // Show error message
                let errorMsg = document.querySelector('.password-error');
                if (!errorMsg) {
                    errorMsg = document.createElement('div');
                    errorMsg.className = 'password-error alert alert-danger mt-2';
                    confirmPassword.parentNode.appendChild(errorMsg);
                }
                errorMsg.textContent = 'Password dan konfirmasi password tidak sama!';
            } else {
                // Remove error message if passwords match
                const errorMsg = document.querySelector('.password-error');
                if (errorMsg) {
                    errorMsg.remove();
                }
            }
            
            // Validate password minimum length
            if (password.value && password.value.length < 6) {
                password.classList.add('is-invalid');
                isValid = false;
            }
            
            // Prevent submission if validation fails
            if (!isValid) {
                e.preventDefault();
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mendaftar...';
            }
        });
    }
});
</script>

<?php include 'includes/user-footer.php'; ?>