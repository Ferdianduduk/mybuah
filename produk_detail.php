<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { redirect('landing.php'); }
$statement = $pdo->prepare('SELECT p.*, k.nama_kategori FROM produk p INNER JOIN kategori k ON k.id_kategori = p.id_kategori WHERE p.id_produk = :id LIMIT 1');
$statement->execute(['id' => $id]);
$product = $statement->fetch();
if (!$product) { http_response_code(404); exit('Produk tidak ditemukan.'); }
$pageTitle = $product['nama'] . ' | MyBuah';
require __DIR__ . '/includes/header.php';
?>
<?php $productImage = trim((string) $product['gambar']); ?>
<section class="container section" style="padding-top:50px"><a href="landing.php" style="color:var(--green-dark);font-weight:700">← Kembali ke produk</a><div class="promo-grid" style="margin-top:24px"><div class="product-image" style="min-height:400px"><img src="<?= $productImage !== '' ? '/mybuah/assets/images/produk/' . e(rawurlencode(basename($productImage))) : '/mybuah/assets/images/placeholder.png' ?>" alt="<?= e($product['nama']) ?>" onerror="this.onerror=null;this.src='/mybuah/assets/images/placeholder.png'"></div><div><span class="product-category"><?= e($product['nama_kategori']) ?></span><h1><?= e($product['nama']) ?></h1><?= renderRatingStars() ?><p style="color:var(--muted);line-height:1.7"><?= nl2br(e($product['detail'])) ?></p><p class="price" style="font-size:25px"><?= formatRupiah($product['harga']) ?> / 1kg</p><p style="color:var(--muted)">Stok tersedia: <?= (int) $product['stok'] ?></p><form action="cart.php" method="post"><input type="hidden" name="id_produk" value="<?= (int) $product['id_produk'] ?>"><label class="form-field" style="max-width:120px">Jumlah<input type="number" name="jumlah" value="1" min="1" max="<?= max(1, (int) $product['stok']) ?>"></label><button class="button button-primary" type="submit">Tambah ke keranjang</button></form></div></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
