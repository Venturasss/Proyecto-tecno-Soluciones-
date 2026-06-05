<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        $viewPath   = dirname(__DIR__) . '/views/' . $view . '.php';
        $headerPath = dirname(__DIR__) . '/layouls/header.php';
        $footerPath = dirname(__DIR__) . '/layouls/footer.php';

        if (!is_file($viewPath)) {
            die("Error Crítico: No se pudo encontrar el archivo de la vista en la ruta: <br><b>" . $viewPath . "</b>");
        }

        if (is_file($headerPath)) require $headerPath;
        require $viewPath;
        if (is_file($footerPath)) require $footerPath;
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . BASE_URL . ltrim($path, '/'));
        exit;
    }

    protected function requireAuth(): void
    {
        if (empty($_SESSION['user'])) {
            $this->redirect('login');
        }
    }

    protected function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function verifyCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(419);
            exit('Token de seguridad inválido');
        }
    }

    // ── Flash messages ────────────────────────────────────────────────────────

    /** Guarda un mensaje flash en sesión */
    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    /** Recupera y borra el mensaje flash (llamado desde la vista) */
    public static function getFlash(): ?array
    {
        if (!empty($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}