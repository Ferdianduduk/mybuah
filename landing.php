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

$productImagePath = static function (?string $image): ?string {
    $filename = basename(trim((string) $image));
    return $filename !== '' ? $filename : null;
};

$pageTitle = 'MyBuah | Buah segar dari kebun lokal';
require __DIR__ . '/includes/header.php';
?>
<section class="container hero">
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
    <div class="hero-promos">
        <article class="hero-promo hero-promo-light">
            <div>
                <span class="promo-badge">Diskon 20%</span>
                <h2>Jeruk Segar</h2>
                <p>Mulai dari Rp15.000</p>
                <a class="promo-link" href="landing.php?kategori=1">Belanja Sekarang <span aria-hidden="true">→</span></a>
            </div>
            <img src="/mybuah/assets/images/hero/jeruk-segar.jpg" alt="Jeruk segar" class="hero-promo-img" onerror="this.hidden=true">
            <span class="promo-fallback" aria-hidden="true">🍊</span>
        </article>
        <article class="hero-promo hero-promo-dark">
            <div>
                <span class="promo-badge">Best Deal</span>
                <h2>Kelapa Sehat</h2>
                <a class="promo-link" href="landing.php?kategori=2">Belanja Sekarang <span aria-hidden="true">→</span></a>
            </div>
            <img src="/mybuah/assets/images/hero/kelapa-sehat.jpg" alt="Kelapa sehat" class="hero-promo-img" onerror="this.hidden=true">
            <span class="promo-fallback" aria-hidden="true">🥥</span>
        </article>
    </div>
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
function scrollProduk(arah, idContainer) {
    const container = document.getElementById(idContainer);
    const jarak = 240;
    if (container) {
        container.scrollBy({ left: arah * jarak, behavior: 'smooth' });
    }
}
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
