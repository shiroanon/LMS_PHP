<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;

class BookController {
    /** Department options for the category checkbox grid (mirrors org app: DISTINCT branch FROM students). */
    private static function departments(): array {
        try {
            $branches = array_column(Database::query("SELECT DISTINCT branch FROM students WHERE branch IS NOT NULL AND branch<>'' ORDER BY branch"), 'branch');
        } catch(\Throwable $e) { $branches = []; }
        try {
            $extra = array_column(Database::query("SELECT DISTINCT category FROM book_categories WHERE category IS NOT NULL AND category<>'' ORDER BY category"), 'category');
        } catch(\Throwable $e) { $extra = []; }
        $out = [];
        foreach(array_merge($branches, $extra) as $d){
            $d = trim((string)$d);
            if($d !== '' && !in_array($d, $out, true)) $out[] = $d;
        }
        return $out;
    }

    /** Normalize posted categories: supports categories[] array (checkbox grid) + legacy category string. */
    private static function postedCategories(): array {
        $cats = [];
        if(isset($_POST['categories']) && is_array($_POST['categories'])){
            foreach($_POST['categories'] as $c){ $c = trim((string)$c); if($c !== '') $cats[] = $c; }
        } elseif(isset($_POST['category'])) {
            foreach(explode(',', (string)$_POST['category']) as $c){ $c = trim($c); if($c !== '') $cats[] = $c; }
        }
        return array_values(array_unique($cats));
    }

