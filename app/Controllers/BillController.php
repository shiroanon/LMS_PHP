<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;

class BillController {
    public function index(): void {
        Auth::requireRole('librarian');
        $bills = Database::query("SELECT pb.*, s.name as supplier_name FROM purchase_bills pb JOIN suppliers s ON s.id=pb.supplier_id ORDER BY pb.purchase_date DESC, pb.id DESC LIMIT 100");
        $suppliers = Database::query("SELECT id, name FROM suppliers ORDER BY name");
        $summary = Database::one("SELECT COALESCE(SUM(total_amount),0) as total, COUNT(*) as cnt FROM purchase_bills");
        View::render('librarian/bills', ['bills'=>$bills,'suppliers'=>$suppliers,'summary'=>$summary,'user'=>Auth::user()]);
    }
    public function show(string $id): void {
        Auth::requireRole('librarian');
        $bill = Database::one("SELECT pb.*, s.name as supplier_name, s.shop_name, s.mobile FROM purchase_bills pb JOIN suppliers s ON s.id=pb.supplier_id WHERE pb.id=?", [$id]);
        if(!$bill){ http_response_code(404); View::render('partials/404',['user'=>Auth::user()]); return; }
        $items = Database::query("SELECT pbi.*, b.title FROM purchase_bill_items pbi LEFT JOIN books b ON b.id=pbi.book_id WHERE pbi.bill_id=?", [$id]);
        View::render('librarian/bill_detail', ['bill'=>$bill,'items'=>$items,'user'=>Auth::user()]);
    }
    public function create(): void {
        Auth::requireRole('librarian');
        $bill_no=trim($_POST['bill_no'] ?? ''); $supplier_id=(int)($_POST['supplier_id'] ?? 0); $date=trim($_POST['purchase_date'] ?? date('Y-m-d')); $total=(float)($_POST['total_amount'] ?? 0);
        if(!$bill_no || !$supplier_id || !$total){ flash('error','Bill no, supplier and total required'); header('Location: /bills'); exit; }
        try{
            Database::exec("INSERT INTO purchase_bills (bill_no, supplier_id, purchase_date, total_amount, payment_mode) VALUES (?,?,?,?,?)", [$bill_no,$supplier_id,$date,$total, $_POST['payment_mode'] ?? 'cash']);
            flash('success',"Bill $bill_no created");
        }catch(\Throwable $e){ flash('error','Bill no already exists'); }
        header('Location: /bills'); exit;
    }
    public function delete(string $id): void {
        Auth::requireRole('librarian');
        Database::exec("DELETE FROM purchase_bills WHERE id=?", [$id]);
        flash('success','Bill deleted'); header('Location: /bills'); exit;
    }
}
