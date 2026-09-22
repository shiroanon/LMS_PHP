<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;

class ProfileController {
    public function show(): void {
        Auth::requireLogin();
        $u = Auth::user();
        if($u['role']==='librarian'){
            View::render('shared/profile', ['profile'=>$u,'user'=>$u]);
            return;
        }
        $s = Database::one("SELECT * FROM students WHERE id=?", [$u['profile']['id']]);
        $issues = Database::query("SELECT i.*, b.title FROM issues i JOIN books b ON b.id=i.book_id WHERE i.student_id=? ORDER BY i.created_at DESC LIMIT 20", [$s['id']]);
        View::render('student/profile', ['s'=>$s,'issues'=>$issues,'user'=>$u]);
    }

    public function update(): void {
        Auth::requireLogin();
        $u = Auth::user();
        $isStudent = $u['role'] === 'student';
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $must = (int)($u['must_change_password'] ?? 0);
        if ($must && $new === '') { flash('error','Set a new password to continue'); header('Location: /profile'); exit; }
        if($new !== ''){
            if(strlen($new) < 4){ flash('error','New password must be at least 4 characters'); header('Location: /profile'); exit; }
            $row = Database::one("SELECT password_hash FROM users WHERE id=?", [$u['id']]);
            if(!$must && !password_verify($current, $row['password_hash'])){ flash('error','Current password wrong'); header('Location: /profile'); exit; }
            $hash = password_hash($new, PASSWORD_BCRYPT);
            Database::exec("UPDATE users SET password_hash=?, must_change_password=0 WHERE id=?", [$hash,$u['id']]);
            Auth::start();
            $_SESSION['user']['must_change_password'] = 0;
        }
        if($isStudent){
            $sid = $u['profile']['id'];
            Database::exec("UPDATE students SET contact=?, email=? WHERE id=?", [$_POST['contact'] ?? null, $_POST['email'] ?? null, $sid]);
        }
        flash('success','Profile updated');
        header('Location: /profile'); exit;
    }
}
