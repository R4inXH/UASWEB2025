<?php
require_once 'config.php';
require_once 'xendit_config.php';
session_start();

if (!isset($_SESSION['transaksi_id'])) {
    header("Location: ticket.php");
    exit();
}

$transaksi_id = $_SESSION['transaksi_id'];

$sql = "SELECT t.*, p.nama_lengkap, p.email, p.telepon 
        FROM transaksi t 
        JOIN pelanggan p ON t.pelanggan_id = p.id 
        WHERE t.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $transaksi_id);
$stmt->execute();
$transaksi = $stmt->get_result()->fetch_assoc();

$sql_detail = "SELECT dt.*, w.nama_wahana 
               FROM detail_transaksi dt 
               JOIN wahana w ON dt.wahana_id = w.id 
               WHERE dt.transaksi_id = ?";
$stmt = $conn->prepare($sql_detail);
$stmt->bind_param("i", $transaksi_id);
$stmt->execute();
$detail = $stmt->get_result()->fetch_assoc();

$page_title = "Pembayaran";
include 'header.php';
?>

<style>
.payment-method-card {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.payment-method-card:hover {
    border-color: #007bff;
    box-shadow: 0 4px 8px rgba(0,123,255,0.1);
}

.payment-method-card.active {
    border-color: #007bff;
    background-color: #f8f9ff;
}

.payment-method-icon {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    font-size: 24px;
    color: white;
}

.bank-transfer { background: linear-gradient(45deg, #28a745, #20c997); }
.e-wallet { background: linear-gradient(45deg, #fd7e14, #ffc107); }
.retail-outlet { background: linear-gradient(45deg, #6f42c1, #e83e8c); }
.qr-code { background: linear-gradient(45deg, #17a2b8, #6610f2); }

.payment-option {
    display: none;
}

.payment-option.active {
    display: block;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loading-spinner {
    color: white;
    font-size: 48px;
}

.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

<header class="py-5 bg-light">
    <div class="container px-4 px-lg-5">
        <div class="row gx-4 gx-lg-5 justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="fw-bolder">Pembayaran</h1>
                <p class="lead">Selesaikan pembayaran untuk mendapatkan tiket elektronik</p>
            </div>
        </div>
    </div>
</header>

<section class="page-section">
    <div class="container px-4 px-lg-5">
        <div class="row gx-4 gx-lg-5 justify-content-center">
            <div class="col-lg-8">
                <div class="card mb-3 border-warning">
                    <div class="card-body">
                        <small class="text-muted">
                            DEBUG: Transaksi ID: <?php echo $transaksi_id; ?> | 
                            Total: <?php echo number_format($transaksi['total_harga'], 0, ',', '.'); ?>
                        </small>
                    </div>
                </div>
                
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-receipt me-2"></i>Detail Pesanan</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Kode Transaksi:</strong><br>
                                <span class="text-primary"><?php echo htmlspecialchars($transaksi['kode_transaksi']); ?></span>
                            </div>
                            <div class="col-md-6">
                                <strong>Tanggal Kunjungan:</strong><br>
                                <?php echo date('d F Y', strtotime($transaksi['tanggal_kunjungan'])); ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Nama:</strong><br>
                                <?php echo htmlspecialchars($transaksi['nama_lengkap']); ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Email:</strong><br>
                                <?php echo htmlspecialchars($transaksi['email']); ?>
                            </div>
                        </div>
                        <hr>
                        <h5><i class="bi bi-ticket-perforated me-2"></i>Detail Tiket</h5>
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($detail['nama_wahana']); ?></strong></td>
                                    <td class="text-end"><?php echo $detail['jumlah_tiket']; ?> tiket</td>
                                </tr>
                                <tr>
                                    <td>Harga per tiket</td>
                                    <td class="text-end">Rp <?php echo number_format($detail['harga_satuan'], 0, ',', '.'); ?></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="table-primary">
                                    <th>Total Pembayaran</th>
                                    <th class="text-end fs-4">Rp <?php echo number_format($transaksi['total_harga'], 0, ',', '.'); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-credit-card me-2"></i>Metode Pembayaran</h4>
                    </div>
                    <div class="card-body">
                        <form id="paymentForm">
                            <input type="hidden" name="transaksi_id" value="<?php echo $transaksi_id; ?>">
                            
                            <div class="mb-4">
                                <div class="payment-method-card" data-method="bank_transfer">
                                    <div class="d-flex align-items-center">
                                        <div class="payment-method-icon bank-transfer">
                                            <i class="bi bi-bank"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-1">Transfer Bank</h5>
                                            <p class="text-muted mb-0">BCA, BNI, BRI, Mandiri, Permata</p>
                                        </div>
                                        <div class="ms-auto">
                                            <i class="bi bi-chevron-right"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="payment-method-card" data-method="e_wallet">
                                    <div class="d-flex align-items-center">
                                        <div class="payment-method-icon e-wallet">
                                            <i class="bi bi-wallet2"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-1">E-Wallet</h5>
                                            <p class="text-muted mb-0">OVO, DANA, LinkAja, ShopeePay, GoPay</p>
                                        </div>
                                        <div class="ms-auto">
                                            <i class="bi bi-chevron-right"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="payment-method-card" data-method="retail_outlet">
                                    <div class="d-flex align-items-center">
                                        <div class="payment-method-icon retail-outlet">
                                            <i class="bi bi-shop"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-1">Retail Outlet</h5>
                                            <p class="text-muted mb-0">Alfamart, Indomaret</p>
                                        </div>
                                        <div class="ms-auto">
                                            <i class="bi bi-chevron-right"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="payment-method-card" data-method="qr_code">
                                    <div class="d-flex align-items-center">
                                        <div class="payment-method-icon qr-code">
                                            <i class="bi bi-qr-code"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-1">QR Code</h5>
                                            <p class="text-muted mb-0">QRIS - Semua aplikasi e-wallet</p>
                                        </div>
                                        <div class="ms-auto">
                                            <i class="bi bi-chevron-right"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="bank_transfer" class="payment-option">
                                <h6>Pilih Bank:</h6>
                                <div class="row">
                                    <?php foreach(XenditConfig::PAYMENT_METHODS['BANK_TRANSFER'] as $bank): ?>
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="bank_code" value="<?php echo $bank; ?>" id="<?php echo $bank; ?>">
                                            <label class="form-check-label" for="<?php echo $bank; ?>">
                                                <?php echo $bank; ?>
                                            </label>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div id="e_wallet" class="payment-option">
                                <h6>Pilih E-Wallet:</h6>
                                <div class="row">
                                    <?php foreach(XenditConfig::PAYMENT_METHODS['E_WALLET'] as $wallet): ?>
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="ewallet_type" value="<?php echo $wallet; ?>" id="<?php echo $wallet; ?>">
                                            <label class="form-check-label" for="<?php echo $wallet; ?>">
                                                <?php echo $wallet; ?>
                                            </label>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div id="retail_outlet" class="payment-option">
                                <h6>Pilih Retail Outlet:</h6>
                                <div class="row">
                                    <?php foreach(XenditConfig::PAYMENT_METHODS['RETAIL_OUTLET'] as $outlet): ?>
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="retail_outlet_name" value="<?php echo $outlet; ?>" id="<?php echo $outlet; ?>">
                                            <label class="form-check-label" for="<?php echo $outlet; ?>">
                                                <?php echo $outlet; ?>
                                            </label>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div id="qr_code" class="payment-option">
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Anda akan mendapatkan QR Code QRIS yang dapat dibayar dengan semua aplikasi e-wallet
                                </div>
                                <input type="hidden" name="qr_type" value="QRIS">
                            </div>
                            
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg" id="payButton" disabled>
                                    <i class="bi bi-credit-card me-2"></i>Bayar Sekarang
                                </button>
                                <a href="ticket.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="loading-overlay" id="loadingOverlay">
    <div class="text-center">
        <div class="loading-spinner">
            <i class="bi bi-arrow-clockwise spin"></i>
        </div>
        <p class="text-white mt-3">Memproses pembayaran...</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Payment page loaded');
    
    const paymentCards = document.querySelectorAll('.payment-method-card');
    const paymentOptions = document.querySelectorAll('.payment-option');
    const payButton = document.getElementById('payButton');
    const paymentForm = document.getElementById('paymentForm');
    const loadingOverlay = document.getElementById('loadingOverlay');
    let selectedMethod = '';
    
    console.log('Found elements:', {
        paymentCards: paymentCards.length,
        paymentOptions: paymentOptions.length,
        payButton: payButton ? 'found' : 'not found',
        paymentForm: paymentForm ? 'found' : 'not found'
    });
    
    paymentCards.forEach(card => {
        card.addEventListener('click', function() {
            console.log('Payment method clicked:', this.dataset.method);
            
            paymentCards.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            
            paymentOptions.forEach(option => option.classList.remove('active'));
            
            selectedMethod = this.dataset.method;
            const selectedOption = document.getElementById(selectedMethod);
            if (selectedOption) {
                selectedOption.classList.add('active');
                console.log('Showing payment option:', selectedMethod);
            }
            
            if (selectedMethod === 'qr_code') {
                payButton.disabled = false;
                console.log('QR Code selected - button enabled');
            } else {
                payButton.disabled = true;
                console.log('Other method selected - waiting for sub-option');
                
                const subOptions = selectedOption.querySelectorAll('input[type="radio"]');
                subOptions.forEach(option => {
                    option.addEventListener('change', function() {
                        payButton.disabled = false;
                        console.log('Sub-option selected:', this.value, '- button enabled');
                    });
                });
            }
        });
    });
    
    paymentForm.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Form submitted with method:', selectedMethod);
        
        if (!selectedMethod) {
            alert('Silakan pilih metode pembayaran');
            return;
        }
        
        loadingOverlay.style.display = 'flex';
        console.log('Loading overlay shown');
        
        const formData = new FormData(paymentForm);
        formData.append('payment_method', selectedMethod);
        
        console.log('Form data to be sent:');
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }
        
        fetch('process_payment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response received:', {
                status: response.status,
                statusText: response.statusText,
                ok: response.ok
            });
            return response.text();
        })
        .then(text => {
            console.log('Raw response text:', text);
            
            loadingOverlay.style.display = 'none';
            
            let data;
            try {
                data = JSON.parse(text);
                console.log('Parsed JSON data:', data);
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Raw text that failed to parse:', text);
                alert('Server response error: ' + text);
                return;
            }
            
            if (data.success === true) {
                console.log('Payment successful!');
                
                if (data.invoice_url) {
                    console.log('Redirecting to invoice URL:', data.invoice_url);
                    setTimeout(() => {
                        window.location.href = data.invoice_url;
                    }, 500);
                } else {
                    console.log('No invoice URL, redirecting to success page');
                    window.location.href = 'payment_success.php?id=' + data.transaksi_id;
                }
            } else if (data.success === false) {
                console.error('Payment failed:', data.message);
                alert('Error: ' + (data.message || 'Terjadi kesalahan dalam pembayaran'));
            } else {
                console.log('Checking for Xendit-like response...');
                
                if (data.invoice_url) {
                    console.log('Found invoice_url, redirecting:', data.invoice_url);
                    window.location.href = data.invoice_url;
                } else if (data.id) {
                    const xenditUrl = `https://checkout.xendit.co/web/${data.id}`;
                    console.log('Constructing Xendit URL:', xenditUrl);
                    window.location.href = xenditUrl;
                } else {
                    console.error('Unknown response format:', data);
                    alert('Unknown response format. Please contact support.');
                }
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            loadingOverlay.style.display = 'none';
            alert('Network error: ' + error.message + '. Please check your connection and try again.');
        });
    });
    
    console.log('Payment form setup complete');
});
</script>

<?php include 'footer.php'; ?>