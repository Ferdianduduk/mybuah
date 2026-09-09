<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    redirect('../landing.php');
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
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
<!doctype html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Login | MyBuah</title><link rel="stylesheet" href="../assets/css/style.css"></head><body>
<main class="container form-page"><a class="brand" href="../landing.php"><span class="brand-dot"></span>MyBuah</a><h1>Selamat datang kembali</h1><p>Masuk untuk melanjutkan belanja buah segar.</p>
<?php if ($error !== ''): ?><div class="form-error"><?= e($error) ?></div><?php endif; ?><form method="post"><label class="form-field">Email<input type="email" name="email" required autocomplete="email"></label><label class="form-field">Password<input type="password" name="password" required autocomplete="current-password"></label><button class="button button-primary" type="submit">Login</button></form><p>Belum punya akun? <a href="register.php" style="color:var(--green-dark);font-weight:700">Register</a></p></main></body></html>
