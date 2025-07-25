<?php
require_once 'config/init.php';

// Check if user is logged in and is a patient
if (!isLoggedIn() || !isPasien()) {
    redirect('login.php');
}

$title = "Booking Konsultasi";
include 'includes/user-header.php';

$db = new Database();

// Try to get doctors, create sample data if table doesn't exist
try {
    $db->query("
        SELECT d.*, u.nama 
        FROM dokter d 
        JOIN users u ON d.user_id = u.id 
        ORDER BY u.nama
    ");
    $doctors = $db->resultset();
} catch (Exception $e) {
    // If dokter table doesn't exist, use sample data from users table
    $db->query("SELECT id, nama, 'Umum' as spesialis FROM users WHERE role = 'dokter'");
    $doctors = $db->resultset();
    
    // Add spesialis field for compatibility
    foreach ($doctors as &$doctor) {
        if (!isset($doctor['spesialis'])) {
            $doctor['spesialis'] = 'Umum';
        }
    }
}

// Get selected doctor if any
$selected_doctor_id = isset($_GET['dokter_id']) ? $_GET['dokter_id'] : '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dokter_id = $_POST['dokter_id'];
    $tanggal_kunjungan = $_POST['tanggal_kunjungan'];
    $jam_kunjungan = $_POST['jam_kunjungan'];
    $keluhan = $_POST['keluhan'];
    
    // Get patient ID
    $db->query("SELECT id FROM pasien WHERE user_id = :user_id");
    $db->bind(':user_id', $_SESSION['user_id']);
    $pasien = $db->single();
    
    if ($pasien) {
        try {
            // Check if the time slot is available
            $db->query("SELECT id FROM booking WHERE dokter_id = :dokter_id AND tanggal_kunjungan = :tanggal AND jam_kunjungan = :jam AND status != 'dibatalkan'");
            $db->bind(':dokter_id', $dokter_id);
            $db->bind(':tanggal', $tanggal_kunjungan);
            $db->bind(':jam', $jam_kunjungan);
            $existing_booking = $db->single();
            
            if ($existing_booking) {
                $_SESSION['error'] = 'Jadwal tersebut sudah dibooking oleh pasien lain!';
            } else {
                // Insert booking
                $db->query("INSERT INTO booking (pasien_id, dokter_id, tanggal_kunjungan, jam_kunjungan, keluhan, status) VALUES (:pasien_id, :dokter_id, :tanggal_kunjungan, :jam_kunjungan, :keluhan, 'pending')");
                $db->bind(':pasien_id', $pasien['id']);
                $db->bind(':dokter_id', $dokter_id);
                $db->bind(':tanggal_kunjungan', $tanggal_kunjungan);
                $db->bind(':jam_kunjungan', $jam_kunjungan);
                $db->bind(':keluhan', $keluhan);
                $db->execute();
                
                $_SESSION['success'] = 'Booking berhasil! Silakan datang sesuai jadwal yang dipilih.';
                redirect('pasien/booking.php');
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Terjadi kesalahan saat melakukan booking!';
        }
    }
}
?>

<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-calendar-check me-2"></i>Booking Konsultasi</h2>
        <p class="text-muted">Pilih dokter dan jadwal konsultasi yang sesuai dengan kebutuhan Anda.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-calendar-plus me-2"></i>Form Booking</h5>
            </div>
            <div class="card-body">
                <form method="POST" id="bookingForm">
                    <div class="mb-3">
                        <label for="dokter_id" class="form-label">Pilih Dokter</label>
                        <select class="form-control" id="dokter_id" name="dokter_id" required onchange="loadDoctorSchedule()">
                            <option value="">-- Pilih Dokter --</option>
                            <?php foreach ($doctors as $doctor): ?>
                            <option value="<?php echo $doctor['id']; ?>" <?php echo $selected_doctor_id == $doctor['id'] ? 'selected' : ''; ?>>
                                <?php echo $doctor['nama']; ?> - <?php echo $doctor['spesialis']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tanggal_kunjungan" class="form-label">Tanggal Kunjungan</label>
                        <input type="date" class="form-control" id="tanggal_kunjungan" name="tanggal_kunjungan" 
                               min="<?php echo date('Y-m-d'); ?>" required onchange="loadAvailableSlots()">
                    </div>
                    
                    <div class="mb-3">
                        <label for="jam_kunjungan" class="form-label">Jam Kunjungan</label>
                        <select class="form-control" id="jam_kunjungan" name="jam_kunjungan" required>
                            <option value="">-- Pilih dokter dan tanggal terlebih dahulu --</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="keluhan" class="form-label">Keluhan</label>
                        <textarea class="form-control" id="keluhan" name="keluhan" rows="4" 
                                  placeholder="Jelaskan keluhan atau gejala yang Anda alami..." required></textarea>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-calendar-check me-2"></i>Booking Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-info-circle me-2"></i>Informasi Booking</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Booking dapat dilakukan maksimal 7 hari ke depan
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Konfirmasi booking akan dikirim via email
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Datang 15 menit sebelum jadwal konsultasi
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Bawa kartu identitas dan kartu BPJS (jika ada)
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6><i class="fas fa-clock me-2"></i>Jadwal Praktik</h6>
            </div>
            <div class="card-body">
                <div id="doctor-schedule">
                    <p class="text-muted">Pilih dokter untuk melihat jadwal praktik.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Fallback showAlert function if not loaded from external scripts
if (typeof showAlert === 'undefined') {
    function showAlert(type, message) {
        alert(message);
    }
}

// Fallback utility functions
if (typeof formatTanggal === 'undefined') {
    function formatTanggal(dateString) {
        return new Date(dateString).toLocaleDateString('id-ID');
    }
}

if (typeof formatWaktu === 'undefined') {
    function formatWaktu(timeString) {
        return new Date('2000-01-01 ' + timeString).toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'});
    }
}

function loadDoctorSchedule() {
    let doctorId = document.getElementById('dokter_id').value;
    let scheduleDiv = document.getElementById('doctor-schedule');
    
    if (!doctorId) {
        scheduleDiv.innerHTML = '<p class="text-muted">Pilih dokter untuk melihat jadwal praktik.</p>';
        return;
    }
    
    // Load doctor schedule via AJAX
    fetch(`get_doctor_schedule.php?dokter_id=${doctorId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = '<div class="table-responsive"><table class="table table-sm">';
                html += '<thead><tr><th>Hari</th><th>Jam</th></tr></thead><tbody>';
                
                if (data.schedule.length > 0) {
                    data.schedule.forEach(schedule => {
                        html += `<tr><td>${schedule.hari}</td><td>${schedule.jam_mulai} - ${schedule.jam_selesai}</td></tr>`;
                    });
                } else {
                    html += '<tr><td colspan="2" class="text-center text-muted">Jadwal belum tersedia</td></tr>';
                }
                
                html += '</tbody></table></div>';
                scheduleDiv.innerHTML = html;
            }
        })
        .catch(error => {
            scheduleDiv.innerHTML = '<p class="text-danger">Error loading schedule.</p>';
        });
}

function loadAvailableSlots() {
    let doctorId = document.getElementById('dokter_id').value;
    let tanggal = document.getElementById('tanggal_kunjungan').value;
    let jamSelect = document.getElementById('jam_kunjungan');
    
    if (!doctorId || !tanggal) {
        jamSelect.innerHTML = '<option value="">-- Pilih dokter dan tanggal terlebih dahulu --</option>';
        return;
    }
    
    // Get day of week
    let date = new Date(tanggal);
    let days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    let dayName = days[date.getDay()];
    
    // Load available time slots
    fetch(`get_available_slots.php?dokter_id=${doctorId}&tanggal=${tanggal}&hari=${dayName}`)
        .then(response => response.json())
        .then(data => {
            jamSelect.innerHTML = '<option value="">-- Pilih jam --</option>';
            
            if (data.success && data.slots.length > 0) {
                data.slots.forEach(slot => {
                    jamSelect.innerHTML += `<option value="${slot}">${slot}</option>`;
                });
            } else {
                jamSelect.innerHTML = '<option value="">Tidak ada slot tersedia</option>';
            }
        })
        .catch(error => {
            jamSelect.innerHTML = '<option value="">Error loading slots</option>';
        });
}

// Simple form validation on submit button click
function validateAndSubmitForm() {
    console.log('Validating booking form...');
    
    // Get form elements
    const doctorId = document.getElementById('dokter_id');
    const tanggalKunjungan = document.getElementById('tanggal_kunjungan');
    const jamKunjungan = document.getElementById('jam_kunjungan');
    const keluhan = document.getElementById('keluhan');
    const form = document.getElementById('bookingForm');
    
    // Clear previous validation
    [doctorId, tanggalKunjungan, jamKunjungan, keluhan].forEach(field => {
        field.classList.remove('is-invalid');
    });
    
    let isValid = true;
    let errorMessage = '';
    
    // Validate each field
    if (!doctorId.value) {
        doctorId.classList.add('is-invalid');
        errorMessage += '- Pilih dokter\n';
        isValid = false;
    }
    
    if (!tanggalKunjungan.value) {
        tanggalKunjungan.classList.add('is-invalid');
        errorMessage += '- Pilih tanggal kunjungan\n';
        isValid = false;
    }
    
    if (!jamKunjungan.value) {
        jamKunjungan.classList.add('is-invalid');
        errorMessage += '- Pilih jam kunjungan\n';
        isValid = false;
    }
    
    if (!keluhan.value.trim()) {
        keluhan.classList.add('is-invalid');
        errorMessage += '- Isi keluhan\n';
        isValid = false;
    }
    
    if (!isValid) {
        alert('Mohon lengkapi field berikut:\n\n' + errorMessage);
        return false;
    }
    
    // Show loading state
    const submitBtn = document.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses Booking...';
    }
    
    // Submit form
    console.log('Submitting form...');
    form.submit();
    return true;
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('Booking page loaded');
    
    // Load schedule if doctor is pre-selected
    const doctorSelect = document.getElementById('dokter_id');
    if (doctorSelect && doctorSelect.value) {
        loadDoctorSchedule();
    }
    
    // Add event listener to form submit button
    const submitBtn = document.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            validateAndSubmitForm();
        });
    }
});
</script>

<?php include 'includes/user-footer.php'; ?>