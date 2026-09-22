<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Sync;
use App\Core\View;

class StaffController {
    public function index(): void {
        Auth::requireRole('librarian');
        $search = trim($_GET['search'] ?? '');
        $cat = trim($_GET['loan_category'] ?? '');
        $page = max(1,(int)($_GET['page'] ?? 1));
        $limit = 15;
        $off = ($page-1)*$limit;
        $where="WHERE 1=1"; $params=[];
        if($search!==''){ $where.=" AND (name LIKE ? OR library_id LIKE ? OR loan_category LIKE ? OR membership_type LIKE ?)"; $s="%$search%"; array_push($params,$s,$s,$s,$s); }
        if($cat!==''){ $where.=" AND loan_category=?"; $params[]=$cat; }
        $total = (int)(Database::one("SELECT COUNT(*) as c FROM staff_members $where", $params)['c'] ?? 0);
        // sorting like the original app: sort_by whitelist + sort_order, id tiebreak
        $sortMap = ['staff_id'=>'library_id','name'=>'name','department'=>'loan_category','type'=>'membership_type','status'=>'status','created'=>'id'];
        $sortBy = $sortMap[$_GET['sort_by'] ?? ''] ?? 'name';
        $sortKey = array_search($sortBy, $sortMap, true);
        $sortOrder = strtolower($_GET['sort_order'] ?? '') === 'desc' ? 'DESC' : 'ASC';
        $staff = Database::query("SELECT * FROM staff_members $where ORDER BY $sortBy $sortOrder, id ASC LIMIT $limit OFFSET $off", $params);
        $cats = array_column(Database::query("SELECT DISTINCT loan_category FROM staff_members WHERE loan_category IS NOT NULL AND loan_category<>'' ORDER BY loan_category"), 'loan_category');
        $pages = max(1,(int)ceil($total/$limit));
        View::render('librarian/staff', ['staff'=>$staff,'cats'=>$cats,'total'=>$total,'pages'=>$pages,'page'=>$page,'sortKey'=>$sortKey,'sortOrder'=>$sortOrder,'user'=>Auth::user()]);
    }

    public function show(string $id): void {
        Auth::requireRole('librarian');
        $s = Database::one("SELECT * FROM staff_members WHERE id=?", [$id]);
        if(!$s){ http_response_code(404); View::render('partials/404',['user'=>Auth::user()]); return; }
        $issues = Database::query("SELECT i.*, b.title, b.book_id as book_code, bc.accession_no FROM issues i JOIN books b ON b.id=i.book_id LEFT JOIN book_copies bc ON bc.id=i.book_copy_id WHERE i.staff_id=? ORDER BY i.created_at DESC", [$id]);
        $fines = Database::query("SELECT f.*, b.title FROM fines f JOIN issues i ON f.issue_id=i.id JOIN books b ON i.book_id=b.id WHERE f.staff_id=? ORDER BY f.created_at DESC", [$id]);
        $sum = Database::one("SELECT COALESCE(SUM(amount),0) as total, COALESCE(SUM(CASE WHEN status='pending' THEN amount ELSE 0 END),0) as pending FROM fines WHERE staff_id=?", [$id]);
        $active = count(array_filter($issues, fn($r)=>in_array($r['status'],['issued','overdue'])));
        $isExpired = false;
        if(!empty($s['expiration_date']) && preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/',$s['expiration_date'],$m)){
            $exp = mktime(0,0,0,(int)$m[2],(int)$m[1],(int)$m[3]);
            $isExpired = $exp < strtotime(date('Y-m-d'));
        }
        View::render('librarian/staff_detail', ['s'=>$s,'issues'=>$issues,'fines'=>$fines,'sum'=>$sum,'active'=>$active,'isExpired'=>$isExpired,'user'=>Auth::user()]);
    }

