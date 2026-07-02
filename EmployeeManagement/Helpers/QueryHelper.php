<?php
trait QueryHelper
{
    public function execute(string $sql, array $params = [])
    {
        $db = new Database();
        $conn = $db->conn;
        if ($conn === null) {
            return false;
        }
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return false;
        }
        if (!empty($params)) {
            $types = "";
            foreach ($params as $param) {
                $types .= match (true) {
                    is_int($param)   => "i",
                    is_float($param) => "d",
                    default          => "s"
                };
            }
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt;
    }
}
