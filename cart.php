<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (!isLoggedIn()) {
    redirect('auth/login.php');
}
$userId = (int) $_SESSION['id_user'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = filter_input(INPUT_POST, 'id_produk', FILTER_VALIDATE_INT);
    $quantity = max(1, (int) ($_POST['jumlah'] ?? 1));
    if ($productId) {
        $check = $pdo->prepare('SELECT id_keranjang, jumlah FROM keranjang WHERE id_user = :user_id AND id_produk = :product_id LIMIT 1');
        $check->execute(['user_id' => $userId, 'product_id' => $productId]);
        $existing = $check->fetch();
        if ($existing) {
            $update = $pdo->prepare('UPDATE keranjang SET jumlah = jumlah + :jumlah WHERE id_keranjang = :id');
            $update->execute(['jumlah' => $quantity, 'id' => $existing['id_keranjang']]);
        } else {
            $insert = $pdo->prepare('INSERT INTO keranjang (id_user, id_produk, jumlah) VALUES (:user_id, :product_id, :jumlah)');
            $insert->execute(['user_id' => $userId, 'product_id' => $productId, 'jumlah' => $quantity]);
        }
    }
    redirect('cart.php');
}
$statement = $pdo->prepare('SELECT k.id_keranjang, k.jumlah, p.id_produk, p.nama, p.gambar, p.harga FROM keranjang k INNER JOIN produk p ON p.id_produk = k.id_produk WHERE k.id_user = :user_id ORDER BY k.id_keranjang DESC');
$statement->execute(['user_id' => $userId]);
$items = $statement->fetchAll();
$total = 0;
foreach ($items as $item) { $total += (float) $item['harga'] * (int) $item['jumlah']; }
$pageTitle = 'Keranjang | MyBuah';
require __DIR__ . '/includes/header.php';
?>
<section class="container section" style="padding-top:50px"><div class="section-heading"><h1>Keranjang belanja</h1><a href="landing.php" class="button button-outline">Lanjut belanja</a></div><?php if ($items === []): ?><div class="empty-state">Keranjangmu masih kosong.</div><?php else: ?><div class="category-grid"><?php foreach ($items as $item): ?><article class="category-card"><div style="font-size:45px"><?= productVisual($item['gambar'], $item['nama']) ?></div><strong><?= e($item['nama']) ?></strong><small><?= (int) $item['jumlah'] ?> × <?= formatRupiah($item['harga']) ?></small></article><?php endforeach; ?></div><p style="text-align:right;font-size:20px"><strong>Total: <?= formatRupiah($total) ?></strong></p><?php endif; ?></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
