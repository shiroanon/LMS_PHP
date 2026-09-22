<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;

class SupplierController {
    public function index(): void {
        Auth::requireRole('librarian');
        $q = trim($_GET['search'] ?? '');
        $where = $q!=='' ? "WHERE name LIKE ? OR shop_name LIKE ? OR location LIKE ?" : "WHERE 1=1";
        $params = $q!=='' ? ["%$q%","%$q%","%$q%"] : [];
        $suppliers = Database::query("SELECT s.*, (SELECT COUNT(*) FROM purchase_bills WHERE supplier_id=s.id) as bills FROM suppliers s $where ORDER BY s.name ASC LIMIT 100", $params);
        View::render('librarian/suppliers', ['suppliers'=>$suppliers,'user'=>Auth::user()]);
    }
    public function create(): void {
        Auth::requireRole('librarian');
        $name=trim($_POST['name'] ?? ''); if(!$name){ flash('error','Name required'); header('Location: /suppliers'); exit; }
        Database::exec("INSERT INTO suppliers (name, shop_name, location, mobile, email, gst_no) VALUES (?,?,?,?,?,?)", [$name, $_POST['shop_name']??'', $_POST['location']??'', $_POST['mobile']??'', $_POST['email']??'', $_POST['gst_no']??'']);
        flash('success',"Supplier $name added"); header('Location: /suppliers'); exit;
    }
}
