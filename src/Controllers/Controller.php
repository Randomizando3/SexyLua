<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\App;
use App\Core\Request;

abstract class Controller
{
    public function __construct(
        protected readonly App $app,
    ) {
    }

    protected function render(string $template, array $data = [], ?string $layout = 'layouts/marketing'): void
    {
        $this->app->view->render($template, $data, $layout);
    }

    protected function redirect(string $path, ?string $message = null, string $type = 'success'): never
    {
        if ($message !== null) {
            $this->app->flash->add($type, $message);
        }

        redirect_to($path);
    }

    protected function validateCsrf(Request $request, string $redirect): void
    {
        if (! $this->app->csrf->validate((string) $request->input('_token'))) {
            $this->redirect($redirect, 'Sessao expirada. Envie o formulario novamente.', 'error');
        }
    }

    protected function user(): ?array
    {
        return $this->app->auth->user();
    }

    protected function json(array $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo (string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
