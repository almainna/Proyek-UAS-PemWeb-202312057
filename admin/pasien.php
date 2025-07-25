<?php
require_once '../config/init.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$db = new Database();
$message = '';
$messageType = '';

// Handle form submission
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'add_patient':
                $nama = trim($_POST['nama']);
                $email = trim($_POST['email']);
                $password = $_POST['password'];
                $alamat = trim($_POST['alamat']);
                $no_hp = trim($_POST['no_hp']);
                $tanggal_lahir = $_POST['tanggal_lahir'];
                $jenis_kelamin = $_POST['jenis_kelamin'];
                
                // Validate input
                if (empty($nama) || empty($email) || empty($password) || empty($alamat) || empty($no_hp) || empty($tanggal_lahir) || empty($jenis_kelamin)) {
                    throw new Exception('Semua field wajib diisi');
                }
                
                // Check if email already exists
                $db->query("SELECT id FROM users WHERE email = :email");
                $db->bind(':email', $email);
                if ($db->single()) {
                    throw new Exception('Email sudah digunakan');
                }
                
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Begin transaction
                $db->beginTransaction();
                
                // Insert user
                $db->query("INSERT INTO users (nama, email, password, role) VALUES (:nama, :email, :password, 'pasien')");
                $db->bind(':nama', $nama);
                $db->bind(':email', $email);
                $db->bind(':password', $hashedPassword);
                $db->execute();
                
                $userId = $db->lastInsertId();
                
                // Insert patient
                $db->query("INSERT INTO pasien (user_id, alamat, no_hp, tanggal_lahir, jenis_kelamin) VALUES (:user_id, :alamat, :no_hp, :tanggal_lahir, :jenis_kelamin)");
                $db->bind(':user_id', $userId);
                $db->bind(':alamat', $alamat);
                $db->bind(':no_hp', $no_hp);
                $db->bind(':tanggal_lahir', $tanggal_lahir);
                $db->bind(':jenis_kelamin', $jenis_kelamin);
                $db->execute();
                
                $db->commit();
                
                $message = 'Pasien berhasil ditambahkan';
                $messageType = 'success';
                break;
                
            case 'update_patient':
                $patientId = $_POST['patient_id'];
                $nama = trim($_POST['nama']);
                $email = trim($_POST['email']);
                $alamat = trim($_POST['alamat']);
                $no_hp = trim($_POST['no_hp']);
                $tanggal_lahir = $_POST['tanggal_lahir'];
                $jenis_kelamin = $_POST['jenis_kelamin'];
                
                if (empty($nama) || empty($email) || empty($alamat) || empty($no_hp) || empty($tanggal_lahir) || empty($jenis_kelamin)) {
                    throw new Exception('Semua field wajib diisi');
                }
                
                // Get user_id for this patient
                $db->query("SELECT user_id FROM pasien WHERE id = :id");
                $db->bind(':id', $patientId);
                $patient = $db->single();
                
                if (!$patient) {
                    throw new Exception('Pasien tidak ditemukan');
                }
                
                // Check if email is used by another user
                $db->query("SELECT id FROM users WHERE email = :email AND id != :user_id");
                $db->bind(':email', $email);
                $db->bind(':user_id', $patient['user_id']);
                if ($db->single()) {
                    throw new Exception('Email sudah digunakan');
                }
                
                $db->beginTransaction();
                
                // Update user
                $db->query("UPDATE users SET nama = :nama, email = :email WHERE id = :id");
                $db->bind(':nama', $nama);
                $db->bind(':email', $email);
                $db->bind(':id', $patient['user_id']);
                $db->execute();
                
                // Update patient
                $db->query("UPDATE pasien SET alamat = :alamat, no_hp = :no_hp, tanggal_lahir = :tanggal_lahir, jenis_kelamin = :jenis_kelamin WHERE id = :id");
                $db->bind(':alamat', $alamat);
                $db->bind(':no_hp', $no_hp);
                $db->bind(':tanggal_lahir', $tanggal_lahir);
                $db->bind(':jenis_kelamin', $jenis_kelamin);
                $db->bind(':id', $patientId);
                $db->execute();
                
                $db->commit();
                
                $message = 'Data pasien berhasil diperbarui';
                $messageType = 'success';
                break;
                
            case 'delete_patient':
                $patientId = $_POST['patient_id'];
                
                // Get user_id for this patient
                $db->query("SELECT user_id FROM pasien WHERE id = :id");
                $db->bind(':id', $patientId);
                $patient = $db->single();
                
                if (!$patient) {
                    throw new Exception('Pasien tidak ditemukan');
                }
                
                // Check if patient has any bookings
                $db->query("SELECT COUNT(*) as count FROM booking WHERE pasien_id = :pasien_id");
                $db->bind(':pasien_id', $patientId);
                $bookingCount = $db->single()['count'];
                
                // Check if patient has any transactions
                $db->query("SELECT COUNT(*) as count FROM transaksi WHERE pasien_id = :pasien_id");
                $db->bind(':pasien_id', $patientId);
                $transactionCount = $db->single()['count'];
                
                if ($bookingCount > 0 || $transactionCount > 0) {
                    throw new Exception('Tidak dapat menghapus pasien yang memiliki riwayat booking atau transaksi');
                }
                
                $db->beginTransaction();
                
                // Delete patient (will cascade to user due to foreign key)
                $db->query("DELETE FROM users WHERE id = :id");
                $db->bind(':id', $patient['user_id']);
                $db->execute();
                
                $db->commit();
                
                $message = 'Pasien berhasil dihapus';
                $messageType = 'success';
                break;
        }
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $message = $e->getMessage();
        $messageType = 'danger';
    }
}

