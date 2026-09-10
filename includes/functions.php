<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function formatRupiah(int|float|string $amount): string
{
    return 'Rp' . number_format((float) $amount, 0, ',', '.');
}

function renderRatingStars(): string
{
    return '<span class="rating" aria-label="Rating 4,5 dari 5">★★★★<span class="star-muted">☆</span></span>';
}

function isLoggedIn(): bool
{
    return isset($_SESSION['id_user']);
}

function currentUserName(): string
{
    return (string) ($_SESSION['nama'] ?? '');
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        redirect('auth/login.php');
    }
}

function shippingCost(int|float $subtotal): int
{
    return (float) $subtotal >= 75000 ? 0 : 10000;
}

function orderStatusLabel(string $status): string
{
    return match ($status) {
        'menunggu_pembayaran', 'menunggu_verifikasi' => 'MENUNGGU',
        'diproses' => 'DIPROSES',
        'dikirim' => 'DIKIRIM',
        'selesai' => 'SELESAI',
        'dibatalkan' => 'DIBATALKAN',
        default => 'MENUNGGU',
    };
}

function orderStatusClass(string $status): string
{
    return match ($status) {
        'diproses' => 'status-blue',
        'dikirim' => 'status-blue',
        'selesai' => 'status-green',
        default => 'status-orange',
        'dibatalkan' => 'status-orange',
    };
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function productVisual(?string $image, string $name = ''): string
{
    $filename = basename(trim((string) $image));
    $source = $filename !== ''
        ? '/mybuah/assets/images/produk/' . rawurlencode($filename)
        : '/mybuah/assets/images/placeholder.png';

    return '<img src="' . e($source) . '" alt="' . e($name) . '" loading="lazy" onerror="this.onerror=null;this.src=\'/mybuah/assets/images/placeholder.png\'">';
}
