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
<?php
$productPath = $productImage !== ''
	? '/mybuah/assets/images/produk/' . rawurlencode(basename($productImage))
	: '/mybuah/assets/images/placeholder.png';
$hasStock = (int) $product['stok'] > 0;
?>
<section class="container product-detail-page">
	<a class="detail-back" href="landing.php">← Kembali ke produk</a>
	<div class="detail-grid">
		<div class="detail-image-box">
			<button type="button" class="detail-image-arrow detail-image-arrow-prev" aria-label="Gambar sebelumnya">←</button>
			<img src="<?= e($productPath) ?>" alt="<?= e($product['nama']) ?>" onerror="this.onerror=null;this.src='/mybuah/assets/images/placeholder.png'">
			<button type="button" class="detail-image-arrow detail-image-arrow-next" aria-label="Gambar berikutnya">→</button>
		</div>

		<div class="detail-info">
			<span class="detail-category"><?= e($product['nama_kategori']) ?></span>
			<div class="detail-title-row">
				<h1><?= e($product['nama']) ?></h1>
				<span class="stock-badge <?= $hasStock ? 'in-stock' : 'out-stock' ?>">
					<?= $hasStock ? 'In Stock' : 'Stok Habis' ?>
				</span>
			</div>

			<div class="detail-rating">
				<?= renderRatingStars() ?>
				<span class="rating-count">4.0 (ulasan belum tersedia)</span>
			</div>

			<div class="detail-price"><?= formatRupiah($product['harga']) ?></div>
			<p class="detail-description"><?= nl2br(e($product['detail'])) ?></p>

			<div class="detail-actions">
				<div class="qty-stepper" aria-label="Jumlah produk">
					<button type="button" class="qty-button" onclick="ubahJumlahDetail(-1)" <?= !$hasStock ? 'disabled' : '' ?>>−</button>
					<span class="qty-value" id="qtyDetail">1</span>
					<button type="button" class="qty-button" onclick="ubahJumlahDetail(1)" <?= !$hasStock ? 'disabled' : '' ?>>+</button>
				</div>

				<form action="cart.php" method="post" class="detail-cart-form">
					<input type="hidden" name="id_produk" value="<?= (int) $product['id_produk'] ?>">
					<input type="hidden" name="jumlah" id="jumlahHidden" value="1">
					<button type="submit" class="detail-button detail-button-cart" <?= !$hasStock ? 'disabled' : '' ?>>Add To Cart</button>
				</form>

				<button type="button" class="detail-button detail-button-buy" onclick="showBuyNowFallback(event)">Buy Now</button>
				<button type="button" class="detail-wishlist" onclick="toggleSaved(this)" aria-label="Simpan produk">♡</button>
			</div>
		</div>
	</div>
</section>

<script>
function ubahJumlahDetail(delta) {
	const valueElement = document.getElementById('qtyDetail');
	const hiddenInput = document.getElementById('jumlahHidden');
	const maximum = <?= max(1, (int) $product['stok']) ?>;
	let quantity = parseInt(valueElement.textContent, 10) + delta;
	quantity = Math.min(Math.max(quantity, 1), maximum);
	valueElement.textContent = quantity;
	hiddenInput.value = quantity;
}

function showBuyNowFallback(event) {
	event.preventDefault();
	alert('Fitur Buy Now belum tersedia. Silakan gunakan Add To Cart.');
}

function toggleSaved(button) {
	button.classList.toggle('is-saved');
	alert(button.classList.contains('is-saved')
		? 'Fitur wishlist belum tersedia. Tanda disimpan hanya sementara di halaman ini.'
		: 'Tanda simpan dibatalkan.');
}
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
