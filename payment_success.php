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

if ($transaksi['status_pembayaran'] == 'processing' && !empty($transaksi['xendit_invoice_id'])) {
    require_once 'xendit_config.php';
    
    $response = XenditConfig::makeApiCall('/v2/invoices/' . $transaksi['xendit_invoice_id'], null, 'GET');
    
    if ($response['status_code'] == 200) {
        $invoice_data = $response['response'];
        $xendit_status = strtolower($invoice_data['status']);
        
        if ($xendit_status == 'paid') {
            $sql_update = "UPDATE transaksi SET 
                          status_pembayaran = 'paid',
                          payment_status_detail = ?,
                          updated_at = CURRENT_TIMESTAMP
                          WHERE id = ?";
            
            $status_detail = json_encode([
                'status_check' => true,
                'xendit_status' => $invoice_data['status'],
                'paid_amount' => $invoice_data['paid_amount'] ?? $invoice_data['amount'],
                'paid_at' => $invoice_data['paid_at'] ?? date('Y-m-d H:i:s'),
                'payment_method' => $invoice_data['payment_method'] ?? 'unknown'
            ]);
            
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("si", $status_detail, $transaksi_id);
            $stmt_update->execute();
            
            $transaksi['status_pembayaran'] = 'paid';
        }
    }
}

if ($transaksi['status_pembayaran'] == 'paid') {
    $sql_check = "SELECT COUNT(*) as ticket_count FROM tiket WHERE transaksi_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $transaksi_id);
    $stmt_check->execute();
    $ticket_check = $stmt_check->get_result()->fetch_assoc();
    
    if ($ticket_check['ticket_count'] == 0) {
        $sql_detail = "SELECT dt.*, w.nama_wahana 
                       FROM detail_transaksi dt 
                       JOIN wahana w ON dt.wahana_id = w.id 
                       WHERE dt.transaksi_id = ?";
        $stmt_detail = $conn->prepare($sql_detail);
        $stmt_detail->bind_param("i", $transaksi_id);
        $stmt_detail->execute();
        $detail_result = $stmt_detail->get_result();
        
        $tickets_created = 0;
        while ($detail = $detail_result->fetch_assoc()) {
            $jumlah_tiket = intval($detail['jumlah_tiket']);
            
            for ($i = 1; $i <= $jumlah_tiket; $i++) {
                $kode_tiket = 'LW-' . strtoupper(substr($transaksi['kode_transaksi'], -8)) . '-' . str_pad($tickets_created + 1, 3, '0', STR_PAD_LEFT);
                
                $tanggal_berlaku = $transaksi['tanggal_kunjungan'];
                
                $tanggal_kadaluarsa = date('Y-m-d', strtotime($transaksi['tanggal_kunjungan'] . ' +30 days'));
                
                $sql_insert = "INSERT INTO tiket (
                    transaksi_id, 
                    wahana_id, 
                    kode_tiket, 
                    status, 
                    tanggal_berlaku,
                    tanggal_kadaluarsa,
                    created_at
                ) VALUES (?, ?, ?, 'aktif', ?, ?, CURRENT_TIMESTAMP)";
                
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param("iisss", 
                    $transaksi_id, 
                    $detail['wahana_id'], 
                    $kode_tiket, 
                    $tanggal_berlaku,
                    $tanggal_kadaluarsa
                );
                
                if ($stmt_insert->execute()) {
                    $tickets_created++;
                } else {
                    error_log("Failed to create ticket: " . $stmt_insert->error);
                }
            }
        }
        
        error_log("Tickets created for transaction $transaksi_id: $tickets_created tickets");
        
        $_SESSION['tickets_created'] = $tickets_created;
    }
}

$sql_detail_display = "SELECT dt.*, w.nama_wahana 
                       FROM detail_transaksi dt 
                       JOIN wahana w ON dt.wahana_id = w.id 
                       WHERE dt.transaksi_id = ?";
$stmt_detail_display = $conn->prepare($sql_detail_display);
$stmt_detail_display->bind_param("i", $transaksi_id);
$stmt_detail_display->execute();
$detail_display = $stmt_detail_display->get_result();

$page_title = "Pembayaran Berhasil";
include 'header.php';
?>

<style>
.success-container {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 60vh;
    display: flex;
    align-items: center;
    color: white;
}

.success-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    color: #333;
}

