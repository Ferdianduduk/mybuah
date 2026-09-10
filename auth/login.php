<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    redirect('../landing.php');
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = trim((string) ($_POST['password'] ?? ''));
    $statement = $pdo->prepare('SELECT id_user, nama, email, password FROM `user` WHERE email = :email LIMIT 1');
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['id_user'] = (int) $user['id_user'];
        $_SESSION['nama'] = $user['nama'];
        redirect('../landing.php');
    }
    $error = 'Email atau password belum sesuai.';
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/mybuah/auth/">
    <title>Masuk | MyBuah</title>
    <link rel="stylesheet" href="/mybuah/assets/css/style.css">
</head>
<body class="auth-body">
<main class="auth-wrapper">
    <section class="auth-form-side">
        <a class="auth-brand" href="../landing.php"><span class="auth-brand-dot"></span>MyBuah</a>
        <div class="auth-form-content">
            <h1>Selamat datang kembali!</h1>
            <p class="auth-subtitle">Belanja buah segar jadi lebih mudah.</p>
            <?php if ($error !== ''): ?><div class="form-error"><?= e($error) ?></div><?php endif; ?>
            <form class="auth-form" method="post">
                <label class="auth-field"><span>Email</span><span class="auth-input-icon"><span aria-hidden="true">✉</span><input type="email" name="email" required autocomplete="email" placeholder="nama@email.com"></span></label>
                <label class="auth-field"><span>Password</span><span class="auth-input-icon"><span aria-hidden="true">🔒</span><input type="password" name="password" required autocomplete="current-password" placeholder="Masukkan password"></span></label>
                <div class="auth-options"><label><input type="checkbox" name="ingat"> Ingat saya</label><a href="#">Lupa password?</a></div>
                <button class="auth-submit" type="submit">Masuk</button>
            </form>
            <div class="auth-divider"></div>
            <p class="auth-switch">Belum punya akun? <a href="/mybuah/register.php">Daftar dulu, yuk.</a></p>
        </div>
    </section>
    <aside class="auth-illustration-side" aria-label="Buah segar MyBuah">
        <span class="auth-fruit auth-fruit-one">🍊</span><span class="auth-fruit auth-fruit-two">🍇</span><span class="auth-fruit auth-fruit-three">🍓</span><span class="auth-fruit auth-fruit-four">🥭</span>
        <div class="auth-art"><div class="auth-sun">☀</div><div class="auth-tree">🌳</div><div class="auth-basket">🧺🍎🍊</div></div>
        <div class="auth-art-copy"><strong>Segar dari kebun</strong><span>Langsung sampai ke pintu rumahmu.</span></div>
    </aside>
</main>
</body>
</html>