<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;

class DashboardController {
    public function index(): void {
        Auth::requireLogin();
        $u = Auth::user();
        if ($u['role']==='librarian') $this->librarian();
        else $this->student();
    }

    private function librarian(): void {
        $pdo = Database::pdo();
        $stats = [];
        try {
            $stats['titles'] = (int)($pdo->query("SELECT COUNT(*) FROM books")->fetchColumn());
            $stats['copies'] = (int)($pdo->query("SELECT COUNT(*) FROM book_copies")->fetchColumn());
            $stats['students'] = (int)($pdo->query("SELECT COUNT(*) FROM students WHERE status='active'")->fetchColumn());
            $stats['active'] = (int)($pdo->query("SELECT COUNT(*) FROM issues WHERE status IN ('issued','overdue')")->fetchColumn());
            $stats['overdue'] = (int)($pdo->query("SELECT COUNT(*) FROM issues WHERE status='overdue'")->fetchColumn());
            $stats['pending_fines'] = (float)($pdo->query("SELECT COALESCE(SUM(amount),0) FROM fines WHERE status='pending'")->fetchColumn());
            $stats['visits_today'] = (int)($pdo->query("SELECT COUNT(*) FROM library_visits WHERE DATE(check_in_time)=CURDATE()")->fetchColumn());
        } catch(\Throwable $e) { }
        $recent_books = Database::query("SELECT * FROM books ORDER BY created_at DESC LIMIT 4");
        $visits_today = Database::query("SELECT s.name, s.enrollment_no, v.check_in_time FROM library_visits v JOIN students s ON s.id=v.student_id WHERE DATE(v.check_in_time)=CURDATE() ORDER BY v.check_in_time DESC LIMIT 6");
        // Chart data
        $branchRows = Database::query("SELECT branch, COUNT(*) as cnt FROM students GROUP BY branch ORDER BY cnt DESC LIMIT 6");
        $catRows = Database::query("SELECT COALESCE(category,'General') as cat, COUNT(*) as cnt FROM books GROUP BY cat ORDER BY cnt DESC LIMIT 6");
        $statusRows = Database::query("SELECT status, COUNT(*) as cnt FROM issues GROUP BY status");
        // Build maps for chart
        $branchLabels = array_column($branchRows, 'branch');
        $branchCounts = array_column($branchRows, 'cnt');
        $catLabels = array_column($catRows, 'cat');
        $catCounts = array_column($catRows, 'cnt');
        $statusMap = ['issued'=>0,'overdue'=>0,'returned'=>0];
        foreach($statusRows as $r) if(isset($statusMap[$r['status']])) $statusMap[$r['status']] = (int)$r['cnt'];
        View::render('librarian/dashboard', [
            'stats'=>$stats, 'recent_books'=>$recent_books, 'visits_today'=>$visits_today, 'fine_per_day'=>2,
            'branchLabels'=>$branchLabels,'branchCounts'=>$branchCounts,
            'catLabels'=>$catLabels,'catCounts'=>$catCounts,
            'statusMap'=>$statusMap,
            'user'=>Auth::user()
        ]);
    }

    private function student(): void {
        $u = Auth::user();
        $sid = $u['profile']['id'] ?? null;
        $issues = [];
        $my_active=0; $my_overdue=0; $my_fines=0;
        if ($sid) {
            $issues = Database::query("SELECT i.*, b.title, b.author, bc.accession_no FROM issues i JOIN books b ON b.id=i.book_id LEFT JOIN book_copies bc ON bc.id=i.book_copy_id WHERE i.student_id=? AND i.status IN ('issued','overdue') ORDER BY i.due_date ASC LIMIT 6", [$sid]);
            $my_active = (int)(Database::one("SELECT COUNT(*) as c FROM issues WHERE student_id=? AND status IN ('issued','overdue')", [$sid])['c'] ?? 0);
            $my_overdue = (int)(Database::one("SELECT COUNT(*) as c FROM issues WHERE student_id=? AND status='overdue'", [$sid])['c'] ?? 0);
            $my_fines = (float)(Database::one("SELECT COALESCE(SUM(amount),0) as s FROM fines WHERE student_id=? AND status='pending'", [$sid])['s'] ?? 0);
        }
        $total_titles = (int)(Database::one("SELECT COUNT(*) as c FROM books")['c'] ?? 0);
        View::render('student/dashboard', ['issues'=>$issues, 'my_active'=>$my_active, 'my_overdue'=>$my_overdue, 'my_fines'=>$my_fines, 'total_titles'=>$total_titles, 'loan_days'=>14, 'user'=>$u]);
    }
}
