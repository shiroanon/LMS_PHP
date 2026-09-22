<?php
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function old(string $k, string $def=''): string { return e($_POST[$k] ?? $def); }
function flash(string $key, string $msg = null): ?string {
    if ($msg !== null) { $_SESSION['_flash'][$key] = $msg; return null; }
    $v = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $v;
}
function is_active(string $path): bool {
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    return $uri === $path || str_starts_with($uri, $path.'?') || ($path !== '/' && str_starts_with($uri, $path));
}
