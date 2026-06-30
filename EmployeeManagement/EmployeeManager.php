<?php
require_once 'Employee.php';
require_once 'FullTimeEmployee.php';
require_once 'PartTimeEmployee.php';
require_once 'EmployeeRepository.php';


class EmployeeManager
{
	private string $file_path = "Employee.json";
	private $employees = [];
	private const SALARY_PATTERN = '/^\d+(\s*-\s*\d+)?$/';
	private const HOUR_RATE = '/\b\$?£?\d+(?:\.\d{2})?\s*(?:\/|per\s+)?(?:hr|hour)\b/i';
	private const ALPHA_PATTERN = "/^[a-zA-Z\s'-]+$/";
	private const PAN_PATTERN = "/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/";
	private const DOB_PATTERN = "/^(0[1-9]|[12][0-9]|3[01])-(0[1-9]|1[0-2])-[0-9]{4}$/";
	private const PHONE_PATTERN = "/^\d{10}$/";
	private const AADHAR_PATTERN = "/^\d{12}$/";
	private const EMAIL_PATTERN = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
	private const EXPERIENCE_PATTERN = "/^(?:[1-9]?[0-9])$/";
	private const EMPLOYEE_ID_PATTERN = "/^(?:0|[1-9]\d{0,5})$/";
	private const MAX_ATTEMPTS = 3;

	/**
	 * Constructor - loads existing employees from database
	 */
	public function __construct()
	{
		$this->loadEmployeesFromDatabase();
	}

	// /**
	//  * Loads employee data from JSON, creating the correct subclass based on type
	//  * @return void
	//  */
	// private function loadExistingEmployees(): void
	// {
	// 	$this->employees = [];
	// 	if (file_exists($this->file_path) && filesize($this->file_path) > 0) {
	// 		$content = file_get_contents($this->file_path);
	// 		$data_array = json_decode($content, true) ?? [];
	// 		foreach ($data_array as $data) {
	// 			$type = $data['type_of_employee'] ?? '';
	// 			if ($type === 'Full Time') {
	// 				$employee = FullTimeEmployee::fromArray($data);
	// 			} elseif ($type === 'Part Time') {
	// 				$employee = PartTimeEmployee::fromArray($data);
	// 			} else {
	// 				$employee = Employee::fromArray($data);
	// 			}
	// 			$this->employees[$employee->getEmployeeId()] = $employee;
	// 		}
	// 	}
	// }