.success-icon {
    font-size: 4rem;
    color: #28a745;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.ticket-preview {
    background: linear-gradient(45deg, #667eea, #764ba2);
    color: white;
    border-radius: 10px;
    position: relative;
    overflow: hidden;
}

.ticket-preview::before {
    content: '';
    position: absolute;
    top: 0;
    left: -50%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    animation: shine 3s infinite;
}

@keyframes shine {
    0% { left: -50%; }
    100% { left: 150%; }
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-weight: bold;
    display: inline-block;
}

.status-paid {
    background: #28a745;
    color: white;
}

.status-processing {
    background: #ffc107;
    color: #212529;
}

.countdown-timer {
    font-size: 1.2rem;
    font-weight: bold;
    color: #007bff;
}

.debug-info {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 1rem;
    margin: 1rem 0;
    font-family: monospace;
    font-size: 0.9rem;
}
</style>

<section class="success-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="success-card p-5 text-center">
                    <div class="success-icon mb-4">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    
                    <h1 class="mb-3">
                        <?php if ($transaksi['status_pembayaran'] == 'paid'): ?>
                            🎉 Pembayaran Berhasil!
                        <?php else: ?>
                            ⏳ Menunggu Konfirmasi Pembayaran
                        <?php endif; ?>
                    </h1>
                    
                    <div class="status-badge <?php echo $transaksi['status_pembayaran'] == 'paid' ? 'status-paid' : 'status-processing'; ?> mb-4">
                        Status: <?php echo ucfirst($transaksi['status_pembayaran']); ?>
                    </div>
                    
                    <?php if (isset($_SESSION['tickets_created']) && $_SESSION['tickets_created'] > 0): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Debug:</strong> <?php echo $_SESSION['tickets_created']; ?> tiket berhasil dibuat!
                        </div>
                        <?php unset($_SESSION['tickets_created']); ?>
                    <?php endif; ?>
                    
                    <div class="row text-start mt-4">
                        <div class="col-md-6">
                            <p><strong>Kode Transaksi:</strong><br>
                            <span class="text-primary fs-5"><?php echo htmlspecialchars($transaksi['kode_transaksi']); ?></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Tanggal Kunjungan:</strong><br>
                            <?php echo date('d F Y', strtotime($transaksi['tanggal_kunjungan'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Nama:</strong><br>
                            <?php echo htmlspecialchars($transaksi['nama_lengkap']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Total Pembayaran:</strong><br>
                            <span class="fs-5 fw-bold text-success">Rp <?php echo number_format($transaksi['total_harga'], 0, ',', '.'); ?></span></p>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h4 class="mb-3"><i class="bi bi-ticket-perforated me-2"></i>Detail Tiket</h4>
                    <div class="row">
                        <?php while ($detail = $detail_display->fetch_assoc()): ?>
                        <div class="col-md-6 mb-3">
                            <div class="ticket-preview p-3">
                                <h5 class="mb-2"><?php echo htmlspecialchars($detail['nama_wahana']); ?></h5>
                                <p class="mb-1">Jumlah: <?php echo $detail['jumlah_tiket']; ?> tiket</p>
                                <p class="mb-0">Harga: Rp <?php echo number_format($detail['harga_satuan'], 0, ',', '.'); ?> /tiket</p>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <?php if ($transaksi['status_pembayaran'] == 'paid'): ?>
                        <div class="alert alert-success mt-4">
                            <h5><i class="bi bi-check-circle me-2"></i>Tiket Elektronik Sudah Siap!</h5>
                            <p class="mb-0">Tiket elektronik Anda telah dibuat dan siap digunakan untuk kunjungan.</p>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-center mt-4">
                            <a href="my_tickets.php" class="btn btn-success btn-lg me-md-2">
                                <i class="bi bi-ticket-perforated me-2"></i>Lihat Tiket Saya
                            </a>
                            <a href="index.php" class="btn btn-outline-primary btn-lg">
                                <i class="bi bi-house me-2"></i>Kembali ke Beranda
                            </a>
                        </div>
                        
                    <?php else: ?>
                        <div class="alert alert-warning mt-4">
                            <h5><i class="bi bi-clock me-2"></i>Menunggu Konfirmasi Pembayaran</h5>
                            <p class="mb-2">Pembayaran Anda sedang diproses. Tiket akan otomatis tersedia setelah pembayaran dikonfirmasi.</p>
                            <div class="countdown-timer" id="autoRefreshTimer">
                                Halaman akan refresh otomatis dalam <span id="countdown">30</span> detik
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-center mt-4">
                            <button onclick="location.reload()" class="btn btn-primary btn-lg me-md-2">
                                <i class="bi bi-arrow-clockwise me-2"></i>Refresh Status
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-house me-2"></i>Kembali ke Beranda
                            </a>
                        </div>
                        
                        <script>
                        let countdown = 30;
                        const countdownElement = document.getElementById('countdown');
                        
                        const timer = setInterval(function() {
                            countdown--;
                            countdownElement.textContent = countdown;
                            
                            if (countdown <= 0) {
                                clearInterval(timer);
                                location.reload();
                            }
                        }, 1000);
                        </script>
                        
                    <?php endif; ?>
                    
                    <div class="row mt-5 text-start">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6><i class="bi bi-info-circle me-2"></i>Informasi Penting:</h6>
                                <ul class="mb-0 ps-3">
                                    <li>Tiket berlaku untuk tanggal kunjungan yang telah dipilih</li>
                                    <li>Tunjukkan tiket elektronik atau kode tiket saat masuk wahana</li>
                                    <li>Tiket tidak dapat dikembalikan atau ditukar</li>
                                    <li>Simpan kode transaksi untuk referensi</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="debug-info text-start mt-3" style="display: none;" id="debugInfo">
                        <strong>Debug Information:</strong><br>
                        Transaction ID: <?php echo $transaksi_id; ?><br>
                        Status: <?php echo $transaksi['status_pembayaran']; ?><br>
                        Session Data: <?php echo json_encode($_SESSION); ?>
                    </div>
                    
                    <button class="btn btn-sm btn-outline-secondary mt-2" onclick="document.getElementById('debugInfo').style.display = document.getElementById('debugInfo').style.display === 'none' ? 'block' : 'none';">
                        Toggle Debug Info
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>