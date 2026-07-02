<?php
require_once __DIR__ . '/Employee.php';

/**
 * FullTimeEmployee - extends Employee with salary and benefits info
 */
class FullTimeEmployee extends Employee
{
    public float $salary;
    public string $benefits;

    public function __construct(
        $_employee_id, $_first_name, $_last_name, $_department,
        $_experience_of_employee, $_phone_number, $_email_address,
        $_aadhar_number, $_pan_number, $_date_of_birth, $_nationality,
        $_marital_status, $_type_of_employee,
        $_salary, $_benefits
    ) {
        parent::__construct(
            $_employee_id, $_first_name, $_last_name, $_department,
            $_experience_of_employee, $_phone_number, $_email_address,
            $_aadhar_number, $_pan_number, $_date_of_birth, $_nationality,
            $_marital_status, $_type_of_employee
        );
        $this->salary = $_salary;
        $this->benefits = $_benefits;
    }

    public static function fromArray(array $_data): self
    {
        return new self(
            $_data['employee_id'], $_data['first_name'], $_data['last_name'],
            $_data['department_id'], $_data['experience_of_employee'],
            $_data['phone_number'], $_data['email_address'],
            $_data['aadhar_number'], $_data['pan_number'],
            $_data['date_of_birth'], $_data['nationality'],
            $_data['marital_status'], $_data['type_of_employee'],
            (float) ($_data['salary'] ?? 0),
            $_data['benefits'] ?? ''
        );
    }

    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'salary' => $this->salary,
            'benefits' => $this->benefits,
        ]);
    }

    public function displayProfile(): void
    {
        parent::displayEmployee($this);
        echo "--- Full Time Details ---\n";
        echo "Salary : " . $this->salary . "\n";
        echo "Benefits : " . $this->benefits . "\n\n";
    }
    public function getMonthlySalary(){
        return $this->salary;
    }
    public function getBenefits(){
        return $this->benefits;
    }
    public function setMonthlySalary($salary){
        $this->salary = $salary;
    }
    public function setBenefits($benefits){
        $this->benefits = $benefits;
    }
}
?>
