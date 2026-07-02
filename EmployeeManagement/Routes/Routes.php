<?php

/**
 * Routes.php - Define all API routes
 * 
 * Expects $router (Router) and $controller (EmployeeController) to be
 * initialized before this file is included.
 */

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$router->add('GET',    '/employees',                              [$controller, 'index']);
$router->add('GET',    '/employees/department-count',              [$controller, 'countByDepartment']);
$router->add('GET',    '/employees/{id}',                         [$controller, 'show']);
$router->add('POST',   '/employees',                              [$controller, 'store']);
$router->add('PUT',    '/employees/{id}',                         [$controller, 'update']);
$router->add('DELETE', '/employees/{id}',                         [$controller, 'destroy']);

$router->dispatch($method, $path);
