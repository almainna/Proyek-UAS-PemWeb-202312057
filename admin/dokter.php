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
            case 'add_doctor':
                $nama = trim($_POST['nama']);
                $email = trim($_POST['email']);
                $password = $_POST['password'];
                $spesialis = trim($_POST['spesialis']);
                $no_str = trim($_POST['no_str']);
                
                // Validate input
                if (empty($nama) || empty($email) || empty($password) || empty($spesialis)) {
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
                $db->query("INSERT INTO users (nama, email, password, role) VALUES (:nama, :email, :password, 'dokter')");
                $db->bind(':nama', $nama);
                $db->bind(':email', $email);
                $db->bind(':password', $hashedPassword);
                $db->execute();
                
                $userId = $db->lastInsertId();
                
                // Insert doctor
                $db->query("INSERT INTO dokter (user_id, spesialis, no_str) VALUES (:user_id, :spesialis, :no_str)");
                $db->bind(':user_id', $userId);
                $db->bind(':spesialis', $spesialis);
                $db->bind(':no_str', $no_str);
                $db->execute();
                
                $db->commit();
                
                $message = 'Dokter berhasil ditambahkan';
                $messageType = 'success';
                break;
                
            case 'update_doctor':
                $doctorId = $_POST['doctor_id'];
                $nama = trim($_POST['nama']);
                $email = trim($_POST['email']);
                $spesialis = trim($_POST['spesialis']);
                $no_str = trim($_POST['no_str']);
                
                if (empty($nama) || empty($email) || empty($spesialis)) {
                    throw new Exception('Nama, email, dan spesialis wajib diisi');
                }
                
                // Get user_id for this doctor
                $db->query("SELECT user_id FROM dokter WHERE id = :id");
                $db->bind(':id', $doctorId);
                $doctor = $db->single();
                
                if (!$doctor) {
                    throw new Exception('Dokter tidak ditemukan');
                }
                
                // Check if email is used by another user
                $db->query("SELECT id FROM users WHERE email = :email AND id != :user_id");
                $db->bind(':email', $email);
                $db->bind(':user_id', $doctor['user_id']);
                if ($db->single()) {
                    throw new Exception('Email sudah digunakan');
                }
                
                $db->beginTransaction();
                
                // Update user
                $db->query("UPDATE users SET nama = :nama, email = :email WHERE id = :id");
                $db->bind(':nama', $nama);
                $db->bind(':email', $email);
                $db->bind(':id', $doctor['user_id']);
                $db->execute();
                
                // Update doctor
                $db->query("UPDATE dokter SET spesialis = :spesialis, no_str = :no_str WHERE id = :id");
                $db->bind(':spesialis', $spesialis);
                $db->bind(':no_str', $no_str);
                $db->bind(':id', $doctorId);
                $db->execute();
                
                $db->commit();
                
                $message = 'Data dokter berhasil diperbarui';
                $messageType = 'success';
                break;
                
            case 'delete_doctor':
                $doctorId = $_POST['doctor_id'];
                
                // Get user_id for this doctor
                $db->query("SELECT user_id FROM dokter WHERE id = :id");
                $db->bind(':id', $doctorId);
                $doctor = $db->single();
                
                if (!$doctor) {
                    throw new Exception('Dokter tidak ditemukan');
                }
                
                // Check if doctor has any bookings
                $db->query("SELECT COUNT(*) as count FROM booking WHERE dokter_id = :dokter_id");
                $db->bind(':dokter_id', $doctorId);
                $bookingCount = $db->single()['count'];
                
                if ($bookingCount > 0) {
                    throw new Exception('Tidak dapat menghapus dokter yang memiliki riwayat booking');
                }
                
                $db->beginTransaction();
                
                // Delete doctor (will cascade to user due to foreign key)
                $db->query("DELETE FROM users WHERE id = :id");
                $db->bind(':id', $doctor['user_id']);
                $db->execute();
                
                $db->commit();
                
                $message = 'Dokter berhasil dihapus';
                $messageType = 'success';
                break;
                
            case 'add_schedule':
                $doctorId = $_POST['doctor_id'];
                $hari = $_POST['hari'];
                $jamMulai = $_POST['jam_mulai'];
                $jamSelesai = $_POST['jam_selesai'];
                
                if (empty($hari) || empty($jamMulai) || empty($jamSelesai)) {
                    throw new Exception('Semua field jadwal wajib diisi');
                }
                
                // Check if schedule already exists
                $db->query("SELECT id FROM jadwal_praktik WHERE dokter_id = :dokter_id AND hari = :hari");
                $db->bind(':dokter_id', $doctorId);
                $db->bind(':hari', $hari);
                if ($db->single()) {
                    throw new Exception('Jadwal untuk hari ' . $hari . ' sudah ada');
                }
                
                $db->query("INSERT INTO jadwal_praktik (dokter_id, hari, jam_mulai, jam_selesai) VALUES (:dokter_id, :hari, :jam_mulai, :jam_selesai)");
                $db->bind(':dokter_id', $doctorId);
                $db->bind(':hari', $hari);
                $db->bind(':jam_mulai', $jamMulai);
                $db->bind(':jam_selesai', $jamSelesai);
                $db->execute();
                
                $message = 'Jadwal praktik berhasil ditambahkan';
                $messageType = 'success';
                break;
                
            case 'delete_schedule':
                $scheduleId = $_POST['schedule_id'];
                
                $db->query("DELETE FROM jadwal_praktik WHERE id = :id");
                $db->bind(':id', $scheduleId);
                $db->execute();
                
                $message = 'Jadwal praktik berhasil dihapus';
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

// Get all doctors with their user information
$db->query("
    SELECT d.*, u.nama, u.email,
           GROUP_CONCAT(CONCAT(jp.id, ':', jp.hari, ' (', jp.jam_mulai, ' - ', jp.jam_selesai, ')') SEPARATOR '||') as jadwal_raw,
           COUNT(b.id) as total_booking
    FROM dokter d 
    JOIN users u ON d.user_id = u.id 
    LEFT JOIN jadwal_praktik jp ON d.id = jp.dokter_id
    LEFT JOIN booking b ON d.id = b.dokter_id
    GROUP BY d.id
    ORDER BY u.nama
");
$doctors = $db->resultset();

// Process schedules for each doctor
foreach ($doctors as &$doctor) {
    $doctor['jadwal'] = [];
    if ($doctor['jadwal_raw']) {
        $schedules = explode('||', $doctor['jadwal_raw']);
        foreach ($schedules as $schedule) {
            if ($schedule) {
                $parts = explode(':', $schedule, 2);
                $doctor['jadwal'][] = [
                    'id' => $parts[0],
                    'text' => $parts[1]
                ];
            }
        }
    }
}

include '../includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">

        <!-- Main content -->
        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-user-md me-2"></i>Kelola Dokter</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDoctorModal">
                    <i class="fas fa-plus me-2"></i>Tambah Dokter
                </button>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Doctors Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daftar Dokter</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Spesialis</th>
                                    <th>No. STR</th>
                                    <th>Jadwal Praktik</th>
                                    <th>Total Booking</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($doctors)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-user-md text-muted mb-2" style="font-size: 2rem;"></i>
                                            <p class="text-muted">Belum ada data dokter</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($doctors as $doctor): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                         style="width: 40px; height: 40px;">
                                                        <i class="fas fa-user-md"></i>
                                                    </div>
                                                    <strong><?= htmlspecialchars($doctor['nama']) ?></strong>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($doctor['email']) ?></td>
                                            <td>
                                                <span class="badge bg-info"><?= htmlspecialchars($doctor['spesialis']) ?></span>
                                            </td>
                                            <td><?= htmlspecialchars($doctor['no_str']) ?: '-' ?></td>
                                            <td>
                                                <?php if (!empty($doctor['jadwal'])): ?>
                                                    <?php foreach ($doctor['jadwal'] as $jadwal): ?>
                                                        <small class="d-block"><?= htmlspecialchars($jadwal['text']) ?></small>
                                                    <?php endforeach; ?>
                                                    <button class="btn btn-sm btn-outline-primary mt-1" 
                                                            onclick="showScheduleModal(<?= $doctor['id'] ?>)">
                                                        <i class="fas fa-plus fa-sm"></i> Tambah
                                                    </button>
                                                <?php else: ?>
                                                    <small class="text-muted">Belum ada jadwal</small><br>
                                                    <button class="btn btn-sm btn-outline-primary mt-1" 
                                                            onclick="showScheduleModal(<?= $doctor['id'] ?>)">
                                                        <i class="fas fa-plus fa-sm"></i> Tambah Jadwal
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?= $doctor['total_booking'] ?> booking</span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="editDoctor(<?= $doctor['id'] ?>, '<?= htmlspecialchars($doctor['nama']) ?>', '<?= htmlspecialchars($doctor['email']) ?>', '<?= htmlspecialchars($doctor['spesialis']) ?>', '<?= htmlspecialchars($doctor['no_str']) ?>')">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <?php if ($doctor['total_booking'] == 0): ?>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteDoctor(<?= $doctor['id'] ?>, '<?= htmlspecialchars($doctor['nama']) ?>')">
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

<!-- Add Doctor Modal -->
<div class="modal fade" id="addDoctorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Dokter Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_doctor">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="6">
                        <small class="form-text text-muted">Minimal 6 karakter</small>
                    </div>
                    <div class="mb-3">
                        <label for="spesialis" class="form-label">Spesialis <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="spesialis" name="spesialis" required>
                    </div>
                    <div class="mb-3">
                        <label for="no_str" class="form-label">Nomor STR</label>
                        <input type="text" class="form-control" id="no_str" name="no_str">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambah Dokter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Doctor Modal -->
<div class="modal fade" id="editDoctorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Data Dokter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_doctor">
                    <input type="hidden" name="doctor_id" id="edit_doctor_id">
                    <div class="mb-3">
                        <label for="edit_nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_nama" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_spesialis" class="form-label">Spesialis <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_spesialis" name="spesialis" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_no_str" class="form-label">Nomor STR</label>
                        <input type="text" class="form-control" id="edit_no_str" name="no_str">
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

<!-- Schedule Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Jadwal Praktik</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_schedule">
                    <input type="hidden" name="doctor_id" id="schedule_doctor_id">
                    <div class="mb-3">
                        <label for="hari" class="form-label">Hari <span class="text-danger">*</span></label>
                        <select class="form-select" id="hari" name="hari" required>
                            <option value="">Pilih Hari</option>
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                            <option value="Sabtu">Sabtu</option>
                            <option value="Minggu">Minggu</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="jam_mulai" class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="jam_selesai" class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambah Jadwal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Forms -->
<form id="deleteDoctorForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete_doctor">
    <input type="hidden" name="doctor_id" id="delete_doctor_id">
</form>

<form id="deleteScheduleForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete_schedule">
    <input type="hidden" name="schedule_id" id="delete_schedule_id">
</form>

<script>
function editDoctor(id, nama, email, spesialis, no_str) {
    document.getElementById('edit_doctor_id').value = id;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_spesialis').value = spesialis;
    document.getElementById('edit_no_str').value = no_str;
    
    new bootstrap.Modal(document.getElementById('editDoctorModal')).show();
}

function deleteDoctor(id, nama) {
    if (confirm('Apakah Anda yakin ingin menghapus dokter "' + nama + '"?')) {
        document.getElementById('delete_doctor_id').value = id;
        document.getElementById('deleteDoctorForm').submit();
    }
}

function showScheduleModal(doctorId) {
    document.getElementById('schedule_doctor_id').value = doctorId;
    new bootstrap.Modal(document.getElementById('scheduleModal')).show();
}

function deleteSchedule(scheduleId) {
    if (confirm('Apakah Anda yakin ingin menghapus jadwal ini?')) {
        document.getElementById('delete_schedule_id').value = scheduleId;
        document.getElementById('deleteScheduleForm').submit();
    }
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const jamMulai = form.querySelector('[name="jam_mulai"]');
            const jamSelesai = form.querySelector('[name="jam_selesai"]');
            
            if (jamMulai && jamSelesai && jamMulai.value && jamSelesai.value) {
                if (jamMulai.value >= jamSelesai.value) {
                    e.preventDefault();
                    alert('Jam mulai harus lebih kecil dari jam selesai');
                    return false;
                }
            }
        });
    });
});
</script>

<?php include '../includes/admin-footer.php'; ?>
