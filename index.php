<?php
require_once 'config.php';
$page_title = "Beranda";
include 'header.php';

$sql = "SELECT * FROM wahana WHERE status = 'aktif' LIMIT 3";
$result = $conn->query($sql);
$wahana_unggulan = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $wahana_unggulan[] = $row;
    }
}
?>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes slideIn {
    from { transform: translateX(-100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.masthead {
    height: 100vh;
    position: relative;
    overflow: hidden;
}

.masthead::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, rgba(0,0,0,0.7), rgba(0,0,0,0.3));
    z-index: 1;
}

.masthead .container {
    position: relative;
    z-index: 2;
}

.hero-title {
    animation: fadeIn 1s ease-out;
    font-size: 3.5rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
}

.hero-subtitle {
    animation: fadeIn 1s ease-out 0.5s both;
}

.hero-button {
    animation: fadeIn 1s ease-out 1s both;
    transition: all 0.3s ease;
}

.hero-button:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
}

.feature-card {
    transition: all 0.3s ease;
    border: none;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.feature-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.2);
}

.carousel-item img {
    height: 500px;
    object-fit: cover;
}

.carousel-caption {
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(10px);
    border-radius: 15px;
    padding: 20px;
    animation: fadeIn 0.5s ease-out;
}

.feature-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 2rem;
    background: linear-gradient(45deg, #4e73df, #224abe);
    color: white;
    transition: all 0.3s ease;
}

.feature-icon:hover {
    transform: rotate(360deg);
}

.page-section {
    padding: 100px 0;
}

.about-section {
    background: linear-gradient(45deg, #0066cc, #0052a3);
    position: relative;
    overflow: hidden;
}

.about-section::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: repeating-linear-gradient(
        45deg,
        transparent,
        transparent 10px,
        rgba(255,255,255,0.05) 10px,
        rgba(255,255,255,0.05) 20px
    );
    animation: slide 20s linear infinite;
}

@keyframes slide {
    from { transform: translateX(0); }
    to { transform: translateX(50px); }
}

.btn-xl {
    padding: 1.25rem 2.25rem;
    font-size: 1.1rem;
    font-weight: 700;
    border-radius: 50px;
    transition: all 0.3s ease;
}

.btn-xl:hover {
    transform: scale(1.05);
}

.stats-section {
    background: #f8f9fa;
    padding: 80px 0;
}

