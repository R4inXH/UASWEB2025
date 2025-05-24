<?php
require_once 'config.php';
session_start();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$transaksi_id = intval($_GET['id']);

$sql = "SELECT t.*, p.nama_lengkap, p.email, p.telepon, tk.kode_tiket, tk.tanggal_kadaluarsa, 
               w.nama_wahana, w.harga, tk.status as status_tiket
        FROM transaksi t 
        JOIN pelanggan p ON t.pelanggan_id = p.id 
        JOIN tiket tk ON tk.transaksi_id = t.id
        JOIN wahana w ON tk.wahana_id = w.id
        WHERE t.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $transaksi_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: index.php");
    exit();
}

$tiket = $result->fetch_assoc();
$page_title = "Informasi Tiket";
include 'header.php';
?>

<header class="py-5 bg-light">
    <div class="container px-4 px-lg-5">
        <div class="row gx-4 gx-lg-5 justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="fw-bolder">Informasi Tiket</h1>
                <p class="lead">Detail tiket yang sudah Anda beli untuk Lampung Walk</p>
            </div>
        </div>
    </div>
</header>

<section class="page-section">
    <div class="container px-4 px-lg-5">
        <div class="row gx-4 gx-lg-5 justify-content-center">
            <div class="col-lg-8">
                <div class="card mb-5 shadow">
                    <div class="card-header bg-primary text-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">E-Ticket Lampung Walk</h4>
                            <span class="badge bg-warning text-dark"><?php echo ucfirst($tiket['status_tiket']); ?></span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <img src="assets/img/qr-code.png" alt="QR Code" style="max-width: 200px;">
                            <p class="mt-2 mb-0 text-muted small">Scan QR code ini di pintu masuk</p>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6>Detail Pengunjung:</h6>
                                <p class="mb-0"><?php echo htmlspecialchars($tiket['nama_lengkap']); ?></p>
                                <p class="mb-0"><?php echo htmlspecialchars($tiket['email']); ?></p>
                                <p class="mb-0"><?php echo htmlspecialchars($tiket['telepon']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Detail Tiket:</h6>
                                <p class="mb-0">Kode: <?php echo htmlspecialchars($tiket['kode_tiket']); ?></p>
                                <p class="mb-0">Wahana: <?php echo htmlspecialchars($tiket['nama_wahana']); ?></p>
                                <p class="mb-0">Harga: Rp <?php echo number_format($tiket['harga'], 0, ',', '.'); ?></p>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Tanggal Kunjungan:</h6>
                                <p class="mb-0"><?php echo date('d F Y', strtotime($tiket['tanggal_kunjungan'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Berlaku Hingga:</h6>
                                <p class="mb-0"><?php echo date('d F Y', strtotime($tiket['tanggal_kadaluarsa'])); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="mb-0 small text-muted">
                                <i class="bi bi-info-circle"></i> Tiket berlaku untuk satu orang
                            </p>
                            <button class="btn btn-sm btn-outline-primary" onclick="window.print();">
                                <i class="bi bi-printer"></i> Cetak
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mb-5">
                    <a href="index.php" class="btn btn-secondary me-2">
                        <i class="bi bi-house"></i> Kembali ke Beranda
                    </a>
                    <a href="wahana.php" class="btn btn-primary">
                        <i class="bi bi-building"></i> Lihat Wahana Lainnya
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>