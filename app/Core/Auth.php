<?php
namespace App\Core;

class Auth {
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params(['lifetime'=>0,'path'=>'/','httponly'=>true,'samesite'=>'Lax']);
            session_start();
        }
    }

    public static function user(): ?array {
        self::start();
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool { return self::user() !== null; }

    public static function isLibrarian(): bool { return (self::user()['role'] ?? '') === 'librarian'; }
    public static function isStudent(): bool { return (self::user()['role'] ?? '') === 'student'; }

    public static function requireLogin(): void {
        if (!self::check()) { header('Location: /login'); exit; }
    }

    public static function requireRole(string ...$roles): void {
        self::requireLogin();
        $role = self::user()['role'] ?? '';
        if (!in_array($role, $roles, true)) { http_response_code(403); die('Forbidden'); }
    }

    public static function login(array $user, array $profile = []): void {
        self::start();
        $_SESSION['user'] = array_merge($user, ['profile'=>$profile]);
        session_regenerate_id(true);
    }

    public static function logout(): void {
        self::start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'] ?? '', $p['secure'] ?? false, $p['httponly'] ?? true);
        }
        session_destroy();
    }

    public static function attempt(string $username, string $password): ?array {
        $pdo = Database::pdo();
        $u = Database::one("SELECT * FROM users WHERE username=? LIMIT 1", [$username]);
        if (!$u) return null;
        if (!password_verify($password, $u['password_hash'])) return null;
        // fetch profile
        $profile = null;
        if ($u['role']==='student') {
            $profile = Database::one("SELECT * FROM students WHERE user_id=? LIMIT 1", [$u['id']]);
        } elseif ($u['role']==='librarian') {
            $profile = ['name'=>$u['display_name'] ?? $u['username']];
        }
        return [$u,$profile];
    }
}
