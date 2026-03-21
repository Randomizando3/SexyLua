<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    private array $shared = [];

    public function __construct(
        private readonly string $basePath,
    ) {
    }

    public function share(array $data): void
    {
        $this->shared = array_merge($this->shared, $data);
    }

    public function render(string $template, array $data = [], ?string $layout = 'layouts/marketing'): void
    {
        $payload = array_merge($this->shared, $data);
        $templatePath = $this->resolve($template);

        extract($payload, EXTR_SKIP);

        ob_start();
        include $templatePath;
        $content = (string) ob_get_clean();
        $content = prototype_apply($content, $payload);

        if ($layout === null) {
            echo $content;

            return;
        }

        $layoutPath = $this->resolve($layout);
        include $layoutPath;
    }

    private function resolve(string $template): string
    {
        $path = $this->basePath . '/' . str_replace('.', '/', $template) . '.php';

        if (! is_file($path)) {
            throw new \RuntimeException(sprintf('Template nao encontrado: %s', $template));
        }

        return $path;
    }
}
