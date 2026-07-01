<?php
class Employee implements JsonSerializable
{
	public int $employee_id;
	private string $first_name;
	private string $last_name;
	private int $department_id;
	private int $experience_of_employee;
	private string $phone_number;
	private string $email_address;
	private string $aadhar_number;
	private string $pan_number;
	private string $date_of_birth;
	private string $nationality;
	private string $marital_status;
	private string $type_of_employee;
	

	/**
	 * Constructor - creates an Employee with the given details
	 * @param int $_employee_id
	 * @param string $_first_name
	 * @param string $_last_name
	 * @param int $_department_id
	 * @param int $_experience_of_employee
	 * @param string $_phone_number
	 * @param string $_email_address
	 * @param string $_aadhar_number
	 * @param string $_pan_number
	 * @param string $_date_of_birth
	 * @param string $_nationality
	 * @param string $_marital_status
	 * @param string $_type_of_employee
	 */
	public function __construct($_employee_id, $_first_name, $_last_name, $_department_id, $_experience_of_employee, $_phone_number, $_email_address, $_aadhar_number, $_pan_number, $_date_of_birth, $_nationality, $_marital_status, $_type_of_employee)
	{
		$this->employee_id = $_employee_id;
		$this->first_name = $_first_name;
		$this->last_name = $_last_name;
		$this->department_id = $_department_id;
		$this->experience_of_employee = $_experience_of_employee;
		$this->phone_number = $_phone_number;
		$this->email_address = $_email_address;
		$this->aadhar_number = $_aadhar_number;
		$this->pan_number = $_pan_number;
		$this->date_of_birth = $_date_of_birth;
		$this->nationality = $_nationality;
		$this->marital_status = $_marital_status;
		$this->type_of_employee = $_type_of_employee;
	}

	/**
	 * Returns the employee's unique ID
	 * @return int
	 */
	public function getEmployeeId(): int
	{
		return $this->employee_id;
	}
	/**
	 * Returns the employee's first name
	 * @return string
	 */
	public function getFirstName(): string
	{
		return $this->first_name;
	}
	/**
	 * Returns the employee's last name
	 * @return string
	 */
	public function getLastName(): string
	{
		return $this->last_name;
	}
	/**
	 * Returns the employee's department ID
	 * @return int
	 */
	public function getDepartmentId(): int
	{
		return $this->department_id;
	}
	/**
	 * Returns the employee's years of experience
	 * @return int
	 */
	public function getExperienceOfEmployee(): int
	{
		return $this->experience_of_employee;
	}
	/**
	 * Returns the employee's phone number
	 * @return string
	 */
	public function getPhoneNumber(): string
	{
		return $this->phone_number;
	}
	/**
	 * Returns the employee's email address
	 * @return string
	 */
	public function getEmailAddress(): string
	{
		return $this->email_address;
	}

	/**
	 * Returns the employee's Aadhar number
	 * @return string
	 */
	public function getAadharNumber(): string
	{
		return $this->aadhar_number;
	}

	/**
	 * Returns the employee's PAN number
	 * @return string
	 */
	public function getPanNumber(): string
	{
		return $this->pan_number;
	}

	/**
	 * Returns the employee's date of birth
	 * @return string
	 */
	public function getDateOfBirth(): string
	{
		return $this->date_of_birth;
	}

	/**
	 * Returns the employee's nationality
	 * @return string
	 */
	public function getNationality(): string
	{
		return $this->nationality;
	}

	/**
	 * Returns the employee's marital status
	 * @return string
	 */
	public function getMaritalStatus(): string
	{
		return $this->marital_status;
	}

	/**
	 * Returns the employee's type (e.g. Developer, Manager)
	 * @return string
	 */
	public function getTypeOfEmployee(): string
	{
		return $this->type_of_employee;
	}

	/**
	 * Sets the employee's unique ID
	 * @param int $_employee_id
	 * @return void
	 */
	public function setEmployeeId($_employee_id): void
	{
		$this->employee_id = $_employee_id;
	}

	/**
	 * Sets the employee's first name
	 * @param string $_first_name
	 * @return void
	 */
	public function setFirstName($_first_name): void
	{
		$this->first_name = $_first_name;
	}

	/**
	 * Sets the employee's last name
	 * @param string $_last_name
	 * @return void
	 */
	public function setLastName($_last_name): void
	{
		$this->last_name = $_last_name;
	}

	/**
	 * Sets the employee's department ID
	 * @param int $_department_id
	 * @return void
	 */
	public function setDepartmentId($_department_id): void
	{
		$this->department_id = $_department_id;
	}

