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
	$employee_id = $conn->real_escape_string($employee->getEmployeeId());
	$first_name = $conn->real_escape_string($employee->getFirstName());
	$last_name = $conn->real_escape_string($employee->getLastName());
	$department = $conn->real_escape_string($employee->getDepartment());
	$experience = $conn->real_escape_string($employee->getExperienceOfEmployee());
	$phone = $conn->real_escape_string($employee->getPhoneNumber());
	$email = $conn->real_escape_string($employee->getEmailAddress());
	$aadhar = $conn->real_escape_string($employee->getAadharNumber());
	$pan = $conn->real_escape_string($employee->getPanNumber());
	$dob = $conn->real_escape_string($employee->getDateOfBirth());
	$nationality = $conn->real_escape_string($employee->getNationality());
	$marital = $conn->real_escape_string($employee->getMaritalStatus());
	$type = $conn->real_escape_string($employee->getTypeOfEmployee());
	$salary = 'NULL';
	$benefits = 'NULL';
	$hourly = 'NULL';
	$shift = 'NULL';

	if ($employee instanceof FullTimeEmployee) {
		$salary = $conn->real_escape_string($employee->salary);
		$benefits = "'" . $conn->real_escape_string($employee->benefits) . "'";
		$salary = "'" . $salary . "'";
	} elseif ($employee instanceof PartTimeEmployee) {
		$hourly = $conn->real_escape_string($employee->hourly_rate);
		$shift = "'" . $conn->real_escape_string($employee->shift_type) . "'";
		$hourly = "'" . $hourly . "'";
	}
	$sql = "INSERT INTO employees 
			(employee_id, first_name, last_name, department, experience_of_employee,
			 phone_number, email_address, aadhar_number, pan_number, date_of_birth,
			 nationality, marital_status, type_of_employee, salary, benefits, hourly_rate, shift_type)
			VALUES 
			('$employee_id', '$first_name', '$last_name', '$department', '$experience',
			 '$phone', '$email', '$aadhar', '$pan', '$dob',
			 '$nationality', '$marital', '$type', $salary, $benefits, $hourly, $shift)";

	if ($conn->query($sql) === true) {
		echo " Employee saved to MySQL database successfully.\n";
		return true;
	} else {
		echo "Error " . $conn->error . "\n";
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
	$sql = "SELECT * FROM employees WHERE id = ?";
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
	$sql = "DELETE FROM employees WHERE id = ?";
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
?>