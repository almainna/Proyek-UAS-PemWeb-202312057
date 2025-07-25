<?php
require_once 'config/init.php';

$title = "Dokter";
include 'includes/user-header.php';

// Get doctors with their schedules
$db = new Database();
$db->query("
    SELECT d.*, u.nama, u.email,
           GROUP_CONCAT(CONCAT(jp.hari, ' (', jp.jam_mulai, ' - ', jp.jam_selesai, ')') SEPARATOR ', ') as jadwal
    FROM dokter d 
    JOIN users u ON d.user_id = u.id 
    LEFT JOIN jadwal_praktik jp ON d.id = jp.dokter_id
    GROUP BY d.id
    ORDER BY u.nama
");
$doctors = $db->resultset();
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-user-md me-2"></i>Dokter Kami</h2>
        </div>
        
        <?php if (empty($doctors)): ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle me-2"></i>Belum ada data dokter.
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($doctors as $doctor): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <?php if ($doctor['foto']): ?>
                        <img src="uploads/doctors/<?php echo $doctor['foto']; ?>" 
                             alt="<?php echo $doctor['nama']; ?>" 
                             class="rounded-circle mb-3" 
                             style="width: 120px; height: 120px; object-fit: cover;">
                        <?php else: ?>
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                             style="width: 120px; height: 120px;">
                            <i class="fas fa-user-md fa-3x"></i>
                        </div>
                        <?php endif; ?>
                        
                        <h5 class="card-title"><?php echo $doctor['nama']; ?></h5>
                        <p class="text-primary fw-bold"><?php echo $doctor['spesialis']; ?></p>
                        
                        <?php if ($doctor['no_str']): ?>
                        <p class="text-muted small">STR: <?php echo $doctor['no_str']; ?></p>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <h6 class="text-muted">Jadwal Praktik:</h6>
                            <?php if ($doctor['jadwal']): ?>
                            <small class="text-dark"><?php echo $doctor['jadwal']; ?></small>
                            <?php else: ?>
                            <small class="text-muted">Jadwal belum tersedia</small>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (isLoggedIn() && isPasien()): ?>
                        <a href="booking.php?dokter_id=<?php echo $doctor['id']; ?>" class="btn btn-primary">
                            <i class="fas fa-calendar-check me-2"></i>Booking Konsultasi
                        </a>
                        <?php else: ?>
                        <a href="login.php" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Login untuk Booking
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Doctor Schedule Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Jadwal Praktik Dokter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Hari</th>
                                <th>Jam Praktik</th>
                            </tr>
                        </thead>
                        <tbody id="scheduleTableBody">
                            <!-- Schedule data will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showSchedule(doctorId) {
    // This would typically load schedule data via AJAX
    // For now, we'll show the modal with existing data
    $('#scheduleModal').modal('show');
}
</script>

<?php include 'includes/user-footer.php'; ?>