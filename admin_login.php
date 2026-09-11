<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if (isset($_SESSION['id_admin'])) {
    redirect('dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['nama'] ?? ''));
    $password = trim((string) ($_POST['password'] ?? ''));
    $statement = $pdo->prepare('SELECT id_admin, nama_admin, password_admin FROM admin WHERE nama_admin = :nama LIMIT 1');
    $statement->execute(['nama' => $name]);
    $admin = $statement->fetch();

    if ($admin && password_verify($password, $admin['password_admin'])) {
        $_SESSION['id_admin'] = (int) $admin['id_admin'];
        $_SESSION['nama_admin'] = $admin['nama_admin'];
        redirect('dashboard.php');
    }
    $error = 'Nama admin atau password salah.';
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin | MyBuah</title>
    <link rel="stylesheet" href="/mybuah/assets/css/style.css">
</head>
<body class="admin-login-page">
<main class="admin-login-wrapper">
    <section class="admin-login-form-side">
        <a class="admin-login-brand" href="landing.php"><span class="admin-login-brand-dot"></span>MyBuah</a>
        <div class="admin-login-content">
            <p class="admin-login-eyebrow">Ruang pengelola</p>
            <h1>Selamat Datang, Admin!</h1>
            <p class="admin-login-subtitle">Kelola toko MyBuah dari sini.</p>
            <?php if ($error !== ''): ?><div class="admin-login-error" role="alert"><?= e($error) ?></div><?php endif; ?>
            <form class="admin-login-form" method="post">
                <label class="admin-login-field"><span>Nama Admin</span><span class="admin-login-input-wrap"><span aria-hidden="true">👤</span><input type="text" name="nama" required autocomplete="username" placeholder="Masukkan nama admin"></span></label>
                <label class="admin-login-field"><span>Password</span><span class="admin-login-input-wrap"><span aria-hidden="true">🔒</span><input type="password" name="password" required autocomplete="current-password" placeholder="Masukkan password"></span></label>
                <button class="admin-login-submit" type="submit">Masuk</button>
            </form>
            <div class="admin-login-divider"></div>
            <p class="admin-login-switch">Bukan admin? <a href="login.php">Kembali ke halaman utama</a></p>
        </div>
    </section>
    <aside class="admin-login-visual" aria-label="Kebun buah MyBuah">
        <div class="admin-login-visual-overlay"></div>
        <div class="admin-login-visual-copy"><strong>Dari kebun mitra ke pelanggan</strong><span>Segarkan toko, rawat setiap pesanan.</span></div>
    </aside>
</main>
</body>
</html>