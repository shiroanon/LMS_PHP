<?php
if (file_exists(__DIR__ . '/../vendor/autoload.php')) require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Core/Database.php';
require __DIR__ . '/../app/Core/View.php';
require __DIR__ . '/../app/Core/Auth.php';
require __DIR__ . '/../app/Core/Router.php';
require __DIR__ . '/../app/Core/Helpers.php';

spl_autoload_register(function($class){
    $prefix = 'App\\';
    if(!str_starts_with($class, $prefix)) return;
    $rel = str_replace('\\','/', substr($class, strlen($prefix)));
    $file = __DIR__ . '/../app/' . $rel . '.php';
    if(file_exists($file)) require $file;
});

use App\Core\Router;
use App\Core\Auth;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\BookController;
use App\Controllers\StudentController;
use App\Controllers\CirculationController;
use App\Controllers\FineController;
use App\Controllers\VisitController;
use App\Controllers\ReportController;
use App\Controllers\SuggestionController;
use App\Controllers\DigitalController;
use App\Controllers\ProfileController;
use App\Controllers\BarcodeController;
use App\Controllers\UploadController;
use App\Controllers\HealthController;
use App\Controllers\SyncController;
use App\Controllers\StaffController;
use App\Controllers\SupplierController;
use App\Controllers\HolidayController;
use App\Controllers\DisposedController;
use App\Controllers\FeedbackController;
use App\Controllers\RuleController;
use App\Controllers\BillController;
use App\Controllers\NotificationController;
use App\Controllers\BackupController;

$router = new Router();

// public
$router->get('/healthz', [HealthController::class,'check']);
// node-to-node sync (HMAC-guarded inside the controller, no session login)
$router->get('/sync/pull', [SyncController::class,'pull']);
$router->post('/sync/push', [SyncController::class,'push']);
$router->get('/sync/files', [SyncController::class,'files']);
$router->get('/sync/file', [SyncController::class,'file']);
$router->post('/sync/file', [SyncController::class,'filePush']);
$router->get('/login', [AuthController::class,'loginForm']);
$router->post('/login', [AuthController::class,'login']);
$router->post('/logout', [AuthController::class,'logout']);
$router->get('/', function(){ header('Location: /dashboard'); exit; });

// protected
$router->get('/dashboard', [DashboardController::class,'index']);

$router->get('/books', [BookController::class,'index']);
$router->get('/books/new', [BookController::class,'newForm']);
$router->post('/books', [BookController::class,'create']);
$router->get('/books/{id}/copies', [BookController::class,'copiesJson']);
$router->get('/books/{id}', [BookController::class,'show']);
$router->post('/books/{id}', [BookController::class,'update']);

$router->get('/students', [StudentController::class,'index']);
$router->get('/students/new', [StudentController::class,'newForm']);
$router->post('/students', [StudentController::class,'create']);
$router->get('/students/{id}', [StudentController::class,'show']);
$router->post('/students/{id}', [StudentController::class,'update']);
$router->post('/students/{id}/delete', [StudentController::class,'delete']);
$router->get('/students/{id}/card', [StudentController::class,'cardJson']);

$router->get('/issue', [CirculationController::class,'issueForm']);
$router->post('/issue', [CirculationController::class,'issue']);
$router->get('/return', [CirculationController::class,'returnForm']);
$router->post('/return', [CirculationController::class,'doReturn']);

$router->get('/fines', [FineController::class,'index']);
$router->post('/fines/{id}/pay', [FineController::class,'pay']);

$router->get('/visits', [VisitController::class,'index']);
$router->post('/visits/check', [VisitController::class,'check']);

$router->get('/reports', [ReportController::class,'index']);
$router->get('/reports/custom', [ReportController::class,'custom']);
$router->post('/reports/pdf', [ReportController::class,'pdf']);
$router->get('/reports/pdf', [ReportController::class,'pdf']);
$router->get('/reports/export', [ReportController::class,'export']);

$router->get('/suggestions', [SuggestionController::class,'index']);
$router->post('/suggestions', [SuggestionController::class,'create']);
$router->post('/suggestions/{id}/status', [SuggestionController::class,'updateStatus']);

$router->get('/digital-library', [DigitalController::class,'index']);
$router->post('/digital-library', [DigitalController::class,'upload']);
$router->get('/digital-library/{id}/download', [DigitalController::class,'download']);

$router->get('/profile', [ProfileController::class,'show']);
$router->post('/profile', [ProfileController::class,'update']);
$router->get('/my-books', function(){
    Auth::requireRole('student');
    $u = Auth::user();
    $issues = \App\Core\Database::query("SELECT i.*, b.title, bc.accession_no FROM issues i JOIN books b ON b.id=i.book_id LEFT JOIN book_copies bc ON bc.id=i.book_copy_id WHERE i.student_id=? ORDER BY i.created_at DESC LIMIT 50", [$u['profile']['id']]);
    \App\Core\View::render('student/my_books', ['issues'=>$issues,'user'=>$u]);
});

