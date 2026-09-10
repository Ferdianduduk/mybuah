<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();
$userId = (int) $_SESSION['id_user'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = filter_input(INPUT_POST, 'id_produk', FILTER_VALIDATE_INT);
    $quantity = max(1, (int) ($_POST['jumlah'] ?? 1));
    if ($productId) {
        $product = $pdo->prepare('SELECT stok FROM produk WHERE id_produk = :id LIMIT 1');
        $product->execute(['id' => $productId]);
        $stock = $product->fetchColumn();
        if ($stock === false) { redirect('landing.php'); }
        $check = $pdo->prepare('SELECT id_keranjang, jumlah_produk FROM keranjang WHERE id_user = :user_id AND id_produk = :product_id LIMIT 1');
        $check->execute(['user_id' => $userId, 'product_id' => $productId]);
        $existing = $check->fetch();
        if ($existing) {
            $quantity = min((int) $stock, (int) $existing['jumlah_produk'] + $quantity);
            $update = $pdo->prepare('UPDATE keranjang SET jumlah_produk = :jumlah WHERE id_keranjang = :id');
            $update->execute(['jumlah' => $quantity, 'id' => $existing['id_keranjang']]);
        } else {
            $insert = $pdo->prepare('INSERT INTO keranjang (id_user, id_produk, jumlah_produk) VALUES (:user_id, :product_id, :jumlah)');
            $insert->execute(['user_id' => $userId, 'product_id' => $productId, 'jumlah' => min((int) $stock, $quantity)]);
        }
    }
    redirect('cart.php');
}
$statement = $pdo->prepare('SELECT k.id_keranjang, k.jumlah_produk AS jumlah, p.id_produk, p.nama, p.gambar, p.harga, p.stok FROM keranjang k INNER JOIN produk p ON p.id_produk = k.id_produk WHERE k.id_user = :user_id ORDER BY k.id_keranjang DESC');
$statement->execute(['user_id' => $userId]);
$items = $statement->fetchAll();
$subtotal = 0;
foreach ($items as $item) { $subtotal += (float) $item['harga'] * (int) $item['jumlah']; }
$ongkir = shippingCost($subtotal);
$total = $subtotal + $ongkir;
$pageTitle = 'Keranjang | MyBuah';
require __DIR__ . '/includes/header.php';
?>
<section class="container section page-shell"><div class="breadcrumb">Katalog / Keranjang</div><div class="section-heading"><h1>Keranjang Belanja</h1><a href="landing.php" class="button button-outline">Lanjut Belanja</a></div><?php if ($items === []): ?><div class="empty-state">Keranjangmu masih kosong.</div><?php else: ?><div class="cart-layout"><div class="cart-list"><?php foreach ($items as $item): ?><article class="cart-item"><div class="cart-thumb"><?= productVisual($item['gambar'], $item['nama']) ?></div><div class="cart-info"><h3><?= e($item['nama']) ?></h3><p><?= formatRupiah($item['harga']) ?> / kg</p><form class="quantity-form" action="update_keranjang.php" method="post"><input type="hidden" name="id_keranjang" value="<?= (int) $item['id_keranjang'] ?>"><button name="jumlah" value="<?= max(1, (int) $item['jumlah'] - 1) ?>" aria-label="Kurangi jumlah">−</button><strong><?= (int) $item['jumlah'] ?></strong><button name="jumlah" value="<?= min((int) $item['stok'], (int) $item['jumlah'] + 1) ?>" aria-label="Tambah jumlah">+</button></form></div><a class="icon-button danger" href="hapus_keranjang.php?id=<?= (int) $item['id_keranjang'] ?>" aria-label="Hapus <?= e($item['nama']) ?>">🗑</a></article><?php endforeach; ?></div><aside class="summary-card"><h2>Ringkasan Pesanan</h2><div class="summary-row"><span>Subtotal</span><strong><?= formatRupiah($subtotal) ?></strong></div><div class="summary-row"><span>Ongkos Kirim</span><strong><?= $ongkir ? formatRupiah($ongkir) : 'Gratis' ?></strong></div><div class="summary-total"><span>Total</span><strong><?= formatRupiah($total) ?></strong></div><a class="button button-primary button-wide" href="checkout.php">Lanjut ke Checkout →</a></aside></div><?php endif; ?></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
