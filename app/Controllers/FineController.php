<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;

class FineController {
    public function index(): void {
        Auth::requireRole('librarian');
        $status = $_GET['status'] ?? 'pending';
        if(!in_array($status,['pending','paid'])) $status='pending';
        $fines = Database::query("SELECT f.*, i.due_date, i.return_date, b.title, bc.accession_no, s.name, s.enrollment_no FROM fines f JOIN issues i ON i.id=f.issue_id JOIN books b ON b.id=i.book_id LEFT JOIN book_copies bc ON bc.id=i.book_copy_id LEFT JOIN students s ON s.id=COALESCE(f.student_id,i.student_id) WHERE f.status=? ORDER BY f.created_at DESC LIMIT 100", [$status]);
        $sum_pending = (float)(Database::one("SELECT COALESCE(SUM(amount),0) as s FROM fines WHERE status='pending'")['s'] ?? 0);
        $sum_paid = (float)(Database::one("SELECT COALESCE(SUM(amount),0) as s FROM fines WHERE status='paid'")['s'] ?? 0);
        $count_pending = (int)(Database::one("SELECT COUNT(*) as c FROM fines WHERE status='pending'")['c'] ?? 0);
        $overdue_count = (int)(Database::one("SELECT COUNT(*) as c FROM issues WHERE status='overdue'")['c'] ?? 0);
        View::render('librarian/fines', ['fines'=>$fines,'sum_pending'=>$sum_pending,'sum_paid'=>$sum_paid,'count_pending'=>$count_pending,'overdue_count'=>$overdue_count,'user'=>Auth::user()]);
    }

    public function pay(string $id): void {
        Auth::requireRole('librarian');
        Database::exec("UPDATE fines SET status='paid', paid_at=NOW() WHERE id=?", [$id]);
        header('Location: /fines?status=pending'); exit;
    }
}
