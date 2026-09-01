<?php
namespace App\Services;

class Router {
    private array $routes = [];

    /**
     * Add a route.
     */
    public function add(string $method, string $path, string $controllerMethod): void {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $controllerMethod
        ];
    }

    /**
     * Add GET route.
     */
    public function get(string $path, string $handler): void {
        $this->add('GET', $path, $handler);
    }

    /**
     * Add POST route.
     */
    public function post(string $path, string $handler): void {
        $this->add('POST', $path, $handler);
    }

    /**
     * Dispatch the current request URI.
     */
    public function dispatch(string $method, string $uri): void {
        // Global CSRF Validation for all state-modifying HTTP methods (POST)
        if (strtoupper($method) === 'POST') {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!\App\Services\CSRF::validate($token)) {
                $storageDir = ROOT_PATH . '/storage/logs';
                if (!file_exists($storageDir)) {
                    mkdir($storageDir, 0755, true);
                }
                $secLog = $storageDir . '/security.log';
                $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
                $uriPath = $_SERVER['REQUEST_URI'] ?? '/';
                $time = date('Y-m-d H:i:s');
                file_put_contents($secLog, "[$time] [CSRF-FAILURE] IP: $ip | URI: $uriPath\n", FILE_APPEND);

                http_response_code(403);
                $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false);
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'error' => 'Échec de la validation du jeton de sécurité (CSRF). Veuillez actualiser la page.'
                    ]);
                } else {
                    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Accès Interdit - CSRF</title>";
                    echo "<style>body{background:#0b0f19;color:#9ca3af;font-family:sans-serif;display:flex;justify-content:center;align-items:center;height:100vh;flex-direction:column;margin:0;} h1{color:#f87171;} a{color:#2563eb;text-decoration:none;border:1px solid #2563eb;padding:8px 16px;border-radius:4px;}</style></head>";
                    echo "<body><h1>Échec de la validation de sécurité CSRF</h1><p>Votre jeton de session a expiré ou est invalide.</p><a href='javascript:history.back()'>Retourner en arrière</a></body></html>";
                }
                exit;
            }
        }

        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        $uri = $uri !== '/' ? rtrim($uri, '/') : '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($method)) {
                continue;
            }

            $routePath = $route['path'];
            if ($routePath !== '/') {
                $routePath = rtrim($routePath, '/');
            }
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $routePath);
            $pattern = '#^' . $pattern . '$#i';

            if (preg_match($pattern, $uri, $matches)) {
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = urldecode($value);
                    }
                }

                $this->executeHandler($route['handler'], $params);
                return;
            }
        }

        $this->handleNotFound();
    }

    /**
     * Parse handler string and execute Controller method.
     */
    private function executeHandler(string $handler, array $params): void {
        list($controllerClass, $method) = explode('@', $handler);
        $fullControllerClass = "App\\Controllers\\" . $controllerClass;

        if (class_exists($fullControllerClass)) {
            $controller = new $fullControllerClass();
            if (method_exists($controller, $method)) {
                call_user_func_array([$controller, $method], [$params]);
            } else {
                $this->handleServerError("Method '{$method}' not found in Controller '{$fullControllerClass}'.");
            }
        } else {
            $this->handleServerError("Controller Class '{$fullControllerClass}' not found.");
        }
    }

    /**
     * Render a standard 404.
     */
    private function handleNotFound(): void {
        http_response_code(404);
        
        $viewPath = APP_PATH . '/Views/frontend/404.php';
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            echo "<!DOCTYPE html><html lang='fr'><head><meta charset='UTF-8'><title>Page non trouvée - 404</title>";
            echo "<style>body{background:#0b0f19;color:#cbd5e1;font-family:sans-serif;display:flex;justify-content:center;align-items:center;height:100vh;flex-direction:column;margin:0;} h1{color:#f87171;font-size:3rem;margin:0;} p{font-size:1.2rem;} a{color:#2563eb;text-decoration:none;}</style></head>";
            echo "<body><h1>404</h1><p>Désolé, la page que vous recherchez n'existe pas.</p><a href='/'>Retour à l'accueil</a></body></html>";
        }
    }

    /**
     * Render a standard 500 error.
     */
    private function handleServerError(string $message): void {
        http_response_code(500);
        if (ENVIRONMENT === 'development') {
            echo "<h1>500 Internal Server Error</h1>";
            echo "<p>{$message}</p>";
        } else {
            echo "<h1>500 Erreur Interne</h1><p>Une erreur est survenue sur le serveur.</p>";
        }
    }
}
