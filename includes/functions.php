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

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function productVisual(?string $image, string $name = ''): string
{
    if ($image !== null && trim($image) !== '') {
        return '<img src="' . e($image) . '" alt="' . e($name) . '" loading="lazy">';
    }

    $emojis = ['🍊', '🍎', '🥭', '🍉', '🍇', '🍓', '🍍', '🥝'];
    $index = abs(crc32($name)) % count($emojis);
    return '<span class="product-emoji" aria-hidden="true">' . $emojis[$index] . '</span>';
}
