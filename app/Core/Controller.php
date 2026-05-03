<?php

namespace App\Core;

class Controller
{
    protected $twig;
    protected $data = [];

    public function __construct($twig)
    {
        $this->twig = $twig;
        $this->data['flash'] = $this->pullFlash();
    }

    protected function view($template, $data = [])
    {
        $data = array_merge($this->data, $data);

        echo $this->twig->render($template, $data);
    }

    protected function redirect($url)
    {
        header("Location: $url");
        exit;
    }

    protected function back()
    {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/'));
        exit;
    }

    protected function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function flash(string $variant, string $message, ?string $title = null): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION['_flash'] = [
            'variant' => $this->normalizeFlashVariant($variant),
            'title' => $title ?? $this->defaultFlashTitle($variant),
            'message' => trim($message),
        ];
    }

    private function pullFlash(): ?array
    {
        if (session_status() !== PHP_SESSION_ACTIVE || !isset($_SESSION['_flash']) || !is_array($_SESSION['_flash'])) {
            return null;
        }

        $flash = $_SESSION['_flash'];
        unset($_SESSION['_flash']);

        $message = trim((string) ($flash['message'] ?? ''));

        if ($message === '') {
            return null;
        }

        return [
            'variant' => $this->normalizeFlashVariant((string) ($flash['variant'] ?? 'info')),
            'title' => trim((string) ($flash['title'] ?? '')) ?: $this->defaultFlashTitle((string) ($flash['variant'] ?? 'info')),
            'message' => $message,
        ];
    }

    private function normalizeFlashVariant(string $variant): string
    {
        return in_array($variant, ['success', 'error', 'info'], true) ? $variant : 'info';
    }

    private function defaultFlashTitle(string $variant): string
    {
        if ($variant === 'success') {
            return 'Tudo certo';
        }

        if ($variant === 'error') {
            return 'Atenção';
        }

        return 'Aviso';
    }
}
