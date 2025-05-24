<?php
require_once 'config.php';
$page_title = "Hubungi Kami";
include 'header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_contact'])) {
    $nama = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $telepon = $conn->real_escape_string($_POST['phone']);
    $pesan = $conn->real_escape_string($_POST['message']);
    
    $sql = "INSERT INTO pesan_kontak (nama, email, telepon, pesan) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nama, $email, $telepon, $pesan);
    
    if ($stmt->execute()) {
        $success_message = "Terima kasih! Pesan Anda telah kami terima.";
    } else {
        $error_message = "Maaf, terjadi kesalahan. Silakan coba lagi.";
    }
}
?>

<section class="page-section" id="contact">
    <div class="container px-4 px-lg-5">
        <div class="row gx-4 gx-lg-5 justify-content-center">
            <div class="col-lg-8 col-xl-6 text-center">
                <h2 class="mt-0">Hubungi Kami</h2>
                <hr class="divider" />
                <p class="text-muted mb-5">Punya pertanyaan tentang Lampung Walk atau pemesanan tiket? Kirim pesan kepada kami!</p>
            </div>
        </div>
        
        <?php if(isset($success_message)): ?>
        <div class="row justify-content-center mb-4">
            <div class="col-lg-6">
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if(isset($error_message)): ?>
        <div class="row justify-content-center mb-4">
            <div class="col-lg-6">
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="row gx-4 gx-lg-5 justify-content-center mb-5">
            <div class="col-lg-6">
                <form id="contactForm" method="post" action="">
                    <div class="form-floating mb-3">
                        <input class="form-control" id="name" name="name" type="text" placeholder="Nama Lengkap" required />
                        <label for="name">Nama Lengkap</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input class="form-control" id="email" name="email" type="email" placeholder="Email" required />
                        <label for="email">Alamat Email</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input class="form-control" id="phone" name="phone" type="tel" placeholder="Nomor Telepon" required />
                        <label for="phone">Nomor Telepon</label>
                    </div>
                    <div class="form-floating mb-3">
                        <textarea class="form-control" id="message" name="message" placeholder="Pesan" style="height: 10rem" required></textarea>
                        <label for="message">Pesan</label>
                    </div>
                    <div class="d-grid">
                        <button class="btn btn-primary btn-xl" name="submit_contact" type="submit">Kirim Pesan</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="row gx-4 gx-lg-5 justify-content-center">
            <div class="col-lg-4 text-center mb-5 mb-lg-0">
                <i class="bi-phone fs-2 mb-3 text-muted"></i>
                <div>+62 812 3456 7890</div>
            </div>
            <div class="col-lg-4 text-center mb-5 mb-lg-0">
                <i class="bi-envelope fs-2 mb-3 text-muted"></i>
                <div>info@lampungwalk.com</div>
            </div>
            <div class="col-lg-4 text-center mb-5 mb-lg-0">
                <i class="bi-geo-alt fs-2 mb-3 text-muted"></i>
                <div>Jl. Raya Lampung Walk No. 123, Bandar Lampung</div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>