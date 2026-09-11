<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (!isset($_SESSION['id_admin'])) {
    redirect('admin_login.php');
}

$error = '';
$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
$editing = null;

if ($editId) {
    $statement = $pdo->prepare('SELECT * FROM banner WHERE id_banner = :id');
    $statement->execute(['id' => $editId]);
    $editing = $statement->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id_banner', FILTER_VALIDATE_INT);
    $type = in_array($_POST['tipe'] ?? 'kotak', ['hero', 'kotak'], true) ? $_POST['tipe'] : 'kotak';
    $oldImage = basename(trim((string) ($_POST['gambar_lama'] ?? '')));
    $data = [
        'judul' => trim((string) ($_POST['judul'] ?? '')),
        'subjudul' => trim((string) ($_POST['subjudul'] ?? '')),
        'kategori' => trim((string) ($_POST['kategori'] ?? '')),
        'gambar' => $oldImage,
        'link' => trim((string) ($_POST['link_tujuan'] ?? '')),
        'aktif' => isset($_POST['aktif']) ? 1 : 0,
        'posisi' => (int) ($_POST['posisi'] ?? 0),
        'tipe' => $type,
    ];

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Upload gambar gagal dengan kode error ' . (int) $_FILES['gambar']['error'] . '.';
        } elseif (!is_uploaded_file($_FILES['gambar']['tmp_name'])) {
            $error = 'File gambar upload tidak valid.';
        } elseif (getimagesize($_FILES['gambar']['tmp_name']) === false) {
            $error = 'File yang diupload harus berupa gambar yang valid.';
        } else {
            $extension = strtolower(pathinfo((string) $_FILES['gambar']['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                $error = 'Format gambar harus JPG, JPEG, PNG, WEBP, atau GIF.';
            } else {
                $uploadDirectory = __DIR__ . '/assets/images/banner/';
                $filename = 'banner_' . bin2hex(random_bytes(8)) . '.' . $extension;
                if (!is_dir($uploadDirectory) || !is_writable($uploadDirectory)) {
                    $error = 'Folder assets/images/banner tidak ada atau tidak dapat ditulis.';
                } elseif (!move_uploaded_file($_FILES['gambar']['tmp_name'], $uploadDirectory . $filename)) {
                    $error = 'Gambar gagal disimpan ke folder assets/images/banner.';
                } else {
                    $data['gambar'] = $filename;
                }
            }
        }
    }

    if ($error !== '') {
        // Tampilkan kesalahan upload tanpa menyimpan data banner setengah jadi.
    } elseif ($data['judul'] === '' || $data['link'] === '') {
        $error = 'Judul dan link tujuan wajib diisi.';
    } elseif ($id) {
        $statement = $pdo->prepare('UPDATE banner SET judul=:judul,subjudul=:subjudul,kategori=:kategori,gambar=:gambar,link_tujuan=:link,aktif=:aktif,posisi=:posisi,tipe=:tipe WHERE id_banner=:id');
        $statement->execute($data + ['id' => $id]);
        redirect('dashboard.php?menu=banner');
    } else {
        $statement = $pdo->prepare('INSERT INTO banner (judul,subjudul,kategori,gambar,link_tujuan,aktif,posisi,tipe) VALUES (:judul,:subjudul,:kategori,:gambar,:link,:aktif,:posisi,:tipe)');
        $statement->execute($data);
        redirect('dashboard.php?menu=banner');
    }
}

if (isset($_GET['hapus'])) {
    $statement = $pdo->prepare('DELETE FROM banner WHERE id_banner=:id');
    $statement->execute(['id' => filter_input(INPUT_GET, 'hapus', FILTER_VALIDATE_INT)]);
    redirect('dashboard.php?menu=banner');
}

