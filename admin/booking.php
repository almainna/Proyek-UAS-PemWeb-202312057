<?php
require_once '../config/init.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$db = new Database();
$error = '';
$message = '';
$messageType = '';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        switch ($_POST['action']) {
            case 'get_detail':
                $bookingId = $_POST['booking_id'];
                $db->query("SELECT b.*, u.nama as nama_dokter, d.spesialis, d.no_str,
                                   up.nama as nama_pasien, up.email as email_pasien,
                                   p.no_hp, p.tanggal_lahir, p.alamat
                             FROM booking b
                             JOIN dokter d ON b.dokter_id = d.id
                             JOIN users u ON d.user_id = u.id
                             JOIN pasien p ON b.pasien_id = p.id
                             JOIN users up ON p.user_id = up.id
                             WHERE b.id = :booking_id");
                $db->bind(':booking_id', $bookingId);
                $booking = $db->single();
                
                if ($booking) {
                    echo json_encode([
                        'success' => true,
                        'data' => $booking
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Booking tidak ditemukan']);
                }
                exit;
                
            case 'confirm_booking':
                $bookingId = $_POST['booking_id'];
                $db->query("UPDATE booking SET status = 'dikonfirmasi' WHERE id = :booking_id AND status = 'pending'");
                $db->bind(':booking_id', $bookingId);
                
                if ($db->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Booking berhasil dikonfirmasi']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Gagal mengkonfirmasi booking']);
                }
                exit;
                
            case 'cancel_booking':
                $bookingId = $_POST['booking_id'];
                $db->query("UPDATE booking SET status = 'dibatalkan' WHERE id = :booking_id AND status IN ('pending', 'dikonfirmasi')");
                $db->bind(':booking_id', $bookingId);
                
                if ($db->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Booking berhasil dibatalkan']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Gagal membatalkan booking']);
                }
                exit;
                
            case 'complete_booking':
                $bookingId = $_POST['booking_id'];
                $db->query("UPDATE booking SET status = 'selesai' WHERE id = :booking_id AND status = 'dikonfirmasi'");
                $db->bind(':booking_id', $bookingId);
                
                if ($db->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Booking berhasil diselesaikan']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Gagal menyelesaikan booking']);
                }
                exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

