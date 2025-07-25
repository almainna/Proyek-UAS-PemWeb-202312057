<?php
require_once '../config/init.php';

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
    
    // Ambil riwayat transaksi
    $stmt = $conn->prepare("
        SELECT t.*, 
               COUNT(dt.id) as total_item,
               GROUP_CONCAT(CONCAT(o.nama_obat, ' (', dt.qty, 'x)') SEPARATOR ', ') as detail_obat
        FROM transaksi t
        LEFT JOIN detail_transaksi dt ON t.id = dt.transaksi_id
        LEFT JOIN obat o ON dt.obat_id = o.id
        WHERE t.pasien_id = ?
        GROUP BY t.id
        ORDER BY t.tanggal DESC
    ");
    $stmt->execute([$pasien['id']]);
    $transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $error = "Terjadi kesalahan: " . $e->getMessage();
}

$page_title = "Riwayat Transaksi";
include '../includes/user-header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-receipt me-2"></i>Riwayat Transaksi</h2>
                <a href="index.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
                </a>
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
            <?php if (empty($transaksi)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-receipt text-muted mb-3" style="font-size: 4rem;"></i>
                        <h4 class="text-muted">Belum Ada Transaksi</h4>
                        <p class="text-muted mb-4">Anda belum memiliki riwayat transaksi pembelian obat.</p>
                        <a href="../obat.php" class="btn btn-primary">
                            <i class="fas fa-pills me-2"></i>Beli Obat Sekarang
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Transaksi</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>No. Transaksi</th>
                                        <th>Tanggal</th>
                                        <th>Total Item</th>
                                        <th>Total Harga</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transaksi as $t): ?>
                                        <tr>
                                            <td>
                                                <strong>#TRX<?= str_pad($t['id'], 6, '0', STR_PAD_LEFT) ?></strong>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?= formatTanggal($t['tanggal']) ?></strong><br>
                                                    <small class="text-muted"><?= formatWaktu($t['tanggal']) ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?= $t['total_item'] ?> item</span>
                                            </td>
                                            <td>
                                                <strong class="text-success"><?= formatRupiah($t['total_harga']) ?></strong>
                                            </td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                $status_text = '';
                                                switch ($t['status']) {
                                                    case 'pending':
                                                        $status_class = 'warning';
                                                        $status_text = 'Menunggu';
                                                        break;
                                                    case 'diproses':
                                                        $status_class = 'info';
                                                        $status_text = 'Diproses';
                                                        break;
                                                    case 'selesai':
                                                        $status_class = 'success';
                                                        $status_text = 'Selesai';
                                                        break;
                                                    case 'dibatalkan':
                                                        $status_class = 'danger';
                                                        $status_text = 'Dibatalkan';
                                                        break;
                                                    default:
                                                        $status_class = 'secondary';
                                                        $status_text = ucfirst($t['status']);
                                                }
                                                ?>
                                                <span class="badge bg-<?= $status_class ?>"><?= $status_text ?></span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="showTransactionDetail(<?= $t['id'] ?>)">
                                                    <i class="fas fa-eye me-1"></i>Detail
                                                </button>
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

<!-- Modal Detail Transaksi -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Transaksi</h5>
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

<script>
// Fallback JavaScript utility functions
if (typeof formatRupiah === 'undefined') {
    function formatRupiah(angka) {
        return 'Rp ' + Number(angka).toLocaleString('id-ID');
    }
}

if (typeof formatTanggal === 'undefined') {
    function formatTanggal(dateString) {
        return new Date(dateString).toLocaleDateString('id-ID');
    }
}

if (typeof formatWaktu === 'undefined') {
    function formatWaktu(dateString) {
        return new Date(dateString).toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'});
    }
}

function showTransactionDetail(transaksiId) {
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
    fetch('get_transaction_detail.php?id=' + transaksiId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                content.innerHTML = generateDetailHTML(data.data);
            } else {
                content.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${data.message || 'Gagal memuat detail transaksi'}
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

function generateDetailHTML(data) {
    const transaksi = data.transaksi;
    const items = data.items;
    
    let itemsHTML = '';
    items.forEach(item => {
        itemsHTML += `
            <tr>
                <td>${item.nama_obat}</td>
                <td class="text-center">${item.qty}</td>
                <td class="text-center"><span class="badge bg-secondary">${item.jenis || 'N/A'}</span></td>
                <td class="text-end">${formatRupiah(item.subtotal)}</td>
            </tr>
        `;
    });
    
    return `
        <div class="row mb-3">
            <div class="col-md-6">
                <h6>Informasi Transaksi</h6>
                <table class="table table-sm">
                    <tr>
                        <td>No. Transaksi</td>
                        <td><strong>#TRX${String(transaksi.id).padStart(6, '0')}</strong></td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td>${formatTanggal(transaksi.tanggal)}</td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td><span class="badge bg-${getStatusClass(transaksi.status)}">${getStatusText(transaksi.status)}</span></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Ringkasan</h6>
                <table class="table table-sm">
                    <tr>
                        <td>Total Item</td>
                        <td><strong>${items.length} item</strong></td>
                    </tr>
                    <tr>
                        <td>Total Harga</td>
                        <td><strong class="text-success">${formatRupiah(transaksi.total_harga)}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <h6>Detail Item</h6>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead class="table-light">
                    <tr>
                        <th>Nama Obat</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Jenis</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    ${itemsHTML}
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="3" class="text-end">Total:</th>
                        <th class="text-end text-success">${formatRupiah(transaksi.total_harga)}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    `;
}

function getStatusClass(status) {
    switch (status) {
        case 'pending': return 'warning';
        case 'diproses': return 'info';
        case 'selesai': return 'success';
        case 'dibatalkan': return 'danger';
        default: return 'secondary';
    }
}

function getStatusText(status) {
    switch (status) {
        case 'pending': return 'Menunggu';
        case 'diproses': return 'Diproses';
        case 'selesai': return 'Selesai';
        case 'dibatalkan': return 'Dibatalkan';
        default: return status.charAt(0).toUpperCase() + status.slice(1);
    }
}
</script>

<?php include '../includes/user-footer.php'; ?>