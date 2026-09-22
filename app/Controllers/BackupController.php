<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;

class BackupController {
    private function dir(): string {
        $d = dirname(__DIR__,2).'/storage/backups';
        if(!is_dir($d)) mkdir($d,0755,true);
        return $d;
    }
    public function index(): void {
        Auth::requireRole('librarian');
        $dir=$this->dir();
        $files = array_values(array_filter(scandir($dir), fn($f)=>str_starts_with($f,'backup-') && str_ends_with($f,'.zip')));
        rsort($files);
        $list=[];
        foreach($files as $f){ $p="$dir/$f"; $list[]=['filename'=>$f,'size'=>filesize($p),'createdAt'=>date('c', filemtime($p))]; }
        View::render('librarian/backup', ['files'=>$list,'user'=>Auth::user()]);
    }
    public function now(): void {
        Auth::requireRole('librarian');
        $dir=$this->dir();
        $ts=date('Y-m-d\THis');
        $zipPath="$dir/backup-$ts.zip";
        $tmpDb="$dir/temp-".time().".sql";
        // mysqldump via mariadb-dump
        $cfg=require dirname(__DIR__,2).'/config/database.php';
        $cmd="mysqldump -h {$cfg['host']} -P {$cfg['port']} -u {$cfg['username']} -p{$cfg['password']} {$cfg['database']} > ".escapeshellarg($tmpDb)." 2>&1";
        exec($cmd, $out, $code);
        if($code!==0 || !file_exists($tmpDb) || filesize($tmpDb)==0){
            flash('error','Backup failed: mysqldump not available or DB error');
            header('Location: /backup'); exit;
        }
        $zip=new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE);
        $zip->addFile($tmpDb, 'lms.sql');
        $uploads=dirname(__DIR__,2).'/storage/uploads';
        if(is_dir($uploads)){
            $files=new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($uploads, \RecursiveDirectoryIterator::SKIP_DOTS));
            foreach($files as $f){ $local='uploads/'.substr($f->getPathname(), strlen($uploads)+1); $zip->addFile($f->getPathname(), $local); }
        }
        $zip->close();
        unlink($tmpDb);
        Database::exec("INSERT INTO backup_log (filename, size, status) VALUES (?,?, 'success')", [basename($zipPath), filesize($zipPath)]);
        flash('success','Backup '.basename($zipPath).' created ('.round(filesize($zipPath)/1024).' KB)');
        header('Location: /backup'); exit;
    }
    public function download(string $filename): void {
        Auth::requireRole('librarian');
        $path=$this->dir().'/'.basename($filename);
        if(!file_exists($path)){ http_response_code(404); die('Not found'); }
        header('Content-Type: application/zip'); header('Content-Disposition: attachment; filename="'.basename($path).'"'); readfile($path); exit;
    }
    public function delete(string $filename): void {
        Auth::requireRole('librarian');
        $path=$this->dir().'/'.basename($filename);
        if(file_exists($path)) unlink($path);
        Database::exec("DELETE FROM backup_log WHERE filename=?", [basename($filename)]);
        flash('success','Backup deleted'); header('Location: /backup'); exit;
    }
}
