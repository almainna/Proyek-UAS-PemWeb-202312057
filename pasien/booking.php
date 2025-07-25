<?php
require_once __DIR__ . '/../config/init.php';

// Cek apakah user sudah login dan merupakan pasien
if (!isLoggedIn() || !isPasien()) {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Ambil data pasien
    $stmt = $conn->prepare("SELECT * FROM pasien WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $pasien = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pasien) {
        redirect('../login.php');
    }
    
    // Ambil riwayat booking
    $stmt = $conn->prepare("
        SELECT b.*, d.spesialis, u.nama as nama_dokter
        FROM booking b
        JOIN dokter d ON b.dokter_id = d.id
        JOIN users u ON d.user_id = u.id
        WHERE b.pasien_id = ?
        ORDER BY b.tanggal_kunjungan DESC, b.jam_kunjungan DESC
    ");
    $stmt->execute([$pasien['id']]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $error = "Terjadi kesalahan: " . $e->getMessage();
}

$page_title = "Riwayat Booking";
include '../includes/user-header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-calendar-alt me-2"></i>Riwayat Booking</h2>
                <div>
                    <a href="../booking.php" class="btn btn-primary me-2">
                        <i class="fas fa-plus me-2"></i>Booking Baru
                    </a>
                    <a href="index.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i><?= $error ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <?php if (empty($bookings)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-calendar-times text-muted mb-3" style="font-size: 4rem;"></i>
                        <h4 class="text-muted">Belum Ada Booking</h4>
                        <p class="text-muted mb-4">Anda belum memiliki riwayat booking konsultasi.</p>
                        <a href="../booking.php" class="btn btn-primary">
                            <i class="fas fa-calendar-plus me-2"></i>Booking Sekarang
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Booking Konsultasi</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>No. Booking</th>
                                        <th>Dokter</th>
                                        <th>Tanggal & Jam</th>
                                        <th>Keluhan</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookings as $booking): ?>
                                        <tr>
                                            <td>
                                                <strong>#BK<?= str_pad($booking['id'], 6, '0', STR_PAD_LEFT) ?></strong>
                                            </td>
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
                                                <?php
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
                                                        <button class="btn btn-sm btn-outline-danger" 
                                                                onclick="cancelBooking(<?= $booking['id'] ?>)">
                                                            <i class="fas fa-times me-1"></i>Batal
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
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

<!-- Modal Konfirmasi Pembatalan -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Pembatalan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin membatalkan booking ini?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Booking yang sudah dibatalkan tidak dapat dikembalikan.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak</button>
                <button type="button" class="btn btn-danger" id="confirmCancelBtn">Ya, Batalkan</button>
            </div>
        </div>
    </div>
</div>

<script>
// Fallback showAlert function if not loaded from external scripts
if (typeof showAlert === 'undefined') {
    function showAlert(type, message) {
        // Create alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Add to page
        document.body.appendChild(alertDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
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

let currentBookingId = null;

function showBookingDetail(bookingId) {
    const modal = new bootstrap.Modal(document.getElementById('detailModal'));
    const content = document.getElementById('detailContent');
    
    // Show loading
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    modal.show();
    
    // Fetch detail
    fetch('get_booking_detail.php?id=' + bookingId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                content.innerHTML = generateBookingDetailHTML(data.data);
            } else {
                content.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${data.message || 'Gagal memuat detail booking'}
                    </div>
                `;
            }
        })
        .catch(error => {
            content.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Terjadi kesalahan saat memuat data
                </div>
            `;
        });
}

function cancelBooking(bookingId) {
    currentBookingId = bookingId;
    const modal = new bootstrap.Modal(document.getElementById('cancelModal'));
    modal.show();
}

document.getElementById('confirmCancelBtn').addEventListener('click', function() {
    if (currentBookingId) {
        // Show loading
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Membatalkan...';
        this.disabled = true;
        
        fetch('cancel_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                booking_id: currentBookingId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Booking berhasil dibatalkan');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showAlert('danger', data.message || 'Gagal membatalkan booking');
            }
        })
        .catch(error => {
            showAlert('danger', 'Terjadi kesalahan saat membatalkan booking');
        })
        .finally(() => {
            bootstrap.Modal.getInstance(document.getElementById('cancelModal')).hide();
            this.innerHTML = 'Ya, Batalkan';
            this.disabled = false;
        });
    }
});

function generateBookingDetailHTML(booking) {
    return `
        <div class="row mb-3">
            <div class="col-md-6">
                <h6>Informasi Booking</h6>
                <table class="table table-sm">
                    <tr>
                        <td>No. Booking</td>
                        <td><strong>#BK${String(booking.id).padStart(6, '0')}</strong></td>
                    </tr>
                    <tr>
                        <td>Tanggal Kunjungan</td>
                        <td>${formatTanggal(booking.tanggal_kunjungan)}</td>
                    </tr>
                    <tr>
                        <td>Jam Kunjungan</td>
                        <td>${formatWaktu(booking.jam_kunjungan)}</td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td><span class="badge bg-${getBookingStatusClass(booking.status)}">${getBookingStatusText(booking.status)}</span></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Informasi Dokter</h6>
                <table class="table table-sm">
                    <tr>
                        <td>Nama Dokter</td>
                        <td><strong>${booking.nama_dokter}</strong></td>
                    </tr>
                    <tr>
                        <td>Spesialisasi</td>
                        <td>${booking.spesialis}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <h6>Keluhan</h6>
        <div class="card bg-light">
            <div class="card-body">
                <p class="mb-0">${booking.keluhan}</p>
            </div>
        </div>
        
        ${booking.catatan_dokter ? `
            <h6 class="mt-3">Catatan Dokter</h6>
            <div class="card bg-light">
                <div class="card-body">
                    <p class="mb-0">${booking.catatan_dokter}</p>
                </div>
            </div>
        ` : ''}
    `;
}

function getBookingStatusClass(status) {
    switch (status) {
        case 'pending': return 'warning';
        case 'dikonfirmasi': return 'info';
        case 'selesai': return 'success';
        case 'dibatalkan': return 'danger';
        default: return 'secondary';
    }
}

function getBookingStatusText(status) {
    switch (status) {
        case 'pending': return 'Menunggu';
        case 'dikonfirmasi': return 'Dikonfirmasi';
        case 'selesai': return 'Selesai';
        case 'dibatalkan': return 'Dibatalkan';
        default: return status.charAt(0).toUpperCase() + status.slice(1);
    }
}
</script>

<?php include '../includes/user-footer.php'; ?>