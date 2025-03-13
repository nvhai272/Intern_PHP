<?php
session_start(); 

class Router {
    private static ?Router $instance = null;
    private array $routes = [];
    
    private array $allowedRoutes = [
        'GET' => ['/home', '/user/profile', '/login', '/', '/logout'], 
        'POST' => ['/login']
    ];

    private array $allowedAdminRoutes = [
        'GET' => ['/home', '/user/profile', '/login','/','/logout','/admin/list-user','/admin/details-user',
        '/admin/edit-user','/admin/create-user','/admin/search-user','/admin/details-admin'], 
        'POST' => ['/login','/admin/edit-user','/admin/delete-user','/admin/create-user']
    ];

    private function __construct() {}

    public static function getInstance(): Router {
        if (self::$instance === null) {
            self::$instance = new Router();
        }
        return self::$instance;
    }

    public function get(string $uri, string $controllerMethod) {
        $this->routes['GET'][$uri] = $controllerMethod;
    }

    public function post(string $uri, string $controllerMethod) {
        $this->routes['POST'][$uri] = $controllerMethod;
    }

    private function checkAccess(string $requestUri, string $requestMethod): bool {
        $role = $_SESSION['account']['role'] ?? 'guest'; 

        if ($role === 'super_admin') {
            return true; // Super Admin có quyền truy cập toàn bộ
        }

        if ($role === 'admin') {
            return in_array($requestUri, $this->allowedAdminRoutes[$requestMethod] ?? []);
        }

        return in_array($requestUri, $this->allowedRoutes[$requestMethod] ?? []);
    }

    public function dispatch(string $requestUri, string $requestMethod) {
        $requestUri = parse_url($requestUri, PHP_URL_PATH); // Loại bỏ query string
        
        if (!$this->checkAccess($requestUri, $requestMethod)) {
            if ($_SESSION['account']['role'] === 'admin') {
                echo "❌ 403 - Bạn không có quyền truy cập trang này";
                exit;
            } else {
                header("Location: /login");
                exit;
            }
        }
        
    
        if (isset($this->routes[$requestMethod][$requestUri])) {
            $controllerAction = $this->routes[$requestMethod][$requestUri];
            list($controllerName, $method) = explode('@', $controllerAction);
    
            $controllerFile = __DIR__ . "/../Controllers/{$controllerName}.php";
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
                $controller = new $controllerName();
                return call_user_func([$controller, $method]);
            } else {
                die("❌ Controller không tồn tại: {$controllerFile}");
            }
        }
    
        die("❌ 404 - Không tìm thấy trang");
    }
}
?>