<?php
namespace App\Controllers;

use App\Core\Auth;

class UploadController {
    /** Serve book covers from storage (docroot is public/, so stream with a traversal guard). */
    public function cover(string $name): void {
        Auth::requireLogin();
        $safe = basename($name);
        if ($safe !== $name || !preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*\.(jpe?g|png|webp)$/i', $safe)) {
            http_response_code(404); die('Not found');
        }
        $path = dirname(__DIR__, 2) . "/storage/uploads/images/$safe";
        if (!is_file($path)) { http_response_code(404); die('Not found'); }
        $mime = match (strtolower(pathinfo($safe, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };
        header("Content-Type: $mime");
        header('Cache-Control: public, max-age=86400');
        header('Content-Length: ' . filesize($path));
        readfile($path); exit;
    }
}
