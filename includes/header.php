<?php
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? 'MyBuah | Buah segar dari kebun lokal';
$searchValue = $_GET['cari'] ?? '';
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="MyBuah menghadirkan buah segar langsung dari kebun mitra ke rumahmu.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="landing.php" aria-label="MyBuah beranda"><span class="brand-dot"></span>MyBuah</a>
        <form class="search-form" action="landing.php" method="get">
            <span class="search-icon" aria-hidden="true">⌕</span>
            <input type="search" name="cari" value="<?= e((string) $searchValue) ?>" placeholder="Cari produk..." aria-label="Cari produk">
            <button type="submit" aria-label="Cari">Cari</button>
        </form>
        <nav class="nav-actions" aria-label="Navigasi utama">
            <a class="saved-link" href="landing.php#popular">♡ <span>Saved</span></a>
            <a class="cart-link" href="cart.php">🛒 <span>Cart</span></a>
            <?php if (isLoggedIn()): ?>
                <span class="greeting">Hai, <?= e(currentUserName()) ?></span>
                <a class="button button-outline" href="auth/logout.php">Logout</a>
            <?php else: ?>
                <a class="button button-outline" href="auth/register.php">Register</a>
                <a class="button button-primary" href="auth/login.php">Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main>
