<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;

class DigitalController {
    public function index(): void {
        Auth::requireLogin();
        $resources = Database::query("SELECT * FROM digital_resources ORDER BY created_at DESC LIMIT 50");
        View::render('shared/digital', ['resources'=>$resources,'user'=>Auth::user()]);
    }

    public function upload(): void {
        Auth::requireRole('librarian');
        if(empty($_FILES['file']['tmp_name'])){ flash('error','File required'); header('Location: /digital-library'); exit; }
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        if($ext!=='pdf'){ flash('error','Only PDF'); header('Location: /digital-library'); exit; }
        $name = time().'-'.bin2hex(random_bytes(4)).'.pdf';
        $dest = dirname(__DIR__,2)."/storage/uploads/pdfs/$name";
        move_uploaded_file($_FILES['file']['tmp_name'], $dest);
        Database::exec("INSERT INTO digital_resources (title,category,file_path,file_size,uploaded_by) VALUES (?,?,?,?,?)", [trim($_POST['title'] ?? 'Untitled'), $_POST['category'] ?? 'ebook', "/storage/uploads/pdfs/$name", filesize($dest), Auth::user()['id']]);
        flash('success','Uploaded to digital shelf');
        header('Location: /digital-library'); exit;
    }

    public function download(string $id): void {
        Auth::requireLogin();
        $r = Database::one("SELECT * FROM digital_resources WHERE id=?", [$id]);
        if(!$r){ http_response_code(404); die('Not found'); }
        $path = dirname(__DIR__,2).$r['file_path'];
        if(!file_exists($path)){ http_response_code(404); die('File missing'); }
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="'.basename($r['title']).'.pdf"');
        readfile($path); exit;
    }
}
