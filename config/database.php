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
    $pdo->exec("CREATE TABLE IF NOT EXISTS banner (
        id_banner INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        judul VARCHAR(150) NOT NULL,
        kategori VARCHAR(80) NULL,
        gambar VARCHAR(255) NULL,
        link_tujuan VARCHAR(255) NULL,
        aktif TINYINT(1) NOT NULL DEFAULT 1,
        posisi INT NOT NULL DEFAULT 0,
        tipe ENUM('hero','kotak') NOT NULL DEFAULT 'kotak',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $addColumn('banner', 'tipe', "enum('hero','kotak') NOT NULL DEFAULT 'kotak'");
    $addColumn('banner', 'subjudul', 'varchar(150) NULL AFTER judul');
} catch (PDOException $exception) {
    http_response_code(500);
    exit('Koneksi database gagal. Pastikan MySQL dan konfigurasi database sudah aktif.');
}
