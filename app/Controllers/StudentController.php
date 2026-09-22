<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Sync;
use App\Core\View;

class StudentController {
    public function index(): void {
        Auth::requireRole('librarian');
        $q = trim($_GET['q'] ?? '');
        $branch = trim($_GET['branch'] ?? '');
        $year = trim($_GET['year'] ?? '');
        $page = max(1,(int)($_GET['page'] ?? 1));
        $limit = 20;
        $off = ($page-1)*$limit;
        $where="WHERE 1=1"; $p=[];
        if($q!==''){ $where.=" AND (s.name LIKE ? OR s.enrollment_no LIKE ? OR s.library_id LIKE ?)"; $s="%$q%"; array_push($p,$s,$s,$s); }
        if($branch!==''){ $where.=" AND s.branch=?"; $p[]=$branch; }
        if($year!==''){ $where.=" AND s.year=?"; $p[]=$year; }
        $totalFiltered = (int)(Database::one("SELECT COUNT(*) as c FROM students s $where", $p)['c'] ?? 0);
        // sorting like the original app: sort_by whitelist + sort_order, id tiebreak
        $sortMap = ['name'=>'s.name','enrollment'=>'s.enrollment_no','branch'=>'s.branch','year'=>'s.year','status'=>'s.status','active'=>'active_count','created'=>'s.created_at'];
        $sortBy = $sortMap[$_GET['sort_by'] ?? ''] ?? 's.created_at';
        $sortKey = array_search($sortBy, $sortMap, true);
        $sortOrder = strtolower($_GET['sort_order'] ?? '') === 'asc' ? 'ASC' : 'DESC';
        $orderSql = "ORDER BY $sortBy $sortOrder, s.id ASC";
        $students = Database::query("SELECT s.*, (SELECT COUNT(*) FROM issues WHERE student_id=s.id AND status IN ('issued','overdue')) as active_count FROM students s $where $orderSql LIMIT $limit OFFSET $off", $p);
        $branches = array_column(Database::query("SELECT DISTINCT branch FROM students ORDER BY branch"), 'branch');
        $total = (int)(Database::one("SELECT COUNT(*) as c FROM students")['c'] ?? 0);
        $pages = max(1,(int)ceil($totalFiltered/$limit));
        View::render('librarian/students', ['students'=>$students,'branches'=>$branches,'total'=>$total,'totalFiltered'=>$totalFiltered,'pages'=>$pages,'page'=>$page,'sortKey'=>$sortKey,'sortOrder'=>$sortOrder,'user'=>Auth::user()]);
    }

    public function show(string $id): void {
        Auth::requireRole('librarian');
        $s = Database::one("SELECT * FROM students WHERE id=?", [$id]);
        if(!$s){ http_response_code(404); View::render('partials/404',['user'=>Auth::user()]); return; }
        $issues = Database::query("SELECT i.*, b.title, bc.accession_no FROM issues i JOIN books b ON b.id=i.book_id LEFT JOIN book_copies bc ON bc.id=i.book_copy_id WHERE i.student_id=? ORDER BY i.created_at DESC LIMIT 20", [$id]);
        $fines = Database::query("SELECT f.*, b.title FROM fines f JOIN issues i ON i.id=f.issue_id JOIN books b ON b.id=i.book_id WHERE f.student_id=? ORDER BY f.created_at DESC LIMIT 20", [$id]);
        $visits = Database::query("SELECT * FROM library_visits WHERE student_id=? ORDER BY check_in_time DESC LIMIT 10", [$id]);
        View::render('librarian/student_detail', ['s'=>$s,'issues'=>$issues,'fines'=>$fines,'visits'=>$visits,'user'=>Auth::user()]);
    }

    public function newForm(): void {
        Auth::requireRole('librarian');
        View::render('librarian/student_form', ['user'=>Auth::user()]);
    }
    public function create(): void {
        Auth::requireRole('librarian');
        $enr = trim($_POST['enrollment_no'] ?? '');
        $name= trim($_POST['name'] ?? '');
        $branch= trim($_POST['branch'] ?? '');
        $year= (int)($_POST['year'] ?? 1);
        if(!$enr||!$name||!$branch){ flash('error','Enrollment, name, branch required'); header('Location: /students/new'); exit; }
        $pdo=Database::pdo();
        try{
            $pdo->beginTransaction();
            $username=$enr;
            $hash=password_hash($enr, PASSWORD_BCRYPT);
            $pdo->prepare("INSERT INTO users (username,password_hash,role,must_change_password,display_name) VALUES (?,?,?,?,?)")->execute([$username,$hash,'student',1,$name]);
            $uid=$pdo->lastInsertId();
            $pdo->prepare("INSERT INTO students (user_id,enrollment_no,library_id,barcode_data,name,branch,year,contact,email,status) VALUES (?,?,?,?,?,?,?,?,?, 'active')")->execute([$uid,$enr,null,null,$name,$branch,$year, $_POST['contact'] ?? null, $_POST['email'] ?? null]);
            $pdo->commit();
            flash('success',"Borrower $name ($enr) created");
            header('Location: /students'); exit;
        }catch(\Throwable $e){ $pdo->rollBack(); flash('error',$e->getMessage()); header('Location: /students/new'); exit; }
    }

