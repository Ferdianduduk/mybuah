<?php

declare(strict_types=1);

$host = getenv('MYBUAH_DB_HOST') ?: '127.0.0.1';
$dbname = getenv('MYBUAH_DB_NAME') ?: 'db_mybuah';
$user = getenv('MYBUAH_DB_USER') ?: 'root';
$password = getenv('MYBUAH_DB_PASSWORD') ?: '';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $exception) {
    http_response_code(500);
    exit('Koneksi database gagal. Pastikan MySQL dan konfigurasi database sudah aktif.');
}
