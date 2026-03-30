<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user']);
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: index.php?page=login');
        exit;
    }
}

function hasRole(string $role): bool
{
    return isLoggedIn() && ($_SESSION['user']['role_name'] ?? '') === $role;
}

function isAdmin(): bool
{
    return hasRole('Admin');
}

function isSupportStaff(): bool
{
    return hasRole('Support');
}

function canAccessStaffTools(): bool
{
    return isAdmin() || isSupportStaff();
}
