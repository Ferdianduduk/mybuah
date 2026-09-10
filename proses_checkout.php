<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirect('checkout.php');
}

$userId = (int) $_SESSION['id_user'];
$statement = $pdo->prepare(
	'SELECT k.id_produk, k.jumlah_produk AS jumlah, p.nama, p.harga
	FROM keranjang k
	INNER JOIN produk p ON p.id_produk = k.id_produk
	WHERE k.id_user = :user_id
	ORDER BY k.id_keranjang'
);
$statement->execute(['user_id' => $userId]);
$items = $statement->fetchAll();

if ($items === []) {
	redirect('cart.php');
}

$subtotal = 0;
foreach ($items as &$item) {
	$item['jumlah'] = (int) $item['jumlah'];
	$item['harga'] = (int) $item['harga'];
	$subtotal += $item['jumlah'] * $item['harga'];
}
unset($item);

$ongkir = $subtotal >= 75000 ? 0 : 10000;
$total = $subtotal + $ongkir;
$recipientName = trim((string) ($_POST['nama_penerima'] ?? ''));
$phone = trim((string) ($_POST['no_telepon'] ?? ''));
$address = trim((string) ($_POST['alamat'] ?? ''));
$note = trim((string) ($_POST['catatan'] ?? ''));

$pdo->beginTransaction();
try {
	$insert = $pdo->prepare(
	"INSERT INTO pesanan
			(id_produk, id_user, jumlah_produk, nama_penerima, no_telepon,
			 alamat, catatan, subtotal, ongkir, total, status, items_json,
			 tanggal, waktu)
		 VALUES
			(:id_produk, :id_user, :jumlah_produk, :nama_penerima, :no_telepon,
			 :alamat, :catatan, :subtotal, :ongkir, :total,
			 'menunggu_pembayaran', :items_json, CURDATE(), CURTIME())"
	);
	$insert->execute([
	'id_produk' => (int) $items[0]['id_produk'],
	'id_user' => $userId,
	'jumlah_produk' => (int) $items[0]['jumlah'],
	'nama_penerima' => $recipientName,
	'no_telepon' => $phone,
	'alamat' => $address,
	'catatan' => $note,
	'subtotal' => $subtotal,
	'ongkir' => $ongkir,
	'total' => $total,
	'items_json' => json_encode($items, JSON_UNESCAPED_UNICODE),
	]);

	$orderId = $pdo->lastInsertId();
	$pdo->prepare('DELETE FROM keranjang WHERE id_user = :user_id')->execute(['user_id' => $userId]);
	$pdo->commit();
	redirect('pembayaran.php?id=' . $orderId);
} catch (Throwable $exception) {
	$pdo->rollBack();
	http_response_code(500);
	exit('Checkout gagal diproses.');
}
