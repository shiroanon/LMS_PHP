<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;

class CirculationController {
    public function issueForm(): void {
        Auth::requireRole('librarian');
        View::render('librarian/circulation', ['mode'=>'issue','user'=>Auth::user()]);
    }

    public function issue(): void {
        Auth::requireRole('librarian');
        $memberCode = trim($_POST['member_code'] ?? '');
        $accession = trim($_POST['accession'] ?? '');
        if(!$memberCode || !$accession){
            View::render('librarian/circulation', ['mode'=>'issue','error'=>'Borrower and accession required','user'=>Auth::user()]);
            return;
        }
        $pdo = Database::pdo();
        try{
            $pdo->beginTransaction();
            // resolve student or staff
            $student = Database::one("SELECT * FROM students WHERE enrollment_no=? OR library_id=? OR barcode_data=? LIMIT 1", [$memberCode,$memberCode,$memberCode]);
            $staff = null;
            $memberType='student';
            $sid=null; $staffId=null;
            if($student){ $sid=$student['id']; if($student['status']!=='active') throw new \Exception($student['name']." is ".$student['status'].". Cannot issue."); if(empty($student['library_id'])) throw new \Exception($student['name']." has no library ID — add one on the borrower record before issuing."); }
            else {
                $staff = Database::one("SELECT * FROM staff_members WHERE library_id=? LIMIT 1", [$memberCode]);
                if($staff){ $memberType='staff'; $staffId=$staff['id']; }
                else throw new \Exception("Borrower not found: $memberCode");
            }
            $copy = Database::one("SELECT * FROM book_copies WHERE accession_no=? OR barcode_data=? LIMIT 1", [$accession,$accession]);
            if(!$copy && ctype_digit($accession)){
                $padded = str_pad((int)$accession, 6, '0', STR_PAD_LEFT);
                if($padded !== $accession) $copy = Database::one("SELECT * FROM book_copies WHERE accession_no=? OR barcode_data=? LIMIT 1", [$padded,$padded]);
                if(!$copy){
                    $stripped = (string)(int)$accession;
                    if($stripped !== $accession && $stripped !== $padded) $copy = Database::one("SELECT * FROM book_copies WHERE accession_no=? OR barcode_data=? LIMIT 1", [$stripped,$stripped]);
                }
            }
            if(!$copy) throw new \Exception("Copy not found: $accession");
            if($copy['status']!=='available') throw new \Exception("Copy $accession is already ".$copy['status']);
            if($copy['is_reference']) throw new \Exception("Reference copy cannot be issued");
            $bookId = $copy['master_book_id'];
            $book = Database::one("SELECT * FROM books WHERE id=? FOR UPDATE", [$bookId]);
            if((int)$book['quantity_available']<=0) throw new \Exception("Out of stock");
            // duplicate active per copy
            $dup = Database::one("SELECT id FROM issues WHERE book_copy_id=? AND status IN ('issued','overdue')", [$copy['id']]);
            if($dup) throw new \Exception("Copy $accession already issued (active #{$dup['id']})");
            // per-member limit 3 and same title duplicate
            if($memberType==='student'){
                $cnt = (int)(Database::one("SELECT COUNT(*) as c FROM issues WHERE student_id=? AND status IN ('issued','overdue')", [$sid])['c'] ?? 0);
                if($cnt>=3) throw new \Exception("Borrower already has 3 books");
                $dupTitle = Database::one("SELECT id FROM issues WHERE student_id=? AND book_id=? AND status IN ('issued','overdue')", [$sid,$bookId]);
                if($dupTitle) throw new \Exception("This title already issued to this borrower");
            } else {
                $cnt = (int)(Database::one("SELECT COUNT(*) as c FROM issues WHERE staff_id=? AND status IN ('issued','overdue')", [$staffId])['c'] ?? 0);
                if($cnt>=3) throw new \Exception("Staff already has 3 books");
            }
            $issueDate = date('Y-m-d');
            $loanDays = $memberType==='staff' ? 90 : 14;
            $dueDate = date('Y-m-d', strtotime("+$loanDays days"));
            $pdo->prepare("INSERT INTO issues (student_id,staff_id,member_type,book_id,book_copy_id,issue_date,due_date,status,issued_by,barcode_scanned) VALUES (?,?,?,?,?,?,?,?,?,?)")
                ->execute([$sid,$staffId,$memberType,$bookId,$copy['id'],$issueDate,$dueDate,'issued', Auth::user()['id'], !empty($_POST['barcode_scanned'])?1:0]);
            $pdo->prepare("UPDATE books SET quantity_available=quantity_available-1 WHERE id=?")->execute([$bookId]);
            $pdo->prepare("UPDATE book_copies SET status='issued' WHERE id=?")->execute([$copy['id']]);
            $pdo->commit();
            View::render('librarian/circulation', ['mode'=>'issue','success'=>"Issued $accession to $memberCode — due $dueDate (".($memberType==='staff'?'90 days, staff':'14 days').")",'user'=>Auth::user()]);
        } catch(\Throwable $e){ $pdo->rollBack(); View::render('librarian/circulation', ['mode'=>'issue','error'=>$e->getMessage(),'user'=>Auth::user()]); }
    }

