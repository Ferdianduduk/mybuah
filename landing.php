<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$search = trim((string) ($_GET['cari'] ?? ''));
$categoryId = filter_input(INPUT_GET, 'kategori', FILTER_VALIDATE_INT) ?: null;

$categoryStatement = $pdo->query(
    'SELECT k.id_kategori, k.nama_kategori, COUNT(p.id_produk) AS jumlah_produk
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
$productSql .= ' ORDER BY p.id_produk DESC LIMIT 5';
$productStatement = $pdo->prepare($productSql);
$productStatement->execute($params);
$products = $productStatement->fetchAll();

$pageTitle = 'MyBuah | Buah segar dari kebun lokal';
require __DIR__ . '/includes/header.php';
?>
<section class="container hero">
    <div>
        <p class="eyebrow">Dari kebun ke pintu rumah</p>
        <h1>Start shopping at our local farms.</h1>
        <p class="hero-copy">Buah segar dipetik langsung dari kebun mitra, sampai ke rumahmu hari ini juga.</p>
        <form class="subscribe-form" action="landing.php" method="get">
            <input type="email" name="subscribe" placeholder="Email kamu" aria-label="Email untuk berlangganan" required>
            <button type="submit">Subscribe</button>
        </form>
    </div>
    <div class="hero-produce" aria-label="Buah dan sayur segar">
        <span class="produce-item" style="--tilt:-8deg">🥕</span>
        <span class="produce-item" style="--tilt:3deg">🥬</span>
        <span class="produce-item" style="--tilt:10deg">🍅</span>
    </div>
</section>

<section class="container section">
    <div class="section-heading">
        <h2>Explore Products</h2>
        <div class="tabs"><a class="active" href="landing.php">All</a><a href="#popular">Fruit</a></div>
    </div>
    <div class="category-grid">
        <?php foreach ($categories as $category): ?>
            <a class="category-card" href="landing.php?kategori=<?= (int) $category['id_kategori'] ?>">
                <span class="category-icon" aria-hidden="true">🍊</span>
                <strong><?= e($category['nama_kategori']) ?></strong>
                <small><?= (int) $category['jumlah_produk'] ?> produk</small>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="container section" id="popular">
    <div class="section-heading">
        <h2><?= $search !== '' ? 'Hasil pencarian' : 'Most Popular' ?></h2>
        <?php if ($search !== ''): ?><a class="button button-outline" href="landing.php">Reset pencarian</a><?php endif; ?>
    </div>
    <?php if ($products === []): ?>
        <div class="empty-state">Belum ada produk yang cocok dengan pencarianmu.</div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <article class="product-card">
                    <a class="product-image" href="produk_detail.php?id=<?= (int) $product['id_produk'] ?>"><?= productVisual($product['gambar'], $product['nama']) ?></a>
                    <span class="product-category"><?= e($product['nama_kategori']) ?></span>
                    <h3><a href="produk_detail.php?id=<?= (int) $product['id_produk'] ?>"><?= e($product['nama']) ?> 1kg</a></h3>
                    <?= renderRatingStars() ?>
                    <div class="product-bottom">
                        <strong class="price"><?= formatRupiah($product['harga']) ?></strong>
                        <form action="cart.php" method="post">
                            <input type="hidden" name="id_produk" value="<?= (int) $product['id_produk'] ?>">
                            <input type="hidden" name="jumlah" value="1">
                            <button class="add-button" type="submit">+ Add</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="container section">
    <div class="promo-grid">
        <article class="promo promo-yellow">
            <span class="promo-badge">FREE SHIPPING</span>
            <h2>Free delivery over Rp75rb</h2>
            <p>Belanja lebih banyak, nikmati kiriman gratis sampai depan pintu.</p>
            <a class="promo-button" href="#popular">Shop Now →</a>
            <span class="promo-art" aria-hidden="true">🚚</span>
        </article>
        <article class="promo promo-mint">
            <span class="promo-badge">FRESH PICKED</span>
            <h2>Panen musiman terbaru</h2>
            <p>Rasakan manisnya buah pilihan yang sedang berada di puncak musimnya.</p>
            <a class="promo-button" href="landing.php?kategori=3">Shop Now →</a>
            <span class="promo-art" aria-hidden="true">🍉</span>
        </article>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