	/**
	 * Sets the employee's years of experience
	 * @param int $_experience_of_employee
	 * @return void
	 */
	public function setExperienceOfEmployee($_experience_of_employee): void
	{
		$this->experience_of_employee = $_experience_of_employee;
	}

	/**
	 * Sets the employee's phone number
	 * @param string $_phone_number
	 * @return void
	 */
	public function setPhoneNumber($_phone_number): void
	{
		$this->phone_number = $_phone_number;
	}

	/**
	 * Sets the employee's email address
	 * @param string $_email_address
	 * @return void
	 */
	public function setEmailAddress($_email_address): void
	{
		$this->email_address = $_email_address;
	}

	/**
	 * Sets the employee's Aadhar number
	 * @param string $_aadhar_number
	 * @return void
	 */
	public function setAadharNumber($_aadhar_number): void
	{
		$this->aadhar_number = $_aadhar_number;
	}

	/**
	 * Sets the employee's PAN number
	 * @param string $_pan_number
	 * @return void
	 */
	public function setPanNumber($_pan_number): void
	{
		$this->pan_number = $_pan_number;
	}

	/**
	 * Sets the employee's date of birth
	 * @param string $_date_of_birth
	 * @return void
	 */
	public function setDateOfBirth($_date_of_birth): void
	{
		$this->date_of_birth = $_date_of_birth;
	}

	/**
	 * Sets the employee's nationality
	 * @param string $_nationality
	 * @return void
	 */
	public function setNationality($_nationality): void
	{
		$this->nationality = $_nationality;
	}

	/**
	 * Sets the employee's marital status
	 * @param string $_marital_status
	 * @return void
	 */
	public function setMaritalStatus($_marital_status): void
	{
		$this->marital_status = $_marital_status;
	}

	/**
	 * Sets the employee's type (e.g. Developer, Manager)
	 * @param string $_type_of_employee
	 * @return void
	 */
	public function setTypeOfEmployee($_type_of_employee): void
	{
		$this->type_of_employee = $_type_of_employee;
	}

	/**
	 * Creates an Employee object from an associative array (e.g. decoded JSON record)
	 * @param array $_data
	 * @return self
	 */
	public static function fromArray(array $_data): self
	{
		return new self(
			$_data['employee_id'],
			$_data['first_name'],
			$_data['last_name'],
			$_data['department_id'],
			$_data['experience_of_employee'],
			$_data['phone_number'],
			$_data['email_address'],
			$_data['aadhar_number'],
			$_data['pan_number'],
			$_data['date_of_birth'],
			$_data['nationality'],
			$_data['marital_status'],
			$_data['type_of_employee']
		);
	}

	/**
	 * Converts the Employee object into an associative array for JSON storage
	 * @return array
	 */
	public function jsonSerialize(): array
	{
		return [
			"employee_id" => $this->employee_id,
			"first_name" => $this->first_name,
			"last_name" => $this->last_name,
			"department_id" => $this->department_id,
			"experience_of_employee" => $this->experience_of_employee,
			"phone_number" => $this->phone_number,
			"email_address" => $this->email_address,
			"aadhar_number" => $this->aadhar_number,
			"pan_number" => $this->pan_number,
			"date_of_birth" => $this->date_of_birth,
			"nationality" => $this->nationality,
			"marital_status" => $this->marital_status,
			"type_of_employee" => $this->type_of_employee
		];
	}
	public function displayEmployee($_employee): void
	{
		echo "\n\n";
		echo "Employee ID : " . $_employee->getEmployeeId() . "\n";
		echo "First Name : " . $_employee->getFirstName() . "\n";
		echo "Last Name : " . $_employee->getLastName() . "\n";
		echo "Department : " . $_employee->getDepartmentId() . "\n";
		echo "Experience : " . $_employee->getExperienceOfEmployee() . " years\n";
		echo "Phone Number : " . $_employee->getPhoneNumber() . "\n";
		echo "Email Address : " . $_employee->getEmailAddress() . "\n";
		echo "Aadhar Number : " . $_employee->getAadharNumber() . "\n";
		echo "PAN Number : " . $_employee->getPanNumber() . "\n";
		echo "Date of Birth : " . $_employee->getDateOfBirth() . "\n";
		echo "Nationality : " . $_employee->getNationality() . "\n";
		echo "Marital Status : " . $_employee->getMaritalStatus() . "\n";
		echo "Type of Employee : " . $_employee->getTypeOfEmployee() . "\n";
		echo "\n";
	}
}
?>