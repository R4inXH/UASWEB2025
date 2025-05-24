<?php
require_once 'config.php';
session_start();

error_log("My Tickets - Session data: " . print_r($_SESSION, true));

$pelanggan_id = null;

if (isset($_SESSION['pelanggan_id'])) {
    $pelanggan_id = $_SESSION['pelanggan_id'];
} elseif (isset($_SESSION['user_id'])) {
    $pelanggan_id = $_SESSION['user_id'];
} elseif (isset($_SESSION['customer_id'])) {
    $pelanggan_id = $_SESSION['customer_id'];
}

if (!$pelanggan_id && isset($_SESSION['transaksi_id'])) {
    $sql_get_customer = "SELECT pelanggan_id FROM transaksi WHERE id = ?";
    $stmt = $conn->prepare($sql_get_customer);
    $stmt->bind_param("i", $_SESSION['transaksi_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $pelanggan_id = $row['pelanggan_id'];
        $_SESSION['pelanggan_id'] = $pelanggan_id; 
    }
}

$show_all_recent = !$pelanggan_id;

$page_title = "Tiket Saya";
include 'header.php';

if ($pelanggan_id) {
    $sql = "SELECT 
                t.id as transaksi_id,
                t.kode_transaksi,
                t.tanggal_kunjungan,
                t.total_harga,
                t.status_pembayaran,
                t.created_at as tanggal_pembelian,
                tk.id as tiket_id,
                tk.kode_tiket,
                tk.status as status_tiket,
                tk.tanggal_kadaluarsa,
                tk.tanggal_digunakan,
                w.nama_wahana,
                w.harga,
                w.deskripsi as deskripsi_wahana,
                p.nama_lengkap,
                p.email,
                p.telepon
            FROM transaksi t 
            LEFT JOIN tiket tk ON tk.transaksi_id = t.id
            LEFT JOIN wahana w ON tk.wahana_id = w.id
            JOIN pelanggan p ON t.pelanggan_id = p.id
            WHERE t.pelanggan_id = ?
            ORDER BY t.created_at DESC, tk.id ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $pelanggan_id);
} else {
    $sql = "SELECT 
                t.id as transaksi_id,
                t.kode_transaksi,
                t.tanggal_kunjungan,
                t.total_harga,
                t.status_pembayaran,
                t.created_at as tanggal_pembelian,
                tk.id as tiket_id,
                tk.kode_tiket,
                tk.status as status_tiket,
                tk.tanggal_kadaluarsa,
                tk.tanggal_digunakan,
                w.nama_wahana,
                w.harga,
                w.deskripsi as deskripsi_wahana,
                p.nama_lengkap,
                p.email,
                p.telepon
            FROM transaksi t 
            LEFT JOIN tiket tk ON tk.transaksi_id = t.id
            LEFT JOIN wahana w ON tk.wahana_id = w.id
            JOIN pelanggan p ON t.pelanggan_id = p.id
            ORDER BY t.created_at DESC, tk.id ASC
            LIMIT 20";
    
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

$transactions = [];
while ($row = $result->fetch_assoc()) {
    $transaksi_id = $row['transaksi_id'];
    
    if (!isset($transactions[$transaksi_id])) {
        $transactions[$transaksi_id] = [
            'info' => $row,
            'tickets' => []
        ];
    }
    
    if ($row['tiket_id']) {
        $transactions[$transaksi_id]['tickets'][] = $row;
    }
}
?>

<style>
.transaction-card {
    border: none;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
    margin-bottom: 2rem;
}

.transaction-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-weight: bold;
    font-size: 0.875rem;
}

