<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$userId = (int) $_SESSION['id_user'];
$userStatement = $pdo->prepare('SELECT * FROM `user` WHERE id_user = :id');
$userStatement->execute(['id' => $userId]);
$user = $userStatement->fetch();
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['nama'] ?? ''));
    $phone = trim((string) ($_POST['no_telp'] ?? ''));
    $address = trim((string) ($_POST['alamat'] ?? ''));
    $statement = $pdo->prepare('UPDATE `user` SET nama = :nama, no_telp = :telp, alamat = :alamat WHERE id_user = :id');
    $statement->execute(['nama' => $name, 'telp' => $phone, 'alamat' => $address, 'id' => $userId]);
    $_SESSION['nama'] = $name;
    $message = 'Profil berhasil diperbarui.';
    $user['nama'] = $name;
    $user['no_telp'] = $phone;
    $user['alamat'] = $address;
}
$statement = $pdo->prepare("SELECT COUNT(*) AS total,
    (SELECT COUNT(*) FROM pesanan WHERE id_user = :active_user AND status NOT IN ('selesai', 'dibatalkan')) AS aktif,
    COALESCE(SUM(CASE WHEN status = 'selesai' THEN total ELSE 0 END), 0) AS belanja
    FROM pesanan WHERE id_user = :user_id");
$statement->execute(['active_user' => $userId, 'user_id' => $userId]);
$stats = $statement->fetch();
$pageTitle = 'Akun Saya | MyBuah';
require __DIR__ . '/includes/header.php';
?>
<section class="container page-shell section">
    <div class="breadcrumb">Katalog / Akun Saya</div>
    <div class="account-hero"><h1>Profil Saya</h1><p class="muted">Kelola informasi akun dan alamat pengirimanmu.</p></div>
    <?php if ($message): ?><div class="form-success"><?= e($message) ?></div><?php endif; ?>
    <div class="account-grid"><div class="stat-card"><small>Total Pesanan</small><strong><?= (int) $stats['total'] ?></strong></div><div class="stat-card"><small>Pesanan Aktif</small><strong><?= (int) $stats['aktif'] ?></strong></div><div class="stat-card"><small>Total Belanja</small><strong><?= formatRupiah($stats['belanja']) ?></strong></div></div>
    <form id="profil" class="checkout-card" style="margin-top:20px" method="post"><h2>Edit Profil</h2><label class="form-field">Nama<input name="nama" value="<?= e($user['nama'] ?? '') ?>" required></label><label class="form-field">No. Telepon<input name="no_telp" value="<?= e($user['no_telp'] ?? '') ?>"></label><label class="form-field">Alamat Default<textarea name="alamat"><?= e($user['alamat'] ?? '') ?></textarea></label><button class="button button-primary">Simpan Profil</button></form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>