<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;

class FeedbackController {
    public function index(): void {
        Auth::requireRole('librarian');
        $rows=Database::query("SELECT f.*, s.name, s.enrollment_no, b.title FROM feedback f JOIN students s ON s.id=f.student_id LEFT JOIN books b ON b.id=f.book_id ORDER BY f.created_at DESC LIMIT 100");
        $stats=Database::one("SELECT AVG(rating) as avg, COUNT(*) as cnt FROM feedback");
        View::render('librarian/feedback', ['rows'=>$rows,'stats'=>$stats,'user'=>Auth::user()]);
    }
}
