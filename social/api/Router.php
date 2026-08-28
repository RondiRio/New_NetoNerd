<?php
class Router
{
    private array $routes = [];

    public function get(string $pattern, callable $handler): void
    {
        $this->routes[] = ['GET', $pattern, $handler];
    }

    public function post(string $pattern, callable $handler): void
    {
        $this->routes[] = ['POST', $pattern, $handler];
    }

    public function patch(string $pattern, callable $handler): void
    {
        $this->routes[] = ['PATCH', $pattern, $handler];
    }

    public function delete(string $pattern, callable $handler): void
    {
        $this->routes[] = ['DELETE', $pattern, $handler];
    }

    public function dispatch(string $method, string $uri): void
    {
        // Remove query string
        $uri = strtok($uri, '?');
        // Remove trailing slash (except root)
        $uri = rtrim($uri, '/') ?: '/';

        foreach ($this->routes as [$routeMethod, $pattern, $handler]) {
            if ($routeMethod !== $method) continue;

            $regex  = $this->patternToRegex($pattern);
            if (preg_match($regex, $uri, $matches)) {
                // Numeric params in order
                $params = array_values(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));
                call_user_func_array($handler, array_map('intval', $params));
                return;
            }
        }

        Response::error('Rota não encontrada.', 'ROUTE_NOT_FOUND', 404);
    }

    private function patternToRegex(string $pattern): string
    {
        // Replace {id} style params with capture groups
        $regex = preg_replace('/\{[a-z_]+\}/', '([0-9]+)', $pattern);
        return '#^' . $regex . '$#';
    }
}
