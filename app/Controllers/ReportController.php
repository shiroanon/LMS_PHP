<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;

class ReportController {
    public function index(): void {
        Auth::requireRole('librarian');
        $branch_stats = Database::query("SELECT branch, COUNT(*) as cnt FROM students GROUP BY branch ORDER BY cnt DESC");
        $fine_branch = Database::query("SELECT COALESCE(s.branch, st.loan_category, 'Unknown') as branch, SUM(CASE WHEN f.status='paid' THEN f.amount ELSE 0 END) as paid_amount, SUM(CASE WHEN f.status='pending' THEN f.amount ELSE 0 END) as pending_amount, SUM(f.amount) as total_amount FROM fines f LEFT JOIN students s ON f.student_id=s.id LEFT JOIN staff_members st ON f.staff_id=st.id GROUP BY COALESCE(s.branch, st.loan_category, 'Unknown') ORDER BY total_amount DESC");
        $purchase_stats = Database::query("SELECT DATE_FORMAT(purchase_date,'%Y-%m') as month, SUM(total_amount) as total_spent, COUNT(*) as bills_count FROM purchase_bills GROUP BY month ORDER BY month ASC");
        $visit_stats = Database::query("SELECT s.branch, COUNT(*) as cnt FROM library_visits lv JOIN students s ON lv.student_id=s.id GROUP BY s.branch ORDER BY cnt DESC");
        $branches = array_column(Database::query("SELECT DISTINCT branch FROM students WHERE branch IS NOT NULL AND branch<>'' ORDER BY branch"), 'branch');
        $suppliers = Database::query("SELECT id, name FROM suppliers ORDER BY name");
        $categories = array_column(Database::query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL AND category<>'' ORDER BY category"), 'category');
        View::render('librarian/reports', ['branch_stats'=>$branch_stats,'fine_branch'=>$fine_branch,'purchase_stats'=>$purchase_stats,'visit_stats'=>$visit_stats,'branches'=>$branches,'suppliers'=>$suppliers,'categories'=>$categories,'user'=>Auth::user()]);
    }

    // GET /reports/custom?type=issues&... returns JSON
    public function custom(): void {
        Auth::requireRole('librarian');
        $type = $_GET['type'] ?? 'issues';
        $data = $this->queryCustom($type, $_GET);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    private function queryCustom(string $type, array $f): array {
        switch($type){
            case 'issues': return $this->customIssues($f);
            case 'fines': return $this->customFines($f);
            case 'students': return $this->customStudents($f);
            case 'books': return $this->customBooks($f);
            case 'visits': return $this->customVisits($f);
            case 'suppliers': return $this->customSuppliers($f);
            case 'bills': return $this->customBills($f);
            default: return [];
        }
    }

    private function customIssues(array $f): array {
        $where="WHERE 1=1"; $p=[];
        if(!empty($f['start_date'])){ $where.=" AND i.issue_date >= ?"; $p[]=$f['start_date']; }
        if(!empty($f['end_date'])){ $where.=" AND i.issue_date <= ?"; $p[]=$f['end_date']; }
        if(!empty($f['branch']) && empty($f['member_id'])){ $where.=" AND (s.branch = ? OR st.loan_category = ?)"; $p[]=$f['branch']; $p[]=$f['branch']; }
        if(!empty($f['status'])){ $where.=" AND i.status = ?"; $p[]=$f['status']; }
        if(!empty($f['member_type']) && $f['member_type']!=='all'){ $where.=" AND i.member_type = ?"; $p[]=$f['member_type']; }
        if(!empty($f['member_id'])){ $where.=" AND (s.enrollment_no = ? OR s.library_id = ? OR s.barcode_data = ? OR st.library_id = ?)"; $p[]=$f['member_id']; $p[]=$f['member_id']; $p[]=$f['member_id']; $p[]=$f['member_id']; }
        $bookCode = $f['book_code'] ?? $f['book_id'] ?? $f['accession_no'] ?? '';
        if($bookCode!==''){ $where.=" AND COALESCE(bc.accession_no, b.book_id) = ?"; $p[]=trim($bookCode); }
        if(!empty($f['q']) && empty($f['member_id']) && $bookCode===''){ $like='%'.$f['q'].'%'; $where.=" AND (s.name LIKE ? OR st.name LIKE ? OR s.enrollment_no LIKE ? OR st.library_id LIKE ? OR b.title LIKE ? OR b.author LIKE ?)"; array_push($p,$like,$like,$like,$like,$like,$like); }
        return Database::query("SELECT COALESCE(s.enrollment_no, st.library_id) as member_id, COALESCE(s.name, st.name) as member_name, COALESCE(s.branch, st.loan_category) as branch, i.member_type, COALESCE(bc.accession_no, b.book_id) as book_code, b.title as book_title, i.issue_date, i.due_date, i.return_date, i.status FROM issues i JOIN books b ON b.id=i.book_id LEFT JOIN book_copies bc ON bc.id=i.book_copy_id LEFT JOIN students s ON s.id=i.student_id LEFT JOIN staff_members st ON st.id=i.staff_id $where ORDER BY i.issue_date DESC", $p);
    }

    private function customFines(array $f): array {
        $where="WHERE 1=1"; $p=[];
        if(!empty($f['member_id'])){ $where.=" AND (s.enrollment_no = ? OR s.library_id = ? OR s.barcode_data = ? OR st.library_id = ?)"; $p[]=$f['member_id']; $p[]=$f['member_id']; $p[]=$f['member_id']; $p[]=$f['member_id']; }
        if(!empty($f['start_date'])){ $where.=" AND DATE(f.created_at) >= ?"; $p[]=$f['start_date']; }
        if(!empty($f['end_date'])){ $where.=" AND DATE(f.created_at) <= ?"; $p[]=$f['end_date']; }
        if(!empty($f['branch']) && empty($f['member_id'])){ $where.=" AND (s.branch = ? OR st.loan_category = ?)"; $p[]=$f['branch']; $p[]=$f['branch']; }
        if(!empty($f['status'])){ $where.=" AND f.status = ?"; $p[]=$f['status']; }
        if(!empty($f['member_type']) && $f['member_type']!=='all'){ $where.=" AND f.member_type = ?"; $p[]=$f['member_type']; }
        if(!empty($f['q']) && empty($f['member_id'])){ $like='%'.$f['q'].'%'; $where.=" AND (s.name LIKE ? OR st.name LIKE ? OR b.title LIKE ?)"; array_push($p,$like,$like,$like); }
        return Database::query("SELECT COALESCE(s.enrollment_no, st.library_id) as member_id, COALESCE(s.name, st.name) as member_name, COALESCE(s.branch, st.loan_category) as branch, f.member_type, b.title as book_title, f.amount, DATEDIFF(COALESCE(i.return_date, CURDATE()), i.due_date) as days_late, f.status, f.created_at, f.paid_at FROM fines f LEFT JOIN students s ON s.id=f.student_id LEFT JOIN staff_members st ON st.id=f.staff_id JOIN issues i ON f.issue_id=i.id JOIN books b ON b.id=i.book_id $where ORDER BY f.created_at DESC", $p);
    }

    private function customStudents(array $f): array {
        $where="WHERE 1=1"; $p=[];
        if(!empty($f['branch'])){ $where.=" AND s.branch = ?"; $p[]=$f['branch']; }
        if(!empty($f['year'])){ $where.=" AND s.year = ?"; $p[]=(int)$f['year']; }
        $data = Database::query("SELECT s.enrollment_no, s.name, s.branch, s.year, s.contact, s.email, (SELECT COUNT(*) FROM issues WHERE student_id=s.id AND status IN ('issued','overdue')) as active_issues, (SELECT COALESCE(SUM(amount),0) FROM fines WHERE student_id=s.id AND status='pending') as pending_fines FROM students s $where ORDER BY s.name ASC", $p);
        if(($f['has_fines'] ?? '')==='true') $data = array_filter($data, fn($r)=> (float)$r['pending_fines']>0);
        if(!empty($f['q'])){
            $q=strtolower($f['q']); $data=array_filter($data, fn($r)=> str_contains(strtolower($r['name']??''), $q) || str_contains(strtolower($r['enrollment_no']??''), $q));
        }
        return array_values($data);
    }

    private function customBooks(array $f): array {
        $where="WHERE 1=1"; $p=[];
        if(!empty($f['category'])){ $where.=" AND EXISTS (SELECT 1 FROM book_categories bc WHERE bc.book_id=b.id AND bc.category=?)"; $p[]=$f['category']; }
        if(!empty($f['supplier_id'])){ $where.=" AND b.supplier_id = ?"; $p[]=(int)$f['supplier_id']; }
        if(($f['availability'] ?? '')==='available') $where.=" AND b.quantity_available > 0";
        elseif(($f['availability'] ?? '')==='out_of_stock') $where.=" AND b.quantity_available = 0";
        if(!empty($f['q'])){ $like='%'.$f['q'].'%'; $where.=" AND (b.title LIKE ? OR b.author LIKE ? OR b.book_id LIKE ?)"; array_push($p,$like,$like,$like); }
        return Database::query("SELECT b.book_id as book_code, b.title, b.author, b.isbn, b.category, b.quantity_total, b.quantity_available, b.shelf_location, s.name as supplier_name FROM books b LEFT JOIN suppliers s ON s.id=b.supplier_id $where ORDER BY b.title ASC", $p);
    }

    private function customVisits(array $f): array {
        $where="WHERE 1=1"; $p=[];
        if(!empty($f['start_date'])){ $where.=" AND DATE(lv.check_in_time) >= ?"; $p[]=$f['start_date']; }
        if(!empty($f['end_date'])){ $where.=" AND DATE(lv.check_in_time) <= ?"; $p[]=$f['end_date']; }
        if(!empty($f['branch'])){ $where.=" AND s.branch = ?"; $p[]=$f['branch']; }
        if(!empty($f['q'])){ $like='%'.$f['q'].'%'; $where.=" AND (s.name LIKE ? OR s.enrollment_no LIKE ?)"; array_push($p,$like,$like); }
        return Database::query("SELECT s.enrollment_no, s.name as student_name, s.branch, s.year, lv.check_in_time, lv.check_out_time, lv.method, lv.thumb_verified FROM library_visits lv JOIN students s ON s.id=lv.student_id $where ORDER BY lv.check_in_time DESC", $p);
    }

    private function customSuppliers(array $f): array {
        $data = Database::query("SELECT sup.id, sup.name, sup.shop_name as contact_person, sup.mobile as phone, sup.email, sup.location as address, (SELECT COUNT(*) FROM purchase_bills WHERE supplier_id=sup.id) as bills_count, (SELECT COALESCE(SUM(total_amount),0) FROM purchase_bills WHERE supplier_id=sup.id) as total_purchases FROM suppliers sup ORDER BY sup.name ASC");
        if(($f['has_bills'] ?? '')==='true') $data=array_filter($data, fn($r)=> (int)$r['bills_count']>0);
        if(($f['has_bills'] ?? '')==='false') $data=array_filter($data, fn($r)=> (int)$r['bills_count']==0);
        if(!empty($f['q'])){ $q=strtolower($f['q']); $data=array_filter($data, fn($r)=> str_contains(strtolower($r['name']),$q)); }
        return array_values($data);
    }

    private function customBills(array $f): array {
        $where="WHERE 1=1"; $p=[];
        if(!empty($f['start_date'])){ $where.=" AND pb.purchase_date >= ?"; $p[]=$f['start_date']; }
        if(!empty($f['end_date'])){ $where.=" AND pb.purchase_date <= ?"; $p[]=$f['end_date']; }
        if(!empty($f['supplier_id'])){ $where.=" AND pb.supplier_id = ?"; $p[]=(int)$f['supplier_id']; }
        if(!empty($f['payment_mode'])){ $where.=" AND pb.payment_mode = ?"; $p[]=$f['payment_mode']; }
        if(!empty($f['q'])){ $like='%'.$f['q'].'%'; $where.=" AND (pb.bill_no LIKE ? OR sup.name LIKE ?)"; array_push($p,$like,$like); }
        return Database::query("SELECT pb.id, pb.bill_no, pb.purchase_date, pb.payment_mode, pb.total_amount, CASE WHEN pb.payment_mode='credit' THEN 'pending' ELSE 'paid' END as status, sup.name as supplier_name FROM purchase_bills pb JOIN suppliers sup ON sup.id=pb.supplier_id $where ORDER BY pb.purchase_date DESC", $p);
    }

    public function export(): void {
        Auth::requireRole('librarian');
        $type = $_GET['type'] ?? 'students';
        // support old export types
        if(in_array($type, ['issued','returned','overdue','all-issues'])){
            $where=""; $params=[];
            if($type==='issued') $where="WHERE i.status='issued'";
            elseif($type==='returned') $where="WHERE i.status='returned'";
            elseif($type==='overdue') $where="WHERE i.status='overdue' OR (i.status='issued' AND i.due_date < CURDATE())";
            $data = Database::query("SELECT i.id, COALESCE(s.enrollment_no, st.library_id) as member_id, COALESCE(s.name, st.name) as member_name, COALESCE(s.branch, st.loan_category) as branch, i.member_type, COALESCE(bc.accession_no, b.book_id) as book_code, b.title as book_title, i.issue_date, i.due_date, i.return_date, i.status FROM issues i JOIN books b ON b.id=i.book_id LEFT JOIN book_copies bc ON bc.id=i.book_copy_id LEFT JOIN students s ON s.id=i.student_id LEFT JOIN staff_members st ON st.id=i.staff_id $where ORDER BY i.created_at DESC", $params);
            $filename=$type."_report.xlsx";
            $this->sendXlsx($data, $type, $filename); return;
        }
        if($type==='suppliers'){
            $data=Database::query("SELECT sup.id, sup.name, sup.shop_name as contact_person, sup.mobile as phone, sup.email, sup.location as address, (SELECT COUNT(*) FROM purchase_bills WHERE supplier_id=sup.id) as bills_count, (SELECT COALESCE(SUM(total_amount),0) FROM purchase_bills WHERE supplier_id=sup.id) as total_purchases FROM suppliers sup ORDER BY sup.name ASC");
            $this->sendXlsx($data,'Suppliers','suppliers_report.xlsx'); return;
        }
        if($type==='visits'){
            $data=Database::query("SELECT lv.id, s.enrollment_no, s.name as student_name, s.branch, s.year, lv.check_in_time, lv.check_out_time, lv.method, lv.thumb_verified FROM library_visits lv JOIN students s ON s.id=lv.student_id ORDER BY lv.check_in_time DESC");
            $this->sendXlsx($data,'Visits','visits_report.xlsx'); return;
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="'.$type.'_'.date('Ymd').'.csv"');
        $out = fopen('php://output','w');
        if($type==='students'){
            fputcsv($out, ['enrollment_no','name','branch','year','status']);
            foreach(Database::query("SELECT enrollment_no,name,branch,year,status FROM students ORDER BY branch, year") as $r) fputcsv($out, $r);
        } elseif($type==='books'){
            fputcsv($out, ['book_id','title','author','category','total','available']);
            foreach(Database::query("SELECT book_id,title,author,category,quantity_total,quantity_available FROM books ORDER BY title") as $r) fputcsv($out, $r);
        } elseif($type==='fines'){
            fputcsv($out, ['student','book','amount','status','due_date']);
            foreach(Database::query("SELECT COALESCE(s.name, st.name) as student, b.title as book, f.amount, f.status, i.due_date FROM fines f JOIN issues i ON i.id=f.issue_id JOIN books b ON b.id=i.book_id LEFT JOIN students s ON s.id=f.student_id LEFT JOIN staff_members st ON st.id=f.staff_id ORDER BY f.created_at DESC") as $r) fputcsv($out, $r);
        }
        fclose($out); exit;
    }

    private function sendXlsx(array $data, string $sheet, string $filename): void {
        if(class_exists('\\PhpOffice\\PhpSpreadsheet\\Spreadsheet')){
            $ss=new \PhpOffice\PhpSpreadsheet\Spreadsheet(); $sh=$ss->getActiveSheet(); $sh->setTitle(substr($sheet,0,31));
            if($data){ $sh->fromArray(array_keys($data[0]), null, 'A1'); $sh->fromArray($data, null, 'A2'); }
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); header('Content-Disposition: attachment; filename="'.$filename.'"');
            $w=new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($ss); $w->save('php://output'); exit;
        }
        // fallback CSV
        header('Content-Type: text/csv; charset=utf-8'); header('Content-Disposition: attachment; filename="'.pathinfo($filename, PATHINFO_FILENAME).'.csv"');
        $out=fopen('php://output','w');
        if($data){ fputcsv($out, array_keys($data[0])); foreach($data as $r) fputcsv($out, $r); }
        fclose($out); exit;
    }

    public function pdf(): void {
        Auth::requireRole('librarian');
        $type=$_GET['type'] ?? $_POST['type'] ?? 'books';
        $filters=$_POST['filters'] ?? $_GET;
        if(isset($filters['type'])) unset($filters['type']);
        // support JSON body
        $raw=file_get_contents('php://input');
        if($raw && str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'json')){
            $j=json_decode($raw,true); if(isset($j['filters'])) $filters=$j['filters']; if(isset($j['type'])) $type=$j['type'];
        }
        $rows=$this->queryCustom($type, is_array($filters)?$filters:[]);
        // limit PDF rows to avoid memory exhaustion (2925 books → huge table)
        $pdfRows = array_slice($rows, 0, 500);
        $truncated = count($rows) > 500;
        if(class_exists('\\Dompdf\\Dompdf')){
            @ini_set('memory_limit','512M');
            $html='<style>table{font-size:9px; width:100%; border-collapse:collapse} th{ background:#8B2D3B; color:white; padding:4px } td{ padding:3px; border:1px solid #ddd } h2{ font-family: sans-serif; color:#0F1D2E }</style>';
            $html.='<h2>'.htmlspecialchars(ucfirst($type)).' Report</h2><p>'.count($rows).' records'.($truncated ? ' — showing first 500 for PDF, use Excel for full' : '').' — '.date('Y-m-d H:i').'</p><table><tr>';
            if($pdfRows){ foreach(array_keys($pdfRows[0]) as $h) $html.='<th>'.htmlspecialchars($h).'</th>'; $html.='</tr>'; foreach($pdfRows as $r){ $html.='<tr>'; foreach($r as $v) $html.='<td>'.htmlspecialchars(mb_strimwidth((string)$v,0,80,'…')).'</td>'; $html.='</tr>'; } } else $html.='<td>No data</td></tr>';
            $html.='</table>';
            if($truncated) $html.='<p style="font-size:9px; color:#677387">Truncated to 500 rows for PDF — download Excel for complete data.</p>';
            $dom=new \Dompdf\Dompdf(['isRemoteEnabled'=>false, 'isHtml5ParserEnabled'=>true]); $dom->loadHtml($html); $dom->setPaper('A4','landscape'); $dom->render();
            header('Content-Type: application/pdf'); header('Content-Disposition: attachment; filename="'.$type.'_report.pdf"'); echo $dom->output(); exit;
        }
        // fallback CSV as PDF placeholder — send HTML
        header('Content-Type: text/html; charset=utf-8');
        echo '<h2>'.htmlspecialchars($type).' Report — '.count($rows).' records</h2><table border="1" cellpadding="4" style="font-size:11px; border-collapse:collapse">';
        if($rows){ echo '<tr>'; foreach(array_keys($rows[0]) as $h) echo '<th>'.htmlspecialchars($h).'</th>'; echo '</tr>'; foreach(array_slice($rows,0,200) as $r){ echo '<tr>'; foreach($r as $v) echo '<td>'.htmlspecialchars((string)$v).'</td>'; echo '</tr>'; } }
        echo '</table><p>PDF generation requires dompdf — install via composer. Showing HTML preview. Use Excel export for full data.</p>'; exit;
    }
}
