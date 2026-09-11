<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['id_admin'])) {
    redirect('admin_login.php');
}
$menu = $_GET['menu'] ?? 'produk';
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Dashboard Admin | MyBuah</title><link rel="stylesheet" href="assets/css/style.css"></head><body><div class="admin-layout"><aside class="admin-sidebar"><a class="admin-brand" href="dashboard.php"><span class="brand-dot"></span> MyBuah</a><nav class="admin-menu"><a class="<?= $menu === 'produk' ? 'active' : '' ?>" href="dashboard.php?menu=produk">▣ Kelola Produk</a><a class="<?= $menu === 'banner' ? 'active' : '' ?>" href="dashboard.php?menu=banner">▤ Kelola Banner</a><a class="<?= $menu === 'transaksi' ? 'active' : '' ?>" href="dashboard.php?menu=transaksi">▤ Transaksi &amp; Pesanan</a></nav><a class="admin-logout" href="admin_logout.php">Keluar</a></aside><main class="admin-content"><?php require $menu === 'transaksi' ? 'admin_transaksi.php' : ($menu === 'banner' ? 'admin_banner.php' : 'admin_produk.php'); ?></main></div></body></html>