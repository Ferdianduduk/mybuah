<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$search = trim((string) ($_GET['cari'] ?? ''));
$categoryId = filter_input(INPUT_GET, 'kategori', FILTER_VALIDATE_INT) ?: null;

$categoryStatement = $pdo->query(
    'SELECT k.id_kategori, k.nama_kategori, COUNT(p.id_produk) AS jumlah_produk,
            MIN(NULLIF(p.gambar, \'\')) AS gambar_kategori
     FROM kategori k
     LEFT JOIN produk p ON p.id_kategori = k.id_kategori
     GROUP BY k.id_kategori, k.nama_kategori
     ORDER BY k.id_kategori'
);
$categories = $categoryStatement->fetchAll();

$productSql = 'SELECT p.id_produk, p.nama, p.detail, p.stok, p.gambar, p.harga, p.id_kategori, k.nama_kategori
               FROM produk p
               INNER JOIN kategori k ON k.id_kategori = p.id_kategori';
$params = [];
$conditions = [];

if ($search !== '') {
    $conditions[] = '(p.nama LIKE :search OR p.detail LIKE :search)';
    $params['search'] = '%' . $search . '%';
}
if ($categoryId !== null) {
    $conditions[] = 'p.id_kategori = :category_id';
    $params['category_id'] = $categoryId;
}
if ($conditions !== []) {
    $productSql .= ' WHERE ' . implode(' AND ', $conditions);
}
$productSql .= ' ORDER BY p.id_produk DESC';
$productStatement = $pdo->prepare($productSql);
$productStatement->execute($params);
$products = $productStatement->fetchAll();

$stmtTerbaru = $pdo->query(
    'SELECT p.id_produk, p.nama, p.detail, p.stok, p.gambar, p.harga, k.nama_kategori
     FROM produk p
     INNER JOIN kategori k ON k.id_kategori = p.id_kategori
     ORDER BY p.id_produk DESC
     LIMIT 20'
);
$produkTerbaru = $stmtTerbaru->fetchAll();

$heroStatement = $pdo->query(
    "SELECT * FROM banner
     WHERE aktif = 1 AND tipe = 'hero'
     ORDER BY posisi ASC"
);
$heroBanners = $heroStatement->fetchAll();

$bannerStatement = $pdo->query(
    "SELECT * FROM banner
     WHERE aktif = 1 AND tipe = 'kotak'
     ORDER BY posisi ASC
     LIMIT 4"
);
$banners = $bannerStatement->fetchAll();

$productImagePath = static function (?string $image): ?string {
    $filename = basename(trim((string) $image));
    return $filename !== '' ? $filename : null;
};

$pageTitle = 'MyBuah | Buah segar dari kebun lokal';
require __DIR__ . '/includes/header.php';
?>
<section class="container hero<?= count($heroBanners) > 1 ? ' hero-carousel' : '' ?>">
    <?php if ($heroBanners === []): ?>
    <article class="hero-main">
        <div class="hero-main-copy">
            <span class="hero-badge">Diskon 20%</span>
            <p class="eyebrow"><?php if (isLoggedIn()): ?>Halo, <?= e(currentUserName()) ?>! 👋<?php else: ?>Dari kebun ke pintu rumah<?php endif; ?></p>
            <h1>100% Buah &amp; Sayur Segar</h1>
            <p class="hero-copy">Gratis ongkir untuk semua pesanan, nikmati belanja mudah.</p>
            <a class="button button-primary" href="#popular">Belanja Sekarang <span aria-hidden="true">→</span></a>
        </div>
        <div class="hero-visual" aria-label="Buah dan sayur segar">
            <img src="/mybuah/assets/images/hero/hero-utama.jpg" alt="Buah dan sayur segar" class="hero-main-img" onerror="this.hidden=true">
            <div class="hero-produce" aria-hidden="true"><span>🥬</span><span>🍊</span><span>🥕</span></div>
        </div>
    </article>
    <?php else: ?>
        <?php foreach ($heroBanners as $heroIndex => $hero): ?>
            <?php $heroImage = basename(trim((string) ($hero['gambar'] ?? ''))); ?>
            <?php $heroFileExists = $heroImage !== '' && is_file(__DIR__ . '/assets/images/banner/' . $heroImage); ?>
            <article class="hero-main hero-slide <?= $heroIndex === 0 ? 'is-active' : '' ?><?= $heroFileExists ? '' : ' hero-slide-no-image' ?>" style="<?= $heroFileExists ? "background-image: linear-gradient(90deg, rgba(15,64,35,.72) 0%, rgba(15,64,35,.35) 52%, rgba(15,64,35,.08) 100%), url('/mybuah/assets/images/banner/" . e(rawurlencode($heroImage)) . "');" : '' ?>">
                <div class="hero-main-copy">
                    <span class="hero-badge"><?= e($hero['subjudul'] ?: 'Promo Spesial') ?></span>
                    <p class="eyebrow">Dari kebun ke pintu rumah</p>
                    <h1><?= e($hero['judul']) ?></h1>
                    <p class="hero-copy">Buah pilihan segar langsung untukmu.</p>
                    <a class="button button-primary" href="<?= e($hero['link_tujuan'] ?: '#popular') ?>">Belanja Sekarang <span aria-hidden="true">→</span></a>
                </div>
                <?php if (!$heroFileExists): ?><div class="hero-fallback-visual" aria-label="Ilustrasi buah segar"><span>🍊</span><span>🍉</span><span>🍍</span></div><?php endif; ?>
            </article>
        <?php endforeach; ?>
        <?php if (count($heroBanners) > 1): ?><div class="hero-dots" role="tablist" aria-label="Pilihan banner utama"><?php foreach ($heroBanners as $heroIndex => $hero): ?><button type="button" class="hero-dot <?= $heroIndex === 0 ? 'is-active' : '' ?>" data-hero-index="<?= $heroIndex ?>" aria-label="Tampilkan banner <?= $heroIndex + 1 ?>"></button><?php endforeach; ?></div><?php endif; ?>
    <?php endif; ?>
</section>

<section class="container section promo-section">
    <div class="section-heading"><h2>Promo Spesial</h2></div>
    <?php if ($banners === []): ?>
        <div class="empty-state">Belum ada promo spesial saat ini.</div>
    <?php else: ?>
        <div class="promo-grid">
            <?php foreach (array_chunk($banners, 2) as $rowIndex => $bannerRow): ?>
                <div class="<?= $rowIndex % 2 === 0 ? 'baris-1' : 'baris-2' ?>">
                    <?php foreach ($bannerRow as $columnIndex => $banner): ?>
                        <?php
                        $bannerNumber = ($rowIndex * 2) + $columnIndex;
                        $bannerImage = basename(trim((string) ($banner['gambar'] ?? '')));
                        $bannerColor = ['promo-card-pink', 'promo-card-yellow', 'promo-card-green', 'promo-card-blue'][$bannerNumber % 4];
                        ?>
                        <a class="promo-card <?= $bannerColor ?>" href="<?= e($banner['link_tujuan'] ?: '#') ?>">
                            <div>
                                <span class="promo-label"><?= e($banner['kategori'] ?: 'Buah Segar') ?></span>
                                <h3 class="promo-title"><?= e($banner['judul']) ?></h3>
                                <span class="promo-btn">Lihat Produk →</span>
                            </div>
                            <?php if ($bannerImage !== ''): ?>
                                <img src="/mybuah/assets/images/produk/<?= e(rawurlencode($bannerImage)) ?>" alt="<?= e($banner['judul']) ?>" onerror="this.hidden=true; this.nextElementSibling.hidden=false">
                                <span class="promo-placeholder" aria-hidden="true" hidden>🍊</span>
                            <?php else: ?>
                                <span class="promo-placeholder" aria-hidden="true">🍊</span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="features-bar" aria-label="Keunggulan MyBuah">
    <div class="container features-grid">
        <div class="feature"><span aria-hidden="true">🚚</span><div><strong>Gratis Ongkir</strong><small>Gratis untuk semua pesanan</small></div></div>
        <div class="feature"><span aria-hidden="true">🎧</span><div><strong>Layanan 24/7</strong><small>Bantuan kapan saja</small></div></div>
        <div class="feature"><span aria-hidden="true">🔒</span><div><strong>Pembayaran Aman</strong><small>Uang kamu terjamin aman</small></div></div>
        <div class="feature"><span aria-hidden="true">📦</span><div><strong>Garansi Uang Kembali</strong><small>Garansi 30 hari</small></div></div>
    </div>
</section>

<section class="container section">
    <div class="section-heading">
        <h2>Jelajahi Produk</h2>
        <div class="tabs"><a class="active" href="landing.php">Semua</a><a href="#popular">Terpopuler</a></div>
    </div>
    <div class="category-grid">
        <?php foreach ($categories as $category): ?>
            <a class="category-card" href="landing.php?kategori=<?= (int) $category['id_kategori'] ?>">
                <span class="category-image"><?= productVisual($productImagePath($category['gambar_kategori']), $category['nama_kategori']) ?></span>
                <strong><?= e($category['nama_kategori']) ?></strong>
                <small><?= (int) $category['jumlah_produk'] ?> produk</small>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="container section" id="popular">
    <div class="section-heading">
        <h2><?= $search !== '' ? 'Hasil pencarian' : 'Produk Terpopuler' ?></h2>
        <?php if ($search !== ''): ?><a class="button button-outline" href="landing.php">Reset pencarian</a><?php endif; ?>
    </div>
    <?php if ($products === []): ?>
        <div class="empty-state">Belum ada produk yang cocok dengan pencarianmu.</div>
    <?php else: ?>
        <div class="carousel-wrapper">
            <button class="carousel-btn carousel-btn-left" type="button" onclick="scrollProduk(-1, 'produkScroll')" aria-label="Produk sebelumnya">‹</button>
            <div class="product-grid" id="produkScroll">
                <?php foreach ($products as $product): ?>
                    <article class="product-card">
                        <a class="product-image" href="produk_detail.php?id=<?= (int) $product['id_produk'] ?>">
                            <?= productVisual($productImagePath($product['gambar']), $product['nama']) ?>
                            <?php if ((int) $product['id_produk'] % 3 === 0): ?><span class="discount-badge">-20%</span><?php endif; ?>
                        </a>
                        <span class="product-category"><?= e($product['nama_kategori']) ?></span>
                        <h3><a href="produk_detail.php?id=<?= (int) $product['id_produk'] ?>"><?= e($product['nama']) ?></a></h3>
                        <?= renderRatingStars() ?>
                        <div class="product-bottom">
                            <strong class="price"><?= formatRupiah($product['harga']) ?></strong>
                            <form action="cart.php" method="post">
                                <input type="hidden" name="id_produk" value="<?= (int) $product['id_produk'] ?>">
                                <input type="hidden" name="jumlah" value="1">
                                <button class="add-button" type="submit">+ Tambah</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <button class="carousel-btn carousel-btn-right" type="button" onclick="scrollProduk(1, 'produkScroll')" aria-label="Produk berikutnya">›</button>
        </div>
    <?php endif; ?>
</section>
<section class="container section popular-section">
    <div class="section-heading section-header">
        <h2>Produk Terbaru</h2>
        <a href="/mybuah/semua_produk.php?urutan=terbaru" class="promo-link see-all-link">Lihat semua →</a>
    </div>
    <?php if ($produkTerbaru === []): ?>
        <div class="empty-state">Belum ada produk terbaru.</div>
    <?php else: ?>
        <div class="carousel-wrapper">
            <button class="carousel-btn carousel-btn-left" type="button" onclick="scrollProduk(-1, 'produkTerbaruScroll')" aria-label="Produk terbaru sebelumnya">‹</button>
            <div class="product-grid" id="produkTerbaruScroll">
                <?php foreach ($produkTerbaru as $product): ?>
                    <article class="product-card">
                        <a class="product-image" href="produk_detail.php?id=<?= (int) $product['id_produk'] ?>">
                            <?= productVisual($productImagePath($product['gambar']), $product['nama']) ?>
                            <?php if ((int) $product['id_produk'] % 3 === 0): ?><span class="discount-badge">-20%</span><?php endif; ?>
                        </a>
                        <span class="product-category"><?= e($product['nama_kategori']) ?></span>
                        <h3><a href="produk_detail.php?id=<?= (int) $product['id_produk'] ?>"><?= e($product['nama']) ?></a></h3>
                        <?= renderRatingStars() ?>
                        <div class="product-bottom">
                            <strong class="price"><?= formatRupiah($product['harga']) ?></strong>
                            <form action="cart.php" method="post">
                                <input type="hidden" name="id_produk" value="<?= (int) $product['id_produk'] ?>">
                                <input type="hidden" name="jumlah" value="1">
                                <button class="add-button" type="submit">+ Tambah</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <button class="carousel-btn carousel-btn-right" type="button" onclick="scrollProduk(1, 'produkTerbaruScroll')" aria-label="Produk terbaru berikutnya">›</button>
        </div>
    <?php endif; ?>
</section>
<script>
const heroSlides = document.querySelectorAll('.hero-slide');
const heroDots = document.querySelectorAll('.hero-dot');
let heroIndex = 0;
let heroTimer;

function tampilkanHero(index) {
    if (heroSlides.length < 2) {
        return;
    }
    heroIndex = (index + heroSlides.length) % heroSlides.length;
    heroSlides.forEach((slide, slideIndex) => slide.classList.toggle('is-active', slideIndex === heroIndex));
    heroDots.forEach((dot, dotIndex) => dot.classList.toggle('is-active', dotIndex === heroIndex));
}

function mulaiHeroCarousel() {
    if (heroSlides.length > 1) {
        heroTimer = setInterval(() => tampilkanHero(heroIndex + 1), 5000);
    }
}

heroDots.forEach((dot) => {
    dot.addEventListener('click', () => {
        tampilkanHero(Number(dot.dataset.heroIndex));
        clearInterval(heroTimer);
        mulaiHeroCarousel();
    });
});
mulaiHeroCarousel();

function scrollProduk(arah, idContainer) {
    const container = document.getElementById(idContainer);
    const jarak = 240;
    if (container) {
        container.scrollBy({ left: arah * jarak, behavior: 'smooth' });
    }
}
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