    public function create(): void {
        Auth::requireRole('librarian');
        $name=trim($_POST['name'] ?? '');
        if(!$name){ flash('error','Name required'); header('Location: /staff'); exit; }
        $status = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';
        Database::exec("INSERT INTO staff_members (library_id, loan_category, membership_type, name, expiration_date, status) VALUES (?,?,?,?,?,?)", [trim($_POST['library_id'] ?? ''), trim($_POST['loan_category'] ?? ''), trim($_POST['membership_type'] ?? ''), $name, trim($_POST['expiration_date'] ?? ''), $status]);
        flash('success',"Staff $name created");
        header('Location: /staff'); exit;
    }

    public function update(string $id): void {
        Auth::requireRole('librarian');
        $s=Database::one("SELECT * FROM staff_members WHERE id=?", [$id]);
        if(!$s){ http_response_code(404); die('Not found'); }
        $status = $_POST['status'] ?? $s['status'] ?? 'active';
        if(!in_array($status, ['active','inactive'], true)) $status = 'active';
        Database::exec("UPDATE staff_members SET library_id=?, loan_category=?, membership_type=?, name=?, expiration_date=?, status=? WHERE id=?", [$_POST['library_id'] ?? $s['library_id'], $_POST['loan_category'] ?? $s['loan_category'], $_POST['membership_type'] ?? $s['membership_type'], $_POST['name'] ?? $s['name'], $_POST['expiration_date'] ?? $s['expiration_date'], $status, $id]);
        flash('success','Staff updated');
        header('Location: /staff/'.$id); exit;
    }

    public function delete(string $id): void {
        Auth::requireRole('librarian');
        $s = Database::one("SELECT * FROM staff_members WHERE id=?", [$id]);
        if(!$s){ flash('error','Staff not found'); header('Location: /staff'); exit; }
        $active = (int)(Database::one("SELECT COUNT(*) as c FROM issues WHERE staff_id=? AND status IN ('issued','overdue')", [$id])['c'] ?? 0);
        if($active > 0){ flash('error', $s['name']." has $active book(s) out — return them before removing the record"); header('Location: /staff/'.$id); exit; }
        Sync::tombstone('staff_members', $id);
        Database::exec("DELETE FROM staff_members WHERE id=?", [$id]);
        flash('success','Staff deleted');
        header('Location: /staff'); exit;
    }

    /** Full staff record as JSON for the popup card. */
    public function cardJson(string $id): void {
        Auth::requireRole('librarian');
        $s = Database::one("SELECT * FROM staff_members WHERE id=?", [$id]);
        if(!$s){ http_response_code(404); header('Content-Type: application/json'); echo json_encode(['error'=>'Not found']); exit; }
        $issues = Database::query("SELECT i.*, b.title as book_title, b.isbn as book_isbn, b.book_id as book_code, bc.accession_no FROM issues i JOIN books b ON b.id=i.book_id LEFT JOIN book_copies bc ON bc.id=i.book_copy_id WHERE i.staff_id=? ORDER BY i.created_at DESC LIMIT 50", [$id]);
        $fines = Database::query("SELECT f.*, b.title as book_title FROM fines f JOIN issues i ON f.issue_id=i.id JOIN books b ON i.book_id=b.id WHERE f.staff_id=? ORDER BY f.created_at DESC LIMIT 50", [$id]);
        $today = date('Y-m-d');
        $active = 0; $overdue = 0;
        foreach($issues as $r){ if(in_array($r['status'],['issued','overdue'],true)){ $active++; if(($r['due_date'] ?? '') < $today) $overdue++; } }
        $pending = 0.0; $paid = 0.0;
        foreach($fines as $f){ if(($f['status'] ?? '')==='pending') $pending += (float)$f['amount']; else $paid += (float)$f['amount']; }
        $isExpired = false;
        if(!empty($s['expiration_date']) && preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/',$s['expiration_date'],$m)){
            $isExpired = mktime(0,0,0,(int)$m[2],(int)$m[1],(int)$m[3]) < strtotime(date('Y-m-d'));
        }
        header('Content-Type: application/json');
        echo json_encode(['record'=>$s,'expired'=>$isExpired,'stats'=>['active'=>$active,'total'=>count($issues),'pending'=>$pending,'paid'=>$paid,'overdue'=>$overdue],'issues'=>$issues,'fines'=>$fines]); exit;
    }
}
