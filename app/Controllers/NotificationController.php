<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;

class NotificationController {
    public function index(): void {
        Auth::requireRole('librarian');
        $templates = [
            ['id'=>'overdue','name'=>'Overdue reminder','subject'=>'Overdue: {book_title}','body'=>'Dear {student_name}, book {book_title} due {due_date} is overdue.'],
            ['id'=>'fine','name'=>'Fine notice','subject'=>'Pending fine ₹{fine_amount}','body'=>'Dear {student_name}, you have fine ₹{fine_amount} pending.'],
            ['id'=>'general','name'=>'General announcement','subject'=>'Library update','body'=>'Dear Student, {custom_message}'],
        ];
        $logs = Database::query("SELECT rl.*, s.name, s.enrollment_no FROM reminder_log rl JOIN students s ON s.id=rl.student_id ORDER BY rl.created_at DESC LIMIT 50");
        $branches = array_column(Database::query("SELECT DISTINCT branch FROM students ORDER BY branch"), 'branch');
        View::render('librarian/notifications', ['templates'=>$templates,'logs'=>$logs,'branches'=>$branches,'user'=>Auth::user()]);
    }
    public function send(): void {
        Auth::requireRole('librarian');
        $branch=trim($_POST['branch'] ?? ''); $subject=trim($_POST['subject'] ?? 'Library notice'); $body=trim($_POST['body'] ?? '');
        if(!$body){ flash('error','Message required'); header('Location: /notifications'); exit; }
        $where="WHERE 1=1"; $params=[];
        if($branch!==''){ $where.=" AND branch=?"; $params[]=$branch; }
        $students = Database::query("SELECT id, name FROM students $where LIMIT 200", $params);
        foreach($students as $s){
            $msg = str_replace(['{student_name}'], [$s['name']], "Subject: $subject\n\n$body");
            Database::exec("INSERT INTO reminder_log (student_id, message, sent_via) VALUES (?,?, 'email')", [$s['id'],$msg]);
        }
        flash('success',"Logged notification for ".count($students)." students (SMTP not configured)");
        header('Location: /notifications'); exit;
    }
}
