<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;

class DisposedController {
    public function index(): void {
        Auth::requireRole('librarian');
        $q=trim($_GET['search'] ?? '');
        $where=$q!=='' ? "WHERE accession_no LIKE ? OR title LIKE ?" : "WHERE 1=1";
        $p=$q!=='' ? ["%$q%","%$q%"] : [];
        $rows=Database::query("SELECT * FROM disposed_books $where ORDER BY id DESC LIMIT 100", $p);
        $total=(int)(Database::one("SELECT COUNT(*) as c FROM disposed_books")['c'] ?? 0);
        View::render('librarian/disposed', ['rows'=>$rows,'total'=>$total,'user'=>Auth::user()]);
    }
}