.status-paid { background: #d4edda; color: #155724; }
.status-processing { background: #fff3cd; color: #856404; }
.status-failed { background: #f8d7da; color: #721c24; }
.status-expired { background: #e2e3e5; color: #383d41; }

.ticket-status-aktif { background: #d1ecf1; color: #0c5460; }
.ticket-status-terpakai { background: #f8d7da; color: #721c24; }
.ticket-status-kadaluarsa { background: #e2e3e5; color: #383d41; }

.ticket-card {
    background: linear-gradient(45deg, #667eea, #764ba2);
    color: white;
    border-radius: 15px;
    position: relative;
    overflow: hidden;
    margin-bottom: 1rem;
}

.ticket-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -50%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
    animation: shine 3s infinite;
}

@keyframes shine {
    0% { left: -50%; }
    100% { left: 150%; }
}

.ticket-code {
    font-family: 'Courier New', monospace;
    font-size: 1.2rem;
    font-weight: bold;
    letter-spacing: 2px;
}

.qr-placeholder {
    width: 80px;
    height: 80px;
    background: white;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
    font-size: 0.8rem;
    text-align: center;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6c757d;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}
</style>

<section class="page-section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>
                        <i class="bi bi-ticket-perforated me-2"></i>
                        <?php echo $show_all_recent ? 'Tiket Terbaru (Development Mode)' : 'Tiket Saya'; ?>
                    </h2>
                    <a href="ticket.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Pesan Tiket Baru
                    </a>
                </div>
                
                <?php if ($show_all_recent): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Development Mode:</strong> Menampilkan tiket terbaru karena tidak ada session pelanggan yang aktif.
                    Dalam mode production, pastikan session pelanggan tersimpan dengan benar.
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($transactions)): ?>
            <?php foreach ($transactions as $transaksi_id => $data): ?>
                <?php $transaction = $data['info']; $tickets = $data['tickets']; ?>
                <div class="transaction-card card">
                    <div class="card-header bg-white border-bottom">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-1">
                                    <i class="bi bi-receipt me-2"></i>
                                    <?php echo htmlspecialchars($transaction['kode_transaksi']); ?>
                                </h5>
                                <small class="text-muted">
                                    <i class="bi bi-calendar me-1"></i>
                                    Dibeli: <?php echo date('d M Y, H:i', strtotime($transaction['tanggal_pembelian'])); ?>
                                    | 
                                    <i class="bi bi-geo-alt me-1"></i>
                                    Kunjungan: <?php echo date('d M Y', strtotime($transaction['tanggal_kunjungan'])); ?>
                                </small>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <div class="status-badge status-<?php echo $transaction['status_pembayaran']; ?> mb-2">
                                    <?php 
                                    switch($transaction['status_pembayaran']) {
                                        case 'paid': echo '✅ Lunas'; break;
                                        case 'processing': echo '⏳ Proses'; break;
                                        case 'failed': echo '❌ Gagal'; break;
                                        case 'expired': echo '⏰ Kadaluarsa'; break;
                                        default: echo ucfirst($transaction['status_pembayaran']);
                                    }
                                    ?>
                                </div>
                                <div class="fw-bold text-success">
                                    Rp <?php echo number_format($transaction['total_harga'], 0, ',', '.'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <?php if (!empty($tickets)): ?>
                            <h6 class="mb-3">
                                <i class="bi bi-ticket-detailed me-2"></i>
                                Tiket Elektronik (<?php echo count($tickets); ?> tiket)
                            </h6>
                            
                            <div class="row">
                                <?php foreach ($tickets as $ticket): ?>
                                <div class="col-lg-6 mb-3">
                                    <div class="ticket-card p-3">
                                        <div class="row align-items-center">
                                            <div class="col-8">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($ticket['nama_wahana']); ?></h6>
                                                <div class="ticket-code mb-2">
                                                    <?php echo htmlspecialchars($ticket['kode_tiket']); ?>
                                                </div>
                                                <div class="small">
                                                    <div class="status-badge ticket-status-<?php echo $ticket['status_tiket']; ?> d-inline-block mb-1">
                                                        <?php 
                                                        switch($ticket['status_tiket']) {
                                                            case 'aktif': echo '✅ Aktif'; break;
                                                            case 'terpakai': echo '✅ Terpakai'; break;
                                                            case 'kadaluarsa': echo '⏰ Kadaluarsa'; break;
                                                            default: echo ucfirst($ticket['status_tiket']);
                                                        }
                                                        ?>
                                                    </div>
                                                    <br>
                                                    <i class="bi bi-calendar-x me-1"></i>
                                                    Berlaku s/d: <?php echo date('d M Y', strtotime($ticket['tanggal_kadaluarsa'])); ?>
                                                    
                                                    <?php if ($ticket['tanggal_digunakan']): ?>
                                                    <br>
                                                    <i class="bi bi-check-circle me-1"></i>
                                                    Digunakan: <?php echo date('d M Y, H:i', strtotime($ticket['tanggal_digunakan'])); ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="col-4 text-center">
                                                <div class="qr-placeholder">
                                                    <div>
                                                        <i class="bi bi-qr-code"></i><br>
                                                        QR Code
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <?php if ($transaction['status_pembayaran'] == 'paid'): ?>
                                    Tiket sedang diproses. Refresh halaman ini dalam beberapa saat.
                                <?php else: ?>
                                    Tiket akan tersedia setelah pembayaran berhasil.
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="d-flex gap-2 flex-wrap">
                                    <?php if ($transaction['status_pembayaran'] == 'processing'): ?>
                                        <a href="payment_success.php?id=<?php echo $transaction['transaksi_id']; ?>" 
                                           class="btn btn-warning btn-sm">
                                            <i class="bi bi-arrow-clockwise me-1"></i>Cek Status Pembayaran
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($transaction['status_pembayaran'] == 'failed'): ?>
                                        <a href="payment.php" class="btn btn-primary btn-sm">
                                            <i class="bi bi-arrow-repeat me-1"></i>Bayar Ulang
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($tickets)): ?>
                                        <button class="btn btn-outline-primary btn-sm" 
                                                onclick="window.print()">
                                            <i class="bi bi-printer me-1"></i>Cetak Tiket
                                        </button>
                                        
                                        <button class="btn btn-outline-success btn-sm"
                                                onclick="shareTicket('<?php echo $transaction['kode_transaksi']; ?>')">
                                            <i class="bi bi-share me-1"></i>Bagikan
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-ticket-perforated"></i>
                <h4>Belum Ada Tiket</h4>
                <p class="lead mb-4">Anda belum memiliki tiket. Pesan tiket wahana favorit Anda sekarang!</p>
                <a href="ticket.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-circle me-2"></i>Pesan Tiket Sekarang
                </a>
            </div>
        <?php endif; ?>
        
        <div class="row mt-5">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body">
                        <h5><i class="bi bi-question-circle me-2"></i>Bantuan</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li><i class="bi bi-check text-success me-2"></i>Tunjukkan kode tiket saat masuk wahana</li>
                                    <li><i class="bi bi-check text-success me-2"></i>Tiket berlaku sesuai tanggal kunjungan yang dipilih</li>
                                    <li><i class="bi bi-check text-success me-2"></i>Simpan screenshot tiket sebagai backup</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li><i class="bi bi-info text-primary me-2"></i>Tiket tidak dapat dikembalikan</li>
                                    <li><i class="bi bi-info text-primary me-2"></i>Hubungi CS jika ada masalah: 0812-3456-7890</li>
                                    <li><i class="bi bi-info text-primary me-2"></i>Email: support@lampungwalk.com</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function shareTicket(kodeTransaksi) {
    if (navigator.share) {
        navigator.share({
            title: 'Tiket Lampung Walk',
            text: 'Tiket saya untuk Lampung Walk - ' + kodeTransaksi,
            url: window.location.href
        });
    } else {
        const url = window.location.href;
        navigator.clipboard.writeText(url).then(() => {
            alert('Link tiket telah disalin ke clipboard!');
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const processingTransactions = document.querySelectorAll('.status-processing');
    
    if (processingTransactions.length > 0) {
        setTimeout(() => {
            location.reload();
        }, 30000);
        
        let countdown = 30;
        const countdownElement = document.createElement('div');
        countdownElement.className = 'alert alert-info mt-3';
        countdownElement.innerHTML = '<i class="bi bi-clock me-2"></i>Halaman akan refresh otomatis dalam <span id="countdown">30</span> detik untuk update status pembayaran.';
        
        document.querySelector('.container').appendChild(countdownElement);
        
        const timer = setInterval(() => {
            countdown--;
            const elem = document.getElementById('countdown');
            if (elem) {
                elem.textContent = countdown;
            }
            
            if (countdown <= 0) {
                clearInterval(timer);
            }
        }, 1000);
    }
});
</script>

<?php include 'footer.php'; ?>