// Retrieve booking information
try {
    $db->query("SELECT b.id, b.tanggal_kunjungan, b.jam_kunjungan, b.keluhan, b.status, 
                       u.nama as nama_dokter, d.spesialis,
                       up.nama as nama_pasien
                 FROM booking b
                 JOIN dokter d ON b.dokter_id = d.id
                 JOIN users u ON d.user_id = u.id
                 JOIN pasien p ON b.pasien_id = p.id
                 JOIN users up ON p.user_id = up.id
                 ORDER BY b.tanggal_kunjungan DESC, b.jam_kunjungan DESC");
    $bookings = $db->resultSet();
} catch (Exception $e) {
    $error = "Error fetching bookings: " . $e->getMessage();
}

$title = "Kelola Booking";
include '../includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">

        <!-- Main content -->
        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="fas fa-calendar-check me-2"></i>Kelola Booking</h1>
            </div>
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i><?= $error ?>
                </div>
            <?php endif; ?>
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Booking List</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No. Booking</th>
                                    <th>Pasien</th>
                                    <th>Dokter</th>
                                    <th>Tanggal & Waktu</th>
                                    <th>Keluhan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($bookings)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No bookings found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($bookings as $booking): ?>
                                        <?php
                                        // Status display logic
                                        $status_class = '';
                                        $status_text = '';
                                        $status_icon = '';
                                        switch ($booking['status']) {
                                            case 'pending':
                                                $status_class = 'warning';
                                                $status_text = 'Menunggu';
                                                $status_icon = 'clock';
                                                break;
                                            case 'dikonfirmasi':
                                                $status_class = 'info';
                                                $status_text = 'Dikonfirmasi';
                                                $status_icon = 'check-circle';
                                                break;
                                            case 'selesai':
                                                $status_class = 'success';
                                                $status_text = 'Selesai';
                                                $status_icon = 'check-double';
                                                break;
                                            case 'dibatalkan':
                                                $status_class = 'danger';
                                                $status_text = 'Dibatalkan';
                                                $status_icon = 'times-circle';
                                                break;
                                            default:
                                                $status_class = 'secondary';
                                                $status_text = ucfirst($booking['status']);
                                                $status_icon = 'question-circle';
                                        }
                                        ?>
                                        <tr>
                                            <td><strong>#BK<?= str_pad($booking['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                                            <td><?= htmlspecialchars($booking['nama_pasien']) ?></td>
                                            <td>
                                                <div>
                                                    <strong><?= htmlspecialchars($booking['nama_dokter']) ?></strong><br>
                                                    <small class="text-muted"><?= htmlspecialchars($booking['spesialis']) ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?= formatTanggal($booking['tanggal_kunjungan']) ?></strong><br>
                                                    <small class="text-muted"><?= formatWaktu($booking['jam_kunjungan']) ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="max-width: 200px;">
                                                    <?= htmlspecialchars(substr($booking['keluhan'], 0, 100)) ?>
                                                    <?php if (strlen($booking['keluhan']) > 100): ?>
                                                        <span class="text-muted">...</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $status_class ?>">
                                                    <i class="fas fa-<?= $status_icon ?> me-1"></i><?= $status_text ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="showBookingDetail(<?= $booking['id'] ?>)">
                                                        <i class="fas fa-eye me-1"></i>Detail
                                                    </button>
                                                    <?php if ($booking['status'] == 'pending'): ?>
                                                        <button class="btn btn-sm btn-outline-success" 
                                                                onclick="confirmBooking(<?= $booking['id'] ?>)">
                                                            <i class="fas fa-check me-1"></i>Konfirmasi
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger" 
                                                                onclick="cancelBooking(<?= $booking['id'] ?>)">
                                                            <i class="fas fa-times me-1"></i>Batal
                                                        </button>
                                                    <?php elseif ($booking['status'] == 'dikonfirmasi'): ?>
                                                        <button class="btn btn-sm btn-outline-success" 
                                                                onclick="completeBooking(<?= $booking['id'] ?>)">
                                                            <i class="fas fa-check-double me-1"></i>Selesai
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

<!-- Modal Detail Booking -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Wait for DOM to be ready
$(document).ready(function() {
    console.log('jQuery loaded successfully');
});

// Function to show booking detail
function showBookingDetail(bookingId) {
    // Create Bootstrap modal instance
    var modal = new bootstrap.Modal(document.getElementById('detailModal'));
    modal.show();
    
    document.getElementById('detailContent').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    // Use Fetch API
    fetch('booking.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_detail&booking_id=' + bookingId
    })
    .then(response => response.json())
    .then(response => {
        if (response.success) {
            const data = response.data;
            let statusBadge = '';
            
            switch(data.status) {
                case 'pending':
                    statusBadge = '<span class="badge bg-warning"><i class="fas fa-clock me-1"></i>Menunggu</span>';
                    break;
                case 'dikonfirmasi':
                    statusBadge = '<span class="badge bg-info"><i class="fas fa-check-circle me-1"></i>Dikonfirmasi</span>';
                    break;
                case 'selesai':
                    statusBadge = '<span class="badge bg-success"><i class="fas fa-check-double me-1"></i>Selesai</span>';
                    break;
                case 'dibatalkan':
                    statusBadge = '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Dibatalkan</span>';
                    break;
            }
            
            document.getElementById('detailContent').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">Informasi Booking</h6>
                        <table class="table table-borderless">
                            <tr><td><strong>ID Booking:</strong></td><td>#BK${String(data.id).padStart(6, '0')}</td></tr>
                            <tr><td><strong>Status:</strong></td><td>${statusBadge}</td></tr>
                            <tr><td><strong>Tanggal:</strong></td><td>${formatDate(data.tanggal_kunjungan)}</td></tr>
                            <tr><td><strong>Waktu:</strong></td><td>${data.jam_kunjungan}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary">Informasi Pasien</h6>
                        <table class="table table-borderless">
                            <tr><td><strong>Nama:</strong></td><td>${data.nama_pasien}</td></tr>
                            <tr><td><strong>Email:</strong></td><td>${data.email_pasien}</td></tr>
                            <tr><td><strong>No. HP:</strong></td><td>${data.no_hp || '-'}</td></tr>
                            <tr><td><strong>Alamat:</strong></td><td>${data.alamat || '-'}</td></tr>
                        </table>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6 class="text-primary">Informasi Dokter</h6>
                        <table class="table table-borderless">
                            <tr><td><strong>Nama:</strong></td><td>${data.nama_dokter}</td></tr>
                            <tr><td><strong>Spesialis:</strong></td><td>${data.spesialis}</td></tr>
                            <tr><td><strong>No. STR:</strong></td><td>${data.no_str || '-'}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary">Keluhan</h6>
                        <p class="text-muted">${data.keluhan}</p>
                    </div>
                </div>
            `;
        } else {
            document.getElementById('detailContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>${response.message}
                </div>
            `;
        }
    })
    .catch(error => {
        document.getElementById('detailContent').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>Terjadi kesalahan saat memuat data
            </div>
        `;
    });
}

// Function to confirm booking
function confirmBooking(bookingId) {
    if (confirm('Apakah Anda yakin ingin mengkonfirmasi booking ini?')) {
        $.ajax({
            url: 'booking.php',
            method: 'POST',
            data: {
                action: 'confirm_booking',
                booking_id: bookingId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Terjadi kesalahan saat mengkonfirmasi booking');
            }
        });
    }
}

// Function to cancel booking
function cancelBooking(bookingId) {
    if (confirm('Apakah Anda yakin ingin membatalkan booking ini?')) {
        $.ajax({
            url: 'booking.php',
            method: 'POST',
            data: {
                action: 'cancel_booking',
                booking_id: bookingId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Terjadi kesalahan saat membatalkan booking');
            }
        });
    }
}

// Function to complete booking
function completeBooking(bookingId) {
    if (confirm('Apakah Anda yakin ingin menyelesaikan booking ini?')) {
        $.ajax({
            url: 'booking.php',
            method: 'POST',
            data: {
                action: 'complete_booking',
                booking_id: bookingId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Terjadi kesalahan saat menyelesaikan booking');
            }
        });
    }
}

// Helper function to format date
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        locale: 'id-ID'
    };
    return date.toLocaleDateString('id-ID', options);
}
</script>

<?php include '../includes/admin-footer.php'; ?>
