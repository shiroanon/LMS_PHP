<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;

class VisitController {
    public function index(): void {
        Auth::requireRole('librarian');
        $visits = Database::query("SELECT v.*, s.name, s.enrollment_no FROM library_visits v JOIN students s ON s.id=v.student_id WHERE DATE(v.check_in_time)=CURDATE() ORDER BY v.check_in_time DESC LIMIT 100");
        $today_count = (int)(Database::one("SELECT COUNT(*) as c FROM library_visits WHERE DATE(check_in_time)=CURDATE()")['c'] ?? 0);
        // peak hours mock
        $peak_hours = [3,5,8,6,7,9,6,4];
        View::render('librarian/visits', ['visits'=>$visits,'today_count'=>$today_count,'peak_hours'=>$peak_hours,'user'=>Auth::user()]);
    }

    public function check(): void {
        Auth::requireRole('librarian');
        $code = trim($_POST['member_code'] ?? '');
        $method = $_POST['method'] ?? 'barcode';
        $action = $_POST['action'] ?? 'in';
        if(!$code){ View::render('librarian/visits', ['error'=>'Borrower code required','visits'=>[],'today_count'=>0,'user'=>Auth::user()]); return; }
        $s = Database::one("SELECT * FROM students WHERE enrollment_no=? OR library_id=? OR barcode_data=? LIMIT 1", [$code,$code,$code]);
        if(!$s){ View::render('librarian/visits', ['error'=>"Borrower not found: $code",'visits'=>[],'today_count'=>0,'user'=>Auth::user()]); return; }
        if($action==='in' && empty($s['library_id'])){ View::render('librarian/visits', ['error'=>$s['name']." has no library ID — add one on the borrower record before check-in",'visits'=>[],'today_count'=>0,'user'=>Auth::user()]); return; }
        if($action==='in'){
            $open = Database::one("SELECT id FROM library_visits WHERE student_id=? AND check_out_time IS NULL LIMIT 1", [$s['id']]);
            if($open){ View::render('librarian/visits', ['error'=>"Already checked in (open visit #{$open['id']})",'visits'=>[],'today_count'=>0,'user'=>Auth::user()]); return; }
            Database::exec("INSERT INTO library_visits (student_id, method) VALUES (?,?)", [$s['id'],$method]);
            Database::exec("UPDATE students SET last_library_visit=NOW() WHERE id=?", [$s['id']]);
            flash('success', $s['name']." checked in");
        } else {
            $open = Database::one("SELECT id FROM library_visits WHERE student_id=? AND check_out_time IS NULL ORDER BY check_in_time DESC LIMIT 1", [$s['id']]);
            if(!$open){ View::render('librarian/visits', ['error'=>"No open check-in for $code",'visits'=>[],'today_count'=>0,'user'=>Auth::user()]); return; }
            Database::exec("UPDATE library_visits SET check_out_time=NOW() WHERE id=?", [$open['id']]);
            flash('success', $s['name']." checked out");
        }
        header('Location: /visits'); exit;
    }
}
