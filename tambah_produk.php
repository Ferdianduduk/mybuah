<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (!isset($_SESSION['id_admin'])) {
    redirect('admin_login.php');
}

$categories = $pdo->query('SELECT * FROM kategori ORDER BY nama_kategori')->fetchAll();
$error = '';
$uploadDirectory = __DIR__ . '/assets/images/produk/';
$allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_FILES['gambar'] ?? null;
    $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
    $imageInfo = $file && $file['error'] === UPLOAD_ERR_OK ? @getimagesize($file['tmp_name']) : false;
    $mimeType = $imageInfo['mime'] ?? '';

    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true)) {
        $error = 'Folder penyimpanan gambar tidak dapat dibuat.';
    } elseif (!is_writable($uploadDirectory)) {
        $error = 'Folder penyimpanan gambar tidak dapat ditulis.';
    } elseif (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Silakan pilih file gambar yang valid.';
    } elseif ($file['size'] > 2 * 1024 * 1024) {
        $error = 'Ukuran gambar maksimal 2MB.';
    } elseif (!in_array($extension, $allowedExtensions, true) || !in_array($mimeType, $allowedMimeTypes, true)) {
        $error = 'Format gambar harus JPG, JPEG, PNG, atau WEBP.';
    } elseif ($imageInfo === false) {
        $error = 'File yang diunggah bukan gambar yang valid.';
    } else {
        $fileName = uniqid('produk_', true) . '.' . $extension;
        if (!move_uploaded_file($file['tmp_name'], $uploadDirectory . $fileName)) {
            $error = 'Gambar gagal disimpan ke folder produk.';
        } else {
            $statement = $pdo->prepare('INSERT INTO produk (nama, detail, stok, gambar, harga, id_kategori) VALUES (:nama, :detail, :stok, :gambar, :harga, :kategori)');
            $statement->execute(['nama' => trim($_POST['nama']), 'detail' => trim($_POST['detail'] ?? ''), 'stok' => (int) $_POST['stok'], 'gambar' => $fileName, 'harga' => (int) $_POST['harga'], 'kategori' => (int) $_POST['id_kategori']]);
            redirect('dashboard.php');
        }
    }
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="assets/css/style.css"><title>Tambah Produk</title></head><body><main class="container form-page admin-form"><a href="dashboard.php">← Kembali</a><h1>Tambah Produk</h1><?php if ($error): ?><div class="form-error"><?= e($error) ?></div><?php endif; ?><form method="post" enctype="multipart/form-data"><label class="form-field">Nama Produk<input name="nama" required></label><label class="form-field">Kategori<select name="id_kategori" required><?php foreach ($categories as $category): ?><option value="<?= $category['id_kategori'] ?>"><?= e($category['nama_kategori']) ?></option><?php endforeach; ?></select></label><label class="form-field">Harga<input type="number" name="harga" min="0" required></label><label class="form-field">Stok<input type="number" name="stok" min="0" required></label><label class="form-field">Gambar Produk<input type="file" name="gambar" accept="image/jpeg,image/png,image/webp" required></label><label class="form-field">Detail<textarea name="detail"></textarea></label><button class="button button-primary">Simpan Produk</button></form></main></body></html>