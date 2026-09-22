<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;

class AuthController {
    public function loginForm(): void {
        if (Auth::check()) { header('Location: /dashboard'); exit; }
        View::render('auth/login', [], '');
    }

    public function login(): void {
        $u = trim($_POST['username'] ?? '');
        $p = $_POST['password'] ?? '';
        $res = Auth::attempt($u, $p);
        if (!$res) {
            View::render('auth/login', ['error'=>'Invalid credentials. Students sign in with library ID, not enrollment.'], '');
            return;
        }
        [$user,$profile] = $res;
        // enrich profile name
        if ($user['role']==='student' && $profile) { $user['display_name']=$profile['name']; }
        Auth::login($user, $profile ?? []);
        if ((int)($user['must_change_password'] ?? 0) === 1) {
            flash('error','First login — set a new password to continue');
            header('Location: /profile'); exit;
        }
        header('Location: /dashboard'); exit;
    }

    public function logout(): void {
        Auth::logout();
        header('Location: /login'); exit;
    }
}