    public function update(string $id): void {        Auth::requireRole('librarian');
        $s = Database::one("SELECT * FROM students WHERE id=?", [$id]);
        if(!$s){ flash('error','Borrower not found'); header('Location: /students'); exit; }
        // enrollment_no is the login username — kept read-only to avoid desyncing auth.
        $name = trim($_POST['name'] ?? '');
        $branch = trim($_POST['branch'] ?? '');
        if(!$name || !$branch){ flash('error','Name and branch required'); header("Location: /students/$id?edit=1"); exit; }
        $year = min(8, max(1, (int)($_POST['year'] ?? $s['year'])));
        $status = $_POST['status'] ?? $s['status'];
        if(!in_array($status, ['active','passed_out','inactive'], true)) $status = $s['status'];
        $library_id = trim($_POST['library_id'] ?? '');
        if($library_id === '') $library_id = null;
        $contact = trim($_POST['contact'] ?? '');
        $email = trim($_POST['email'] ?? '');
        try {
            Database::exec(
                "UPDATE students SET name=?, branch=?, year=?, contact=?, email=?, status=?, library_id=? WHERE id=?",
                [$name, $branch, $year, $contact === '' ? null : $contact, $email === '' ? null : $email, $status, $library_id, $id]
            );
            Database::exec("UPDATE users SET display_name=? WHERE id=?", [$name, $s['user_id']]);
        } catch(\Throwable $e){ flash('error',$e->getMessage()); header("Location: /students/$id?edit=1"); exit; }
        flash('success',"Borrower $name updated");
        header("Location: /students/$id"); exit;
    }

    public function delete(string $id): void {
        Auth::requireRole('librarian');
        $s = Database::one("SELECT * FROM students WHERE id=?", [$id]);
        if(!$s){ flash('error','Borrower not found'); header('Location: /students'); exit; }
        $active = (int)(Database::one("SELECT COUNT(*) as c FROM issues WHERE student_id=? AND status IN ('issued','overdue')", [$id])['c'] ?? 0);
        if($active > 0){ flash('error', $s['name']." has $active book(s) out — return them before removing the borrower"); header("Location: /students/$id"); exit; }
        Sync::tombstone('students', $id);
        Sync::tombstone('users', $s['user_id']);
        Database::exec("DELETE FROM students WHERE id=?", [$id]);
        Database::exec("DELETE FROM users WHERE id=?", [$s['user_id']]);
        flash('success',"Borrower {$s['name']} removed with linked loans, fines and login");
        header('Location: /students'); exit;
    }

    /** Full borrower record as JSON for the popup card. */
    public function cardJson(string $id): void {
        Auth::requireRole('librarian');
        $s = Database::one("SELECT s.*, u.username FROM students s JOIN users u ON u.id=s.user_id WHERE s.id=?", [$id]);
        if(!$s){ http_response_code(404); header('Content-Type: application/json'); echo json_encode(['error'=>'Not found']); exit; }
        $issues = Database::query("SELECT i.*, b.title as book_title, b.isbn as book_isbn, bc.accession_no FROM issues i JOIN books b ON b.id=i.book_id LEFT JOIN book_copies bc ON bc.id=i.book_copy_id WHERE i.student_id=? ORDER BY i.created_at DESC LIMIT 50", [$id]);
        $fines = Database::query("SELECT f.*, b.title as book_title FROM fines f JOIN issues i ON i.id=f.issue_id JOIN books b ON b.id=i.book_id WHERE f.student_id=? ORDER BY f.created_at DESC LIMIT 50", [$id]);
        $visits = Database::query("SELECT * FROM library_visits WHERE student_id=? ORDER BY check_in_time DESC LIMIT 20", [$id]);
        $reservations = Database::query("SELECT r.*, b.title as book_title FROM book_reservations r JOIN books b ON b.id=r.book_id WHERE r.student_id=? ORDER BY r.reservation_date DESC LIMIT 20", [$id]);
        $today = date('Y-m-d');
        $active = 0; $overdue = 0;
        foreach($issues as $r){ if(in_array($r['status'],['issued','overdue'],true)){ $active++; if(($r['due_date'] ?? '') < $today) $overdue++; } }
        $pending = 0.0; $paid = 0.0;
        foreach($fines as $f){ if(($f['status'] ?? '')==='pending') $pending += (float)$f['amount']; else $paid += (float)$f['amount']; }
        header('Content-Type: application/json');
        echo json_encode(['record'=>$s,'stats'=>['active'=>$active,'total'=>count($issues),'pending'=>$pending,'paid'=>$paid,'overdue'=>$overdue],'issues'=>$issues,'fines'=>$fines,'visits'=>$visits,'reservations'=>$reservations]); exit;
    }
}
