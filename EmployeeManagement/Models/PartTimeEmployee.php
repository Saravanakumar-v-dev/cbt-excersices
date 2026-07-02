<?php
require_once __DIR__ . '/Employee.php';

/**
 * PartTimeEmployee - extends Employee with hourly rate and shift info
 */
class PartTimeEmployee extends Employee
{
    public float $hourly_rate;
    public string $shift_type;

    public function __construct(
        $_employee_id, $_first_name, $_last_name, $_department,
        $_experience_of_employee, $_phone_number, $_email_address,
        $_aadhar_number, $_pan_number, $_date_of_birth, $_nationality,
        $_marital_status, $_type_of_employee,
        $_hourly_rate, $_shift_type
    ) {
        parent::__construct(
            $_employee_id, $_first_name, $_last_name, $_department,
            $_experience_of_employee, $_phone_number, $_email_address,
            $_aadhar_number, $_pan_number, $_date_of_birth, $_nationality,
            $_marital_status, $_type_of_employee
        );
        $this->hourly_rate = $_hourly_rate;
        $this->shift_type = $_shift_type;
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
            (float) ($_data['hourly_rate'] ?? 0),
            $_data['shift_type'] ?? ''
        );
    }

    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'hourly_rate' => $this->hourly_rate,
            'shift_type'  => $this->shift_type,
        ]);
    }

    public function displayProfile(): void
    {
        parent::displayEmployee($this);
        echo "--- Part Time Details ---\n";
        echo "Hourly Rate      : " . $this->hourly_rate . "\n";
        echo "Shift Type       : " . $this->shift_type . "\n\n";
    }

    public function getHourlyRate(){
        return $this->hourly_rate;
    }
    public function getShiftType(){
        return $this->shift_type;
    }

    public function setHourlyRate($hourly_rate){
        $this->hourly_rate = $hourly_rate;
    }
    public function setShiftType($shift_type){
        $this->shift_type = $shift_type;
    }
}
?>
