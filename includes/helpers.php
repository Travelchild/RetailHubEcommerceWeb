<?php
function sanitize(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header("Location: {$url}");
    exit;
}

function assetImageUrl(?string $imagePath): string
{
    $value = trim((string)$imagePath);

    if ($value === '') {
        return '../assets/images/products/default-product.svg';
    }

    if (preg_match('/^https?:\/\//i', $value)) {
        return $value;
    }

    return '../assets/images/products/' . ltrim($value, '/\\');
}

/** Tailwind alert box classes for flash messages */
/** Display amount in Rs. (store values unchanged in DB). */
function formatCurrency($amount): string
{
    return 'Rs. ' . number_format((float)$amount, 2, '.', ',');
}

function flashBoxClass(string $type): string
{
    if ($type === 'success') {
        return 'border-emerald-200 bg-emerald-50 text-emerald-900';
    }
    if ($type === 'danger') {
        return 'border-red-200 bg-red-50 text-red-800';
    }
    if ($type === 'warning') {
        return 'border-amber-200 bg-amber-50 text-amber-900';
    }
    return 'border-sky-200 bg-sky-50 text-sky-900';
}
