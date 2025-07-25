<?php
require_once 'config/init.php';

$title = "Obat";
include 'includes/user-header.php';

// Get search and filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';

// Build query
$query = "SELECT * FROM obat WHERE stok > 0";
$params = [];

if ($search) {
    $query .= " AND (nama_obat LIKE :search OR deskripsi LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($jenis) {
    $query .= " AND jenis = :jenis";
    $params[':jenis'] = $jenis;
}

$query .= " ORDER BY nama_obat";

$db = new Database();
$db->query($query);
foreach ($params as $param => $value) {
    $db->bind($param, $value);
}
$medicines = $db->resultset();

// Get medicine types for filter
$db->query("SELECT DISTINCT jenis FROM obat WHERE jenis IS NOT NULL AND jenis != '' ORDER BY jenis");
$types = $db->resultset();
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-pills me-2"></i>Daftar Obat</h2>
        </div>
        
        <!-- Search and Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <label for="search" class="form-label">Cari Obat</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Nama obat atau deskripsi..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="jenis" class="form-label">Jenis Obat</label>
                        <select class="form-control" id="jenis" name="jenis">
                            <option value="">Semua Jenis</option>
                            <?php foreach ($types as $type): ?>
                            <option value="<?php echo $type['jenis']; ?>" <?php echo $jenis == $type['jenis'] ? 'selected' : ''; ?>>
                                <?php echo $type['jenis']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Cari
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if (empty($medicines)): ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle me-2"></i>
            <?php echo $search || $jenis ? 'Tidak ada obat yang sesuai dengan pencarian.' : 'Belum ada data obat.'; ?>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($medicines as $medicine): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card medicine-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title"><?php echo $medicine['nama_obat']; ?></h5>
                            <span class="badge bg-secondary"><?php echo $medicine['jenis']; ?></span>
                        </div>
                        
                        <p class="card-text text-muted"><?php echo $medicine['deskripsi']; ?></p>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong class="text-primary h5"><?php echo formatRupiah($medicine['harga']); ?></strong>
                            </div>
                            <div class="col-6 text-end">
                                <small class="text-muted">
                                    Stok: <span class="fw-bold <?php echo $medicine['stok'] < 10 ? 'text-danger' : 'text-success'; ?>">
                                        <?php echo $medicine['stok']; ?>
                                    </span>
                                </small>
                            </div>
                        </div>
                        
                        <?php if (isLoggedIn() && isPasien()): ?>
                        <div class="d-grid">
                            <button class="btn btn-success" 
                                    onclick="addToCart(<?php echo $medicine['id']; ?>, '<?php echo addslashes($medicine['nama_obat']); ?>', <?php echo $medicine['harga']; ?>)"
                                    <?php echo $medicine['stok'] == 0 ? 'disabled' : ''; ?>>
                                <i class="fas fa-cart-plus me-2"></i>
                                <?php echo $medicine['stok'] == 0 ? 'Stok Habis' : 'Tambah ke Keranjang'; ?>
                            </button>
                        </div>
                        <?php else: ?>
                        <div class="d-grid">
                            <a href="login.php" class="btn btn-outline-success">
                                <i class="fas fa-sign-in-alt me-2"></i>Login untuk Beli
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <?php if (isLoggedIn() && isPasien()): ?>
        <div class="fixed-bottom p-3" style="z-index: 1000;">
            <div class="container">
                <div class="row justify-content-end">
                    <div class="col-auto">
                        <a href="keranjang.php" class="btn btn-primary btn-lg rounded-pill shadow">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Lihat Keranjang
                            <span class="badge bg-warning text-dark ms-2" id="cart-count">0</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Medicine Detail Modal -->
<div class="modal fade" id="medicineModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="medicineModalTitle">Detail Obat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="medicineModalBody">
                <!-- Medicine details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-success" id="addToCartFromModal">
                    <i class="fas fa-cart-plus me-2"></i>Tambah ke Keranjang
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showMedicineDetail(id, nama, jenis, deskripsi, harga, stok) {
    document.getElementById('medicineModalTitle').textContent = nama;
    document.getElementById('medicineModalBody').innerHTML = `
        <div class="row">
            <div class="col-12">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Nama Obat:</strong></td>
                        <td>${nama}</td>
                    </tr>
                    <tr>
                        <td><strong>Jenis:</strong></td>
                        <td><span class="badge bg-secondary">${jenis}</span></td>
                    </tr>
                    <tr>
                        <td><strong>Harga:</strong></td>
                        <td class="text-primary h5">${formatCurrency(harga)}</td>
                    </tr>
                    <tr>
                        <td><strong>Stok:</strong></td>
                        <td><span class="badge ${stok < 10 ? 'bg-danger' : 'bg-success'}">${stok}</span></td>
                    </tr>
                    <tr>
                        <td><strong>Deskripsi:</strong></td>
                        <td>${deskripsi}</td>
                    </tr>
                </table>
            </div>
        </div>
    `;
    
    document.getElementById('addToCartFromModal').onclick = function() {
        addToCart(id, nama, harga);
        bootstrap.Modal.getInstance(document.getElementById('medicineModal')).hide();
    };
    
    new bootstrap.Modal(document.getElementById('medicineModal')).show();
}
</script>

<?php include 'includes/user-footer.php'; ?>