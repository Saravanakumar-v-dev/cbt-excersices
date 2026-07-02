<?php

class EmployeeService
{
    private mysqli $conn;
    private EmployeeRepository $repository;

    public function __construct(mysqli $conn, EmployeeRepository $repository)
    {
        $this->conn = $conn;
        $this->repository = $repository;
    }

    public function getAllEmployees(): array
    {
        $rows = $this->repository->getAllEmployeesAsArray($this->conn);

        if (empty($rows)) {
            return [
                'status_code' => 200,
                'body' => [
                    'status' => 'Success',
                    'message' => 'No employees found.',
                    'total_count' => 0,
                    'data' => []
                ]
            ];
        }

        $employees = array_map([$this, 'buildEmployeeResponse'], $rows);

        return [
            'status_code' => 200,
            'body' => [
                'status' => 'Success',
                'message' => 'Employees retrieved successfully.',
                'total_count' => count($employees),
                'data' => $employees
            ]
        ];
    }

    public function getEmployeeById(int $id): array
    {
        if ($id <= 0) {
            return [
                'status_code' => 400,
                'body' => [
                    'status' => 'Bad Request',
                    'message' => 'Invalid employee ID.'
                ]
            ];
        }

        $row = $this->repository->getEmployeeByIdAsArray($this->conn, $id);

        if ($row === null) {
            return [
                'status_code' => 404,
                'body' => [
                    'status' => 'Not Found',
                    'message' => "Employee with ID $id not found."
                ]
            ];
        }

        return [
            'status_code' => 200,
            'body' => [
                'status' => 'Success',
                'message' => 'Employee retrieved successfully.',
                'data' => $this->buildEmployeeResponse($row)
            ]
        ];
    }

    public function getEmployeeCountByDepartment(): array
    {
        $employees_count = $this->repository->getEmployeeCount($this->conn);

        return [
            'status_code' => 200,
            'body' => [
                'status' => 'Success',
                'message' => 'Employee count by department retrieved successfully.',
                'total_count' => count($employees_count),
                'data' => $employees_count
            ]
        ];
    }

    public function createEmployee(array $data): array
    {
        if ($data === null || empty($data)) {
            return [
                'status_code' => 400,
                'body' => [
                    'status' => 'Bad request',
                    'message' => 'Invalid or empty JSON body. Please send a valid JSON object.',
                ]
            ];
        }

        $required_base_fields = [
            'employee_id',
            'first_name',
            'last_name',
            'department_id',
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
            return [
                'status_code' => 400,
                'body' => [
                    'status' => 'Bad request',
                    'message' => 'Missing required fields.',
                    'missing_fields' => $missing_fields,
                ]
            ];
        }

        $type = trim($data['type_of_employee']);
        if ($type !== 'Full Time' && $type !== 'Part Time') {
            return [
                'status_code' => 400,
                'body' => [
                    'status' => 'Bad request',
                    'message' => 'type_of_employee must be either "Full Time" or "Part Time".',
                ]
            ];
        }

        if ($type === 'Full Time') {
            if (!isset($data['salary']) || !isset($data['benefits'])) {
                return [
                    'status_code' => 400,
                    'body' => [
                        'status' => 'Bad request',
                        'message' => 'Full Time employees require "salary" and "benefits" fields.',
                    ]
                ];
            }
        } else {
            if (!isset($data['hourly_rate']) || !isset($data['shift_type'])) {
                return [
                    'status_code' => 400,
                    'body' => [
                        'status' => 'Bad request',
                        'message' => 'Part Time employees require "hourly_rate" and "shift_type" fields.',
                    ]
                ];
            }
        }

        $employee_id = $data['employee_id'];
        if (!is_numeric($employee_id) || (int) $employee_id <= 0) {
            return [
                'status_code' => 400,
                'body' => [
                    'status' => 'Bad request',
                    'message' => 'employee_id must be a positive integer.',
                ]
            ];
        }
        $employee_id = (int) $employee_id;

        if ($this->repository->employeeExists($this->conn, $employee_id)) {
            return [
                'status_code' => 409,
                'body' => [
                    'status' => 'Conflict error',
                    'message' => "Employee with ID $employee_id already exists. Use a different ID.",
                ]
            ];
        }

        $employee = $this->buildEmployeeObject($data, $type, $employee_id);
        $success = $this->repository->insertEmployee($employee);

        if ($success) {
            return [
                'status_code' => 201,
                'body' => [
                    'status' => 'success',
                    'message' => "Employee with ID $employee_id created successfully.",
                    'data' => $employee->jsonSerialize(),
                ]
            ];
        }

        return [
            'status_code' => 500,
            'body' => [
                'status' => 'Internal Server Error',
                'message' => 'Failed to create employee. Database error occurred.',
            ]
        ];
    }

