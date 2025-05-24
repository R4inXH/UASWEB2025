<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit();
}

$sql_pengunjung = "SELECT COUNT(*) as total FROM pelanggan";
$result_pengunjung = $conn->query($sql_pengunjung);
$total_pengunjung = $result_pengunjung->fetch_assoc()['total'];

$sql_transaksi = "SELECT COUNT(*) as total, SUM(total_harga) as pendapatan FROM transaksi WHERE status_pembayaran = 'paid'";
$result_transaksi = $conn->query($sql_transaksi);
$data_transaksi = $result_transaksi->fetch_assoc();
$total_transaksi = $data_transaksi['total'];
$total_pendapatan = $data_transaksi['pendapatan'] ?? 0;

$sql_tiket = "SELECT COUNT(*) as total FROM tiket WHERE status = 'aktif'";
$result_tiket = $conn->query($sql_tiket);
$total_tiket_aktif = $result_tiket->fetch_assoc()['total'];

$sql_wahana_populer = "SELECT w.nama_wahana, COUNT(dt.id) as jumlah_tiket 
                       FROM wahana w 
                       LEFT JOIN detail_transaksi dt ON w.id = dt.wahana_id 
                       LEFT JOIN transaksi t ON dt.transaksi_id = t.id 
                       WHERE t.status_pembayaran = 'paid' 
                       GROUP BY w.id, w.nama_wahana 
                       ORDER BY jumlah_tiket DESC 
                       LIMIT 5";
$result_wahana_populer = $conn->query($sql_wahana_populer);

$sql_transaksi_terbaru = "SELECT t.*, p.nama_lengkap, w.nama_wahana 
                          FROM transaksi t 
                          JOIN pelanggan p ON t.pelanggan_id = p.id 
                          JOIN detail_transaksi dt ON t.id = dt.transaksi_id 
                          JOIN wahana w ON dt.wahana_id = w.id 
                          ORDER BY t.created_at DESC 
                          LIMIT 10";
$result_transaksi_terbaru = $conn->query($sql_transaksi_terbaru);

$sql_pendapatan_bulanan = "SELECT 
                            DATE_FORMAT(tanggal_pembelian, '%Y-%m') as bulan,
                            SUM(total_harga) as pendapatan 
                          FROM transaksi 
                          WHERE status_pembayaran = 'paid' 
                          AND tanggal_pembelian >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                          GROUP BY DATE_FORMAT(tanggal_pembelian, '%Y-%m')
                          ORDER BY bulan ASC";
$result_pendapatan_bulanan = $conn->query($sql_pendapatan_bulanan);

