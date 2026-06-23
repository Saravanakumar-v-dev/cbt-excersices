<?php
require_once 'Employee.php';
class EmployeeManager
{
	private string $file_path = "Employee.json";
	private $employees = [];
	private const ALPHA_PATTERN  = "/^[a-zA-Z\s'-]+$/";
	private const PAN_PATTERN    = "/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/";
	private const DOB_PATTERN    = "/^(0[1-9]|[12][0-9]|3[01])-(0[1-9]|1[0-2])-[0-9]{4}$/";
	private const PHONE_PATTERN  = "/^\d{10}$/";
	private const AADHAR_PATTERN = "/^\d{12}$/";
	private const MAX_ATTEMPTS   = 3;

	/**
	 * Constructor - initializes the file path and loads existing employees from the JSON file
	 */
	public function __construct()
	{
		$this->loadExistingEmployees();
	}

	/**
	 * Loads existing employee data from the JSON file and converts each record into an Employee object.
	 * Employees are stored in an ID-keyed associative array for O(1) lookup.
	 * @return void
	 */
	private function loadExistingEmployees(): void
	{
		$this->employees = [];
		if (file_exists($this->file_path) && filesize($this->file_path) > 0) {
			$content    = file_get_contents($this->file_path);
			$data_array = json_decode($content, true) ?? [];
			foreach ($data_array as $data) {
				$emp = Employee::fromArray($data);
				$this->employees[$emp->getEmployeeId()] = $emp;
			}
		}
	}

	/**
	 * Starts the application flow and displays the system title
	 * @return void
	 */
	public function run(): void
	{
		echo " \n\n Employee Management System\n";
		$this->service();
	}

	/**
	 * Displays the menu and handles user input in a loop until the user chooses to exit
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
			if ($manage_choice === null) {
				echo "\nExiting Employee Management System. Thank You!\n";
				$running = false;
			} elseif ($manage_choice == 1) {
				$this->createEmployee();
			} elseif ($manage_choice == 2) {
				$this->viewEmployees();
			} elseif ($manage_choice == 3) {
				$this->updateEmployee();
			} elseif ($manage_choice == 4) {
				$this->deleteEmployee();
			} elseif ($manage_choice == 5) {
				echo "\nExiting Employee Management System. Thank You!\n";
				$running = false;
			}
		}
	}

	/**
	 * Asks for a valid menu choice within a given range and returns null if maximum attempts exceeded
	 * @param int $_min
	 * @param int $_max
	 * @param int $_max_attempts
	 * @return int|null
	 */
	private function getValidChoice(int $_min, int $_max, int $_max_attempts = self::MAX_ATTEMPTS): int|null
	{
		$attempts = 0;
		while ($attempts < $_max_attempts) {
			$choice = trim(readline("Enter the Choice From Above: "));
			if (is_numeric($choice) && (int) $choice >= $_min && (int) $choice <= $_max) {
				return (int) $choice;
			}
			$attempts++;
			$remaining = $_max_attempts - $attempts;
			if ($remaining > 0) {
				echo "Invalid choice. Attempts left: $remaining\n";
			}
		}
		echo "\nExceeded maximum attempt limit ($attempts/$_max_attempts). Exiting...\n";
		return null;
	}

	/**
	 * Unified required-input helper. Reads from stdin, validates with $_validator, and retries up to $_max_attempts.
	 * Returns the trimmed input string on success, or null after exhausting all attempts.
	 * @param string   $_prompt       Prompt shown to the user
	 * @param callable $_validator    fn(string $input): bool — returns true if the input is valid
	 * @param string   $_error        Error message shown on invalid input
	 * @param int      $_max_attempts Maximum allowed attempts
	 * @return string|null
	 */
	private function askForInput(string $_prompt, callable $_validator, string $_error, int $_max_attempts = self::MAX_ATTEMPTS): string|null
	{
		$attempts = 0;
		while ($attempts < $_max_attempts) {
			$input = trim(readline($_prompt));
			if ($input !== '' && $_validator($input)) {
				return $input;
			}
			$attempts++;
			$remaining = $_max_attempts - $attempts;
			if ($remaining > 0) {
				echo $_error . " Attempts left: $remaining\n";
			}
		}
		echo "\nExceeded maximum attempt limit ($attempts/$_max_attempts). Operation aborted.\n";
		return null;
	}

