<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;

class HolidayController {
    public function index(): void {
        Auth::requireRole('librarian');
        $holidays = Database::query("SELECT * FROM holidays ORDER BY holiday_date ASC");
        View::render('librarian/holidays', ['holidays'=>$holidays,'user'=>Auth::user()]);
    }
    public function create(): void {
        Auth::requireRole('librarian');
        $d=trim($_POST['holiday_date'] ?? ''); $desc=trim($_POST['description'] ?? '');
        if(!$d){ flash('error','Date required'); header('Location: /holidays'); exit; }
        try{ Database::exec("INSERT INTO holidays (holiday_date, description) VALUES (?,?)", [$d,$desc]); flash('success',"Holiday $d added"); }catch(\Throwable $e){ flash('error','Date already exists'); }
        header('Location: /holidays'); exit;
    }
    public function delete(string $id): void {
        Auth::requireRole('librarian');
        Database::exec("DELETE FROM holidays WHERE id=?", [$id]);
        flash('success','Holiday removed'); header('Location: /holidays'); exit;
    }
}
