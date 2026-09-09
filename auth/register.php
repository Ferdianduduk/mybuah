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
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        $error = 'Isi data dengan benar. Password minimal 6 karakter.';
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
            redirect('../landing.php');
        }
    }
}
?>
<!doctype html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Register | MyBuah</title><link rel="stylesheet" href="../assets/css/style.css"></head><body>
<main class="container form-page"><a class="brand" href="../landing.php"><span class="brand-dot"></span>MyBuah</a><h1>Buat akun MyBuah</h1><p>Siap menikmati buah segar dari kebun lokal.</p>
<?php if ($error !== ''): ?><div class="form-error"><?= e($error) ?></div><?php endif; ?><form method="post"><label class="form-field">Nama lengkap<input type="text" name="nama" required autocomplete="name"></label><label class="form-field">Email<input type="email" name="email" required autocomplete="email"></label><label class="form-field">Password<input type="password" name="password" minlength="6" required autocomplete="new-password"></label><button class="button button-primary" type="submit">Register</button></form><p>Sudah punya akun? <a href="login.php" style="color:var(--green-dark);font-weight:700">Login</a></p></main></body></html>
