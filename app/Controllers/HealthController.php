<?php
namespace App\Controllers;

use App\Core\Database;
use App\Core\Sync;

class HealthController {
    /** Unauthenticated liveness probe for the backend selector + peer monitor. Minimal info only. */
    public function check(): void {
        $db = 'ok';
        try {
            Database::one("SELECT 1 AS ok");
        } catch (\Throwable $e) {
            $db = 'down';
        }
        header('Content-Type: application/json');
        header('Cache-Control: no-store');
        http_response_code($db === 'ok' ? 200 : 503);
        echo json_encode([
            'app' => 'lms-php',
            'node' => Sync::nodeId(),
            'role' => Sync::role(),
            'time' => gmdate('c'),
            'db' => $db,
        ]);
        exit;
    }
}
