<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<int, array{method:string, pattern:string, regex:string, keys:array, handler:mixed}> */
    private array $routes = [];

    public function get(string $pattern, mixed $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, mixed $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    private function add(string $method, string $pattern, mixed $handler): void
    {
        $keys = [];
        $regex = preg_replace_callback('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', function ($m) use (&$keys) {
            $keys[] = $m[1];
            return '([^/]+)';
        }, $pattern);

        $this->routes[] = [
            'method' => $method,
            'regex' => '#^' . $regex . '$#u',
            'keys' => $keys,
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request): void
    {
        $path = rtrim($request->path, '/');
        if ($path === '') {
            $path = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method) {
                continue;
            }
            if (preg_match($route['regex'], $path, $matches)) {
                array_shift($matches);
                $params = array_combine($route['keys'], $matches);
                $request->setParams($params);
                $this->call($route['handler'], $request);
                return;
            }
        }

        $redirect = \App\Models\RedirectRule::findByFrom($path);
        if ($redirect !== null) {
            Response::redirect($redirect['to_path'], (int) $redirect['status_code']);
        }

        Response::notFound();
    }

    private function call(mixed $handler, Request $request): void
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = new $class();
            $controller->$method($request);
            return;
        }

        if (is_callable($handler)) {
            $handler($request);
            return;
        }

        throw new \RuntimeException('Geçersiz route handler.');
    }
}
