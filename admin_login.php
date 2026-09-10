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
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login Admin | MyBuah</title><link rel="stylesheet" href="assets/css/style.css"></head><body><main class="container form-page"><a class="brand" href="landing.php"><span class="brand-dot"></span>MyBuah</a><h1>Login Admin</h1><?php if ($error): ?><div class="form-error"><?= e($error) ?></div><?php endif; ?><form method="post"><label class="form-field">Nama Admin<input name="nama" required></label><label class="form-field">Password<input type="password" name="password" required></label><button class="button button-primary">Masuk</button></form></main></body></html>