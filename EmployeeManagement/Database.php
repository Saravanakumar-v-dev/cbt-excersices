<?php
class Database {
    private const DB_HOST = 'localhost';
    private const DB_USER = 'root';
    private const DB_PASSWORD = 'root123';
    private const DB_NAME = 'employee_management';
    
    public $conn;

    public function __construct() {
        $this->conn = new mysqli(self::DB_HOST, self::DB_USER, self::DB_PASSWORD, self::DB_NAME);
        if ($this->conn->connect_error) {
            echo " Connection failed: " . $this->conn->connect_error . "\n";
            echo " System will work with JSON storage only.\n";
            $this->conn = null;
        }
    }
}


?>