$banners = $pdo->query('SELECT * FROM banner ORDER BY posisi, id_banner DESC')->fetchAll();
$kategoriList = $pdo->query('SELECT id_kategori, nama_kategori FROM kategori ORDER BY id_kategori ASC')->fetchAll();
$produkList = $pdo->query('SELECT id_produk, nama FROM produk ORDER BY nama ASC')->fetchAll();
$form = $editing ?: ['id_banner' => '', 'judul' => '', 'subjudul' => '', 'kategori' => '', 'gambar' => '', 'link_tujuan' => 'landing.php', 'aktif' => 1, 'posisi' => 0, 'tipe' => 'kotak'];
$linkTujuan = (string) ($form['link_tujuan'] ?? 'landing.php');
?>
<div class="admin-heading"><h1>Kelola Banner</h1></div>
<div class="checkout-layout">
    <form class="checkout-card admin-form" method="post" enctype="multipart/form-data">
        <h2><?= $editing ? 'Edit Banner' : 'Tambah Banner' ?></h2>
        <?php if ($error): ?><div class="form-error"><?= e($error) ?></div><?php endif; ?>
        <input type="hidden" name="id_banner" value="<?= e((string) $form['id_banner']) ?>">
        <label class="form-field">Judul<input name="judul" value="<?= e($form['judul']) ?>" required></label>
        <label class="form-field">Subjudul / Badge<input name="subjudul" value="<?= e($form['subjudul'] ?? '') ?>" placeholder="Diskon hingga 50%"></label>
        <label class="form-field">Label Kategori<input name="kategori" value="<?= e($form['kategori']) ?>" placeholder="Buah Segar"></label>
        <label class="form-field">Gambar Banner<input type="file" name="gambar" accept="image/jpeg,image/png,image/webp,image/gif"><input type="hidden" name="gambar_lama" value="<?= e((string) ($form['gambar'] ?? '')) ?>"><small>Biarkan kosong untuk mempertahankan gambar saat edit.</small></label>
        <label class="form-field">Link Tujuan
            <select name="link_tujuan" required>
                <optgroup label="Halaman Umum">
                    <option value="landing.php" <?= $linkTujuan === 'landing.php' ? 'selected="selected"' : '' ?>>Halaman Utama</option>
                </optgroup>
                <optgroup label="Kategori">
                    <?php foreach ($kategoriList as $kategori): ?>
                        <?php $kategoriLink = 'landing.php?kategori=' . (int) $kategori['id_kategori']; ?>
                        <option value="<?= e($kategoriLink) ?>" <?= $linkTujuan === $kategoriLink ? 'selected="selected"' : '' ?>>Kategori: <?= e($kategori['nama_kategori']) ?></option>
                    <?php endforeach; ?>
                </optgroup>
                <optgroup label="Produk">
                    <?php foreach ($produkList as $p): ?>
                        <?php $produkLink = 'produk_detail.php?id=' . (int) $p['id_produk']; ?>
                        <option value="<?= e($produkLink) ?>" <?= $linkTujuan === $produkLink ? 'selected="selected"' : '' ?>><?= e($p['nama']) ?></option>
                    <?php endforeach; ?>
                </optgroup>
            </select>
        </label>
        <label class="form-field">Posisi<input type="number" name="posisi" value="<?= (int) $form['posisi'] ?>" min="0"></label>
        <fieldset class="form-field"><legend>Tipe Banner</legend><label><input type="radio" name="tipe" value="kotak" <?= $form['tipe'] === 'kotak' ? 'checked' : '' ?>> Kotak (grid promo)</label><label><input type="radio" name="tipe" value="hero" <?= $form['tipe'] === 'hero' ? 'checked' : '' ?>> Hero (slide besar)</label></fieldset>
        <label><input type="checkbox" name="aktif" <?= $form['aktif'] ? 'checked' : '' ?>> Banner aktif</label>
        <div style="margin-top:18px"><button class="button button-primary" type="submit">Simpan Banner</button><?php if ($editing): ?> <a class="button button-outline" href="dashboard.php?menu=banner">Batal</a><?php endif; ?></div>
    </form>
    <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Preview</th><th>Judul</th><th>Tipe</th><th>Status</th><th>Posisi</th><th>Aksi</th></tr></thead><tbody><?php foreach ($banners as $banner): $bannerImage = basename((string) $banner['gambar']); $bannerFileExists = $bannerImage !== '' && is_file(__DIR__ . '/assets/images/banner/' . $bannerImage); ?><tr><td><div class="admin-banner-preview admin-banner-preview-<?= e($banner['tipe']) ?>"><?php if ($bannerFileExists): ?><img src="/mybuah/assets/images/banner/<?= e(rawurlencode($bannerImage)) ?>" alt="<?= e($banner['judul']) ?>" onerror="this.hidden=true; this.nextElementSibling.hidden=false"><span hidden>🍊</span><?php else: ?><span>🍊</span><?php endif; ?></div></td><td><?= e($banner['judul']) ?></td><td><?= strtoupper(e($banner['tipe'])) ?></td><td><?= $banner['aktif'] ? 'Aktif' : 'Nonaktif' ?></td><td><?= (int) $banner['posisi'] ?></td><td><div class="action-row"><a class="icon-button" href="dashboard.php?menu=banner&edit=<?= (int) $banner['id_banner'] ?>" aria-label="Edit">✎</a><a class="icon-button danger" href="dashboard.php?menu=banner&hapus=<?= (int) $banner['id_banner'] ?>" onclick="return confirm('Hapus banner ini?')" aria-label="Hapus">🗑</a></div></td></tr><?php endforeach; ?></tbody></table></div>
</div>
