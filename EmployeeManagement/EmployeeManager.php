<?php
require_once 'Employee.php';
class EmployeeManager
{
	private string $file_path = "Employee.json";
	private $employees = [];
	private const ALPHA_PATTERN = "/^[a-zA-Z\s'-]+$/";
	private const PAN_PATTERN   = "/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/";
	private const DOB_PATTERN   = "/^(0[1-9]|[12][0-9]|3[01])-(0[1-9]|1[0-2])-[0-9]{4}$/";
	private const MAX_ATTEMPTS  = 3;

	/**
	 * Constructor - initializes the file path and loads existing employees from the JSON file
	 */
	public function __construct()
	{
		$this->loadExistingEmployees();
	}
	/**
	 * Loads existing employee data from the JSON file and converts each record into an Employee object
	 * @return void
	 */
	private function loadExistingEmployees():void
	{
		$this->employees = [];
		if (file_exists($this->file_path) && filesize($this->file_path) > 0) {
			$content = file_get_contents($this->file_path);
			$data_array = json_decode($content, true) ?? [];
			// print_r($data_array);die;
			foreach ($data_array as $data) {
				$this->employees[] = Employee::fromArray($data);
			}
		}

		// print_r($this->employees);die;
	}
	/**
	 * Starts the application flow and displays the system title
	 * @return void
	 */
	public function run():void
	{
		echo "\n";
		echo "  Employee Management System\n";
		echo "\n";
		$this->service();
	}

