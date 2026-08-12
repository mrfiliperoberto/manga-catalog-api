<?php

declare(strict_types=1);

namespace Mrfiliperoberto\MangaCatalogApi\Http;

final class Router
{
    /**
     * @var array<int, array{method: string, pattern: string, handler: callable}>
     */
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, callable $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, callable $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable $handler): void
    {
        // Converts "/manga/{id}" into a regex like "#^/manga/(?<id>[^/]+)$#"
        $pattern = preg_replace('#\{(\w+)\}#', '(?<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request): Response
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method) {
                continue;
            }

            if (preg_match($route['pattern'], $request->path, $matches) === 1) {
                $parameters = array_filter(
                    $matches,
                    fn (string|int $key): bool => is_string($key),
                    ARRAY_FILTER_USE_KEY,
                );

                return ($route['handler'])($request, $parameters);
            }
        }

        return Response::json(['error' => 'Route not found'], 404);
    }
}