    /**
     * Handle optional cover-page upload (field `cover`, mirrors original app:
     * images only, 5 MB cap, unique name under storage/uploads/images/).
     * Returns [webUrl|null, error|null].
     */
    private static function storeCover(): array {
        if (empty($_FILES['cover']['tmp_name'])) return [null, null];
        $f = $_FILES['cover'];
        if (($f['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) return [null, 'Cover upload failed'];
        if (($f['size'] ?? 0) > 5*1024*1024) return [null, 'Cover must be under 5 MB'];
        $ext = strtolower(pathinfo($f['name'] ?? '', PATHINFO_EXTENSION));
        if ($ext === 'jpeg') $ext = 'jpg';
        if (!in_array($ext, ['jpg','png','webp'], true)) return [null, 'Cover must be JPG, PNG or WebP'];
        if (@getimagesize($f['tmp_name']) === false) return [null, 'Cover file is not a valid image'];
        $name = time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = dirname(__DIR__, 2) . "/storage/uploads/images/$name";
        if (!move_uploaded_file($f['tmp_name'], $dest)) return [null, 'Could not save cover'];
        return ["/uploads/images/$name", null];
    }

    /** Delete a previously stored cover file (only inside our uploads dir). */
    private static function deleteCover(?string $url): void {
        if (!$url || !str_starts_with($url, '/uploads/images/')) return;
        $path = dirname(__DIR__, 2) . '/storage/uploads/images/' . basename($url);
        if (is_file($path)) @unlink($path);
    }

    public function index(): void {
        Auth::requireLogin();
        $search = trim($_GET['search'] ?? '');
        $cat = trim($_GET['category'] ?? '');
        $avail = !empty($_GET['available']);
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;
        $off = ($page-1)*$limit;
        $where = "WHERE 1=1";
        $params = [];
        if ($search !== '') { $where .= " AND (b.title LIKE ? OR b.author LIKE ? OR b.book_id LIKE ? OR b.isbn LIKE ?)"; $s="%$search%"; array_push($params,$s,$s,$s,$s); }
        if ($cat !== '') { $where .= " AND b.category=?"; $params[]=$cat; }
        if ($avail) { $where .= " AND b.quantity_available>0"; }
        $total = 0;
        $viewMode = 'cards'; $sortKey = 'title'; $sortOrder = 'ASC';
        try {
            $row = Database::one("SELECT COUNT(*) as c FROM books b $where", $params);
            $total = (int)($row['c'] ?? 0);
            // cards/table toggle + sorting like the original app (whitelist + id tiebreak)
            $viewMode = (($_GET['view'] ?? 'cards') === 'table') ? 'table' : 'cards';
            $sortMap = ['title'=>'b.title','author'=>'b.author','accession'=>'b.book_id','category'=>'b.category','shelf'=>'b.shelf_location','avail'=>'b.quantity_available','added'=>'b.created_at'];
            $sortBy = $sortMap[$_GET['sort_by'] ?? ''] ?? 'b.title';
            $sortKey = array_search($sortBy, $sortMap, true);
            $sortOrder = strtolower($_GET['sort_order'] ?? '') === 'desc' ? 'DESC' : 'ASC';
            $orderSql = "ORDER BY $sortBy $sortOrder, b.id ASC";
            $books = Database::query("SELECT b.*, s.name as supplier_name, (SELECT MAX(is_reference) FROM book_copies WHERE master_book_id=b.id) as has_reference FROM books b LEFT JOIN suppliers s ON s.id=b.supplier_id $where $orderSql LIMIT $limit OFFSET $off", $params);
            // attach categories (department) per book
            $ids = array_column($books,'id');
            $catMap = [];
            if($ids){
                $ph = implode(',', array_fill(0,count($ids),'?'));
                $rows = Database::query("SELECT book_id, category FROM book_categories WHERE book_id IN ($ph)", $ids);
                foreach($rows as $r){ $catMap[$r['book_id']][] = $r['category']; }
            }
            foreach($books as &$b){
                $b['categories'] = $catMap[$b['id']] ?? ($b['category'] ? [$b['category']] : []);
                // accession = barcode (user request): if barcode_data empty, use book_id/accession
                if(empty($b['barcode_data'])) $b['barcode_data'] = $b['book_id'];
                // ensure isnn/issn etc are available
            }
            unset($b);
            $categories = array_column(Database::query("SELECT DISTINCT category FROM book_categories WHERE category IS NOT NULL AND category<>'' ORDER BY category"), 'category');
            if(!$categories) $categories = array_column(Database::query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL AND category<>'' ORDER BY category"), 'category');
            $copies = (int)(Database::one("SELECT COUNT(*) as c FROM book_copies")['c'] ?? 0);
        } catch(\Throwable $e) { $books=[]; $categories=[]; $copies=0; }
        $pages = max(1, (int)ceil($total/$limit));
        View::render('librarian/books', ['books'=>$books,'categories'=>$categories,'total'=>$total,'copies'=>$copies,'pages'=>$pages,'page'=>$page,'limit'=>$limit,'viewMode'=>$viewMode,'sortKey'=>$sortKey,'sortOrder'=>$sortOrder,'user'=>Auth::user()]);
    }

    public function copiesJson(string $id): void {
        Auth::requireLogin();
        $copies = Database::query("SELECT accession_no, barcode_data, status FROM book_copies WHERE master_book_id=? ORDER BY accession_no", [$id]);
        foreach($copies as &$c){ if(empty($c['barcode_data'])) $c['barcode_data']=$c['accession_no']; }
        header('Content-Type: application/json'); echo json_encode($copies); exit;
    }

    public function show(string $id): void {
        Auth::requireLogin();
        $b = Database::one("SELECT b.*, s.name as supplier_name, (SELECT MAX(is_reference) FROM book_copies WHERE master_book_id=b.id) as has_reference FROM books b LEFT JOIN suppliers s ON s.id=b.supplier_id WHERE b.id=?", [$id]);
        if (!$b) { http_response_code(404); View::render('partials/404', ['user'=>Auth::user()]); return; }
        $copies = Database::query("SELECT bc.*, s.name as supplier_name FROM book_copies bc LEFT JOIN suppliers s ON bc.supplier_id=s.id WHERE bc.master_book_id=? ORDER BY bc.accession_no", [$id]);
        $cats = Database::query("SELECT category FROM book_categories WHERE book_id=?", [$id]);
        $cats = array_column($cats,'category');
        if(!$cats && !empty($b['category'])) $cats = [$b['category']];
        // ensure barcode = accession for display
        foreach($copies as &$c){ if(empty($c['barcode_data'])) $c['barcode_data'] = $c['accession_no']; }
        unset($c);
        if(empty($b['barcode_data'])) $b['barcode_data'] = $b['book_id'];
        $suppliers = Database::query("SELECT id, name FROM suppliers ORDER BY name");
        View::render('librarian/book_detail', ['book'=>$b,'copies'=>$copies,'cats'=>$cats,'departments'=>self::departments(),'suppliers'=>$suppliers,'user'=>Auth::user()]);
    }

    public function update(string $id): void {
        Auth::requireRole('librarian');
        $b = Database::one("SELECT * FROM books WHERE id=?", [$id]);
        if(!$b){ flash('error','Book not found'); header('Location: /books'); exit; }
        $fields = ['title','author','isbn','issn','category','edition','publication','num_pages','shelf_location','purchase_date','purchase_price','supplier_id','contents','notes','barcode_data'];
        $data = [];
        foreach($fields as $f){
            $v = $_POST[$f] ?? null;
            if($v==='') $v=null;
            if($f==='num_pages' && $v!==null) $v=(int)$v;
            if($f==='purchase_price' && $v!==null) $v=(float)$v;
            if($f==='supplier_id' && $v!==null) $v=(int)$v;
            $data[$f]=$v;
        }
        // accession = barcode
        if(!empty($data['barcode_data'])) $data['barcode_data'] = trim($data['barcode_data']);
        else $data['barcode_data'] = $b['book_id'];
        // cover page: replacement upload wins, otherwise optional removal
        $coverUrl = $b['book_image_url'] ?? null;
        if (!empty($_POST['remove_cover'])) { self::deleteCover($coverUrl); $coverUrl = null; }
        if (!empty($_FILES['cover']['tmp_name'])) {
            [$newCover, $coverErr] = self::storeCover();
            if ($coverErr) { flash('error', $coverErr); header("Location: /books/$id"); exit; }
            if ($newCover) { self::deleteCover($coverUrl); $coverUrl = $newCover; }
        }
        $data['book_image_url'] = $coverUrl;
        // build SET
        $sets=[]; $params=[];
        foreach($data as $k=>$v){ if($k==='category') continue; $sets[]="`$k`=?"; $params[]=$v; }
        if($sets){
            $params[]=$id;
            Database::exec("UPDATE books SET ".implode(',',$sets)." WHERE id=?", $params);
        }
        if(isset($_POST['category']) || isset($_POST['categories']) || isset($_POST['categories_present'])){
            Database::exec("DELETE FROM book_categories WHERE book_id=?", [$id]);
            $cats = self::postedCategories();
            foreach($cats as $c){ Database::exec("INSERT IGNORE INTO book_categories (book_id, category) VALUES (?,?)", [$id,$c]); }
            if($cats) Database::exec("UPDATE books SET category=? WHERE id=?", [$cats[0], $id]);
        }
        // also sync copies barcode = accession if needed
        Database::exec("UPDATE book_copies SET barcode_data=accession_no WHERE master_book_id=? AND (barcode_data IS NULL OR barcode_data='')", [$id]);
        flash('success','Book updated');
        header("Location: /books/$id"); exit;
    }

    public function newForm(): void {
        Auth::requireRole('librarian');
        View::render('librarian/book_form', ['departments'=>self::departments(),'user'=>Auth::user()]);
    }

    public function create(): void {
        Auth::requireRole('librarian');
        $book_id = trim($_POST['book_id'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $author = trim($_POST['author'] ?? '');
        if (!$book_id || !$title || !$author) { flash('error','Accession, title and author required'); header('Location: /books/new'); exit; }
        [$coverUrl, $coverErr] = self::storeCover();
        if ($coverErr) { flash('error', $coverErr); header('Location: /books/new'); exit; }
        $pdo = Database::pdo();
        try {
            $pdo->beginTransaction();
            // check duplicate accession
            $dup = Database::one("SELECT id FROM book_copies WHERE accession_no=?", [$book_id]);
            if ($dup) throw new \Exception("Accession $book_id already exists");
            $dup2 = Database::one("SELECT id FROM books WHERE book_id=?", [$book_id]);
            if ($dup2) throw new \Exception("Book code $book_id already exists");
            $master = Database::one("SELECT id FROM books WHERE LOWER(title)=LOWER(?) AND LOWER(author)=LOWER(?)", [$title,$author]);
            $qty = max(1, (int)($_POST['quantity_total'] ?? 1));
            $barcode = trim($_POST['barcode_data'] ?? '');
            if($barcode === '') $barcode = $book_id; // accession = barcode per user request
            if ($master) {
                // add copy — barcode always equals accession
                Database::exec("INSERT INTO book_copies (master_book_id, accession_no, barcode_data, status) VALUES (?,?,?, 'available')", [$master['id'], $book_id, $barcode]);
                Database::exec("UPDATE books SET quantity_total=quantity_total+1, quantity_available=quantity_available+1, barcode_data=? WHERE id=?", [$barcode, $master['id']]);
                // first cover wins on an existing title (mirrors original auto-set)
                if ($coverUrl) Database::exec("UPDATE books SET book_image_url=? WHERE id=? AND (book_image_url IS NULL OR book_image_url='')", [$coverUrl, $master['id']]);
                Database::exec("INSERT IGNORE INTO book_barcodes (book_id, barcode_data) VALUES (?,?)", [$master['id'], $barcode]);
                $pdo->commit();
                flash('success',"Copy $book_id added to existing title");
            } else {
                $cats = self::postedCategories();
                $firstCat = $cats[0] ?? null;
                $pdo->prepare("INSERT INTO books (book_id,title,author,isbn,issn,category,edition,publication,num_pages,shelf_location,quantity_total,quantity_available,barcode_data,book_image_url) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
                    ->execute([$book_id,$title,$author, $_POST['isbn'] ?? null, $_POST['issn'] ?? null, $firstCat, $_POST['edition'] ?? null, $_POST['publication'] ?? null, $_POST['num_pages'] ? (int)$_POST['num_pages'] : null, $_POST['shelf_location'] ?? null, $qty,$qty, $barcode, $coverUrl]);
                $mid = $pdo->lastInsertId();
                Database::exec("INSERT INTO book_copies (master_book_id, accession_no, barcode_data, status) VALUES (?,?,?, 'available')", [$mid,$book_id,$barcode]);
                Database::exec("INSERT IGNORE INTO book_barcodes (book_id, barcode_data) VALUES (?,?)", [$mid, $barcode]);
                foreach($cats as $c) Database::exec("INSERT IGNORE INTO book_categories (book_id,category) VALUES (?,?)", [$mid, $c]);
                $pdo->commit();
                flash('success',"Book $title created with accession $book_id");
            }
            header('Location: /books'); exit;
        } catch(\Throwable $e) { $pdo->rollBack(); flash('error',$e->getMessage()); header('Location: /books/new'); exit; }
    }
}
