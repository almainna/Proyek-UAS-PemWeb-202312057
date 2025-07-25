<?php
require_once 'config/init.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $role = $_SESSION['role'];
    switch ($role) {
        case 'admin':
            redirect('admin/');
            break;
        case 'dokter':
            redirect('dokter/');
            break;
        case 'pasien':
            redirect('pasien/');
            break;
        default:
            redirect('');
    }
}

// Debug POST data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    error_log('POST data received: ' . print_r($_POST, true));
    
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    error_log('Processing login for email: ' . $email);
    
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Email dan password harus diisi!';
        error_log('Login failed: Empty email or password');
    } else {
        try {
            $db = new Database();
            $db->query("SELECT * FROM users WHERE email = :email");
            $db->bind(':email', $email);
            $user = $db->single();
            
            error_log('User found: ' . ($user ? 'Yes' : 'No'));
            
            if ($user && password_verify($password, $user['password'])) {
                error_log('Password verification successful');
                
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                // Success message
                $_SESSION['success'] = 'Login berhasil! Selamat datang, ' . $user['nama'];
                
                error_log('Redirecting user with role: ' . $user['role']);
                
                // Redirect based on role
                switch ($user['role']) {
                    case 'admin':
                        error_log('Redirecting to admin dashboard');
                        redirect('admin/');
                        break;
                    case 'dokter':
                        error_log('Redirecting to dokter dashboard');
                        redirect('dokter/');
                        break;
                    case 'pasien':
                        error_log('Redirecting to pasien dashboard');
                        redirect('pasien/');
                        break;
                    default:
                        error_log('Redirecting to home');
                        redirect('');
                }
            } else {
                $_SESSION['error'] = 'Email atau password salah!';
                error_log('Login failed: Invalid credentials');
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Terjadi kesalahan sistem. Silakan coba lagi.';
            error_log('Login error: ' . $e->getMessage());
        }
    }
} else {
    error_log('GET request - showing login form');
}

$title = "Login";
include 'includes/user-header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow">
            <div class="card-header text-center">
                <h4><i class="fas fa-sign-in-alt me-2"></i>Login</h4>
            </div>
            <div class="card-body">
                <form method="POST" id="loginForm" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </div>
                </form>
                
                <hr>
                
                <div class="text-center">
                    <p class="mb-0">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
                </div>
                
                <div class="mt-3">
                    <small class="text-muted">
                        <strong>Demo Account:</strong><br>
                        Admin: admin@klinik.com / password<br>
                        Dokter: ahmad@klinik.com / password<br>
                        Pasien: Daftar akun baru
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Debug logs
console.log('JavaScript file loaded');

// Test alert to see if JS is working
alert('JavaScript is working! Click OK to continue.');

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded');
    
    const loginForm = document.getElementById('loginForm');
    console.log('Login form element:', loginForm);
    
    if (loginForm) {
        console.log('Form found, adding event listener');
        
        // Handle button click and manually submit form
        const submitBtn = loginForm.querySelector('button[type="submit"]');
        if (submitBtn) {
            console.log('Adding click event to submit button');
            
            submitBtn.addEventListener('click', function(e) {
                console.log('=== SUBMIT BUTTON CLICKED ===');
                alert('Submit button clicked! Processing form...');
                
                // Prevent default button behavior
                e.preventDefault();
                
                // Get form elements
                const email = document.getElementById('email');
                const password = document.getElementById('password');
                
                console.log('Email value:', email.value);
                console.log('Password value:', password.value ? '***filled***' : 'empty');
                
                let isValid = true;
                
                // Remove previous validation classes
                email.classList.remove('is-invalid');
                password.classList.remove('is-invalid');
                
                // Validate email
                if (!email.value.trim()) {
                    console.log('Email validation failed');
                    email.classList.add('is-invalid');
                    isValid = false;
                }
                
                // Validate password
                if (!password.value.trim()) {
                    console.log('Password validation failed');
                    password.classList.add('is-invalid');
                    isValid = false;
                }
                
                console.log('Form valid:', isValid);
                
                // If validation fails, don't submit
                if (!isValid) {
                    console.log('Validation failed - not submitting form');
                    alert('Validation failed! Please fill all fields.');
                    return false;
                }
                
                // Show loading state
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
                console.log('Loading state set');
                
                // Manually submit the form
                console.log('Manually submitting form...');
                alert('Form will be submitted now!');
                loginForm.submit();
            });
        }
        
    } else {
        console.error('Login form not found!');
        alert('ERROR: Login form not found!');
    }
});

// Fallback event listener
window.addEventListener('load', function() {
    console.log('Window load event triggered');
});
</script>

<?php include 'includes/user-footer.php'; ?>