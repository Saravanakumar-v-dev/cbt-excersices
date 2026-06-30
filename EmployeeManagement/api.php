<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'Database.php';
require_once 'Employee.php';
require_once 'FullTimeEmployee.php';
require_once 'PartTimeEmployee.php';
require_once 'EmployeeRepository.php';

function sendJsonResponse(int $status_code, array $data): void
{
	http_response_code($status_code);
	echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
	exit;
}

function buildEmployeeResponse(array $row): array
{
	$employee = [
		'employee_id' => (int) $row['employee_id'],
		'first_name' => $row['first_name'],
		'last_name' => $row['last_name'],
		'department' => $row['department'],
		'experience_of_employee' => (int) $row['experience_of_employee'],
		'phone_number' => $row['phone_number'],
		'email_address' => $row['email_address'],
		'aadhar_number' => $row['aadhar_number'],
		'pan_number' => $row['pan_number'],
		'date_of_birth' => $row['date_of_birth'],
		'nationality' => $row['nationality'],
		'marital_status' => $row['marital_status'],
		'type_of_employee' => $row['type_of_employee'],
	];

	if ($row['type_of_employee'] === 'Full Time') {
		$employee['salary'] = $row['salary'] !== null ? (float) $row['salary'] : null;
		$employee['benefits'] = $row['benefits'] ?? null;
	} elseif ($row['type_of_employee'] === 'Part Time') {
		$employee['hourly_rate'] = $row['hourly_rate'] !== null ? (float) $row['hourly_rate'] : null;
		$employee['shift_type'] = $row['shift_type'] ?? null;
	}
	return $employee;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
	http_response_code(200);
	exit;
}

$db = new Database();
$conn = $db->conn;
$repository = new EmployeeRepository();

if ($conn === null) {
	sendJsonResponse(503, [
		'status' => 'Service Unavailable',
		'message' => 'Database connection unavailable. Please try again later.',
	]);
}

if ($method === 'GET') {
	handleGet($conn, $repository);
} elseif ($method === 'POST') {
	handlePost($conn, $repository);
} elseif ($method === 'PUT') {
	handlePut($conn,$repository);
} elseif ($method === 'DELETE') {
	handleDelete($conn, $repository);
} else {
	sendJsonResponse(405, [
		'status' => 'Method Not allowed',
		'message' => "Method '$method' is not allowed. Use GET or POST.",
	]);
}

function handleGet(mysqli $conn, EmployeeRepository $repository): void
{
	if (isset($_GET['id'])) {
		$id = $_GET['id'];

		if (!ctype_digit((string) $id) || (int) $id <= 0) {
			sendJsonResponse(400, [
				'status' => 'Bad request',
				'message' => 'Invalid employee ID. Must be a positive integer.',
			]);
		}

		$employee_id = (int) $id;
		$row = $repository->getEmployeeByIdAsArray($conn, $employee_id);

		if ($row !== null) {
			$employee_data = buildEmployeeResponse($row);

			sendJsonResponse(200, [
				'status' => 'success',
				'message' => 'Employee found.',
				'data' => $employee_data,
			]);
		} else {
			sendJsonResponse(404, [
				'status' => 'Not Found',
				'message' => "Employee with ID $employee_id not found.",
			]);
		}
	} else {
		$rows = $repository->getAllEmployeesAsArray($conn);

		if (!empty($rows)) {
			$employees = array_map('buildEmployeeResponse', $rows);

			sendJsonResponse(200, [
				'status' => 'success',
				'message' => 'Employees retrieved successfully.',
				'total_count' => count($employees),
				'data' => $employees,
			]);
		} else {
			sendJsonResponse(200, [
				'status' => 'success',
				'message' => 'No employees found.',
				'total_count' => 0,
				'data' => [],
			]);
		}
	}
}

