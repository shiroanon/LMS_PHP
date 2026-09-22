<?php
namespace App\Core;

class Router {
    private array $routes = [];

    public function get(string $path, callable|string|array $handler, array $middleware = []): void { $this->add('GET',$path,$handler,$middleware); }
    public function post(string $path, callable|string|array $handler, array $middleware = []): void { $this->add('POST',$path,$handler,$middleware); }

    private function add(string $method, string $path, callable|string|array $handler, array $mw): void {
        $this->routes[] = ['method'=>$method,'path'=>$path,'handler'=>$handler,'mw'=>$mw];
    }

    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        // support method spoofing _method
        if ($method==='POST' && isset($_POST['_method'])) $method = strtoupper($_POST['_method']);
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        foreach ($this->routes as $r) {
            $pattern = $this->compile($r['path']);
            if ($r['method'] !== $method) continue;
            if (preg_match($pattern, $uri, $m)) {
                $args = [];
                foreach($m as $k=>$v){ if(is_int($k) && $k>0) $args[]=$v; }
                foreach ($r['mw'] as $fn) { $fn(); }
                $h = $r['handler'];
                if (is_string($h)) {
                    [$cls,$meth] = explode('@', $h);
                    $obj = new $cls();
                    $obj->$meth(...$args);
                } elseif (is_array($h)) {
                    [$cls,$meth] = $h;
                    $obj = new $cls();
                    $obj->$meth(...$args);
                } else {
                    ($h)(...$args);
                }
                return;
            }
        }
        http_response_code(404);
        View::render('partials/404', [], 'layouts/app');
    }

    private function compile(string $path): string {
        $regex = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $path);
        return '#^' . $regex . '$#';
    }
}
