<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    redirect('../landing.php');
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['nama'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        $error = 'Isi data dengan benar. Password minimal 6 karakter.';
    } elseif ($password !== $passwordConfirmation) {
        $error = 'Konfirmasi password belum sesuai.';
    } else {
        $check = $pdo->prepare('SELECT id_user FROM `user` WHERE email = :email LIMIT 1');
        $check->execute(['email' => $email]);
        if ($check->fetch()) {
            $error = 'Email tersebut sudah terdaftar.';
        } else {
            $statement = $pdo->prepare('INSERT INTO `user` (nama, email, password) VALUES (:nama, :email, :password)');
            $statement->execute(['nama' => $name, 'email' => $email, 'password' => password_hash($password, PASSWORD_DEFAULT)]);
            $_SESSION['id_user'] = (int) $pdo->lastInsertId();
            $_SESSION['nama'] = $name;
            redirect('../akun.php');
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/mybuah/auth/">
    <title>Daftar | MyBuah</title>
    <link rel="stylesheet" href="/mybuah/assets/css/style.css">
</head>
<body class="auth-body">
<main class="auth-wrapper">
    <section class="auth-form-side">
        <a class="auth-brand" href="../landing.php"><span class="auth-brand-dot"></span>MyBuah</a>
        <div class="auth-form-content">
            <h1>Buat akun baru</h1>
            <p class="auth-subtitle">Gabung dan nikmati promo spesial member.</p>
            <?php if ($error !== ''): ?><div class="form-error"><?= e($error) ?></div><?php endif; ?>
            <form class="auth-form" method="post">
                <label class="auth-field"><span>Nama Lengkap</span><span class="auth-input-icon"><span aria-hidden="true">👤</span><input type="text" name="nama" required autocomplete="name" placeholder="Nama lengkapmu"></span></label>
                <label class="auth-field"><span>Email</span><span class="auth-input-icon"><span aria-hidden="true">✉</span><input type="email" name="email" required autocomplete="email" placeholder="nama@email.com"></span></label>
                <label class="auth-field"><span>Password</span><span class="auth-input-icon"><span aria-hidden="true">🔒</span><input type="password" name="password" minlength="6" required autocomplete="new-password" placeholder="Minimal 6 karakter"></span></label>
                <label class="auth-field"><span>Konfirmasi Password</span><span class="auth-input-icon"><span aria-hidden="true">🔒</span><input type="password" name="password_confirmation" minlength="6" required autocomplete="new-password" placeholder="Ulangi password"></span></label>
                <button class="auth-submit" type="submit">Daftar</button>
            </form>
            <div class="auth-divider"></div>
            <p class="auth-switch">Sudah punya akun? <a href="/mybuah/login.php">Masuk di sini</a></p>
        </div>
    </section>
    <aside class="auth-illustration-side" aria-label="Buah segar MyBuah">
        <span class="auth-fruit auth-fruit-one">🍊</span><span class="auth-fruit auth-fruit-two">🍇</span><span class="auth-fruit auth-fruit-three">🍓</span><span class="auth-fruit auth-fruit-four">🥭</span>
        <div class="auth-art"><div class="auth-sun">☀</div><div class="auth-tree">🌳</div><div class="auth-basket">🧺🍎🍊</div></div>
        <div class="auth-art-copy"><strong>Selamat datang di kebun kami</strong><span>Buah pilihan untuk hari yang lebih segar.</span></div>
    </aside>
</main>
</body>
</html>