.stat-item {
    text-align: center;
    padding: 30px;
    border-radius: 15px;
    background: white;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.stat-item:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.stat-number {
    font-size: 3rem;
    font-weight: 700;
    color: #0066cc;
    margin-bottom: 10px;
}

.stat-text {
    font-size: 1.2rem;
    color: #666;
}

.scroll-animation {
    opacity: 0;
    transform: translateY(50px);
    transition: all 0.8s ease;
}

.scroll-animation.show {
    opacity: 1;
    transform: translateY(0);
}

.floating {
    animation: floating 3s ease-in-out infinite;
}

@keyframes floating {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
    100% { transform: translateY(0px); }
}
</style>

<header class="masthead" style="background-image: url('assets/img/studio.jpg');">
    <div class="container px-4 px-lg-5 h-100">
        <div class="row gx-4 gx-lg-5 h-100 align-items-center justify-content-center text-center">
            <div class="col-lg-8 align-self-end">
                <h1 class="text-white font-weight-bold hero-title">Selamat Datang di<br>Lampung Walk</h1>
                <hr class="divider" style="animation: fadeIn 1s ease-out 0.5s both;" />
            </div>
            <div class="col-lg-8 align-self-baseline">
                <p class="text-white mb-5 hero-subtitle" style="font-size: 1.3rem;">
                    Nikmati berbagai wahana menarik untuk keluarga di Lampung Walk. 
                    Pesan tiket online sekarang untuk pengalaman rekreasi yang tak terlupakan!
                </p>
                <a class="btn btn-light btn-xl hero-button" href="wahana.php">
                    <i class="bi bi-arrow-right-circle me-2"></i>Jelajahi Wahana
                </a>
            </div>
        </div>
    </div>
    
    <div class="position-absolute bottom-0 start-50 translate-middle-x mb-4" style="animation: floating 2s ease-in-out infinite;">
        <a href="#features" class="text-white">
            <i class="bi bi-chevron-double-down" style="font-size: 2rem;"></i>
        </a>
    </div>
</header>

<section class="page-section" id="features">
    <div class="container px-4 px-lg-5">
        <h2 class="text-center mt-0 mb-4">Mengapa Memilih Lampung Walk?</h2>
        <hr class="divider" />
        <div class="row gx-4 gx-lg-5 mt-5">
            <div class="col-lg-3 col-md-6 text-center scroll-animation">
                <div class="feature-card p-4">
                    <div class="feature-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h3 class="h4 mb-2">Aman & Nyaman</h3>
                    <p class="text-muted mb-0">Standar keamanan tinggi dengan petugas terlatih di setiap wahana</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 text-center scroll-animation">
                <div class="feature-card p-4">
                    <div class="feature-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h3 class="h4 mb-2">Untuk Semua Usia</h3>
                    <p class="text-muted mb-0">Wahana yang cocok untuk anak-anak hingga dewasa</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 text-center scroll-animation">
                <div class="feature-card p-4">
                    <div class="feature-icon">
                        <i class="bi bi-credit-card"></i>
                    </div>
                    <h3 class="h4 mb-2">Harga Terjangkau</h3>
                    <p class="text-muted mb-0">Nikmati berbagai promo dan harga spesial setiap bulannya</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 text-center scroll-animation">
                <div class="feature-card p-4">
                    <div class="feature-icon">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <h3 class="h4 mb-2">Lokasi Strategis</h3>
                    <p class="text-muted mb-0">Mudah dijangkau dari berbagai area di Bandar Lampung</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="page-section bg-light">
    <div class="container px-4 px-lg-5">
        <h2 class="text-center mt-0">Wahana Unggulan</h2>
        <hr class="divider" />
        
        <div id="highlightsCarousel" class="carousel slide mb-5" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <?php foreach($wahana_unggulan as $index => $wahana): ?>
                <button type="button" data-bs-target="#highlightsCarousel" data-bs-slide-to="<?php echo $index; ?>" 
                        class="<?php echo $index === 0 ? 'active' : ''; ?>" 
                        aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>" 
                        aria-label="Slide <?php echo $index + 1; ?>"></button>
                <?php endforeach; ?>
            </div>
            <div class="carousel-inner rounded shadow-lg">
                <?php foreach($wahana_unggulan as $index => $wahana): ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <img src="<?php echo htmlspecialchars($wahana['gambar']); ?>" 
                         class="d-block w-100" 
                         alt="<?php echo htmlspecialchars($wahana['nama_wahana']); ?>">
                    <div class="carousel-caption d-md-block">
                        <h4 class="text-primary mb-3"><?php echo htmlspecialchars($wahana['nama_wahana']); ?></h4>
                        <p class="mb-3 text-black"><?php echo htmlspecialchars($wahana['deskripsi']); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-primary p-2">
                                <i class="bi bi-tag-fill me-2"></i>Rp <?php echo number_format($wahana['harga'], 0, ',', '.'); ?>
                            </span>
                            <a href="ticket.php" class="btn btn-primary">Pesan Sekarang</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#highlightsCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#highlightsCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
</section>

<section class="stats-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3">
                <div class="stat-item">
                    <div class="stat-number" data-target="3">0</div>
                    <div class="stat-text">Wahana Seru</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-item">
                    <div class="stat-number" data-target="200">0</div>
                    <div class="stat-text">Pengunjung/Hari</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-item">
                    <div class="stat-number" data-target="98">0</div>
                    <div class="stat-text">% Kepuasan</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-item">
                    <div class="stat-number" data-target="2">0</div>
                    <div class="stat-text">Tahun Pengalaman</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="page-section about-section">
    <div class="container px-4 px-lg-5 position-relative">
        <div class="row gx-4 gx-lg-5 justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="text-white mt-0">Tentang Lampung Walk</h2>
                <hr class="divider divider-light" />
                <p class="text-white-75 mb-4" style="font-size: 1.2rem;">
                    Lampung Walk adalah destinasi wisata keluarga terbaru dan terbesar di Provinsi Lampung. 
                    Kami menawarkan berbagai wahana rekreasi yang menarik dan menyenangkan untuk semua usia.
                </p>
                <p class="text-white-75 mb-4" style="font-size: 1.2rem;">
                    Dengan fasilitas modern dan pelayanan terbaik, Lampung Walk hadir untuk memberikan pengalaman 
                    liburan yang tak terlupakan. Kunjungi wahana-wahana kami dan rasakan keseruan yang berbeda!
                </p>
                <a class="btn btn-light btn-xl" href="ticket.php">
                    <i class="bi bi-ticket-perforated me-2"></i>Pesan Tiket Sekarang!
                </a>
            </div>
        </div>
    </div>
</section>

<section class="page-section bg-dark text-white">
    <div class="container px-4 px-lg-5 text-center">
        <h2 class="mb-4">Siap untuk Petualangan Seru?</h2>
        <p class="mb-4">Dapatkan diskon spesial untuk pemesanan online!</p>
        <a class="btn btn-light btn-xl" href="ticket.php">
            Pesan Tiket Online <i class="bi bi-arrow-right ms-2"></i>
        </a>
    </div>
</section>

<script>
function handleScrollAnimation() {
    const elements = document.querySelectorAll('.scroll-animation');
    elements.forEach(element => {
        const elementTop = element.getBoundingClientRect().top;
        const elementBottom = element.getBoundingClientRect().bottom;
        
        if (elementTop < window.innerHeight - 100 && elementBottom > 0) {
            element.classList.add('show');
        }
    });
}

window.addEventListener('scroll', handleScrollAnimation);
window.addEventListener('load', handleScrollAnimation);

function animateCounter() {
    const counters = document.querySelectorAll('.stat-number');
    
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        const duration = 2000; // 2 seconds
        const step = target / (duration / 16); // 60 FPS
        let current = 0;
        
        const updateCounter = () => {
            current += step;
            if (current < target) {
                counter.textContent = Math.floor(current);
                requestAnimationFrame(updateCounter);
            } else {
                counter.textContent = target;
            }
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    updateCounter();
                    observer.unobserve(entry.target);
                }
            });
        });
        
        observer.observe(counter);
    });
}

document.addEventListener('DOMContentLoaded', animateCounter);

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});
</script>

<?php include 'footer.php'; ?>