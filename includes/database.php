<?php
if (!class_exists('Database')) {
    class Database {
        private $host = "localhost";
        private $username = "root";
        private $password = "";
        private $dbname = "airline_db";
        public $conn;
        
        public function getConnection() {
            $this->conn = null;
            try {
                $this->conn = new PDO(
                    "mysql:host=" . $this->host . ";dbname=" . $this->dbname . ";charset=utf8mb4",
                    $this->username,
                    $this->password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch(PDOException $exception) {
                error_log("connection ERROR:" . $exception->getMessage());
                return null;
            }
            return $this->conn;
        }
    }
}
?>