function handlePost(mysqli $conn, EmployeeRepository $repository): void
{
	$raw_body = file_get_contents('php://input');
	$data = json_decode($raw_body, true);

	if ($data === null || empty($data)) {
		sendJsonResponse(400, [
			'status' => 'Bad request',
			'message' => 'Invalid or empty JSON body. Please send a valid JSON object.',
		]);
	}
	$required_base_fields = [
		'employee_id',
		'first_name',
		'last_name',
		'department',
		'experience_of_employee',
		'phone_number',
		'email_address',
		'aadhar_number',
		'pan_number',
		'date_of_birth',
		'nationality',
		'marital_status',
		'type_of_employee',
	];

	$missing_fields = [];
	foreach ($required_base_fields as $field) {
		if (!array_key_exists($field, $data) || (is_string($data[$field]) && trim($data[$field]) === '')) {
			$missing_fields[] = $field;
		}
	}

	if (!empty($missing_fields)) {
		sendJsonResponse(400, [
			'status' => 'Bad request',
			'message' => 'Missing required fields.',
			'missing_fields' => $missing_fields,
		]);
	}
	$type = trim($data['type_of_employee']);
	if ($type !== 'Full Time' && $type !== 'Part Time') {
		sendJsonResponse(400, [
			'status' => 'Bad request',
			'message' => 'type_of_employee must be either "Full Time" or "Part Time".',
		]);
	}

	if ($type === 'Full Time') {
		if (!isset($data['salary']) || !isset($data['benefits'])) {
			sendJsonResponse(400, [
				'status' => 'Bad request',
				'message' => 'Full Time employees require "salary" and "benefits" fields.',
			]);
		}
	} else {
		if (!isset($data['hourly_rate']) || !isset($data['shift_type'])) {
			sendJsonResponse(400, [
				'status' => 'Bad request',
				'message' => 'Part Time employees require "hourly_rate" and "shift_type" fields.',
			]);
		}
	}
	$employee_id = $data['employee_id'];
	if (!is_numeric($employee_id) || (int) $employee_id <= 0) {
		sendJsonResponse(400, [
			'status' => 'Bad request',
			'message' => 'employee_id must be a positive integer.',
		]);
	}
	$employee_id = (int) $employee_id;
	if ($repository->employeeExists($conn, $employee_id)) {
		sendJsonResponse(409, [
			'status' => 'Conflict error',
			'message' => "Employee with ID $employee_id already exists. Use a different ID.",
		]);
	}
	if ($type === 'Full Time') {
		$employee = new FullTimeEmployee(
			$employee_id,
			trim($data['first_name']),
			trim($data['last_name']),
			trim($data['department']),
			(int) $data['experience_of_employee'],
			trim($data['phone_number']),
			trim($data['email_address']),
			trim($data['aadhar_number']),
			strtoupper(trim($data['pan_number'])),
			trim($data['date_of_birth']),
			trim($data['nationality']),
			trim($data['marital_status']),
			'Full Time',
			(float) $data['salary'],
			trim($data['benefits'])
		);
	} else {
		$employee = new PartTimeEmployee(
			$employee_id,
			trim($data['first_name']),
			trim($data['last_name']),
			trim($data['department']),
			(int) $data['experience_of_employee'],
			trim($data['phone_number']),
			trim($data['email_address']),
			trim($data['aadhar_number']),
			strtoupper(trim($data['pan_number'])),
			trim($data['date_of_birth']),
			trim($data['nationality']),
			trim($data['marital_status']),
			'Part Time',
			(float) $data['hourly_rate'],
			trim($data['shift_type'])
		);
	}
	$success = $repository->insertEmployee($employee);

	if ($success) {
		sendJsonResponse(201, [
			'status' => 'success',
			'message' => "Employee with ID $employee_id created successfully.",
			'data' => $employee->jsonSerialize(),
		]);
	} else {
		sendJsonResponse(500, [
			'status' => 'Internal Server Error',
			'message' => 'Failed to create employee. Database error occurred.',
		]);
	}
}