	/**
	 * Unified optional-input helper for update flows. Pressing Enter keeps the current value.
	 * Returns the new value on success, $_current if skipped, or false after exhausting all attempts.
	 * @param string     $_label        Field label shown alongside the current value
	 * @param string|int $_current      Current value (shown in brackets)
	 * @param callable   $_validator    fn(string $input): bool — returns true if the input is valid
	 * @param string     $_error        Error message shown on invalid input
	 * @param int        $_max_attempts Maximum allowed attempts
	 * @return string|int|false
	 */
	private function askForOptionalInput(string $_label, string|int $_current, callable $_validator, string $_error, int $_max_attempts = self::MAX_ATTEMPTS): string|int|false
	{
		$attempts = 0;
		while ($attempts < $_max_attempts) {
			$input = trim(readline("$_label [$_current]: "));
			if ($input === '') {
				return $_current;
			}
			if ($_validator($input)) {
				return $input;
			}
			$attempts++;
			$remaining = $_max_attempts - $attempts;
			if ($remaining > 0) {
				echo $_error . " Attempts left: $remaining\n";
			}
		}
		echo "\nExceeded maximum attempt limit ($attempts/$_max_attempts). Update aborted.\n";
		return false;
	}

	/**
	 * Returns true if an employee with the given ID already exists in the ID-keyed array
	 * @param int $_id
	 * @return bool
	 */
	private function isIdDuplicate(int $_id): bool
	{
		return isset($this->employees[$_id]);
	}
	/**
	 * Returns true if an email address already exists, optionally excluding one ID
	 * @param string   $_email
	 * @param int|null $_exclude_id  Employee ID to skip during the check (used during updates)
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

	/**
	 * Saves all Employee objects to the JSON file.
	 * Uses array_values() to re-index the ID-keyed array so the JSON is saved as an array, not an object.
	 * @return void
	 */
	private function saveToJson(): void
	{
		$json_string = json_encode(array_values($this->employees), JSON_PRETTY_PRINT);
		file_put_contents($this->file_path, $json_string);
	}

