<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}
require_once __DIR__ . '/Config/Database.php';
require_once __DIR__ . '/Helpers/QueryHelper.php';
require_once __DIR__ . '/Models/Employee.php';
require_once __DIR__ . '/Models/FullTimeEmployee.php';
require_once __DIR__ . '/Models/PartTimeEmployee.php';
require_once __DIR__ . '/Repository/EmployeeRepository.php';
require_once __DIR__ . '/Services/EmployeeService.php';
require_once __DIR__ . '/Routes/Router.php';
require_once __DIR__ . '/Controllers/EmployeeController.php';

$db = new Database();
$conn = $db->conn;

if ($conn === null) {
    http_response_code(503);
    echo json_encode([
        'status' => 'Service Unavailable',
        'message' => 'Database connection unavailable.',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$repository = new EmployeeRepository();
$service = new EmployeeService($conn, $repository);
$controller = new EmployeeController($service);
$router = new Router();
require_once __DIR__ . '/Routes/Routes.php';
