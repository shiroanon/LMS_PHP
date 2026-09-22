<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;

class SuggestionController {
    public function index(): void {
        Auth::requireLogin();
        $u = Auth::user();
        if($u['role']==='student'){
            $suggestions = Database::query("SELECT s.*, st.name FROM suggestions s JOIN students st ON st.id=s.student_id WHERE s.student_id=? ORDER BY s.created_at DESC LIMIT 50", [$u['profile']['id']]);
        } else {
            $suggestions = Database::query("SELECT s.*, st.name FROM suggestions s JOIN students st ON st.id=s.student_id ORDER BY s.created_at DESC LIMIT 100");
        }
        View::render('shared/suggestions', ['suggestions'=>$suggestions,'user'=>$u]);
    }

    public function create(): void {
        Auth::requireRole('student');
        $u = Auth::user();
        $sid = $u['profile']['id'];
        Database::exec("INSERT INTO suggestions (student_id,type,title,author,description) VALUES (?,?,?,?,?)", [$sid, $_POST['type'] ?? 'other', trim($_POST['title'] ?? ''), trim($_POST['author'] ?? ''), trim($_POST['description'] ?? '')]);
        flash('success','Suggestion sent to desk');
        header('Location: /suggestions'); exit;
    }

    public function updateStatus(string $id): void {
        Auth::requireRole('librarian');
        $status = $_POST['status'] ?? 'approved';
        if(!in_array($status,['approved','rejected'])) $status='approved';
        Database::exec("UPDATE suggestions SET status=? WHERE id=?", [$status,$id]);
        header('Location: /suggestions'); exit;
    }
}