	/**
	 * Loads employee data from the MySQL database
	 * @return void
	 */
	private function loadEmployeesFromDatabase(): void
	{
		global $conn;
		$this->employees = [];
		if ($conn === null) {
			echo "\nDatabase connection not available. No employees loaded.\n";
			return;
		}
		$sql = "SELECT * FROM employees";
		$result = $conn->query($sql);
		if ($result && $result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$type = $row['type_of_employee'] ?? '';
				if ($type === 'Full Time') {
					$employee = FullTimeEmployee::fromArray($row);
				} elseif ($type === 'Part Time') {
					$employee = PartTimeEmployee::fromArray($row);
				} else {
					$employee = Employee::fromArray($row);
				}
				$this->employees[$employee->getEmployeeId()] = $employee;
			}
		}
	}

	/**
	 * Starts the application
	 * @return void
	 */
	public function run(): void
	{
		echo " \n\n Employee Management System\n";
		$this->service();
	}
	/**
	 * Main menu loop
	 * @return void
	 */
	public function service(): void
	{
		$running = true;

		while ($running) {
			echo "\n--- Main Menu ---\n";
			echo "1. Create Employee\n";
			echo "2. View Employees\n";
			echo "3. Update Employee\n";
			echo "4. Delete Employee\n";
			echo "5. Exit\n";

			$manage_choice = $this->getValidChoice(1, 5);
			if ($manage_choice == 1) {
				$this->createEmployee();
			} elseif ($manage_choice == 2) {
				$this->viewEmployees();
			} elseif ($manage_choice == 3) {
				$this->updateEmployee();
			} elseif ($manage_choice == 4) {
				$this->deleteEmployee();
			} elseif ($manage_choice == 5 || $manage_choice === null) {
				echo "\nExiting Employee Management System. Thank You!\n";
				$running = false;
			}
		}
	}

	/**
	 * Asks for a valid menu choice within a given range
	 * @param int $_min
	 * @param int $_max
	 * @return int|null
	 */
	private function getValidChoice(int $_min, int $_max): int|null
	{
		$attempts = 0;
		while ($attempts < self::MAX_ATTEMPTS) {
			$choice = trim(readline("Enter the Choice From Above: "));
			if (is_numeric($choice) && (int) $choice >= $_min && (int) $choice <= $_max) {
				return (int) $choice;
			}
			$attempts++;
			$remaining = self::MAX_ATTEMPTS - $attempts;
			if ($remaining > 0) {
				echo "Invalid choice. Attempts left: $remaining\n";
			}
		}
		echo "\nExceeded maximum attempt limit ($attempts/" . self::MAX_ATTEMPTS . "). Exiting...\n";
		return null;
	}

	/**
	 * Reads input with validation and retry
	 * @param string $_prompt
	 * @param string $_preg_match_pattern
	 * @param string $_error
	 * @return string|null
	 */
	private function askForInput(string $_prompt, string $_preg_match_pattern, string $_error, ): string|null
	{
		$attempts = 0;
		while ($attempts < self::MAX_ATTEMPTS) {
			$input = trim(readline($_prompt));
			if ($input !== '' && preg_match($_preg_match_pattern, $input)) {
				return $input;
			}
			$attempts++;
			$remaining = self::MAX_ATTEMPTS - $attempts;
			if ($remaining > 0) {
				echo $_error . " Attempts left: $remaining\n";
			}
		}
		echo "\nExceeded maximum attempt limit ($attempts/" . self::MAX_ATTEMPTS . " ). Operation aborted.\n";
		return null;
	}

	/**
	 * Optional input for updates — press Enter to keep current value
	 * @param string $_label
	 * @param string|int $_current
	 * @param string $_preg_match_pattern
	 * @param string $_error
	 * @return string|int|false
	 */
	private function askForOptionalInput(string $_label, string|int $_current, string $_preg_match_pattern, string $_error): string|int|false
	{
		$attempts = 0;
		while ($attempts < self::MAX_ATTEMPTS) {
			$input = trim(readline("$_label [$_current]: "));
			if ($input === '') {
				return $_current;
			}
			if ($input !== '' && preg_match($_preg_match_pattern, $input)) {
				return $input;
			}
			$attempts++;
			$remaining = self::MAX_ATTEMPTS - $attempts;
			if ($remaining > 0) {
				echo $_error . " Attempts left: $remaining\n";
			}
		}
		echo "\nExceeded maximum attempt limit ($attempts/" . self::MAX_ATTEMPTS . "). Update aborted.\n";
		return false;
	}

	/**
	 * Checks if email already exists
	 * @param string $_email
	 * @param int|null $_exclude_id
	 * @return bool
	 */
	private function isEmailDuplicate(string $_email, int|null $_exclude_id = null): bool
	{
		$matches = array_filter(
			$this->employees,
			fn($emp) =>
			($_exclude_id === null || $emp->getEmployeeId() !== $_exclude_id) &&
			$emp->getEmailAddress() === $_email
		);
		return !empty($matches);
	}

	// /**
	//  * Saves all employees to JSON file
	//  * @return void
	//  */
	// private function saveToJson(): void
	// {
	// 	$json_string = json_encode(array_values($this->employees), JSON_PRETTY_PRINT);
	// 	file_put_contents($this->file_path, $json_string);
	// }
	/**
	 * Displays a single employee — calls displayProfile() for subclasses
	 * @param Employee $_employee
	 * @return void
	 */
	private function displayEmployee(Employee $_employee): void
	{
		if ($_employee instanceof FullTimeEmployee || $_employee instanceof PartTimeEmployee) {
			$_employee->displayProfile();
		} else {
			echo "\n\n";
			echo "Employee ID      : " . $_employee->getEmployeeId() . "\n";
			echo "First Name       : " . $_employee->getFirstName() . "\n";
			echo "Last Name        : " . $_employee->getLastName() . "\n";
			echo "Department       : " . $_employee->getDepartment() . "\n";
			echo "Experience       : " . $_employee->getExperienceOfEmployee() . " years\n";
			echo "Phone Number     : " . $_employee->getPhoneNumber() . "\n";
			echo "Email Address    : " . $_employee->getEmailAddress() . "\n";
			echo "Aadhar Number    : " . $_employee->getAadharNumber() . "\n";
			echo "PAN Number       : " . $_employee->getPanNumber() . "\n";
			echo "Date of Birth    : " . $_employee->getDateOfBirth() . "\n";
			echo "Nationality      : " . $_employee->getNationality() . "\n";
			echo "Marital Status   : " . $_employee->getMaritalStatus() . "\n";
			echo "Type of Employee : " . $_employee->getTypeOfEmployee() . "\n\n";
		}
	}

	/**
	 * Creates a new employee — collects base fields, asks type, collects role fields,
	 * saves to both JSON and MySQL
	 * @return void
	 */
	private function createEmployee(): void
	{
		global $conn;

		echo "\n--- Create Employee ---\n";
		$employee_id = (int) $this->askForInput("Enter the Employee ID: ", self::EMPLOYEE_ID_PATTERN, "Please enter a valid numeric ID between 0 and 999999.");
		if ($employee_id === 0 || isset($this->employees[$employee_id])) {
			if (isset($this->employees[$employee_id])) {
				echo "\nEmployee ID $employee_id already exists. Creation cancelled.\n";
			}
			return;
		}
		echo "\n--- Select Employee Type ---\n";
		echo "1. Full Time Employee\n";
		echo "2. Part Time Employee\n";
		$type_choice = $this->getValidChoice(1, 2);
		if ($type_choice === null) {
			return;
		}
		$is_full_time = ($type_choice === 1);
		$field_definitions = $this->getFieldDefinitions($is_full_time);
		$values = [];
		foreach ($field_definitions as $field_definition) {
			$result = $this->askForInput("Enter Your " . $field_definition['label'] . ": ", $field_definition['validator'], $field_definition['error']);
			if ($result === null) {
				return;
			}
			if (isset($field_definition['transform'])) {
				$result = call_user_func($field_definition['transform'], $result);
			}
			if (isset($field_definition['cast']) && $field_definition['cast'] === 'int') {
				$result = (int) $result;
			}
			$values[$field_definition['setter']] = $result;
		}

		if ($this->isEmailDuplicate($values['setEmailAddress'])) {
			echo "\nEmail address already exists. Creation cancelled.\n";
			return;
		}
		if ($is_full_time) {
			$employee = new FullTimeEmployee(
				$employee_id,
				$values['setFirstName'],
				$values['setLastName'],
				$values['setDepartment'],
				(int) $values['setExperienceOfEmployee'],
				$values['setPhoneNumber'],
				$values['setEmailAddress'],
				$values['setAadharNumber'],
				$values['setPanNumber'],
				$values['setDateOfBirth'],
				$values['setNationality'],
				$values['setMaritalStatus'],
				'Full Time',
				(float) $values['setMonthlySalary'],
				$values['setBenefits']
			);
		} else {
			$employee = new PartTimeEmployee(
				$employee_id,
				$values['setFirstName'],
				$values['setLastName'],
				$values['setDepartment'],
				(int) $values['setExperienceOfEmployee'],
				$values['setPhoneNumber'],
				$values['setEmailAddress'],
				$values['setAadharNumber'],
				$values['setPanNumber'],
				$values['setDateOfBirth'],
				$values['setNationality'],
				$values['setMaritalStatus'],
				'Part Time',
				(float) $values['setHourlyRate'],
				$values['setShiftType']
			);
		}

		$this->employees[$employee_id] = $employee;
		insertEmployee($conn, $employee);
		echo "\nEmployee ID $employee_id has been created successfully!\n";
	}

	/**
	 * View employees submenu
	 * @return void
	 */
	private function viewEmployees(): void
	{
		echo "\n--- View Employees ---\n";
		echo "\n1. View All Employees\n";
		echo "2. Search Employee by ID\n";
		$choice = $this->getValidChoice(1, 2);
		if ($choice === null)
			return;
		if ($choice == 1) {
			if (empty($this->employees)) {
				echo "\nNo employees found.\n";
				return;
			}
			$this->viewAllEmployees();
		} elseif ($choice == 2) {
			$this->searchEmployeeById();
		}
	}

	/**
	 * Displays all employees
	 * @return void
	 */
	private function viewAllEmployees(): void
	{
		global $conn;
		echo "\n--- All Employees ---\n";
		echo "Total Employees: " . count($this->employees) . "\n";
		foreach ($this->employees as $employee) {
			$this->displayEmployee($employee);
		}
		viewAllEmployee($conn, $employee);
	}
	/**
	 * Search employee by ID
	 * @return void
	 */
	private function searchEmployeeById(): void
	{
		global $conn;
		$search_input = $this->askForInput(
			"\nEnter the ID to Search: ",
			self::EMPLOYEE_ID_PATTERN,
			"Please enter a valid numeric ID"
		);
		// if ($search_input === null) return;
		// 	$search_id = (int) $search_input;
		// 	if (!isset($this->employees[$search_id])) {
		// 		echo "\nEmployee with ID $search_id not found.\n";
		// 		return;
		// 	}
		if ($search_input === null)
			return;
		$search_id = (int) $search_input;
		viewEmployeeById($conn, $search_id);
	}
	/**
	 * Base field definitions for create/update (type is handled separately via menu)
	 * @return array
	 */
	private function getFieldDefinitions($_is_full_time_employee = false)
	{
		$input_fields = [
			['label' => 'First Name', 'getter' => 'getFirstName', 'setter' => 'setFirstName', 'validator' => self::ALPHA_PATTERN, 'error' => 'Invalid. Enter the First Name in Alphabets.'],
			['label' => 'Last Name', 'getter' => 'getLastName', 'setter' => 'setLastName', 'validator' => self::ALPHA_PATTERN, 'error' => 'Invalid. Please Enter a Valid Last Name.'],
			['label' => 'Department', 'getter' => 'getDepartment', 'setter' => 'setDepartment', 'validator' => self::ALPHA_PATTERN, 'error' => 'Enter a Valid Department.'],
			['label' => 'Experience (years)', 'getter' => 'getExperienceOfEmployee', 'setter' => 'setExperienceOfEmployee', 'validator' => self::EXPERIENCE_PATTERN, 'error' => 'Invalid input! Enter a number between 0 and 99.', 'cast' => 'int'],
			['label' => 'Phone Number', 'getter' => 'getPhoneNumber', 'setter' => 'setPhoneNumber', 'validator' => self::PHONE_PATTERN, 'error' => 'Please Enter a Valid 10-Digit Phone Number.'],
			['label' => 'Email Address', 'getter' => 'getEmailAddress', 'setter' => 'setEmailAddress', 'validator' => self::EMAIL_PATTERN, 'error' => 'Enter a Valid Email Address.'],
			['label' => 'Aadhar Number', 'getter' => 'getAadharNumber', 'setter' => 'setAadharNumber', 'validator' => self::AADHAR_PATTERN, 'error' => 'Enter a Valid 12-Digit Aadhar Number.'],
			['label' => 'PAN Number', 'getter' => 'getPanNumber', 'setter' => 'setPanNumber', 'validator' => self::PAN_PATTERN, 'error' => 'Enter a Valid PAN Number (e.g. ABCDE1234F).', 'transform' => 'strtoupper'],
			['label' => 'Date of Birth (DD-MM-YYYY)', 'getter' => 'getDateOfBirth', 'setter' => 'setDateOfBirth', 'validator' => self::DOB_PATTERN, 'error' => 'Enter a Valid Date Of Birth.'],
			['label' => 'Nationality', 'getter' => 'getNationality', 'setter' => 'setNationality', 'validator' => self::ALPHA_PATTERN, 'error' => 'Enter a Valid Nationality.'],
			['label' => 'Marital Status', 'getter' => 'getMaritalStatus', 'setter' => 'setMaritalStatus', 'validator' => self::ALPHA_PATTERN, 'error' => 'Enter a Valid Marital Status.'],

		];

		if ($_is_full_time_employee) {
			$full_time_employee_input_field = [
				['label' => 'Salary', 'getter' => 'getMonthlySalary', 'setter' => 'setMonthlySalary', 'validator' => self::SALARY_PATTERN, 'error' => 'Enter the Valid Salary'],
				['label' => 'Benefits', 'getter' => 'getBenefits', 'setter' => 'setBenefits', 'validator' => self::ALPHA_PATTERN, 'error' => 'Enter the Correct Benefits'],
			];
		} else {
			$full_time_employee_input_field = [
				['label' => 'Hourly Rate', 'getter' => 'getHourlyRate', 'setter' => 'setHourlyRate', 'validator' => self::HOUR_RATE, 'error' => 'Enter the Correct Hourly Rate (Ex:1000/hr)'],
				['label' => 'Shift Type', 'getter' => 'getShiftType', 'setter' => 'setShiftType', 'validator' => self::ALPHA_PATTERN, 'error' => 'Enter the Correct Shift Type (Ex: Morning/Night)'],
			];
		}

		return array_merge($input_fields, $full_time_employee_input_field);
	}

	/**
	 * Updates an existing employee
	 * @return void
	 */
	private function updateEmployee(): void
	{
		global $conn;
		echo "\n--- Update Employee ---\n";
		if (empty($this->employees)) {
			echo "\nNo employees found.\n";
			return;
		}

		$id_input = $this->askForInput("Enter Employee ID to update: ", self::EMPLOYEE_ID_PATTERN, "Please enter a valid numeric ID.");
		if ($id_input === null)
			return;

		$target_id = (int) $id_input;
		if (!isset($this->employees[$target_id])) {
			echo "\nEmployee not found.\n";
			return;
		}
		$employee = $this->employees[$target_id];
		echo "\nEnter new values (press Enter to keep current value):\n";
		$is_full_time = ($employee instanceof FullTimeEmployee);
		$field_definitions = $this->getFieldDefinitions($is_full_time);

		$updated_values = [];
		foreach ($field_definitions as $field_def) {
			$current_value = $employee->{$field_def['getter']}();
			$result = $this->askForOptionalInput("Enter your " . $field_def['label'], $current_value, $field_def['validator'], $field_def['error']);

			if ($result === false)
				return;
			if ($result !== $current_value && isset($field_def['transform'])) {
				$result = call_user_func($field_def['transform'], $result);
			}
			$updated_values[$field_def['setter']] = $result;
		}
		if ($updated_values['setEmailAddress'] !== $employee->getEmailAddress() && $this->isEmailDuplicate($updated_values['setEmailAddress'], $target_id)) {
			echo "\nEmail address already exists. Update cancelled.\n";
			return;
		}
		foreach ($updated_values as $setter => $value) {
			$employee->{$setter}($value);
		}

		$this->employees[$target_id] = $employee;
		// $this->saveToJson(); 
		updateEmployee($conn, $employee);

		echo "\nEmployee ID $target_id has been updated successfully!\n\nUpdated Details:";
		$this->displayEmployee($employee);
	}


	/**
	 * Deletes an employee by ID
	 * @return void
	 */
	private function deleteEmployee(): void
	{
		global $conn;
		echo "\n--- Delete Employee ---\n";
		if (empty($this->employees)) {
			echo "\nNo employees found.\n";
			return;
		}
		$id_input = $this->askForInput("Enter the ID to delete: ", self::EMPLOYEE_ID_PATTERN, "Please enter a valid numeric ID.");
		if ($id_input === null)
			return;
		$target_id = (int) $id_input;
		// if (!isset($this->employees[$target_id])) {
		// 	echo "\nEmployee not found.\n";
		// 	return;
		// }
		// $employee = $this->employees[$target_id];
		echo "\nEmployee details to be deleted:\n";
		// $this->displayEmployee($employee);
		$confirm = strtolower(trim(readline("Are you sure you want to delete this employee? (yes/no): ")));
		if ($confirm !== 'yes' && $confirm !== 'y') {
			echo "\nDeletion cancelled.\n";
			return;
		}
		// $deleted_name = $employee->getFirstName() . " " . $employee->getLastName();
		deleteEmployeeById($conn, $target_id);
		// unset($this->employees[$target_id]);
		// $this->saveToJson();
		// echo "\nEmployee '$deleted_name' (ID: $target_id) deleted successfully!\n";
	}
}
?>