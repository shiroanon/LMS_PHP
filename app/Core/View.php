<?php
namespace App\Core;

class View {
    public static function render(string $view, array $data = [], string $layout = 'layouts/app'): void {
        extract($data);
        $viewFile = dirname(__DIR__, 2) . "/resources/views/{$view}.php";
        if (!file_exists($viewFile)) { http_response_code(500); die("View not found: $view"); }
        ob_start();
        include $viewFile;
        $content = ob_get_clean();
        if ($layout) {
            $layoutFile = dirname(__DIR__, 2) . "/resources/views/{$layout}.php";
            include $layoutFile;
        } else {
            echo $content;
        }
    }

    public static function partial(string $partial, array $data = []): void {
        extract($data);
        include dirname(__DIR__, 2) . "/resources/views/{$partial}.php";
    }

    public static function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
}
