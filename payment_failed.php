<?php
require_once 'config.php';
session_start();

$transaksi_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($transaksi_id <= 0) {
    header("Location: ticket.php");
    exit();
}

$sql = "SELECT t.*, p.nama_lengkap, p.email, p.telepon 
        FROM transaksi t 
        JOIN pelanggan p ON t.pelanggan_id = p.id 
        WHERE t.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $transaksi_id);
$stmt->execute();
$result = $stmt->get_result();
$transaksi = $result->fetch_assoc();

if (!$transaksi) {
    header("Location: ticket.php");
    exit();
}

if ($transaksi['status_pembayaran'] != 'failed') {
    $sql_update = "UPDATE transaksi SET 
                  status_pembayaran = 'failed',
                  payment_status_detail = ?,
                  updated_at = CURRENT_TIMESTAMP
                  WHERE id = ?";
    
    $status_detail = json_encode([
        'failure_reason' => 'Payment failed or cancelled by user',
        'failed_at' => date('Y-m-d H:i:s'),
        'redirect_source' => 'payment_failed_page'
    ]);
    
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $status_detail, $transaksi_id);
    $stmt_update->execute();
}

$page_title = "Pembayaran Gagal";
include 'header.php';
?>

<style>
.failed-container {
    background: linear-gradient(135deg, #ff6b6b 0%, #ffa500 100%);
    min-height: 60vh;
    display: flex;
    align-items: center;
    color: white;
}

.failed-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    color: #333;
}

.failed-icon {
    font-size: 4rem;
    color: #dc3545;
    animation: shake 0.5s;
    animation-iteration-count: 1;
}

@keyframes shake {
    0% { transform: translate(1px, 1px) rotate(0deg); }
    10% { transform: translate(-1px, -2px) rotate(-1deg); }
    20% { transform: translate(-3px, 0px) rotate(1deg); }
    30% { transform: translate(3px, 2px) rotate(0deg); }
    40% { transform: translate(1px, -1px) rotate(1deg); }
    50% { transform: translate(-1px, 2px) rotate(-1deg); }
    60% { transform: translate(-3px, 1px) rotate(0deg); }
    70% { transform: translate(3px, 1px) rotate(-1deg); }
    80% { transform: translate(-1px, -1px) rotate(1deg); }
    90% { transform: translate(1px, 2px) rotate(0deg); }
    100% { transform: translate(1px, -2px) rotate(-1deg); }
}

.retry-section {
    background: #f8f9fa;
    border-radius: 10px;
    border-left: 4px solid #007bff;
}
</style>

<section class="failed-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="failed-card p-5 text-center">
                    <div class="failed-icon mb-4">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>
                    
                    <h1 class="mb-3 text-danger">Pembayaran Gagal</h1>
                    <p class="lead mb-4">Maaf, pembayaran Anda tidak dapat diproses atau dibatalkan.</p>
                    
                    <div class="row text-start mt-4">
                        <div class="col-md-6">
                            <p><strong>Kode Transaksi:</strong><br>
                            <span class="text-primary fs-5"><?php echo htmlspecialchars($transaksi['kode_transaksi']); ?></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Total Pembayaran:</strong><br>
                            <span class="fs-5 fw-bold">Rp <?php echo number_format($transaksi['total_harga'], 0, ',', '.'); ?></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Tanggal Kunjungan:</strong><br>
                            <?php echo date('d F Y', strtotime($transaksi['tanggal_kunjungan'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong><br>
                            <span class="badge bg-danger">Gagal</span></p>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="retry-section p-4 text-start">
                        <h5><i class="bi bi-arrow-repeat me-2"></i>Apa yang bisa Anda lakukan?</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Coba lakukan pembayaran ulang</li>
                            <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Pilih metode pembayaran yang berbeda</li>
                            <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Pastikan saldo atau limit kartu mencukupi</li>
                            <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Hubungi customer service jika masalah berlanjut</li>
                        </ul>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-center mt-4">
                        <a href="payment.php" class="btn btn-primary btn-lg me-md-2">
                            <i class="bi bi-arrow-repeat me-2"></i>Coba Bayar Lagi
                        </a>
                        <a href="ticket.php" class="btn btn-outline-secondary btn-lg me-md-2">
                            <i class="bi bi-ticket me-2"></i>Pesan Tiket Baru
                        </a>
                        <a href="index.php" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-house me-2"></i>Kembali ke Beranda
                        </a>
                    </div>
                    
                    <div class="row mt-5 text-start">
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <h6><i class="bi bi-exclamation-triangle me-2"></i>Penyebab Umum Kegagalan Pembayaran:</h6>
                                <ul class="mb-0 ps-3">
                                    <li>Saldo atau limit kartu tidak mencukupi</li>
                                    <li>Pembayaran dibatalkan oleh pengguna</li>
                                    <li>Waktu pembayaran telah habis</li>
                                    <li>Gangguan pada sistem pembayaran</li>
                                    <li>Informasi pembayaran tidak valid</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <h6><i class="bi bi-headset me-2"></i>Butuh Bantuan?</h6>
                        <p class="mb-0">Hubungi customer service kami di <strong>WhatsApp: 0812-3456-7890</strong> atau email: <strong>support@lampungwalk.com</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>