<?php
namespace App\Controllers;

use App\Services\Session;
use App\Helpers\Sanitizer;

abstract class Controller {
    /**
     * Render a view file with an optional layout file.
     */
    protected function render(string $view, array $data = [], string $layout = ''): void {
        extract($data);

        ob_start();
        $viewFile = APP_PATH . '/Views/' . $view . '.php';

        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            trigger_error("View file not found: " . $viewFile, E_USER_ERROR);
        }

        $content = ob_get_clean();

        if (!empty($layout)) {
            $layoutFile = APP_PATH . '/Views/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                require $layoutFile;
            } else {
                trigger_error("Layout file not found: " . $layoutFile, E_USER_ERROR);
            }
        } else {
            echo $content;
        }
    }

    /**
     * Redirect to a specific URL and optionally set a flash message.
     */
    protected function redirect(string $url, string $flashType = '', string $flashMessage = ''): void {
        if (!empty($flashType) && !empty($flashMessage)) {
            Session::setFlash($flashType, $flashMessage);
        }
        header("Location: " . $url);
        exit;
    }

    /**
     * Output clean JSON response.
     */
    protected function json(mixed $data, int $statusCode = 200): void {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    /**
     * Generate and set a CSRF token in session.
     */
    protected function generateCsrf(): string {
        return \App\Services\CSRF::getToken();
    }

    /**
     * Validate the post request's CSRF token.
     */
    protected function validateCsrf(): bool {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!\App\Services\CSRF::validate($token)) {
                http_response_code(403);
                die("Validation CSRF échouée. Requête bloquée pour des raisons de sécurité.");
            }
        }
        return true;
    }

    /**
     * Sanitize an array or string using Sanitizer helper.
     */
    protected function sanitize(mixed $data): mixed {
        return Sanitizer::clean($data);
    }
}