// stubs for remaining librarian nav — same signature
$stub = function(string $title){
    return function() use ($title){
        \App\Core\Auth::requireRole('librarian');
        \App\Core\View::render('librarian/generic', ['title'=>$title,'user'=>\App\Core\Auth::user()]);
    };
};
$router->get('/staff', [StaffController::class,'index']);
$router->get('/staff/new', function(){ \App\Core\Auth::requireRole('librarian'); \App\Core\View::render('librarian/staff_form', ['user'=>\App\Core\Auth::user()]); });
$router->post('/staff', [StaffController::class,'create']);
$router->get('/staff/{id}', [StaffController::class,'show']);
$router->get('/staff/{id}/card', [StaffController::class,'cardJson']);
$router->post('/staff/{id}', [StaffController::class,'update']);
$router->post('/staff/{id}/delete', [StaffController::class,'delete']);
$router->get('/barcodes', [BarcodeController::class,'index']);
$router->post('/barcodes/generate', [BarcodeController::class,'generate']);
$router->get('/barcode/render', [BarcodeController::class,'render']);
$router->get('/uploads/images/{name}', [UploadController::class,'cover']);
$router->get('/scanner', function(){ \App\Core\Auth::requireRole('librarian'); \App\Core\View::render('librarian/scanner', ['user'=>\App\Core\Auth::user()]); });
$router->get('/reservations', function(){
    Auth::requireLogin();
    $u = Auth::user();
    if($u['role']==='student'){
        $rows = \App\Core\Database::query("SELECT r.*, b.title FROM book_reservations r JOIN books b ON b.id=r.book_id WHERE r.student_id=? ORDER BY r.created_at DESC LIMIT 50", [$u['profile']['id']]);
        \App\Core\View::render('student/reservations', ['rows'=>$rows,'user'=>$u]);
    } else {
        $rows = \App\Core\Database::query("SELECT r.*, b.title, s.name, s.enrollment_no FROM book_reservations r JOIN books b ON b.id=r.book_id JOIN students s ON s.id=r.student_id ORDER BY r.reservation_date DESC LIMIT 100");
        \App\Core\View::render('librarian/reservations', ['rows'=>$rows,'user'=>$u]);
    }
});
$router->post('/reservations/{id}/cancel', function(string $id){ \App\Core\Auth::requireLogin(); \App\Core\Database::exec("UPDATE book_reservations SET status='cancelled' WHERE id=?", [$id]); header('Location: /reservations'); exit; });
$router->post('/reservations/create', function(){
    \App\Core\Auth::requireRole('student');
    $u = \App\Core\Auth::user();
    $bookId = (int)($_POST['book_id'] ?? 0);
    $book = $bookId ? \App\Core\Database::one("SELECT id, title FROM books WHERE id=?", [$bookId]) : null;
    if(!$book){ flash('error','Book not found — check the book ID'); header('Location: /reservations'); exit; }
    $dup = \App\Core\Database::one("SELECT id FROM book_reservations WHERE student_id=? AND book_id=? AND status IN ('pending','notified') LIMIT 1", [$u['profile']['id'],$bookId]);
    if($dup){ flash('error','You already have an active hold on this title'); header('Location: /reservations'); exit; }
    \App\Core\Database::exec("INSERT INTO book_reservations (student_id, book_id) VALUES (?,?)", [$u['profile']['id'],$bookId]);
    flash('success',"Reserved {$book['title']} — you’ll be notified on return");
    header('Location: /reservations'); exit;
});
$router->post('/reservations/{id}/fulfill', function(string $id){ \App\Core\Auth::requireRole('librarian'); \App\Core\Database::exec("UPDATE book_reservations SET status='fulfilled' WHERE id=?", [$id]); header('Location: /reservations'); exit; });
$router->get('/suppliers', [SupplierController::class,'index']);
$router->post('/suppliers', [SupplierController::class,'create']);
$router->get('/bills', [BillController::class,'index']);
$router->post('/bills', [BillController::class,'create']);
$router->get('/bills/{id}', [BillController::class,'show']);
$router->post('/bills/{id}/delete', [BillController::class,'delete']);
$router->get('/holidays', [HolidayController::class,'index']);
$router->post('/holidays', [HolidayController::class,'create']);
$router->post('/holidays/{id}/delete', [HolidayController::class,'delete']);
$router->get('/disposed', [DisposedController::class,'index']);
$router->get('/notifications', [NotificationController::class,'index']);
$router->post('/notifications/send', [NotificationController::class,'send']);
$router->get('/feedback', [FeedbackController::class,'index']);
$router->get('/rules', [RuleController::class,'index']);
$router->post('/rules', [RuleController::class,'create']);
$router->post('/rules/{id}/delete', [RuleController::class,'delete']);
$router->get('/backup', [BackupController::class,'index']);
$router->post('/backup/now', [BackupController::class,'now']);
$router->get('/backup/{filename}/download', [BackupController::class,'download']);
$router->post('/backup/{filename}/delete', [BackupController::class,'delete']);

$router->dispatch();
