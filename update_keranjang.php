<?php
require_once __DIR__ . '/config/database.php'; require_once __DIR__ . '/includes/functions.php'; requireLogin();
$id = filter_input(INPUT_POST, 'id_keranjang', FILTER_VALIDATE_INT); $jumlah = max(1, (int) ($_POST['jumlah'] ?? 1));
if ($id) { $q = $pdo->prepare('SELECT k.id_keranjang, p.stok FROM keranjang k JOIN produk p ON p.id_produk=k.id_produk WHERE k.id_keranjang=:id AND k.id_user=:user'); $q->execute(['id'=>$id,'user'=>$_SESSION['id_user']]); if ($row=$q->fetch()) { $s=$pdo->prepare('UPDATE keranjang SET jumlah_produk=:jumlah WHERE id_keranjang=:id'); $s->execute(['jumlah'=>min($jumlah,(int)$row['stok']),'id'=>$id]); } }
redirect('cart.php');