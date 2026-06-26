<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', 'root123');
define('DB_NAME', 'employee_management');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
	echo " Connection failed: " . $conn->connect_error . "\n";
	echo " System will work with JSON storage only.\n";
	$conn = null;
}

/**
 * Inserts an employee into the MySQL database
 * @param mysqli|null $conn
 * @param Employee $employee
 * @return bool
 */
function insertEmployee(?mysqli $conn, Employee $employee): bool
{
	if ($conn === null) {
		return false;
	}

	$employee_id   = $employee->getEmployeeId();
	$first_name    = $employee->getFirstName();
	$last_name     = $employee->getLastName();
	$department    = $employee->getDepartment();
	$experience    = $employee->getExperienceOfEmployee();
	$phone_number  = $employee->getPhoneNumber();
	$email_address = $employee->getEmailAddress();
	$aadhar_number = $employee->getAadharNumber();
	$pan_number    = $employee->getPanNumber();
	$date_of_birth = $employee->getDateOfBirth();
	$nationality   = $employee->getNationality();
	$marital_status= $employee->getMaritalStatus();
	$type_of_employee = $employee->getTypeOfEmployee();
	
	$salary   = null;
	$benefits = null;
	$hourly   = null;
	$shift    = null;

	if ($employee instanceof FullTimeEmployee) {
		$salary   = $employee->salary;
		$benefits = $employee->benefits;
	} elseif ($employee instanceof PartTimeEmployee) {
		$hourly   = $employee->hourly_rate;
		$shift    = $employee->shift_type;
	}

	$sql = "INSERT INTO employees 
			(employee_id, first_name, last_name, department, experience_of_employee,
			 phone_number, email_address, aadhar_number, pan_number, date_of_birth,
			 nationality, marital_status, type_of_employee, salary, benefits, hourly_rate, shift_type)
			VALUES 
			(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

	$stmt = $conn->prepare($sql);
	if (!$stmt) {
		echo "Prepare Failed: " . $conn->error . "\n";
		return false;
	}

	$stmt->bind_param("isssissssssssisss",
		$employee_id,
		$first_name,
		$last_name,
		$department,
		$experience,
		$phone_number,
		$email_address,
		$aadhar_number,
		$pan_number,
		$date_of_birth,
		$nationality,
		$marital_status,
		$type_of_employee,
		$salary,
		$benefits,
		$hourly,
		$shift
	);

	if ($stmt->execute()) {
		echo "Employee saved to MySQL database successfully.\n";
		$stmt->close();
		return true;
	} else {
		echo "Error: " . $stmt->error . "\n";
		$stmt->close();
		return false;
	}
}

function viewAllEmployee(?mysqli $conn, Employee $employee)
{
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
function viewEmployeeById(?mysqli $conn, int $id): bool
{
	if ($conn === null) {
		echo "\nMySQL connection is not available. Cannot search by ID.\n";
		return false;
	}
	$sql = "SELECT * FROM employees WHERE employee_id = ?";
	$stmt = $conn->prepare($sql);
	if ($stmt) {
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($result && $result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				echo "ID: " . $row["id"] . "\n";
				echo "Employee ID: " . $row["employee_id"] . "\n";
				echo "First Name: " . $row["first_name"] . "\n";
				echo "Last Name: " . $row["last_name"] . "\n";
				echo "Department: " . $row["department"] . "\n";
				echo "Experience of the Employee: " . $row["experience_of_employee"] . "\n";
				echo "Phone Number: " . $row["phone_number"] . "\n";
				echo "Email Address: " . $row["email_address"] . "\n";
				echo "Aadhar Number: " . $row["aadhar_number"] . "\n";
				echo "Pan Number: " . $row["pan_number"] . "\n";
				echo "Date Of Birth: " . $row["date_of_birth"] . "\n";
				echo "Nationality: " . $row["nationality"] . "\n";
				echo "Marital Status: " . $row["marital_status"] . "\n";
				if ($row["type_of_employee"] == "Full Time") {
					echo "\n------ Full Time Employee ------\n";
					echo "Monthly Salary:" . $row["salary"] . "\n";
					echo "Benefits:" . $row["benefits"] . "\n";
				} else if ($row["type_of_employee"] == "Part Time") {
					echo "\n------ Part Time Employee ------\n";
					echo "Hourly Rate :" . $row["hourly_rate"] . "\n";
					echo "Shift :" . $row["shift_type"] . "\n\n";
				}
			}
			$stmt->close();
			return true;
		} else {
			echo "\nEmployee with ID $id not found.\n";
		}
		$stmt->close();
	} else {
		echo "\nError preparing statement: " . $conn->error . "\n";
	}
	return false;
}
function deleteEmployeeById(?mysqli $conn, int $id): bool
{
	if ($conn === null) {
		echo "\nMySQL connection is not available. Cannot delete by ID.\n";
		return false;
	}
	$sql = "DELETE FROM employees WHERE employee_id = ?";
	$stmt = $conn->prepare($sql);
	if ($stmt) {
		$stmt->bind_param("i", $id);
		$stmt->execute();
		if ($stmt->affected_rows > 0) {
			echo "\nEmployee with ID $id successfully deleted.\n";
			$stmt->close();
			return true;
		} else {
			echo "\nEmployee with ID $id not found or already deleted.\n";
		}
		$stmt->close();
	} else {
		echo "\nError preparing statement: " . $conn->error . "\n";
	}
	return false;
}
function updateEmployee(?mysqli $conn, Employee $employee): bool
{
    if ($conn === null) {
        return false;
    }

    $employee_id   = $employee->getEmployeeId();
    $first_name    = $employee->getFirstName();
    $last_name     = $employee->getLastName();
    $department    = $employee->getDepartment();
    $experience    = $employee->getExperienceOfEmployee();
    $phone_number  = $employee->getPhoneNumber();
    $email_address = $employee->getEmailAddress();
    $aadhar_number = $employee->getAadharNumber();
    $pan_number    = $employee->getPanNumber();
    $date_of_birth = $employee->getDateOfBirth();
    $nationality   = $employee->getNationality();
    $marital_status = $employee->getMaritalStatus();
    $type_of_employee = $employee->getTypeOfEmployee();

    $salary   = null;
    $benefits = null;
    $hourly   = null;
    $shift    = null;

    if ($employee instanceof FullTimeEmployee) {
        $salary   = $employee->salary;
        $benefits = $employee->benefits;
    } elseif ($employee instanceof PartTimeEmployee) {
        $hourly   = $employee->hourly_rate;
        $shift    = $employee->shift_type;
    }

    $sql = "UPDATE employees SET 
            first_name = ?, 
            last_name = ?, 
            department = ?, 
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
            WHERE employee_id = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed: " . $conn->error . "\n";
        return false;
    }
    $stmt->bind_param(
        "sssisssssssssssss", 
        $first_name, 
        $last_name, 
        $department, 
        $experience, 
        $phone_number, 
        $email_address, 
        $aadhar_number, 
        $pan_number, 
        $date_of_birth, 
        $nationality, 
        $marital_status, 
        $type_of_employee, 
        $salary, 
        $benefits, 
        $hourly, 
        $shift,
        $employee_id
    );
    if ($stmt->execute()) {
        echo "Employee Data Updated to MySQL database successfully.\n";
        $stmt->close();
        return true;
    } else {
        echo "Execution Error: " . $stmt->error . "\n";
        $stmt->close();
        return false;
    }
}


?>