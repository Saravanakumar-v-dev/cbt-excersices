<?php
require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../Helpers/QueryHelper.php';

class EmployeeRepository
{
    use QueryHelper;

    function insertEmployee(Employee $employee)
    {
        $salary = null;
        $benefits = null;
        $hourlyRate = null;
        $shiftType = null;
        if ($employee instanceof FullTimeEmployee) {
            $salary = $employee->getMonthlySalary();
            $benefits = $employee->getBenefits();
        } elseif ($employee instanceof PartTimeEmployee) {
            $hourlyRate = $employee->getHourlyRate();
            $shiftType = $employee->getShiftType();
        }
        $sql = 'INSERT INTO employees (
    employee_id,
    first_name,
    last_name,
    department_id,
    experience_of_employee,
    phone_number,
    email_address,
    aadhar_number,
    pan_number,
    date_of_birth,
    nationality,
    marital_status,
    type_of_employee,
    salary,
    benefits,
    hourly_rate,
    shift_type,
    is_deleted
) VALUES 
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $this->execute($sql, [
            $employee->getEmployeeId(),
            $employee->getFirstName(),
            $employee->getLastName(),
            $employee->getDepartmentId(),
            $employee->getExperienceOfEmployee(),
            $employee->getPhoneNumber(),
            $employee->getEmailAddress(),
            $employee->getAadharNumber(),
            $employee->getPanNumber(),
            $employee->getDateOfBirth(),
            $employee->getNationality(),
            $employee->getMaritalStatus(),
            $employee->getTypeOfEmployee(),
            $salary,
            $benefits,
            $hourlyRate,
            $shiftType,
            0  // is_deleted = 0
        ]);

        if ($stmt) {
            $stmt->close();
            return true;
        }
        return false;
    }

    public function viewAllEmployee(?mysqli $conn): array
    {
        if ($conn === null) {
            return [];
        }
        $sql = 'SELECT e.*, d.department_name
        FROM employees e
        INNER JOIN departments d
        ON e.department_id = d.id
        WHERE e.is_deleted = 0
        ORDER BY e.id';

        $result = $conn->query($sql);
        $employees = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $employees[] = $row;
            }
        }
        return $employees;
    }

    public function getEmployeeCount(?mysqli $conn): array
    {
        if ($conn === null) {
            return [];
        }
        $sql = 'SELECT d.id AS department_id,
    d.department_name, 
    COUNT(e.id) AS employees_count
    FROM departments d
    LEFT JOIN employees e 
    ON d.id = e.department_id   
    AND e.is_deleted = 0
    GROUP BY 
    d.id, 
    d.department_name';
        $result = $conn->query($sql);
        if (!$result) {
            return [];
        }
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        return $data;
    }

    public function viewEmployeeById(int $id): ?array
    {
        $sql = 'SELECT e.*, d.department_name
        FROM employees e
        INNER JOIN departments d
        ON e.department_id = d.id
        WHERE e.id = ?
        AND e.is_deleted = 0';
        $stmt = $this->execute($sql, [$id]);
        if (!$stmt) {
            return null;
        }
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $stmt->close();
            return null;
        }
        $employee = $result->fetch_assoc();
        $stmt->close();

        return $employee;
    }

    public function deleteEmployee(int $id): bool
    {
        $sql = 'UPDATE employees
        SET is_deleted = 1
        WHERE id = ?
        AND is_deleted = 0';
        $stmt = $this->execute($sql, [$id]);
        if ($stmt) {
            $success = $stmt->affected_rows > 0;
            $stmt->close();
            return $success;
        }

        return false;
    }

    public function updateEmployee(Employee $employee, int $id): bool
    {
        $salary = null;
        $benefits = null;
        $hourlyRate = null;
        $shiftType = null;

        if ($employee instanceof FullTimeEmployee) {
            $salary = $employee->getMonthlySalary();
            $benefits = $employee->getBenefits();
        } elseif ($employee instanceof PartTimeEmployee) {
            $hourlyRate = $employee->getHourlyRate();
            $shiftType = $employee->getShiftType();
        }

        $sql = 'UPDATE employees
        SET
            first_name = ?,
            last_name = ?,
            department_id = ?,
            experience_of_employee = ?,
            phone_number = ?,
            email_address = ?,
            aadhar_number = ?,
            pan_number = ?,
            date_of_birth = ?,
            nationality = ?,
            marital_status = ?,
            type_of_employee = ?,
            salary = ?,
            benefits = ?,
            hourly_rate = ?,
            shift_type = ?
            WHERE id = ?
            AND is_deleted = 0';

        $stmt = $this->execute($sql, [
            $employee->getFirstName(),
            $employee->getLastName(),
            $employee->getDepartmentId(),
            $employee->getExperienceOfEmployee(),
            $employee->getPhoneNumber(),
            $employee->getEmailAddress(),
            $employee->getAadharNumber(),
            $employee->getPanNumber(),
            $employee->getDateOfBirth(),
            $employee->getNationality(),
            $employee->getMaritalStatus(),
            $employee->getTypeOfEmployee(),
            $salary,
            $benefits,
            $hourlyRate,
            $shiftType,
            $id
        ]);

        if ($stmt) {
            $stmt->close();
            return true;
        }

        return false;
    }

    public function getAllEmployeesAsArray(?mysqli $conn): array
    {
        if ($conn === null) {
            return [];
        }

        $sql = 'SELECT e.*, d.department_name FROM employees e
    INNER JOIN departments d
    ON e.department_id = d.id
    WHERE e.is_deleted = 0';
        $result = $conn->query($sql);

        $employees = [];

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $employees[] = $row;
            }
        }

        return $employees;
    }

    public function getEmployeeByIdAsArray(?mysqli $conn, int $id): ?array
    {
        if ($conn === null) {
            return null;
        }
        $sql = 'SELECT e.*, d.department_name
        FROM employees e
        INNER JOIN departments d
        ON e.department_id = d.id
        WHERE e.id = ?
        AND e.is_deleted = 0';

        $stmt = $this->execute($sql,[$id]);
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;

    }
    public function employeeExists(?mysqli $conn, int $id): bool
    {
        return $this->getEmployeeByIdAsArray($conn, $id) !== null;
    }
}
?>
