<?php

/**
 * Router - matches incoming HTTP requests to registered route handlers
 * 
 * Supports URL parameters with {param} syntax (e.g., /employees/{id})
 */
class Router
{
    private array $routes = [];

    /**
     * Register a new route
     * 
     * @param string   $method   HTTP method (GET, POST, PUT, DELETE)
     * @param string   $pattern  URL pattern with optional {param} placeholders
     * @param callable $handler  Callback to invoke when route matches
     */
    public function add(string $method, string $pattern, callable $handler): void
    {
        $this->routes[] = [
            'method'  => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    /**
     * Dispatch the incoming request to the matching route handler
     * 
     * @param string $method HTTP method of the current request
     * @param string $path   URL path of the current request
     */
    public function dispatch(string $method, string $path): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($method)) {
                continue;
            }
            $params = $this->match($route['pattern'], $path);
            if ($params !== null) {
                call_user_func_array($route['handler'], $params);
                return;
            }
        }
        http_response_code(404);
        echo json_encode([
            'status'  => 'Not Found',
            'message' => "No route matches $method $path",
        ]);
    }

    /**
     * Match a route pattern against a URL path
     * 
     * @param string $pattern Route pattern (e.g., /employees/{id})
     * @param string $path    Actual URL path (e.g., /employees/5)
     * @return array|null     Array of extracted parameters, or null if no match
     */
    private function match(string $pattern, string $path): ?array
    {
        $pattern_parts = array_values(array_filter(explode('/', $pattern), 'strlen'));
        $path_parts    = array_values(array_filter(explode('/', $path), 'strlen'));

        if (count($pattern_parts) !== count($path_parts)) {
            return null;
        }

        $params = [];
        foreach ($pattern_parts as $i => $part) {
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $params[] = $path_parts[$i];
            } elseif ($part !== $path_parts[$i]) {
                return null;
            }
        }
        return $params;
    }
}
