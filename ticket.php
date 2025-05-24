<?php
require_once 'config.php';
$page_title = "Pemesanan Tiket";
include 'header.php';

$is_logged_in = isset($_SESSION['user_id']);
$user_data = [];

if ($is_logged_in) {
    $sql_user = "SELECT * FROM pelanggan WHERE id = ?";
    $stmt = $conn->prepare($sql_user);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $user_data = $stmt->get_result()->fetch_assoc();
}

$sql = "SELECT * FROM wahana WHERE status = 'aktif' ORDER BY nama_wahana";
$result = $conn->query($sql);
$wahana = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $wahana[] = $row;
    }
}
?>

<section class="page-section">
    <div class="container px-4 px-lg-5">
        <div class="row mt-5">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h4 class="mb-4 text-center">Form Pemesanan Tiket</h4>
                        
                        <?php if(!$is_logged_in): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            <a href="login.php">Login</a> untuk mempermudah pemesanan tiket berikutnya
                        </div>
                        <?php endif; ?>
                        
                        <form id="tiketForm" method="post" action="process_booking.php">
                            <?php if(!$is_logged_in): ?>
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama" name="nama" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="telepon" class="form-label">Nomor Telepon</label>
                                <input type="tel" class="form-control" id="telepon" name="telepon" required>
                            </div>
                            <?php else: ?>
                            <div class="mb-3">
                                <p><strong>Nama:</strong> <?php echo htmlspecialchars($user_data['nama_lengkap']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($user_data['email']); ?></p>
                                <p><strong>Telepon:</strong> <?php echo htmlspecialchars($user_data['telepon']); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="tanggal" class="form-label">Tanggal Kunjungan</label>
                                <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="wahana" class="form-label">Pilih Wahana</label>
                                <select class="form-select" id="wahana" name="wahana" required>
                                    <option value="">Pilih wahana</option>
                                    <?php foreach($wahana as $item): ?>
                                        <option value="<?php echo $item['id']; ?>" 
                                                data-harga="<?php echo $item['harga']; ?>">
                                            <?php echo htmlspecialchars($item['nama_wahana']); ?> - 
                                            Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title">Ringkasan Pembelian</h5>
                                        <div id="ticket-summary">
                                            <p class="text-muted">Silakan pilih wahana</p>
                                        </div>
                                        <div class="fw-bold text-end">
                                            Total: <span id="total-harga">Rp 0</span>
                                            <input type="hidden" name="total_harga" id="total_harga_input" value="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Lanjut ke Pembayaran</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", function() {
    function hitungTotal() {
        let totalHarga = 0;
        let wahanaSelect = document.getElementById("wahana");
        let summaryHtml = "";
        
        if (wahanaSelect.value) {
            let selectedOption = wahanaSelect.options[wahanaSelect.selectedIndex];
            let harga = parseInt(selectedOption.getAttribute("data-harga"));
            let namaWahana = selectedOption.text.split(" - ")[0];
            
            totalHarga = harga;
            summaryHtml = "<ul class='list-unstyled mb-0'>";
            summaryHtml += "<li>" + namaWahana + " x 1 = Rp " + harga.toLocaleString("id-ID") + "</li>";
            summaryHtml += "</ul>";
        } else {
            summaryHtml = "<p class='text-muted'>Silakan pilih wahana</p>";
        }
        
        document.getElementById("ticket-summary").innerHTML = summaryHtml;
        document.getElementById("total-harga").textContent = "Rp " + totalHarga.toLocaleString("id-ID");
        document.getElementById("total_harga_input").value = totalHarga;
    }
    
    document.getElementById("wahana").addEventListener("change", hitungTotal);
});
</script>

<?php include 'footer.php'; ?>