function handleDelete(mysqli $conn, EmployeeRepository $repository): void
{
	if (!isset($_GET['id'])) {
		sendJsonResponse(400, [
			'status' => 'Bad request',
			'message' => 'Employee ID is required.',
		]);
	}
	$id = $_GET['id'];
	if (!ctype_digit((string) $id) || (int) $id <= 0) {
		sendJsonResponse(400, [
			'status' => 'Bad request',
			'message' => 'Invalid employee ID. Must be a positive integer.',
		]);
	}
	$employee_id = (int) $id;
	$existing = $repository->getEmployeeByIdAsArray($conn, $employee_id);

	if ($existing === null) {
		sendJsonResponse(404, [
			'status' => 'Not Found',
			'message' => "Employee with ID $employee_id not found.",
		]);
	}
	$deleted = $repository->deleteEmployee($employee_id);
	if ($deleted) {
		sendJsonResponse(200, [
			'status' => 'success',
			'message' => "Employee with ID $employee_id deleted successfully.",
		]);
	} else {
		sendJsonResponse(500, [
			'status' => 'Internal Server error',
			'message' => 'Failed to delete employee. Please try again.',
		]);
	}
}

function handlePut(mysqli $conn, EmployeeRepository $repository)
{
	$raw_body = file_get_contents('php://input');
	$data = json_decode($raw_body, true);

	if ($data === null || empty($data)) {
		sendJsonResponse(400, [
			'status' => 'Bad Request',
			'message' => 'Invalid or empty JSON body.'
		]);
	}

	$id = $data['id'];

	$employee = $repository->getEmployeeByIdAsArray($conn, $id);

	if ($employee === null) {
		sendJsonResponse(404, [
			'status' => 'Not Found',
			'message' => "Employee with ID $id not found."
		]);
	}

	if (!isset($data['type_of_employee'])) {
		sendJsonResponse(400, [
			'status' => 'Bad Request',
			'message' => 'type_of_employee is required.'
		]);
	}
	$type = trim($data['type_of_employee']);
	if ($type !== 'Full Time' && $type !== 'Part Time') {
		sendJsonResponse(400, [
			'status' => 'Bad Request',
			'message' => 'type_of_employee must be either "Full Time" or "Part Time".'
		]);
	}
	if ($type === 'Full Time') {
		foreach (['salary', 'benefits'] as $field) {
			if (!isset($data[$field])) {
				sendJsonResponse(400, [
					'status' => 'Bad Request',
					'message' => "$field is required for Full Time employees."
				]);
			}
		}
		$updated_employee = new FullTimeEmployee(
			$id,
			trim($data['first_name']),
			trim($data['last_name']),
			trim($data['department']),
			(int) $data['experience_of_employee'],
			trim($data['phone_number']),
			trim($data['email_address']),
			trim($data['aadhar_number']),
			strtoupper(trim($data['pan_number'])),
			trim($data['date_of_birth']),
			trim($data['nationality']),
			trim($data['marital_status']),
			'Full Time',
			(float) $data['salary'],
			trim($data['benefits'])
		);
	} else {
		foreach (['hourly_rate', 'shift_type'] as $field) {
			if (!isset($data[$field])) {
				sendJsonResponse(400, [
					'status' => 'Bad Request',
					'message' => "$field is required for Part Time employees."
				]);
			}
		}

		if (!is_numeric($data['hourly_rate'])) {
			sendJsonResponse(400, [
				'status' => 'Bad Request',
				'message' => 'hourly_rate must be numeric.'
			]);
		}

		$updated_employee = new PartTimeEmployee(
			$id,
			trim($data['first_name']),
			trim($data['last_name']),
			trim($data['department']),
			(int) $data['experience_of_employee'],
			trim($data['phone_number']),
			trim($data['email_address']),
			trim($data['aadhar_number']),
			strtoupper(trim($data['pan_number'])),
			trim($data['date_of_birth']),
			trim($data['nationality']),
			trim($data['marital_status']),
			'Part Time',
			(float) $data['hourly_rate'],
			trim($data['shift_type'])
		);
	}
	$updated = $repository->updateEmployee($updated_employee);
	if ($updated) {
		sendJsonResponse(200, [
			'status' => 'Success',
			'message' => "Employee with ID $id updated successfully.",
			'data' => $updated_employee->jsonSerialize()
		]);
	}
	sendJsonResponse(500, [
		'status' => 'Internal Server Error',
		'message' => 'Failed to update employee.'
	]);
}

?>
