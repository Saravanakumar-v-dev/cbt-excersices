<?php
require_once 'Database.php';
require_once 'QueryHelper.php';

class EmployeeRepository{
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
    $sql = "INSERT INTO employee VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $this->execute($sql, [
        $employee->getEmployeeId(),
        $employee->getFirstName(),
        $employee->getLastName(),
        $employee->getDepartment(),
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
        $shiftType
    ]);

    if ($stmt) {
        $stmt->close();
        return true;
    }
    return false;
}
function viewAllEmployee(?mysqli $conn, Employee $employee){
    if ($conn == null) {
        return false;
    }
    $sql = "SELECT * from employees";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "Employee ID:  " . $row["employee_id"] . " \n ";
            echo "First Name: " . $row["first_name"] . "\n  ";
            echo "Last Name: " . $row["last_name"] . " \n ";
            echo "Department: " . $row["department"] . "\n";
            echo "Experience of the Employee: " . $row["experience_of_employee"] . " \n";
            echo "Phone Number: " . $row["phone_number"] . "\n";
            echo "Email Address: " . $row["email_address"] . "\n";
            echo "Aadhar Number: " . $row["aadhar_number"] . "\n";
            echo "Pan Number: " . $row["pan_number"] . "\n";
            echo "Date Of Birth :" . $row["date_of_birth"] . "\n";
            echo "Nationality :" . $row["nationality"] . "\n";
            echo "Marital Status :" . $row["marital_status"] . "\n";
            if ($row["type_of_employee"] == "Full Time") {
                echo "\n------ Full Time Employee ------\n";
                echo "Monthly Salary :" . $row["salary"] . "\n";
                echo "Benefits :" . $row["benefits"] . "\n";
            } else if ($row["type_of_employee"] == "Part Time") {
                echo "\n------ Part Time Employee ------\n";
                echo "Hourly Rate :" . $row["hourly_rate"] . "\n";
                echo "Shift :" . $row["shift_type"] . "\n\n";
            }
            }
        } else {
            echo "0 Results Found";
        }
}
function viewEmployeeById($employee_id){
    $sql = "SELECT * FROM employee WHERE employee_id=?";
    $stmt = $this->execute($sql, [$employee_id]);
    $result = $stmt->get_result();
    $employee = $result->fetch_assoc();
    $stmt->close();
    return $employee;
}
    // public function deleteEmployeeById(?mysqli $conn, int $id): bool{
    //     if ($conn === null) {
    //         echo "\nMySQL connection is not available. Cannot delete by ID.\n";
    //         return false;
    //     }
    //     $sql = "DELETE FROM employees WHERE employee_id = ?";
    //     $stmt = $this->execute($sql,[$id]);
    //     if (!$stmt) {
    //         return false;
    //     }
    //     if ($stmt->affected_rows > 0) {
    //         echo "Deleted Successfully\n";
    //     }
    //     return false;
    // }
function deleteEmployee(int $employeeId){
    $sql = "UPDATE employee
            SET is_deleted = 1
            WHERE employee_id = ?";
    $stmt = $this->execute($sql, [$employeeId]);
    if ($stmt && $stmt->affected_rows > 0) {
        echo "Employee deleted successfully.\n";
        $stmt->close();
        return true;
    }
    echo "Employee not found.\n";
    return false;
}

function updateEmployee(Employee $employee){

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
    $sql = "UPDATE employee SET
        first_name=?,
        last_name=?,
        department=?,
        experience=?,
        phone_number=?,
        email_address=?,
        aadhar_number=?,
        pan_number=?,
        date_of_birth=?,
        nationality=?,
        marital_status=?,
        type_of_employee=?,
        salary=?,
        benefits=?,
        hourly=?,
        shift=?
        WHERE employee_id=?";
    $stmt = $this->execute($sql, [
        $employee->getFirstName(),
        $employee->getLastName(),
        $employee->getDepartment(),
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
        $shiftType
    ]);
    if ($stmt->affected_rows > 0) {
        $stmt->close();
        return true;
    }
    return false;
}
function getAllEmployeesAsArray(?mysqli $conn): array{
        if ($conn === null) {
            return [];
        }
        $sql = "SELECT * FROM employees";
        $result = $conn->query($sql);
        $employees = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $employees[] = $row;
            }
        }
        return $employees;
}
function getEmployeeByIdAsArray(?mysqli $conn, int $id): ?array{
        
    if ($conn === null) {
    return null;
    }
    $sql = "SELECT * FROM employees WHERE employee_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row;
    }
    $stmt->close();
    return null;
}
function employeeExists(?mysqli $conn, int $id): bool{
    return $this->getEmployeeByIdAsArray($conn, $id) !== null;
}
}
?>