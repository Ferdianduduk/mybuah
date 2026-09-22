<?php
if (!isset($pdo)) {
    require_once __DIR__ . '/config/database.php';
}
$total = (int) $pdo->query('SELECT COUNT(*) FROM produk')->fetchColumn();
$low = (int) $pdo->query('SELECT COUNT(*) FROM produk WHERE stok < 20')->fetchColumn();
$categoriesCount = (int) $pdo->query('SELECT COUNT(*) FROM kategori')->fetchColumn();
$kataKunciAdmin = trim((string) ($_GET['cari'] ?? ''));
$productSql = 'SELECT p.*, k.nama_kategori FROM produk p LEFT JOIN kategori k ON k.id_kategori = p.id_kategori';

if ($kataKunciAdmin !== '') {
    $productSql .= ' WHERE p.nama LIKE :cari ORDER BY p.nama ASC';
    $productStatement = $pdo->prepare($productSql);
    $productStatement->execute(['cari' => '%' . $kataKunciAdmin . '%']);
} else {
    $productSql .= ' ORDER BY p.id_produk DESC';
    $productStatement = $pdo->query($productSql);
}
$products = $productStatement->fetchAll();
?>
<div class="admin-heading"><h1>Kelola Produk</h1><a class="button" style="background:var(--orange);color:#fff" href="tambah_produk.php">+ Tambah Produk</a></div>
<div class="stats-grid"><div class="stat-card"><small>TOTAL PRODUK</small><strong><?= $total ?></strong></div><div class="stat-card warning"><small>STOK MENIPIS (&lt;20)</small><strong><?= $low ?></strong></div><div class="stat-card"><small>KATEGORI</small><strong><?= $categoriesCount ?></strong></div></div>
<form action="admin_produk.php" method="get" class="admin-search-form">
    <input type="search" name="cari" placeholder="Cari nama produk..." value="<?= e($kataKunciAdmin) ?>" aria-label="Cari nama produk">
    <button type="submit" class="button button-outline">Cari</button>
    <?php if ($kataKunciAdmin !== ''): ?><a class="button button-outline" href="admin_produk.php">Reset</a><?php endif; ?>
</form>
<div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Aksi</th></tr></thead><tbody>
<?php if ($products === []): ?>
<tr><td colspan="5" class="admin-empty-search">Tidak ada produk dengan kata kunci &quot;<?= e($kataKunciAdmin) ?>&quot;.</td></tr>
<?php else: ?>
<?php foreach ($products as $product): ?>
<tr><td><div class="table-product"><span><img src="/mybuah/assets/images/produk/<?= e(basename((string) $product['gambar'])) ?>" alt="<?= e($product['nama']) ?>" onerror="this.onerror=null;this.src='/mybuah/assets/images/placeholder.png'" style="width:40px;height:40px;object-fit:cover;border-radius:8px"></span><?= e($product['nama']) ?></div></td><td><?= e($product['nama_kategori'] ?? '-') ?></td><td><?= formatRupiah($product['harga']) ?>/kg</td><td style="color:<?= $product['stok'] < 20 ? 'var(--orange)' : 'var(--green-dark)' ?>;font-weight:700"><?= $product['stok'] ?></td><td><div class="action-row"><a class="icon-button" href="edit_produk.php?id=<?= $product['id_produk'] ?>" aria-label="Edit">✎</a><a class="icon-button danger" onclick="return confirm('Hapus produk ini?')" href="hapus_produk.php?id=<?= $product['id_produk'] ?>" aria-label="Hapus">🗑</a></div></td></tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody></table></div>