	/**
	 * Displays the menu and handles user input in a loop until the user chooses to exit
	 * @return void
	 */
	public function service():void
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
	private function getValidChoice(int $_min,int $_max,int $_max_attempts = self::MAX_ATTEMPTS):int|null
	{
		$attempts = 0;
		while ($attempts < $_max_attempts) {
			$choice = trim(readline("Enter the Choice From Above: "));
			if (is_numeric($choice) && (int)$choice >= $_min && (int)$choice <= $_max) {
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
	 * Asks for a valid number within a given range and returns null if maximum attempts exceeded
	 * @param string $_prompt
	 * @param int $_min
	 * @param int $_max
	 * @param int $_max_attempts
	 * @return int|null
	 */
	private function askForNumber(string $_prompt,int $_min,int $_max,int $_max_attempts = self::MAX_ATTEMPTS):int|null
	{
		$attempts = 0;
		while ($attempts < $_max_attempts) {
			$input = trim(readline($_prompt));
			if (is_numeric($input) && (int) $input >= $_min && (int) $input <= $_max) {
				return (int) $input;
			}
			$attempts++;
			$remaining = $_max_attempts - $attempts;
			if ($remaining > 0) {
				echo "Invalid input! Please enter a number between $_min and $_max. Attempts left: $remaining\n";
			}
		}
		echo "\nExceeded maximum attempt limit ($attempts/$_max_attempts). Operation aborted.\n";
		return null;
	}

	/**
	 * Asks for input matching a regex pattern and returns null if maximum attempts exceeded
	 * @param string $_prompt
	 * @param string $_pattern
	 * @param string $_error_message
	 * @param int $_max_attempts
	 * @return string|null
	 */
	private function askForPattern(string $_prompt,string $_pattern,string $_error_message,int $_max_attempts = self::MAX_ATTEMPTS):string|null
	{
		$attempts = 0;
		while ($attempts < $_max_attempts) {
			$input = trim(readline($_prompt));
			if ($input != "" && preg_match($_pattern, $input)) {
				return $input;
			}
			$attempts++;
			$remaining = $_max_attempts - $attempts;
			if ($remaining > 0) {
				echo $_error_message . " Attempts left: $remaining\n";
			}
		}
		echo "\nExceeded maximum attempt limit ($attempts/$_max_attempts). Operation aborted.\n";
		return null;
	}

	/**
	 * Asks for input with an exact number of digits and returns null if maximum attempts exceeded
	 * @param string $_prompt
	 * @param int $_length
	 * @param string $_error_message
	 * @param int $_max_attempts
	 * @return string|null
	 */
	private function askForDigits(string $_prompt,int $_length,string $_error_message,int $_max_attempts = self::MAX_ATTEMPTS):string|null
	{
		$attempts = 0;
		while ($attempts < $_max_attempts) {
			$input = trim(readline($_prompt));
			if (is_numeric($input) && strlen($input) == $_length) {
				return $input;
			}
			$attempts++;
			$remaining = $_max_attempts - $attempts;
			if ($remaining > 0) {
				echo $_error_message . " Attempts left: $remaining\n";
			}
		}
		echo "\nExceeded maximum attempt limit ($attempts/$_max_attempts). Operation aborted.\n";
		return null;
	}
	/**
	 * Asks for a valid email address and returns null if maximum attempts exceeded
	 * @param string $_prompt
	 * @param int $_max_attempts
	 * @return string|null
	 */
	private function askForEmail(string $_prompt,int $_max_attempts = self::MAX_ATTEMPTS):string|null
	{
		$attempts = 0;
		while ($attempts < $_max_attempts) {
			$input = trim(readline($_prompt));
			if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
				return $input;
			}
			$attempts++;
			$remaining = $_max_attempts - $attempts;
			if ($remaining > 0) {
				echo "Enter a Valid Email Address. Attempts left: $remaining\n";
			}
		}
		echo "\nExceeded maximum attempt limit ($attempts/$_max_attempts). Operation aborted.\n";
		return null;
	}
	/**
	 * Asks for optional pattern input during update, press Enter to keep current value
	 * Returns the new value, the current value if skipped, or false if attempts exceeded
	 * @param string $_label
	 * @param string $_current_value
	 * @param string $_pattern
	 * @param string $_error_message
	 * @param int $_max_attempts
	 * @return string|false
	 */
	private function askForOptionalPattern(string $_label,string $_current_value,string $_pattern,string $_error_message,int $_max_attempts = self::MAX_ATTEMPTS):string|false
	{
		$attempts = 0;
		while ($attempts < $_max_attempts) {
			$input = trim(readline("$_label [$_current_value]: "));
			if ($input === "") {
				return $_current_value;
			}
			if (preg_match($_pattern, $input)) {
				return $input;
			}
			$attempts++;
			$remaining = $_max_attempts - $attempts;
			if ($remaining > 0) {
				echo $_error_message . " Attempts left: $remaining\n";
			}
		}
		echo "\nExceeded maximum attempt limit ($attempts/$_max_attempts). Update aborted.\n";
		return false;
	}
	/**
	 * Asks for optional number input during update, press Enter to keep current value
	 * Returns the new value, the current value if skipped, or false if attempts exceeded
	 * @param string $_label
	 * @param int $_current_value
	 * @param int $_min
	 * @param int $_max
	 * @param int $_max_attempts
	 * @return int|false
	 */
	private function askForOptionalNumber(string $_label,int $_current_value,int $_min,int $_max,int $_max_attempts = self::MAX_ATTEMPTS):int|false
	{
		$attempts = 0;
		while ($attempts < $_max_attempts) {
			$input = trim(readline("$_label [$_current_value]: "));
			if ($input === "") {
				return $_current_value;
			}
			if (is_numeric($input) && (int) $input >= $_min && (int) $input <= $_max) {
				return (int) $input;
			}
			$attempts++;
			$remaining = $_max_attempts - $attempts;
			if ($remaining > 0) {
				echo "Invalid input! Enter a number between $_min and $_max. Attempts left: $remaining\n";
			}
		}
		echo "\nExceeded maximum attempt limit ($attempts/$_max_attempts). Update aborted.\n";
		return false;
	}
	/**
	 * Asks for optional digit input during update, press Enter to keep current value
	 * Returns the new value, the current value if skipped, or false if attempts exceeded
	 * @param string $_label
	 * @param string $_current_value
	 * @param int $_length
	 * @param string $_error_message
	 * @param int $_max_attempts
	 * @return string|false
	 */
	private function askForOptionalDigits(string $_label,string $_current_value,int $_length,string $_error_message,int $_max_attempts = self::MAX_ATTEMPTS):string|false
	{
		$attempts = 0;
		while ($attempts < $_max_attempts) {
			$input = trim(readline("$_label [$_current_value]: "));
			if ($input === "") {
				return $_current_value;
			}
			if (is_numeric($input) && strlen($input) == $_length) {
				return $input;
			}
			$attempts++;
			$remaining = $_max_attempts - $attempts;
			if ($remaining > 0) {
				echo $_error_message . " Attempts left: $remaining\n";
			}
		}
		echo "\nExceeded maximum attempt limit ($attempts/$_max_attempts). Update aborted.\n";
		return false;
	}
	/**
	 * Asks for optional email input during update, press Enter to keep current value
	 * Returns the new value, the current value if skipped, or false if attempts exceeded
	 * @param string $_label
	 * @param string $_current_value
	 * @param int $_max_attempts
	 * @return string|false
	 */
	private function askForOptionalEmail(string $_label,string $_current_value,int $_max_attempts = self::MAX_ATTEMPTS):string|false
	{
		$attempts = 0;
		while ($attempts < $_max_attempts) {
			$input = trim(readline("$_label [$_current_value]: "));
			if ($input === "") {
				return $_current_value;
			}
			if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
				return $input;
			}
			$attempts++;
			$remaining = $_max_attempts - $attempts;
			if ($remaining > 0) {
				echo "Enter a Valid Email Address. Attempts left: $remaining\n";
			}
		}
		echo "\nExceeded maximum attempt limit ($attempts/$_max_attempts). Update aborted.\n";
		return false;
	}
	/**
	 * Checks if an employee ID already exists in the employees array
	 * @param int $_id
	 * @return bool
	 */
	private function isIdDuplicate(int $_id):bool
	{
		foreach ($this->employees as $emp) {
			if ($emp->getEmployeeId() == $_id) {
				return true;
			}
		}
		return false;
	}
	/**
	 * Checks if an employee name (first + last) already exists in the employees array
	 * @param string $_first_name
	 * @param string $_last_name
	 * @param int|null $_exclude_index
	 * @return bool
	 */
	private function isNameDuplicate(string $_first_name,string $_last_name, $_exclude_index = null):bool
	{
		foreach ($this->employees as $index => $emp) {
			if ($_exclude_index !== null && $index == $_exclude_index) {
				continue;
			}
			if (strtolower($emp->getFirstName()) == strtolower($_first_name) && strtolower($emp->getLastName()) == strtolower($_last_name)) {
				return true;
			}
		}
		return false;
	}
	/**
	 * Checks if an email address already exists in the employees array
	 * @param string $_email
	 * @param int|null $_exclude_index
	 * @return bool
	 */
	private function isEmailDuplicate(string $_email,$_exclude_index = null):bool
	{
		foreach ($this->employees as $index => $emp) {
			if ($_exclude_index !== null && $index == $_exclude_index) {
				continue;
			}
			if ($emp->getEmailAddress() == $_email) {
				return true;
			}
		}
		return false;
	}
	/**
	 * Finds the index of an employee by their ID in the employees array
	 * @param int $_id
	 * @return int|null
	 */
	private function findEmployeeIndexById(int $_id):int|null
	{
		foreach ($this->employees as $index => $emp) {
			if ($emp->getEmployeeId() == $_id) {
				return $index;
			}
		}
		return null;
	}
	/**
	 * Saves all Employee objects to the JSON file by converting each object to an array using toArray() and encoding to JSON
	 * @return void
	 */
	private function saveToJson() :void
	{
		$json_string = json_encode($this->employees, JSON_PRETTY_PRINT);
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
		echo "PAN Number    : " . $_emp->getPanNumber() . "\n";
		echo "Date of Birth : " . $_emp->getDateOfBirth() . "\n";
		echo "Nationality   : " . $_emp->getNationality() . "\n";
		echo "Marital Status : " . $_emp->getMaritalStatus() . "\n";
		echo "Type of Employee : " . $_emp->getTypeOfEmployee() . "\n";
		echo "\n";
	}
	/**
	 * Creates a new employee after validating all fields with retry limits
	 * Validates ID uniqueness, name uniqueness, and all field formats
	 * Adds the employee to the in-memory array and saves to JSON file
	 * @return void
	 */
	private function createEmployee():void
	{
		echo "\n--- Create Employee ---\n";

		$id = null;
		$id_attempts = 0;
		while ($id_attempts < self::MAX_ATTEMPTS) {
			$id = $this->askForNumber("Enter Employee ID: ", 1, 9999999);
			if ($id === null) return;

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

		$first_name = $this->askForPattern("Enter First Name: ", self::ALPHA_PATTERN, "Invalid. Enter the First Name in Alphabets.");
		if ($first_name === null) return;

		$last_name = $this->askForPattern("Enter Last Name: ", self::ALPHA_PATTERN, "Invalid. Please Enter a Valid Last Name.");
		if ($last_name === null) return;

		if ($this->isNameDuplicate($first_name, $last_name)) {
			echo "\nEmployee name already exists. Please enter another name.\n";
			return;
		}

		$department_name = $this->askForPattern("Enter Department: ", self::ALPHA_PATTERN, "Enter a Valid Department.");
		if ($department_name === null) return;

		$experience = $this->askForNumber("Enter Experience (in years): ", 0, 99);
		if ($experience === null) return;

		$phone_number = $this->askForDigits("Enter Phone Number: ", 10, "Please Enter a Valid 10-Digit Phone Number.");
		if ($phone_number === null) return;

		$email_address = $this->askForEmail("Enter Email Address: ");
		if ($email_address === null) return;

		if ($this->isEmailDuplicate($email_address)) {
			echo "\nEmail address already exists. Please enter another email.\n";
			return;
		}

		$aadhar_number = $this->askForDigits("Enter Aadhar Number: ", 12, "Enter a Valid 12-Digit Aadhar Number.");
		if ($aadhar_number === null) return;

		$pan_input = $this->askForPattern("Enter PAN Number: ", self::PAN_PATTERN, "Enter a Valid PAN Number (e.g. ABCDE1234F).");
		if ($pan_input === null) return;
		$pan_number = strtoupper($pan_input);

		$date_of_birth = $this->askForPattern("Enter Date of Birth (DD-MM-YYYY): ", self::DOB_PATTERN, "Enter a Valid Date Of Birth.");
		if ($date_of_birth === null) return;

		$nationality = $this->askForPattern("Enter Nationality: ", self::ALPHA_PATTERN, "Enter a Valid Nationality.");
		if ($nationality === null) return;

		$marital_status = $this->askForPattern("Enter Marital Status: ", self::ALPHA_PATTERN, "Enter a Valid Marital Status.");
		if ($marital_status === null) return;

		$type_of_employee = $this->askForPattern("Enter Type of Employee: ", self::ALPHA_PATTERN, "Enter a Valid Type of Employee.");
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

		$this->employees[] = $new_employee;
		$this->saveToJson();

		echo "\nEmployee created successfully!\n";
	}

	/**
	 * Displays the view submenu to view all employees or search by ID
	 * @return void
	 */
	private function viewEmployees():void
	{
		echo "\n--- View Employees ---\n";

		if (empty($this->employees)) {
			echo "\nNo employees found.\n";
			return;
		}

		echo "\n1. View All Employees\n";
		echo "2. Search Employee by ID\n";

		$choice = $this->getValidChoice(1, 2);
		if ($choice === null) return;

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
	private function viewAllEmployees():void
	{
		echo "\n--- All Employees ---\n";
		echo "Total Employees: " . count($this->employees) . "\n";

		foreach ($this->employees as $emp) {
			$this->displayEmployee($emp);
		}
	}

	/**
	 * Searches for a specific employee by ID and displays their details
	 * @return void
	 */
	private function searchEmployeeById():void
	{
		$search_id = $this->askForNumber("\nEnter Employee ID to Search: ", 1, 9999999);
		if ($search_id === null) return;

		$found_index = $this->findEmployeeIndexById($search_id);
		if ($found_index === null) {
			echo "\nEmployee with ID $search_id not found.\n";
			return;
		}
		echo "\nEmployee Details Found:\n";
		$this->displayEmployee($this->employees[$found_index]);
	}

	/**
	 * Returns the field definitions array used for update validation
	 * Each entry defines the label, getter, setter, validation type, and validation parameters
	 * @return array
	 */
	private function getUpdateFieldDefinitions(): array
{
	return [
		[
			'label'   => 'First Name',
			'getter'  => 'getFirstName',
			'setter'  => 'setFirstName',
			'type'    => 'pattern',
			'pattern' => self::ALPHA_PATTERN,
			'error'   => 'Invalid. Enter the First Name in Alphabets.',
		],
		[
			'label'   => 'Last Name',
			'getter'  => 'getLastName',
			'setter'  => 'setLastName',
			'type'    => 'pattern',
			'pattern' => self::ALPHA_PATTERN,
			'error'   => 'Invalid. Please Enter a Valid Last Name.',
		],
		[
			'label'   => 'Department',
			'getter'  => 'getDepartment',
			'setter'  => 'setDepartment',
			'type'    => 'pattern',
			'pattern' => self::ALPHA_PATTERN,
			'error'   => 'Enter a Valid Department.',
		],
		[
			'label'   => 'Experience (years)',
			'getter'  => 'getExperienceOfEmployee',
			'setter'  => 'setExperienceOfEmployee',
			'type'    => 'number',
			'min'     => 0,
			'max'     => 99,
		],
		[
			'label'   => 'Phone Number',
			'getter'  => 'getPhoneNumber',
			'setter'  => 'setPhoneNumber',
			'type'    => 'digits',
			'length'  => 10,
			'error'   => 'Please Enter a Valid 10-Digit Phone Number.',
		],
		[
			'label'   => 'Email Address',
			'getter'  => 'getEmailAddress',
			'setter'  => 'setEmailAddress',
			'type'    => 'email',
		],
		[
			'label'   => 'Aadhar Number',
			'getter'  => 'getAadharNumber',
			'setter'  => 'setAadharNumber',
			'type'    => 'digits',
			'length'  => 12,
			'error'   => 'Enter a Valid 12-Digit Aadhar Number.',
		],
		[
			'label'     => 'PAN Number',
			'getter'    => 'getPanNumber',
			'setter'    => 'setPanNumber',
			'type'      => 'pattern',
			'pattern'   => self::PAN_PATTERN,
			'error'     => 'Enter a Valid PAN Number (e.g. ABCDE1234F).',
			'transform' => 'strtoupper',
		],
		[
			'label'   => 'Date of Birth (DD-MM-YYYY)',
			'getter'  => 'getDateOfBirth',
			'setter'  => 'setDateOfBirth',
			'type'    => 'pattern',
			'pattern' => self::DOB_PATTERN,
			'error'   => 'Enter a Valid Date Of Birth.',
		],
		[
			'label'   => 'Nationality',
			'getter'  => 'getNationality',
			'setter'  => 'setNationality',
			'type'    => 'pattern',
			'pattern' => self::ALPHA_PATTERN,
			'error'   => 'Enter a Valid Nationality.',
		],
		[
			'label'   => 'Marital Status',
			'getter'  => 'getMaritalStatus',
			'setter'  => 'setMaritalStatus',
			'type'    => 'pattern',
			'pattern' => self::ALPHA_PATTERN,
			'error'   => 'Enter a Valid Marital Status.',
		],
		[
			'label'   => 'Type of Employee',
			'getter'  => 'getTypeOfEmployee',
			'setter'  => 'setTypeOfEmployee',
			'type'    => 'pattern',
			'pattern' => self::ALPHA_PATTERN,
			'error'   => 'Enter a Valid Type of Employee.',
		],
	];
}


	/**
	 * Collects a single field's updated value using the appropriate optional input helper
	 * Returns the new value, the current value if skipped, or false if attempts exceeded
	 * @param array $_field_def
	 * @param Employee $_employee
	 * @return mixed
	 */
	private function collectUpdatedField(array $_field_def, Employee $_employee):mixed
	{
		$current_value = $_employee->{$_field_def['getter']}();

		switch ($_field_def['type']) {
			case 'pattern':
				$result = $this->askForOptionalPattern($_field_def['label'], $current_value, $_field_def['pattern'], $_field_def['error']);
				if ($result !== false && isset($_field_def['transform'])) {
					$result = call_user_func($_field_def['transform'], $result);
				}
				return $result;
			case 'number':
				return $this->askForOptionalNumber($_field_def['label'], $current_value, $_field_def['min'], $_field_def['max']);
			case 'digits':
				return $this->askForOptionalDigits($_field_def['label'], $current_value, $_field_def['length'], $_field_def['error']);
			case 'email':
				return $this->askForOptionalEmail($_field_def['label'], $current_value);
		}
		return false;
	}

	/**
	 * Updates an existing employee's details using a data-driven field definitions loop
	 * Collects all field values, validates name and email uniqueness, then applies changes via setters
	 * Press Enter to keep the current value for any field
	 * @return void
	 */
	private function updateEmployee():void
	{
		echo "\n--- Update Employee ---\n";
		if (empty($this->employees)) {
			echo "\nNo employees found.\n";
			return;
		}
		$target_id = $this->askForNumber("Enter Employee ID to update: ", 1, 9999999);
		if ($target_id === null) return;
		$found_index = $this->findEmployeeIndexById($target_id);
		if ($found_index === null) {
			echo "\nEmployee not found.\n";
			return;
		}
		$employee = $this->employees[$found_index];
		echo "\nCurrent Details:";
		$this->displayEmployee($employee);
		echo "\nEnter new values (press Enter to keep current value):\n";
		$field_definitions = $this->getUpdateFieldDefinitions();
		$updated_values = [];
		foreach ($field_definitions as $field_def) {
			$value = $this->collectUpdatedField($field_def, $employee);
			if ($value === false) return;
			$updated_values[$field_def['setter']] = $value;
		}
		if ($updated_values['setFirstName'] !== $employee->getFirstName() || $updated_values['setLastName'] !== $employee->getLastName()) {
			if ($this->isNameDuplicate($updated_values['setFirstName'], $updated_values['setLastName'], $found_index)) {
				echo "\nEmployee name already exists. Update cancelled.\n";
				return;
			}
		}
		if ($updated_values['setEmailAddress'] !== $employee->getEmailAddress()) {
			if ($this->isEmailDuplicate($updated_values['setEmailAddress'], $found_index)) {
				echo "\nEmail address already exists. Update cancelled.\n";
				return;
			}
		}
		foreach ($updated_values as $setter => $value) {
			$employee->{$setter}($value);
		}
		$this->employees[$found_index] = $employee;
		$this->saveToJson();
		echo "\nEmployee ID $target_id has been updated successfully!\n";
		echo "\nUpdated Details:";
		$this->displayEmployee($employee);
	}

	/**
	 * Deletes an employee by ID after showing their details and asking for confirmation
	 * Removes from the in-memory array and saves updated data to JSON file
	 * @return void
	 */
	private function deleteEmployee():void
	{
		echo "\n--- Delete Employee ---\n";
		if (empty($this->employees)) {
			echo "\nNo employees found.\n";
			return;
		}
		$target_id = $this->askForNumber("Enter Employee ID to delete: ", 1, 9999999);
		if ($target_id === null) return;
		$found_index = $this->findEmployeeIndexById($target_id);
		if ($found_index === null) {
			echo "\nEmployee not found.\n";
			return;
		}
		$employee = $this->employees[$found_index];
		echo "\nEmployee details to be deleted:";
		$this->displayEmployee($employee);
		$confirm = strtolower(trim(readline("Are you sure you want to delete this employee? (yes/no): ")));
		if ($confirm !== 'yes' && $confirm !== 'y') {
			echo "\nDeletion cancelled.\n";
			return;
		}
		$deleted_name = $employee->getFirstName() . " " . $employee->getLastName();
		array_splice($this->employees, $found_index, 1);
		$this->saveToJson();
		echo "\nEmployee '" . $deleted_name . "' (ID: " . $target_id . ") deleted successfully!\n";
	}
}
?>