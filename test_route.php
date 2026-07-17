<?php
// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create a request to /
$request = Illuminate\Http\Request::create('http://localhost/projects/aariva/', 'GET');
$response = $kernel->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
echo "Content preview: " . substr($response->getContent(), 0, 500) . "\n";

// Check which route matched
$routes = app('router')->getRoutes();
foreach ($routes as $route) {
    if ($route->uri() === '/') {
        echo "Route URI: /, Name: " . ($route->getName() ?? 'unnamed') . ", Action: " . (is_string($route->getAction('uses')) ? $route->getAction('uses') : 'Closure') . "\n";
    }
}