    public function returnForm(): void {
        Auth::requireRole('librarian');
        $search = trim($_GET['search'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $page = max(1,(int)($_GET['page'] ?? 1));
        $limit = 20; $off = ($page-1)*$limit;
        $where = "WHERE i.status IN ('issued','overdue')";
        $params = [];
        if($status==='issued' || $status==='overdue'){ $where = "WHERE i.status=?"; $params[]=$status; }
        if($search!==''){
            $where .= " AND (b.title LIKE ? OR bc.accession_no LIKE ? OR s.name LIKE ? OR s.enrollment_no LIKE ? OR st.name LIKE ?)";
            $s="%$search%"; array_push($params,$s,$s,$s,$s,$s);
        }
        $total = (int)(Database::one("SELECT COUNT(*) as c FROM issues i JOIN books b ON b.id=i.book_id LEFT JOIN book_copies bc ON bc.id=i.book_copy_id LEFT JOIN students s ON s.id=i.student_id LEFT JOIN staff_members st ON st.id=i.staff_id $where", $params)['c'] ?? 0);
        $pages = max(1,(int)ceil($total/$limit));
        $issued = Database::query("SELECT i.*, b.title, b.book_id as book_code, bc.accession_no, COALESCE(s.name, st.name) as borrower_name, COALESCE(s.enrollment_no, st.library_id) as borrower_id FROM issues i JOIN books b ON b.id=i.book_id LEFT JOIN book_copies bc ON bc.id=i.book_copy_id LEFT JOIN students s ON s.id=i.student_id LEFT JOIN staff_members st ON st.id=i.staff_id $where ORDER BY i.due_date ASC LIMIT $limit OFFSET $off", $params);
        $overdue = Database::query("SELECT i.due_date, b.title, bc.accession_no, COALESCE(s.name, st.name) as name FROM issues i JOIN books b ON b.id=i.book_id LEFT JOIN book_copies bc ON bc.id=i.book_copy_id LEFT JOIN students s ON s.id=i.student_id LEFT JOIN staff_members st ON st.id=i.staff_id WHERE i.status='overdue' ORDER BY i.due_date ASC LIMIT 5");
        View::render('librarian/circulation', ['mode'=>'return','issuedList'=>$issued,'overdue'=>$overdue,'pages'=>$pages,'page'=>$page,'total'=>$total,'search'=>$search,'status'=>$status,'user'=>Auth::user()]);
    }

    public function doReturn(): void {
        Auth::requireRole('librarian');
        $accession = trim($_POST['accession'] ?? '');
        if(!$accession){ View::render('librarian/circulation', ['mode'=>'return','error'=>'Accession required','user'=>Auth::user()]); return; }
        $pdo = Database::pdo();
        try{
            $pdo->beginTransaction();
            $copy = Database::one("SELECT * FROM book_copies WHERE accession_no=? OR barcode_data=? LIMIT 1", [$accession,$accession]);
            if(!$copy && ctype_digit($accession)){
                $padded = str_pad((int)$accession, 6, '0', STR_PAD_LEFT);
                if($padded !== $accession) $copy = Database::one("SELECT * FROM book_copies WHERE accession_no=? OR barcode_data=? LIMIT 1", [$padded,$padded]);
                if(!$copy){
                    $stripped = (string)(int)$accession;
                    if($stripped !== $accession && $stripped !== $padded) $copy = Database::one("SELECT * FROM book_copies WHERE accession_no=? OR barcode_data=? LIMIT 1", [$stripped,$stripped]);
                }
            }
            if(!$copy) throw new \Exception("Copy not found: $accession");
            $issue = Database::one("SELECT * FROM issues WHERE book_copy_id=? AND status IN ('issued','overdue') LIMIT 1", [$copy['id']]);
            if(!$issue) throw new \Exception("No active issue for $accession");
            // Verify borrower barcode if provided — prevents returning someone else's book on wrong name
            $scannedBorrower = trim($_POST['member_code'] ?? '');
            if($scannedBorrower !== ''){
                $expected = null; $expectedVals=[];
                if($issue['student_id']){
                    $stu = Database::one("SELECT enrollment_no, library_id, barcode_data FROM students WHERE id=?", [$issue['student_id']]);
                    if($stu) $expectedVals = array_filter([$stu['enrollment_no']??'', $stu['library_id']??'', $stu['barcode_data']??'']);
                } elseif($issue['staff_id']){
                    $st = Database::one("SELECT library_id FROM staff_members WHERE id=?", [$issue['staff_id']]);
                    if($st) $expectedVals = array_filter([$st['library_id']??'']);
                }
                $expectedVals = array_map(fn($v)=>strtolower(trim((string)$v)), $expectedVals);
                $scannedNorm = strtolower(trim($scannedBorrower));
                // also handle padded numeric comparison
                $scannedStripped = ltrim($scannedNorm,'0');
                $matches = in_array($scannedNorm, $expectedVals, true);
                if(!$matches){
                    // try stripped comparison for numeric barcodes
                    foreach($expectedVals as $ev){
                        if(ltrim($ev,'0') === $scannedStripped && $scannedStripped!==''){ $matches=true; break; }
                    }
                }
                if(!$matches){
                    $expDisplay = $expectedVals[0] ?? 'unknown';
                    throw new \Exception("Borrower mismatch — scanned “$scannedBorrower” does not match expected borrower “$expDisplay”. This book is not issued to $scannedBorrower. Please scan the correct card.");
                }
            }
            $returnDate = date('Y-m-d');
            // holiday-aware fine: simple net days minus holidays
            $due = $issue['due_date'];
            $net = 0;
            if($returnDate > $due){
                $total = (int)((strtotime($returnDate)-strtotime($due))/86400);
                $holidays = $pdo->query("SELECT holiday_date FROM holidays")->fetchAll(\PDO::FETCH_COLUMN);
                $cur = date('Y-m-d', strtotime($due.' +1 day'));
                $h=0;
                while($cur <= $returnDate){ if(in_array($cur,$holidays)) $h++; $cur=date('Y-m-d', strtotime($cur.' +1 day')); }
                $net = max(0,$total-$h);
            }
            $pdo->prepare("UPDATE issues SET return_date=?, status='returned', returned_by=? WHERE id=?")->execute([$returnDate, Auth::user()['id'], $issue['id']]);
            $pdo->prepare("UPDATE books SET quantity_available=quantity_available+1 WHERE id=?")->execute([$issue['book_id']]);
            $pdo->prepare("UPDATE book_copies SET status='available' WHERE id=?")->execute([$copy['id']]);
            if($net>0){
                $amt = $net*2;
                $pdo->prepare("INSERT INTO fines (issue_id,student_id,staff_id,member_type,amount,reason,status) VALUES (?,?,?,?,?,?, 'pending')")->execute([$issue['id'], $issue['student_id'], $issue['staff_id'], $issue['member_type'], $amt, 'late_return']);
            }
            // reservation sweep: notify next pending
            $next = Database::one("SELECT * FROM book_reservations WHERE book_id=? AND status='pending' ORDER BY reservation_date ASC LIMIT 1", [$issue['book_id']]);
            if($next){
                $pdo->prepare("UPDATE book_reservations SET status='notified', notification_date=NOW(), expires_at=DATE_ADD(NOW(), INTERVAL 1 DAY) WHERE id=?")->execute([$next['id']]);
            }
            $pdo->commit();
            $msg = "Returned $accession";
            if($net>0) $msg.=" — fine ₹".($net*2)." for $net net days";
            flash('success',$msg);
            header('Location: /return'); exit;
        } catch(\Throwable $e){ $pdo->rollBack(); flash('error',$e->getMessage()); header('Location: /return'); exit; }
    }
}
