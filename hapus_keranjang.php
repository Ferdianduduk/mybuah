<?php
require_once __DIR__ . '/config/database.php'; require_once __DIR__ . '/includes/functions.php'; requireLogin();
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT); if($id){$s=$pdo->prepare('DELETE FROM keranjang WHERE id_keranjang=:id AND id_user=:user');$s->execute(['id'=>$id,'user'=>$_SESSION['id_user']]);} redirect('cart.php');