$page_title = "Dashboard Admin";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Dashboard Admin - Lampung Walk" />
    <meta name="author" content="Lampung Walk" />
    <title><?php echo $page_title; ?> - Lampung Walk</title>
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Merriweather+Sans:400,700" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic" rel="stylesheet" type="text/css" />
    <link href="css/styles.css" rel="stylesheet" />
    <style>
    .dashboard-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    .dashboard-card:hover {
        transform: translateY(-5px);
    }
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
    }
    .bg-gradient-primary {
        background: linear-gradient(45deg, #4e73df, #224abe);
    }
    .bg-gradient-success {
        background: linear-gradient(45deg, #1cc88a, #13855c);
    }
    .bg-gradient-info {
        background: linear-gradient(45deg, #36b9cc, #258391);
    }
    .bg-gradient-warning {
        background: linear-gradient(45deg, #f6c23e, #dda20a);
    }
    .chart-container {
        position: relative;
        height: 300px;
    }
    body {
        background-color: #f8f9fa;
    }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top py-3" id="mainNav">
        <div class="container px-4 px-lg-5">
            <a class="navbar-brand" href="index.php">Lampung Walk</a>
            <button class="navbar-toggler navbar-toggler-right" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav ms-auto my-2 my-lg-0">
                    <li class="nav-item"><a class="nav-link" href="admin.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_wahana.php">Wahana</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_users.php">Pengguna</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_transactions.php">Transaksi</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="index.php">Lihat Website</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="bg-primary bg-gradient text-white py-5" style="margin-top: 72px;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="display-4 fw-bold">Dashboard Admin</h1>
                    <p class="lead">Selamat datang di panel administrasi Lampung Walk</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Login sebagai: <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                    <p class="mb-0"><?php echo date('l, d F Y'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5">
        <div class="row g-4 mb-5">
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="stat-icon bg-gradient-primary">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                            </div>
                            <div class="col">
                                <h6 class="text-muted text-uppercase mb-1">Total Pengunjung</h6>
                                <h2 class="mb-0"><?php echo number_format($total_pengunjung); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="stat-icon bg-gradient-success">
                                    <i class="bi bi-cart-check-fill"></i>
                                </div>
                            </div>
                            <div class="col">
                                <h6 class="text-muted text-uppercase mb-1">Total Transaksi</h6>
                                <h2 class="mb-0"><?php echo number_format($total_transaksi); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="stat-icon bg-gradient-info">
                                    <i class="bi bi-currency-dollar"></i>
                                </div>
                            </div>
                            <div class="col">
                                <h6 class="text-muted text-uppercase mb-1">Total Pendapatan</h6>
                                <h2 class="mb-0">Rp <?php echo number_format($total_pendapatan); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="stat-icon bg-gradient-warning">
                                    <i class="bi bi-ticket-detailed-fill"></i>
                                </div>
                            </div>
                            <div class="col">
                                <h6 class="text-muted text-uppercase mb-1">Tiket Aktif</h6>
                                <h2 class="mb-0"><?php echo number_format($total_tiket_aktif); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-4 mb-5">
            <div class="col-lg-8">
                <div class="card dashboard-card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Pendapatan 6 Bulan Terakhir</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="pendapatanChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card dashboard-card">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Wahana Terpopuler</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="wahanaChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card dashboard-card">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Transaksi Terbaru</h5>
                        <a href="admin_transactions.php" class="btn btn-sm btn-primary">Lihat Semua</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID Transaksi</th>
                                        <th>Pelanggan</th>
                                        <th>Wahana</th>
                                        <th>Tanggal</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($transaksi = $result_transaksi_terbaru->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($transaksi['kode_transaksi']); ?></td>
                                        <td><?php echo htmlspecialchars($transaksi['nama_lengkap']); ?></td>
                                        <td><?php echo htmlspecialchars($transaksi['nama_wahana']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($transaksi['tanggal_pembelian'])); ?></td>
                                        <td>Rp <?php echo number_format($transaksi['total_harga']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $transaksi['status_pembayaran'] == 'paid' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($transaksi['status_pembayaran']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-12">
                <h4 class="mb-4">Aksi Cepat</h4>
            </div>
            <div class="col-md-3">
                <a href="admin_wahana.php" class="btn btn-primary btn-lg w-100 mb-3">
                    <i class="bi bi-building-add me-2"></i> Kelola Wahana
                </a>
            </div>
            <div class="col-md-3">
                <a href="admin_users.php" class="btn btn-success btn-lg w-100 mb-3">
                    <i class="bi bi-person-lines-fill me-2"></i> Kelola Pengguna
                </a>
            </div>
            <div class="col-md-3">
                <a href="admin_transactions.php" class="btn btn-info btn-lg w-100 mb-3">
                    <i class="bi bi-receipt me-2"></i> Kelola Transaksi
                </a>
            </div>
            <div class="col-md-3">
                <a href="admin_reports.php" class="btn btn-warning btn-lg w-100 mb-3">
                    <i class="bi bi-file-earmark-bar-graph me-2"></i> Laporan
                </a>
            </div>
        </div>
    </div>

    <footer class="bg-light py-5">
        <div class="container px-4 px-lg-5">
            <div class="small text-center text-muted">Copyright &copy; <?php echo date('Y'); ?> - Lampung Walk</div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    <?php
    $bulan_labels = [];
    $pendapatan_data = [];
    while($row = $result_pendapatan_bulanan->fetch_assoc()) {
        $bulan_labels[] = date('M Y', strtotime($row['bulan'].'-01'));
        $pendapatan_data[] = $row['pendapatan'];
    }
    ?>

    const ctxPendapatan = document.getElementById('pendapatanChart').getContext('2d');
    new Chart(ctxPendapatan, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($bulan_labels); ?>,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: <?php echo json_encode($pendapatan_data); ?>,
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });


    <?php
    $wahana_labels = [];
    $wahana_data = [];
    while($row = $result_wahana_populer->fetch_assoc()) {
        $wahana_labels[] = $row['nama_wahana'];
        $wahana_data[] = $row['jumlah_tiket'];
    }
    ?>


    const ctxWahana = document.getElementById('wahanaChart').getContext('2d');
    new Chart(ctxWahana, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($wahana_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($wahana_data); ?>,
                backgroundColor: [
                    '#4e73df',
                    '#1cc88a',
                    '#36b9cc',
                    '#f6c23e',
                    '#e74a3b'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    </script>
</body>
</html>