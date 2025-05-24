<?php
require_once 'config.php';
$page_title = "Wahana";
include 'header.php';

$sql = "SELECT * FROM wahana WHERE status = 'aktif'";
$result = $conn->query($sql);
$wahana = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $wahana[] = $row;
    }
}
?>

<style>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes zoomIn {
    from {
        opacity: 0;
        transform: scale(0.8);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.wahana-hero {
    background: linear-gradient(45deg, #0066cc, #0052a3);
    padding: 120px 0 80px;
    position: relative;
    overflow: hidden;
}

.wahana-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 200%;
    height: 100%;
    background: repeating-linear-gradient(
        45deg,
        transparent,
        transparent 20px,
        rgba(255,255,255,0.05) 20px,
        rgba(255,255,255,0.05) 40px
    );
    animation: slidePattern 20s linear infinite;
}

@keyframes slidePattern {
    from { transform: translateX(0); }
    to { transform: translateX(50%); }
}

.wahana-carousel {
    max-width: 900px;
    margin: 0 auto;
}

.wahana-carousel .carousel-item {
    height: 500px;
}

.wahana-carousel .carousel-item img {
    height: 100%;
    object-fit: cover;
    border-radius: 20px;
}

.wahana-carousel .carousel-caption {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    animation: fadeInUp 0.8s ease-out;
}

.wahana-card {
    border: none;
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.4s ease;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    background: white;
}

.wahana-card:hover {
    transform: translateY(-15px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

.wahana-image-container {
    position: relative;
    overflow: hidden;
    height: 300px;
}

.wahana-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.wahana-card:hover .wahana-image {
    transform: scale(1.1);
}

.wahana-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.7) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.wahana-card:hover .wahana-overlay {
    opacity: 1;
}

.wahana-price {
    position: absolute;
    bottom: 20px;
    left: 20px;
    color: white;
    font-size: 1.5rem;
    font-weight: bold;
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.3s ease;
}

.wahana-card:hover .wahana-price {
    opacity: 1;
    transform: translateY(0);
}

.feature-list {
    list-style: none;
    padding: 0;
}

.feature-list li {
    padding: 10px 0;
    border-bottom: 1px solid #eee;
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
}

.feature-list li:last-child {
    border-bottom: none;
}

.feature-list li:hover {
    padding-left: 10px;
    background: #f8f9fa;
}

.feature-list i {
    color: #0066cc;
    font-size: 1.2rem;
    margin-right: 15px;
}

.btn-wahana {
    padding: 12px 30px;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn-wahana::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: all 0.5s ease;
}

.btn-wahana:hover::before {
    left: 100%;
}

.filter-buttons {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-bottom: 40px;
}

.filter-btn {
    padding: 10px 25px;
    border: 2px solid #0066cc;
    background: transparent;
    color: #0066cc;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
}

.filter-btn:hover,
.filter-btn.active {
    background: #0066cc;
    color: white;
}

.animate-on-scroll {
    opacity: 0;
    transform: translateY(50px);
    transition: all 0.8s ease;
}

.animate-on-scroll.show {
    opacity: 1;
    transform: translateY(0);
}
</style>

<section class="wahana-hero">
    <div class="container px-4 px-lg-5 position-relative">
        <div class="row gx-4 gx-lg-5 justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="text-white display-4 fw-bold mb-3">Wahana Lampung Walk</h1>
                <p class="text-white-75 fs-5 mb-4">
                    Temukan berbagai wahana seru yang akan membuat liburan Anda tak terlupakan
                </p>
                <div class="d-flex gap-3 justify-content-center">
                    <a href="#wahana-list" class="btn btn-light btn-lg">
                        <i class="bi bi-grid-3x3-gap me-2"></i>Lihat Semua
                    </a>
                    <a href="ticket.php" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-ticket-perforated me-2"></i>Pesan Tiket
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="page-section bg-light">
    <div class="container px-4 px-lg-5">
        <h2 class="text-center mb-4">Wahana Pilihan</h2>
        <hr class="divider mb-5" />
        
        <div class="wahana-carousel">
            <div id="wahanaCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <?php foreach($wahana as $index => $item): ?>
                        <button type="button" data-bs-target="#wahanaCarousel" 
                                data-bs-slide-to="<?php echo $index; ?>" 
                                <?php echo $index === 0 ? 'class="active" aria-current="true"' : ''; ?> 
                                aria-label="Slide <?php echo $index + 1; ?>"></button>
                    <?php endforeach; ?>
                </div>
                <div class="carousel-inner">
                    <?php foreach($wahana as $index => $item): ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <img src="<?php echo htmlspecialchars($item['gambar']); ?>" 
                                 class="d-block w-100" 
                                 alt="<?php echo htmlspecialchars($item['nama_wahana']); ?>">
                            <div class="carousel-caption">
                                <h3 class="text-primary mb-3"><?php echo htmlspecialchars($item['nama_wahana']); ?></h3>
                                <p class="mb-3 text-black "><?php echo htmlspecialchars($item['deskripsi']); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-primary p-3">
                                        <i class="bi bi-tag-fill me-2"></i>Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?>
                                    </span>
                                    <a href="ticket.php" class="btn btn-primary btn-wahana">
                                        Pesan Sekarang <i class="bi bi-arrow-right ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#wahanaCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#wahanaCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
    </div>
</section>

<section class="page-section" id="wahana-list">
    <div class="container px-4 px-lg-5">
        <h2 class="text-center mb-4">Semua Wahana</h2>
        <hr class="divider mb-5" />
        
        <div class="filter-buttons mb-5">
            <button class="filter-btn active" data-filter="all">Semua</button>
            <button class="filter-btn" data-filter="air">Wahana Air</button>
            <button class="filter-btn" data-filter="ekstrem">Wahana Ekstrem</button>
            <button class="filter-btn" data-filter="keluarga">Wahana Keluarga</button>
        </div>
        
        <div class="row g-4">
            <?php foreach($wahana as $index => $item): ?>
                <div class="col-lg-4 col-md-6 animate-on-scroll" data-category="all">
                    <div class="wahana-card h-100">
                        <div class="wahana-image-container">
                            <img src="<?php echo htmlspecialchars($item['gambar']); ?>" 
                                 class="wahana-image" 
                                 alt="<?php echo htmlspecialchars($item['nama_wahana']); ?>">
                            <div class="wahana-overlay"></div>
                            <div class="wahana-price">
                                Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <h4 class="card-title text-primary mb-3">
                                <?php echo htmlspecialchars($item['nama_wahana']); ?>
                            </h4>
                            <p class="card-text mb-4">
                                <?php echo htmlspecialchars($item['deskripsi']); ?>
                            </p>
                            <ul class="feature-list mb-4">
                                <li>
                                    <i class="bi bi-clock-fill"></i>
                                    Jam Operasional: <?php echo date('H:i', strtotime($item['jam_buka'])); ?> - <?php echo date('H:i', strtotime($item['jam_tutup'])); ?> WIB
                                </li>
                                <li>
                                    <i class="bi bi-people-fill"></i>
                                    Cocok untuk semua usia
                                </li>
                                <li>
                                    <i class="bi bi-shield-check-fill"></i>
                                    Standar keamanan tinggi
                                </li>
                            </ul>
                            <div class="d-grid">
                                <a href="ticket.php" class="btn btn-primary btn-wahana">
                                    <i class="bi bi-ticket-perforated me-2"></i>Pesan Tiket
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="page-section bg-primary text-white">
    <div class="container px-4 px-lg-5 text-center">
        <h2 class="mb-4">Dapatkan Promo Spesial!</h2>
        <p class="mb-4">Pesan tiket online sekarang dan dapatkan diskon hingga 20%</p>
        <a href="ticket.php" class="btn btn-light btn-xl">
            Pesan Tiket Sekarang <i class="bi bi-arrow-right ms-2"></i>
        </a>
    </div>
</section>

<script>
function handleScrollAnimation() {
    const elements = document.querySelectorAll('.animate-on-scroll');
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

document.querySelectorAll('.filter-btn').forEach(button => {
    button.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        this.classList.add('active');
        
        const filter = this.getAttribute('data-filter');
        console.log('Filter:', filter);
    });
});

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
</script>

<?php include 'footer.php'; ?>