	/**
	 * Displays a single employee's details in a formatted way using getter methods
	 * @param Employee $_emp
	 * @return void
	 */
	private function displayEmployee(Employee $_emp): void
	{
		echo "\n\n";
		echo "Employee ID : " . $_emp->getEmployeeId() . "\n";
		echo "First Name : " . $_emp->getFirstName() . "\n";
		echo "Last Name : " . $_emp->getLastName() . "\n";
		echo "Department : " . $_emp->getDepartment() . "\n";
		echo "Experience : " . $_emp->getExperienceOfEmployee() . " years\n";
		echo "Phone Number : " . $_emp->getPhoneNumber() . "\n";
		echo "Email Address : " . $_emp->getEmailAddress() . "\n";
		echo "Aadhar Number : " . $_emp->getAadharNumber() . "\n";
		echo "PAN Number : " . $_emp->getPanNumber() . "\n";
		echo "Date of Birth : " . $_emp->getDateOfBirth() . "\n";
		echo "Nationality : " . $_emp->getNationality() . "\n";
		echo "Marital Status : " . $_emp->getMaritalStatus() . "\n";
		echo "Type of Employee : " . $_emp->getTypeOfEmployee() . "\n";
		echo "\n";
	}
	/**
	 * Creates a new employee after validating all fields with retry limits.
	 * Validates ID uniqueness, name uniqueness, and all field formats.
	 * Adds the employee to the ID-keyed in-memory array and saves to JSON.
	 * @return void
	 */
	private function createEmployee(): void
	{
		echo "\n--- Create Employee ---\n";
		$id          = null;
		$id_attempts = 0;
		$id_validator = fn($v) => is_numeric($v) && (int) $v >= 1 && (int) $v <= 9999999;

		while ($id_attempts < self::MAX_ATTEMPTS) {
			$id_input = $this->askForInput("Enter Employee ID: ", $id_validator, "Please enter a number between 1 and 9999999.");
			if ($id_input === null) return;

			$id = (int) $id_input;
			if ($this->isIdDuplicate($id)) {
				$id_attempts++;
				$remaining = self::MAX_ATTEMPTS - $id_attempts;
				if ($remaining > 0) {
					echo "Employee ID already exists. Please enter a different ID. Attempts left: $remaining\n";
				} else {
					echo "\nExceeded maximum attempt limit. Returning to main menu.\n";
					return;
				}
			} else {
				break;
			}
		}

		$first_name = $this->askForInput("Enter First Name: ", fn($v) => preg_match(self::ALPHA_PATTERN, $v), "Invalid. Enter the First Name in Alphabets.");
		if ($first_name === null) return;

		$last_name = $this->askForInput("Enter Last Name: ", fn($v) => preg_match(self::ALPHA_PATTERN, $v), "Invalid. Please Enter a Valid Last Name.");
		if ($last_name === null) return;

		$department_name = $this->askForInput("Enter Department: ", fn($v) => preg_match(self::ALPHA_PATTERN, $v), "Enter a Valid Department.");
		if ($department_name === null) return;

		$exp_input = $this->askForInput("Enter Experience (in years): ", fn($v) => is_numeric($v) && (int) $v >= 0 && (int) $v <= 99, "Invalid input! Please enter a number between 0 and 99.");
		if ($exp_input === null) return;
		$experience = (int) $exp_input;

		$phone_number = $this->askForInput("Enter Phone Number: ", fn($v) => preg_match(self::PHONE_PATTERN, $v), "Please Enter a Valid 10-Digit Phone Number.");
		if ($phone_number === null) return;

		$email_address = $this->askForInput("Enter Email Address: ", fn($v) => (bool) filter_var($v, FILTER_VALIDATE_EMAIL), "Enter a Valid Email Address.");
		if ($email_address === null) return;

		if ($this->isEmailDuplicate($email_address)) {
			echo "\nEmail address already exists. Please enter another email.\n";
			return;
		}

		$aadhar_number = $this->askForInput("Enter Aadhar Number: ", fn($v) => preg_match(self::AADHAR_PATTERN, $v), "Enter a Valid 12-Digit Aadhar Number.");
		if ($aadhar_number === null) return;

		$pan_input = $this->askForInput("Enter PAN Number: ", fn($v) => preg_match(self::PAN_PATTERN, $v), "Enter a Valid PAN Number (e.g. ABCDE1234F).");
		if ($pan_input === null) return;
		$pan_number = strtoupper($pan_input);

		$date_of_birth = $this->askForInput("Enter Date of Birth (DD-MM-YYYY): ", fn($v) => preg_match(self::DOB_PATTERN, $v), "Enter a Valid Date Of Birth.");
		if ($date_of_birth === null) return;

		$nationality = $this->askForInput("Enter Nationality: ", fn($v) => preg_match(self::ALPHA_PATTERN, $v), "Enter a Valid Nationality.");
		if ($nationality === null) return;

		$marital_status = $this->askForInput("Enter Marital Status: ", fn($v) => preg_match(self::ALPHA_PATTERN, $v), "Enter a Valid Marital Status.");
		if ($marital_status === null) return;

		$type_of_employee = $this->askForInput("Enter Type of Employee: ", fn($v) => preg_match(self::ALPHA_PATTERN, $v), "Enter a Valid Type of Employee.");
		if ($type_of_employee === null) return;

		$new_employee = new Employee(
			$id,
			$first_name,
			$last_name,
			$department_name,
			$experience,
			$phone_number,
			$email_address,
			$aadhar_number,
			$pan_number,
			$date_of_birth,
			$nationality,
			$marital_status,
			$type_of_employee
		);

		$this->employees[$id] = $new_employee;
		$this->saveToJson();
		echo "\nEmployee created successfully!\n";
	}
	/**
	 * Displays the view submenu to view all employees or search by ID
	 * @return void
	 */
	private function viewEmployees(): void
	{
		echo "\n--- View Employees ---\n";
		if (empty($this->employees)) {
			echo "\nNo employees found.\n";
			return;
		}
		echo "\n1. View All Employees\n";
		echo "2. Search Employee by ID\n";

		$choice = $this->getValidChoice(1, 2);
		if ($choice === null)
			return;

		if ($choice == 1) {
			$this->viewAllEmployees();
		} elseif ($choice == 2) {
			$this->searchEmployeeById();
		}
	}
	/**
	 * Displays all employees from the in-memory array with total count
	 * @return void
	 */
	private function viewAllEmployees(): void
	{
		echo "\n--- All Employees ---\n";
		echo "Total Employees: " . count($this->employees) . "\n";

		foreach ($this->employees as $employee) {
			$this->displayEmployee($employee);
		}
	}
	/**
	 * Searches for a specific employee by ID and displays their details.
	 * Uses direct O(1) array access on the ID-keyed employees array.
	 * @return void
	 */
	private function searchEmployeeById(): void
	{
		$search_input = $this->askForInput(
			"\nEnter Employee ID to Search: ",
			fn($v) => is_numeric($v) && (int) $v >= 1 && (int) $v <= 9999999,
			"Please enter a valid numeric ID between 1 and 9999999."
		);
		if ($search_input === null) return;
		$search_id = (int) $search_input;
		if (!isset($this->employees[$search_id])) {
			echo "\nEmployee with ID $search_id not found.\n";
			return;
		}
		echo "\nEmployee Details Found:\n";
		$this->displayEmployee($this->employees[$search_id]);
	}
	/**
	 * Returns the field definitions array used for update validation.
	 * Each entry defines the label, getter, setter, a validator callable, and an error message.
	 * An optional 'transform' key applies a string function (e.g. strtoupper) to the accepted value.
	 * An optional 'cast' key casts the accepted string value to a target type (e.g. 'int').
	 * @return array
	 */
	private function getUpdateFieldDefinitions(): array
	{
		return [
			[
				'label'     => 'First Name',
				'getter'    => 'getFirstName',
				'setter'    => 'setFirstName',
				'validator' => fn($v) => preg_match(self::ALPHA_PATTERN, $v),
				'error'     => 'Invalid. Enter the First Name in Alphabets.',
			],
			[
				'label'     => 'Last Name',
				'getter'    => 'getLastName',
				'setter'    => 'setLastName',
				'validator' => fn($v) => preg_match(self::ALPHA_PATTERN, $v),
				'error'     => 'Invalid. Please Enter a Valid Last Name.',
			],
			[
				'label'     => 'Department',
				'getter'    => 'getDepartment',
				'setter'    => 'setDepartment',
				'validator' => fn($v) => preg_match(self::ALPHA_PATTERN, $v),
				'error'     => 'Enter a Valid Department.',
			],
			[
				'label'     => 'Experience (years)',
				'getter'    => 'getExperienceOfEmployee',
				'setter'    => 'setExperienceOfEmployee',
				'validator' => fn($v) => is_numeric($v) && (int) $v >= 0 && (int) $v <= 99,
				'error'     => 'Invalid input! Enter a number between 0 and 99.',
				'cast'      => 'int',
			],
			[
				'label'     => 'Phone Number',
				'getter'    => 'getPhoneNumber',
				'setter'    => 'setPhoneNumber',
				'validator' => fn($v) => preg_match(self::PHONE_PATTERN, $v),
				'error'     => 'Please Enter a Valid 10-Digit Phone Number.',
			],
			[
				'label'     => 'Email Address',
				'getter'    => 'getEmailAddress',
				'setter'    => 'setEmailAddress',
				'validator' => fn($v) => (bool) filter_var($v, FILTER_VALIDATE_EMAIL),
				'error'     => 'Enter a Valid Email Address.',
			],
			[
				'label'     => 'Aadhar Number',
				'getter'    => 'getAadharNumber',
				'setter'    => 'setAadharNumber',
				'validator' => fn($v) => preg_match(self::AADHAR_PATTERN, $v),
				'error'     => 'Enter a Valid 12-Digit Aadhar Number.',
			],
			[
				'label'     => 'PAN Number',
				'getter'    => 'getPanNumber',
				'setter'    => 'setPanNumber',
				'validator' => fn($v) => preg_match(self::PAN_PATTERN, $v),
				'error'     => 'Enter a Valid PAN Number (e.g. ABCDE1234F).',
				'transform' => 'strtoupper',
			],
			[
				'label'     => 'Date of Birth (DD-MM-YYYY)',
				'getter'    => 'getDateOfBirth',
				'setter'    => 'setDateOfBirth',
				'validator' => fn($v) => preg_match(self::DOB_PATTERN, $v),
				'error'     => 'Enter a Valid Date Of Birth.',
			],
			[
				'label'     => 'Nationality',
				'getter'    => 'getNationality',
				'setter'    => 'setNationality',
				'validator' => fn($v) => preg_match(self::ALPHA_PATTERN, $v),
				'error'     => 'Enter a Valid Nationality.',
			],
			[
				'label'     => 'Marital Status',
				'getter'    => 'getMaritalStatus',
				'setter'    => 'setMaritalStatus',
				'validator' => fn($v) => preg_match(self::ALPHA_PATTERN, $v),
				'error'     => 'Enter a Valid Marital Status.',
			],
			[
				'label'     => 'Type of Employee',
				'getter'    => 'getTypeOfEmployee',
				'setter'    => 'setTypeOfEmployee',
				'validator' => fn($v) => preg_match(self::ALPHA_PATTERN, $v),
				'error'     => 'Enter a Valid Type of Employee.',
			],
		];
	}
	/**
	 * Updates an existing employee's details using a data-driven field definitions loop.
	 * Collects all field values via askForOptionalInput(), validates name and email uniqueness,
	 * then applies changes through setters. Press Enter to keep the current value for any field.
	 * @return void
	 */
	private function updateEmployee(): void
	{
		echo "\n--- Update Employee ---\n";
		if (empty($this->employees)) {
			echo "\nNo employees found.\n";
			return;
		}

		$id_input = $this->askForInput(
			"Enter Employee ID to update: ",
			fn($v) => is_numeric($v) && (int) $v >= 1 && (int) $v <= 9999999,
			"Please enter a valid numeric ID."
		);
		if ($id_input === null) return;
		$target_id = (int) $id_input;
		if (!isset($this->employees[$target_id])) {
			echo "\nEmployee not found.\n";
			return;
		}
		$employee = $this->employees[$target_id];
		echo "\nEnter new values (press Enter to keep current value):\n";

		$field_definitions = $this->getUpdateFieldDefinitions();
		$updated_values    = [];

		foreach ($field_definitions as $field_def) {
			$current_value = $employee->{$field_def['getter']}();
			$result = $this->askForOptionalInput($field_def['label'], $current_value, $field_def['validator'], $field_def['error']);
			if ($result === false) return;
			if ($result !== $current_value && isset($field_def['transform'])) {
				$result = call_user_func($field_def['transform'], $result);
			}
			$updated_values[$field_def['setter']] = $result;
		}
		if ($updated_values['setEmailAddress'] !== $employee->getEmailAddress()) {
			if ($this->isEmailDuplicate($updated_values['setEmailAddress'], $target_id)) {
				echo "\nEmail address already exists. Update cancelled.\n";
				return;
			}
		}
		foreach ($updated_values as $setter => $value) {
			$employee->{$setter}($value);
		}
		$this->employees[$target_id] = $employee;
		$this->saveToJson();

		echo "\nEmployee ID $target_id has been updated successfully!\n";
		echo "\nUpdated Details:";
		$this->displayEmployee($employee);
	}

