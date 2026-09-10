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

    $columns = static function (string $table) use ($pdo): array {
        $statement = $pdo->prepare('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table');
        $statement->execute(['table' => $table]);
        return array_column($statement->fetchAll(), 'COLUMN_NAME');
    };
    $addColumn = static function (string $table, string $column, string $definition) use ($columns, $pdo): void {
        if (!in_array($column, $columns($table), true)) {
            $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
        }
    };

    $addColumn('user', 'nama', 'varchar(100) NULL AFTER id_user');
    $addColumn('pesanan', 'nama_penerima', 'varchar(100) NULL');
    $addColumn('pesanan', 'no_telepon', 'varchar(20) NULL');
    $addColumn('pesanan', 'alamat', 'text NULL');
    $addColumn('pesanan', 'catatan', 'text NULL');
    $addColumn('pesanan', 'subtotal', 'int NOT NULL DEFAULT 0');
    $addColumn('pesanan', 'ongkir', 'int NOT NULL DEFAULT 0');
    $addColumn('pesanan', 'total', 'int NOT NULL DEFAULT 0');
    $addColumn('pesanan', 'items_json', 'text NULL');
    $addColumn('pesanan', 'created_at', 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP');
    $addColumn('pembayaran', 'created_at', 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP');
} catch (PDOException $exception) {
    http_response_code(500);
    exit('Koneksi database gagal. Pastikan MySQL dan konfigurasi database sudah aktif.');
}