    public function updateEmployee(int $id, array $data): array
    {
        if (!is_array($data)) {
            return [
                'status_code' => 400,
                'body' => [
                    'status' => 'Bad Request',
                    'message' => 'Invalid or empty JSON body.'
                ]
            ];
        }

        if ($id <= 0) {
            return [
                'status_code' => 400,
                'body' => [
                    'status' => 'Bad Request',
                    'message' => 'Employee id is required.'
                ]
            ];
        }

        $employee = $this->repository->getEmployeeByIdAsArray($this->conn, $id);

        if ($employee === null) {
            return [
                'status_code' => 404,
                'body' => [
                    'status' => 'Not Found',
                    'message' => "Employee with ID $id not found."
                ]
            ];
        }

        if (!isset($data['type_of_employee'])) {
            return [
                'status_code' => 400,
                'body' => [
                    'status' => 'Bad Request',
                    'message' => 'type_of_employee is required.'
                ]
            ];
        }

        $type = $data['type_of_employee'];
        $updatedEmployee = $this->buildEmployeeObject($data, $type, (int) $employee['employee_id']);
        $updated = $this->repository->updateEmployee($updatedEmployee, $id);

        if ($updated) {
            return [
                'status_code' => 200,
                'body' => [
                    'status' => 'Success',
                    'message' => "Employee with ID $id updated successfully.",
                    'data' => $updatedEmployee->jsonSerialize()
                ]
            ];
        }

        return [
            'status_code' => 500,
            'body' => [
                'status' => 'Internal Server Error',
                'message' => 'Failed to update employee.'
            ]
        ];
    }

    public function deleteEmployee(int $id): array
    {
        if ($id <= 0) {
            return [
                'status_code' => 400,
                'body' => [
                    'status' => 'Bad Request',
                    'message' => 'Invalid employee ID.'
                ]
            ];
        }

        $employee = $this->repository->getEmployeeByIdAsArray($this->conn, $id);

        if ($employee === null) {
            return [
                'status_code' => 404,
                'body' => [
                    'status' => 'Not Found',
                    'message' => "Employee with ID $id not found."
                ]
            ];
        }

        $deleted = $this->repository->deleteEmployee($id);

        if ($deleted) {
            return [
                'status_code' => 200,
                'body' => [
                    'status' => 'Success',
                    'message' => "Employee with ID $id deleted successfully."
                ]
            ];
        }

        return [
            'status_code' => 500,
            'body' => [
                'status' => 'Internal Server Error',
                'message' => 'Failed to delete employee.'
            ]
        ];
    }

    private function buildEmployeeObject(array $data, string $type, int $employee_id): Employee
    {
        if ($type === 'Full Time') {
            return new FullTimeEmployee(
                $employee_id,
                trim($data['first_name']),
                trim($data['last_name']),
                trim($data['department_id']),
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
        }

        return new PartTimeEmployee(
            $employee_id,
            trim($data['first_name']),
            trim($data['last_name']),
            trim($data['department_id']),
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

    private function buildEmployeeResponse(array $row): array
    {
        $employee = [
            'employee_id' => (int) $row['employee_id'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'department_id' => $row['department_id'],
            'department_name' => $row['department_name'] ?? null,
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
}
