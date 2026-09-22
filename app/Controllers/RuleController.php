<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Sync;
use App\Core\View;

class RuleController {
    public function index(): void {
        Auth::requireRole('librarian');
        $rules=Database::query("SELECT * FROM library_rules ORDER BY sort_order ASC, id ASC");
        View::render('librarian/rules', ['rules'=>$rules,'user'=>Auth::user()]);
    }
    public function create(): void {
        Auth::requireRole('librarian');
        $text=trim($_POST['rule_text'] ?? ''); if(!$text){ flash('error','Rule text required'); header('Location: /rules'); exit; }
        $max=(int)(Database::one("SELECT COALESCE(MAX(sort_order),0) as m FROM library_rules")['m'] ?? 0);
        Database::exec("INSERT INTO library_rules (rule_text, sort_order) VALUES (?,?)", [$text,$max+1]);
        flash('success','Rule added'); header('Location: /rules'); exit;
    }
    public function delete(string $id): void {
        Auth::requireRole('librarian');
        Sync::tombstone('library_rules', $id);
        Database::exec("DELETE FROM library_rules WHERE id=?", [$id]);
        flash('success','Rule removed'); header('Location: /rules'); exit;
    }
}