	/**
	 * Deletes an employee by ID after showing their details and asking for confirmation.
	 * Removes the entry from the ID-keyed array via unset() and saves the updated data to JSON.
	 * @return void
	 */
	private function deleteEmployee(): void
	{
		echo "\n--- Delete Employee ---\n";
		if (empty($this->employees)) {
			echo "\nNo employees found.\n";
			return;
		}
		$id_input = $this->askForInput(
			"Enter Employee ID to delete: ",
			fn($v) => is_numeric($v) && (int) $v >= 1 && (int) $v <= 9999999,
			"Please enter a valid numeric ID."
		);
		if ($id_input === null) return;
		$target_id = (int) $id_input;
		if (!isset($this->employees[$target_id])) {
			echo "\nEmployee not found.\n";
			return;
		}
		$employee = $this->employees[$target_id];
		echo "\nEmployee details to be deleted:";
		$this->displayEmployee($employee);
		$confirm = strtolower(trim(readline("Are you sure you want to delete this employee? (yes/no): ")));
		if ($confirm !== 'yes' && $confirm !== 'y') {
			echo "\nDeletion cancelled.\n";
			return;
		}
		$deleted_name = $employee->getFirstName() . " " . $employee->getLastName();
		unset($this->employees[$target_id]);
		$this->saveToJson();
		echo "\nEmployee '$deleted_name' (ID: $target_id) deleted successfully!\n";
	}
}
?>