// Get all patients with their user information and statistics
$db->query("
    SELECT p.*, u.nama, u.email,
           COUNT(DISTINCT b.id) as total_booking,
           COUNT(DISTINCT t.id) as total_transaksi
    FROM pasien p 
    JOIN users u ON p.user_id = u.id 
    LEFT JOIN booking b ON p.id = b.pasien_id
    LEFT JOIN transaksi t ON p.id = t.pasien_id
    GROUP BY p.id
    ORDER BY u.nama
");
$patients = $db->resultset();

include '../includes/admin-header.php';
?>

<!-- <div class="container-fluid">
    <div class="row"> -->

        <!-- Main content -->
        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-user-injured me-2"></i>Kelola Pasien</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPatientModal">
                    <i class="fas fa-plus me-2"></i>Tambah Pasien
                </button>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Patients Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daftar Pasien</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>No. HP</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Umur</th>
                                    <th>Total Booking</th>
                                    <th>Total Transaksi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($patients)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-user-injured text-muted mb-2" style="font-size: 2rem;"></i>
                                            <p class="text-muted">Belum ada data pasien</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($patients as $patient): ?>
                                        <?php
                                        // Calculate age
                                        $birthDate = new DateTime($patient['tanggal_lahir']);
                                        $today = new DateTime();
                                        $age = $today->diff($birthDate)->y;
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                         style="width: 40px; height: 40px;">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <div>
                                                        <strong><?= htmlspecialchars($patient['nama']) ?></strong>
                                                        <br><small class="text-muted"><?= htmlspecialchars($patient['alamat']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($patient['email']) ?></td>
                                            <td><?= htmlspecialchars($patient['no_hp']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $patient['jenis_kelamin'] == 'L' ? 'primary' : 'pink' ?>">
                                                    <?= $patient['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?>
                                                </span>
                                            </td>
                                            <td><?= $age ?> tahun</td>
                                            <td>
                                                <span class="badge bg-secondary"><?= $patient['total_booking'] ?> booking</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success"><?= $patient['total_transaksi'] ?> transaksi</span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="editPatient(<?= $patient['id'] ?>, '<?= htmlspecialchars($patient['nama']) ?>', '<?= htmlspecialchars($patient['email']) ?>', '<?= htmlspecialchars($patient['alamat']) ?>', '<?= htmlspecialchars($patient['no_hp']) ?>', '<?= $patient['tanggal_lahir'] ?>', '<?= $patient['jenis_kelamin'] ?>')">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <?php if ($patient['total_booking'] == 0 && $patient['total_transaksi'] == 0): ?>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            onclick="deletePatient(<?= $patient['id'] ?>, '<?= htmlspecialchars($patient['nama']) ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Add Patient Modal -->
<div class="modal fade" id="addPatientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pasien Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_patient">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama" name="nama" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" required minlength="6">
                                <small class="form-text text-muted">Minimal 6 karakter</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="no_hp" class="form-label">No. HP <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="no_hp" name="no_hp" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tanggal_lahir" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="jenis_kelamin" class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambah Pasien</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Patient Modal -->
<div class="modal fade" id="editPatientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Data Pasien</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_patient">
                    <input type="hidden" name="patient_id" id="edit_patient_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_nama" name="nama" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="edit_email" name="email" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_alamat" class="form-label">Alamat <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit_alamat" name="alamat" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_no_hp" class="form-label">No. HP <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="edit_no_hp" name="no_hp" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_tanggal_lahir" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_tanggal_lahir" name="tanggal_lahir" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_jenis_kelamin" class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_jenis_kelamin" name="jenis_kelamin" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Forms -->
<form id="deletePatientForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete_patient">
    <input type="hidden" name="patient_id" id="delete_patient_id">
</form>

<script>
function editPatient(id, nama, email, alamat, no_hp, tanggal_lahir, jenis_kelamin) {
    document.getElementById('edit_patient_id').value = id;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_alamat').value = alamat;
    document.getElementById('edit_no_hp').value = no_hp;
    document.getElementById('edit_tanggal_lahir').value = tanggal_lahir;
    document.getElementById('edit_jenis_kelamin').value = jenis_kelamin;
    
    new bootstrap.Modal(document.getElementById('editPatientModal')).show();
}

function deletePatient(id, nama) {
    if (confirm('Apakah Anda yakin ingin menghapus pasien "' + nama + '"?\n\nPeringatan: Pasien ini tidak akan dapat dihapus jika memiliki riwayat booking atau transaksi.')) {
        document.getElementById('delete_patient_id').value = id;
        document.getElementById('deletePatientForm').submit();
    }
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const tanggalLahir = form.querySelector('[name="tanggal_lahir"]');
            
            if (tanggalLahir && tanggalLahir.value) {
                const birthDate = new Date(tanggalLahir.value);
                const today = new Date();
                const age = today.getFullYear() - birthDate.getFullYear();
                
                if (age > 120) {
                    e.preventDefault();
                    alert('Tanggal lahir tidak valid');
                    return false;
                }
                
                if (birthDate > today) {
                    e.preventDefault();
                    alert('Tanggal lahir tidak boleh di masa depan');
                    return false;
                }
            }
        });
    });
});

// Add custom CSS for pink badge
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        .bg-pink {
            background-color: #e83e8c !important;
        }
    `;
    document.head.appendChild(style);
});
</script>

<?php include '../includes/admin-footer.php'; ?>
