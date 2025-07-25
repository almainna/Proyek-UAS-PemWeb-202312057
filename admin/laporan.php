<?php
require_once '../config/init.php';
require_once '../config/database.php';

// Check admin authentication
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Initialize database connection
$db = new Database();

// Get report data
$totalPatients = 0;
$totalDoctors = 0;
$totalTransactions = 0;
$totalRevenue = 0;
$recentTransactions = [];
$monthlyStats = [];

try {
    // Get total patients
    $result = $db->query("SELECT COUNT(*) as total FROM pasien");
    if ($result && $row = $result->fetch_assoc()) {
        $totalPatients = $row['total'];
    }

    // Get total doctors
    $result = $db->query("SELECT COUNT(*) as total FROM dokter");
    if ($result && $row = $result->fetch_assoc()) {
        $totalDoctors = $row['total'];
    }

    // Get total transactions
    $result = $db->query("SELECT COUNT(*) as total, SUM(total_bayar) as revenue FROM transaksi");
    if ($result && $row = $result->fetch_assoc()) {
        $totalTransactions = $row['total'];
        $totalRevenue = $row['revenue'] ?? 0;
    }

    // Get recent transactions (last 10)
    $result = $db->query("
        SELECT t.*, p.nama as nama_pasien, d.nama as nama_dokter 
        FROM transaksi t 
        LEFT JOIN pasien p ON t.pasien_id = p.id 
        LEFT JOIN dokter d ON t.dokter_id = d.id 
        ORDER BY t.tanggal DESC 
        LIMIT 10
    ");
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $recentTransactions[] = $row;
        }
    }

    // Get monthly statistics for current year
    $currentYear = date('Y');
    $result = $db->query("
        SELECT 
            MONTH(tanggal) as bulan,
            COUNT(*) as jumlah_transaksi,
            SUM(total_bayar) as total_pendapatan
        FROM transaksi 
        WHERE YEAR(tanggal) = $currentYear
        GROUP BY MONTH(tanggal)
        ORDER BY MONTH(tanggal)
    ");
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $monthlyStats[$row['bulan']] = $row;
        }
    }

} catch (Exception $e) {
    // Handle database errors gracefully
    error_log("Database error in laporan.php: " . $e->getMessage());
}

// Helper function to format currency
function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Helper function for month names
function getMonthName($month) {
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    return $months[$month] ?? '';
}

// Helper function for status badge
function getStatusBadge($status) {
    switch (strtolower($status)) {
        case 'pending':
            return '<span class="badge badge-warning">Pending</span>';
        case 'completed':
            return '<span class="badge badge-success">Selesai</span>';
        case 'cancelled':
            return '<span class="badge badge-danger">Dibatalkan</span>';
        default:
            return '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
    }
}

include '../includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Laporan & Statistik</h1>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Pasien</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($totalPatients) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Dokter</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($totalDoctors) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-md fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Transaksi</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($totalTransactions) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Pendapatan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= formatCurrency($totalRevenue) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Monthly Statistics Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Statistik Bulanan <?= date('Y') ?></h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Transaksi Terbaru</h6>
                    <a href="transaksi.php" class="btn btn-sm btn-primary">Lihat Semua</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentTransactions)): ?>
                        <div class="text-center text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Belum ada transaksi</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentTransactions as $transaction): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars($transaction['nama_pasien'] ?? 'N/A') ?></h6>
                                        <small><?= date('d/m/Y', strtotime($transaction['tanggal'])) ?></small>
                                    </div>
                                    <p class="mb-1 text-muted small">
                                        Dr. <?= htmlspecialchars($transaction['nama_dokter'] ?? 'N/A') ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted"><?= formatCurrency($transaction['total_bayar']) ?></small>
                                        <?= getStatusBadge($transaction['status'] ?? 'pending') ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Reports Section -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Laporan Detail</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="#" class="btn btn-outline-primary btn-block" onclick="generateReport('pasien')">
                                <i class="fas fa-users mr-2"></i>
                                Laporan Pasien
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="#" class="btn btn-outline-success btn-block" onclick="generateReport('dokter')">
                                <i class="fas fa-user-md mr-2"></i>
                                Laporan Dokter
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="#" class="btn btn-outline-info btn-block" onclick="generateReport('transaksi')">
                                <i class="fas fa-chart-bar mr-2"></i>
                                Laporan Transaksi
                            </a>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label for="startDate">Tanggal Mulai:</label>
                            <input type="date" id="startDate" class="form-control" value="<?= date('Y-m-01') ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="endDate">Tanggal Akhir:</label>
                            <input type="date" id="endDate" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Monthly chart data
const monthlyData = <?php echo json_encode($monthlyStats); ?>;
const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];

// Prepare chart data
const chartLabels = [];
const transactionData = [];
const revenueData = [];

for (let i = 1; i <= 12; i++) {
    chartLabels.push(monthNames[i-1]);
    if (monthlyData[i]) {
        transactionData.push(monthlyData[i].jumlah_transaksi);
        revenueData.push(monthlyData[i].total_pendapatan);
    } else {
        transactionData.push(0);
        revenueData.push(0);
    }
}

// Initialize Chart
const ctx = document.getElementById('monthlyChart').getContext('2d');
const monthlyChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: chartLabels,
        datasets: [{
            label: 'Jumlah Transaksi',
            data: transactionData,
            borderColor: 'rgb(54, 162, 235)',
            backgroundColor: 'rgba(54, 162, 235, 0.1)',
            tension: 0.1,
            yAxisID: 'y'
        }, {
            label: 'Pendapatan (Rp)',
            data: revenueData,
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.1)',
            tension: 0.1,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Jumlah Transaksi'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Pendapatan (Rp)'
                },
                grid: {
                    drawOnChartArea: false,
                },
            }
        },
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        }
    }
});

// Format currency for chart tooltips
Chart.defaults.plugins.tooltip.callbacks.label = function(context) {
    let label = context.dataset.label || '';
    if (label) {
        label += ': ';
    }
    if (context.datasetIndex === 1) { // Revenue dataset
        label += formatCurrency(context.parsed.y);
    } else {
        label += context.parsed.y;
    }
    return label;
};

// Utility functions
function formatCurrency(amount) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

function generateReport(type) {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    if (!startDate || !endDate) {
        alert('Mohon pilih tanggal mulai dan akhir terlebih dahulu!');
        return;
    }
    
    if (startDate > endDate) {
        alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir!');
        return;
    }
    
    // Here you would typically send an AJAX request to generate the report
    // For now, we'll show a simple alert
    alert(`Generating ${type} report from ${startDate} to ${endDate}...\n\nThis feature can be extended to generate PDF/Excel reports.`);
    
    // Example of how you might implement this:
    /*
    fetch('generate_report.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            type: type,
            start_date: startDate,
            end_date: endDate
        })
    })
    .then(response => response.blob())
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = `laporan_${type}_${startDate}_${endDate}.pdf`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
    });
    */
}

// Auto-refresh data every 5 minutes
setInterval(function() {
    location.reload();
}, 300000);
</script>

<?php include '../includes/admin-footer.php'; ?>
