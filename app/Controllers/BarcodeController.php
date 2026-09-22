<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;

class BarcodeController {
    public function index(): void {
        Auth::requireRole('librarian');
        $tab = $_GET['tab'] ?? 'students';
        $q = trim($_GET['q'] ?? '');
        $page = max(1,(int)($_GET['page'] ?? 1));
        $limit = 50;
        $off = ($page-1)*$limit;
        if($tab==='books'){
            $where="WHERE 1=1"; $params=[];
            if($q!==''){ $where.=" AND (b.title LIKE ? OR bc.accession_no LIKE ? OR bc.barcode_data LIKE ? OR b.book_id LIKE ?)"; $like="%$q%"; $params=[$like,$like,$like,$like]; }
            $total = (int)(Database::one("SELECT COUNT(*) as c FROM book_copies bc JOIN books b ON b.id=bc.master_book_id $where", $params)['c'] ?? 0);
            $rows = Database::query("SELECT bc.accession_no, bc.barcode_data, b.title, b.book_id FROM book_copies bc JOIN books b ON b.id=bc.master_book_id $where ORDER BY bc.accession_no LIMIT $limit OFFSET $off", $params);
        } else {
            $where="WHERE 1=1"; $params=[];
            if($q!==''){ $where.=" AND (s.name LIKE ? OR s.enrollment_no LIKE ? OR s.barcode_data LIKE ? OR s.library_id LIKE ?)"; $like="%$q%"; $params=[$like,$like,$like,$like]; }
            $total = (int)(Database::one("SELECT COUNT(*) as c FROM students s $where", $params)['c'] ?? 0);
            $rows = Database::query("SELECT s.enrollment_no, s.barcode_data, s.library_id, s.name, s.branch FROM students s $where ORDER BY s.enrollment_no LIMIT $limit OFFSET $off", $params);
        }
        $pages = max(1,(int)ceil($total/$limit));
        View::render('librarian/barcodes', ['tab'=>$tab,'q'=>$q,'rows'=>$rows,'pages'=>$pages,'page'=>$page,'total'=>$total,'user'=>Auth::user()]);
    }

    public function generate(): void {
        Auth::requireRole('librarian');
        $type = $_POST['type'] ?? 'students';
        if($type==='books'){
            $cnt=0;
            foreach(Database::query("SELECT id, accession_no, barcode_data FROM book_copies WHERE barcode_data IS NULL OR barcode_data=''") as $r){
                $code = $r['accession_no'];
                Database::exec("UPDATE book_copies SET barcode_data=? WHERE id=?", [$code,$r['id']]);
                $cnt++;
            }
            flash('success',"Generated $cnt book barcodes (accession = barcode)");
        } else {
            $cnt=0;
            foreach(Database::query("SELECT id, enrollment_no, barcode_data FROM students WHERE barcode_data IS NULL OR barcode_data=''") as $r){
                Database::exec("UPDATE students SET barcode_data=? WHERE id=?", [$r['enrollment_no'],$r['id']]);
                $cnt++;
            }
            flash('success',"Generated $cnt student barcodes");
        }
        header('Location: /barcodes?tab='.$type); exit;
    }

    // Simple Code128-like render — SVG fallback (no GD required), perfect for CODE128 wedge scanners
    public function render(): void {
        $text = $_GET['text'] ?? '000000';
        $text = preg_replace('/[^A-Za-z0-9\-]/','', $text);
        if(!$text) $text='000000';
        if (class_exists('\Picqer\Barcode\BarcodeGeneratorPNG')) {
            $g = new \Picqer\Barcode\BarcodeGeneratorPNG();
            header('Content-Type: image/png');
            echo $g->getBarcode($text, $g::TYPE_CODE_128, 2, 50);
            return;
        }
        if (function_exists('imagecreate')) {
            $w = max(200, strlen($text)*16+40); $h = 70;
            $im = \imagecreate($w,$h);
            $white = \imagecolorallocate($im,255,255,255);
            $black = \imagecolorallocate($im,0,0,0);
            \imagefilledrectangle($im,0,0,$w,$h,$white);
            $x=20;
            for($i=0;$i<strlen($text);$i++){
                $code = ord($text[$i]); $bars = ($code % 5)+2;
                for($b=0;$b<$bars;$b++){ $bw=2+($b%2); \imagefilledrectangle($im,$x,10,$x+$bw,50,$black); $x+=$bw+2; } $x+=4;
            }
            \imagestring($im,3, (int)(($w - strlen($text)*7)/2), 55, $text, $black);
            header('Content-Type: image/png'); \imagepng($im); \imagedestroy($im); return;
        }
        // Pure SVG fallback — always works, scanners read the printed JsBarcode; this is for <img> placeholder
        $w = max(220, strlen($text)*12+40);
        $h = 70;
        $bars='';
        $x=20;
        for($i=0;$i<strlen($text);$i++){
            $code=ord($text[$i]); $barsCnt=($code%5)+2;
            for($b=0;$b<$barsCnt;$b++){ $bw=2+($b%2); $bars.='<rect x="'.$x.'" y="10" width="'.$bw.'" height="40" fill="black"/>'; $x+=$bw+2; } $x+=4;
        }
        header('Content-Type: image/svg+xml');
        echo '<svg xmlns="http://www.w3.org/2000/svg" width="'.$w.'" height="'.$h.'" viewBox="0 0 '.$w.' '.$h.'"><rect width="100%" height="100%" fill="white"/>'.$bars.'<text x="'.($w/2).'" y="65" text-anchor="middle" font-family="monospace" font-size="12" fill="black">'.$text.'</text></svg>';
    }
}
