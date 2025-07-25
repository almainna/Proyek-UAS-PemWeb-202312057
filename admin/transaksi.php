<?php
require_once '../config/init.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}
include '../includes/admin-header.php';
require_once '../config/database.php';

// Initialize database connection
$db = new Database();

// Helper functions
function getStatusClass($status) {
    switch ($status) {
        case 'pending': return 'warning';
        case 'diproses': return 'info';
        case 'selesai': return 'success';
        case 'dibatalkan': return 'danger';
        default: return 'secondary';
    }
}

function getStatusText($status) {
    switch ($status) {
        case 'pending': return 'Menunggu';
        case 'diproses': return 'Diproses';
        case 'selesai': return 'Selesai';
        case 'dibatalkan': return 'Dibatalkan';
        default: return ucfirst($status);
    }
}

// Fetch transactions
$query = "SELECT t.*, u.nama as nama_pasien
          FROM transaksi t
          LEFT JOIN pasien p ON t.pasien_id = p.id
          LEFT JOIN users u ON p.user_id = u.id
          ORDER BY t.tanggal DESC";
$db->query($query);
$transactions = $db->resultset();
?>

<div class="container mt-5">
    <h2>Kelola Transaksi</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>No. Transaksi</th>
                <th>Pasien</th>
                <th>Tanggal</th>
                <th>Total Harga</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $trx): ?>
            <tr>
                <td>#TRX<?= str_pad($trx['id'], 6, '0', STR_PAD_LEFT) ?></td>
                <td><?= htmlspecialchars($trx['nama_pasien']) ?></td>
                <td><?= date('d-m-Y', strtotime($trx['tanggal'])) ?></td>
                <td><?= number_format($trx['total_harga']) ?></td>
                <td><span class="badge bg-<?= getStatusClass($trx['status']) ?>"><?= getStatusText($trx['status']) ?></span></td>
                <td>
                    <button class="btn btn-info btn-sm" onclick="showTransactionDetail(<?= $trx['id'] ?>)">Detail</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Detail Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detailContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<script>
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

function formatRupiah(amount) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

function formatTanggal(dateString) {
    const date = new Date(dateString);
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return date.toLocaleDateString('id-ID', options);
}
</script>

<?php include '../includes/admin-footer.php'; ?>
