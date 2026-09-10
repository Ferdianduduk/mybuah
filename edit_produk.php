<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (!isset($_SESSION['id_admin'])) {
    redirect('admin_login.php');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$statement = $pdo->prepare('SELECT * FROM produk WHERE id_produk = :id');
$statement->execute(['id' => $id]);
$product = $statement->fetch();
if (!$product) {
    redirect('dashboard.php');
}
$categories = $pdo->query('SELECT * FROM kategori ORDER BY nama_kategori')->fetchAll();
$error = '';
$uploadDirectory = __DIR__ . '/assets/images/produk/';
$allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_FILES['gambar'] ?? null;
    $hasNewImage = $file && $file['error'] !== UPLOAD_ERR_NO_FILE && $file['size'] > 0;
    $newFileName = $product['gambar'];
    $oldFilePath = $uploadDirectory . basename((string) $product['gambar']);

    if ($hasNewImage) {
        $extension = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        $imageInfo = $file['error'] === UPLOAD_ERR_OK ? @getimagesize($file['tmp_name']) : false;
        $mimeType = $imageInfo['mime'] ?? '';
        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true)) {
            $error = 'Folder penyimpanan gambar tidak dapat dibuat.';
        } elseif (!is_writable($uploadDirectory)) {
            $error = 'Folder penyimpanan gambar tidak dapat ditulis.';
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Upload gambar gagal.';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $error = 'Ukuran gambar maksimal 2MB.';
        } elseif (!in_array($extension, $allowedExtensions, true) || !in_array($mimeType, $allowedMimeTypes, true)) {
            $error = 'Format gambar harus JPG, JPEG, PNG, atau WEBP.';
        } elseif ($imageInfo === false) {
            $error = 'File yang diunggah bukan gambar yang valid.';
        } else {
            $newFileName = uniqid('produk_', true) . '.' . $extension;
            if (!move_uploaded_file($file['tmp_name'], $uploadDirectory . $newFileName)) {
                $error = 'Gambar gagal disimpan ke folder produk.';
            } elseif ($product['gambar'] && is_file($oldFilePath) && !unlink($oldFilePath)) {
                @unlink($uploadDirectory . $newFileName);
                $error = 'Gambar lama gagal dihapus.';
            }
        }
    }

    if ($error === '') {
        $statement = $pdo->prepare('UPDATE produk SET nama = :nama, detail = :detail, stok = :stok, gambar = :gambar, harga = :harga, id_kategori = :kategori WHERE id_produk = :id');
        $statement->execute(['nama' => trim($_POST['nama']), 'detail' => trim($_POST['detail'] ?? ''), 'stok' => (int) $_POST['stok'], 'gambar' => $newFileName, 'harga' => (int) $_POST['harga'], 'kategori' => (int) $_POST['id_kategori'], 'id' => $id]);
        redirect('dashboard.php');
    }
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="assets/css/style.css"><title>Edit Produk</title></head><body><main class="container form-page admin-form"><a href="dashboard.php">← Kembali</a><h1>Edit Produk</h1><?php if ($error): ?><div class="form-error"><?= e($error) ?></div><?php endif; ?><form method="post" enctype="multipart/form-data"><label class="form-field">Nama Produk<input name="nama" value="<?= e($product['nama']) ?>" required></label><label class="form-field">Kategori<select name="id_kategori"><?php foreach ($categories as $category): ?><option value="<?= $category['id_kategori'] ?>" <?= $category['id_kategori'] == $product['id_kategori'] ? 'selected' : '' ?>><?= e($category['nama_kategori']) ?></option><?php endforeach; ?></select></label><label class="form-field">Harga<input type="number" name="harga" value="<?= $product['harga'] ?>" required></label><label class="form-field">Stok<input type="number" name="stok" value="<?= $product['stok'] ?>" required></label><?php if ($product['gambar']): ?><div class="form-field">Gambar Saat Ini<img src="/mybuah/assets/images/produk/<?= e(basename($product['gambar'])) ?>" alt="<?= e($product['nama']) ?>" onerror="this.onerror=null;this.src='/mybuah/assets/images/placeholder.png'" style="width:100px;height:100px;object-fit:cover;border-radius:12px"></div><?php endif; ?><label class="form-field">Ganti Gambar<input type="file" name="gambar" accept="image/jpeg,image/png,image/webp"></label><label class="form-field">Detail<textarea name="detail"><?= e($product['detail']) ?></textarea></label><button class="button button-primary">Simpan Perubahan</button></form></main></body></html>