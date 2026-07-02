<?php

class EmployeeController
{
    private EmployeeService $service;

    public function __construct(EmployeeService $service)
    {
        $this->service = $service;
    }

    private function sendJsonResponse(int $status_code, array $data): void
    {
        http_response_code($status_code);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function index(): void
    {
        $result = $this->service->getAllEmployees();
        $this->sendJsonResponse($result['status_code'], $result['body']);
    }

    public function show(string $id): void
    {
        $result = $this->service->getEmployeeById((int) $id);
        $this->sendJsonResponse($result['status_code'], $result['body']);
    }

    public function countByDepartment(): void
    {
        $result = $this->service->getEmployeeCountByDepartment();
        $this->sendJsonResponse($result['status_code'], $result['body']);
    }

    public function store(): void
    {
        $raw_body = file_get_contents('php://input');
        $data = json_decode($raw_body, true);
        $result = $this->service->createEmployee($data ?? []);
        $this->sendJsonResponse($result['status_code'], $result['body']);
    }

    public function update(string $id): void
    {
        $rawBody = file_get_contents('php://input');
        $data = json_decode($rawBody, true);
        $result = $this->service->updateEmployee((int) $id, $data ?? []);
        $this->sendJsonResponse($result['status_code'], $result['body']);
    }

    public function destroy(string $id): void
    {
        $result = $this->service->deleteEmployee((int) $id);
        $this->sendJsonResponse($result['status_code'], $result['body']);
    }
}
