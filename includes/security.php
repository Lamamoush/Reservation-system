<?php
if (!class_exists('Security')) {
    class Security {
        
        public static function cleanInput($data) {
            if (is_array($data)) {
                return array_map([self::class, 'cleanInput'], $data);
            }
            
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
            return $data;
        }
        
        public static function safeQuery($conn, $sql, $params = []) {
            if (!$conn) {
                throw new Exception("No database connection");
            }
            
            try {
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
                return $stmt;
            } catch(PDOException $e) {
                throw new Exception("Query error: " . $e->getMessage());
            }
        }
        
        public static function validateEmail($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        }
        
        public static function validatePhone($phone) {
            return preg_match('/^[0-9]{10,15}$/', $phone);
        }
        
        public static function escapeOutput($data) {
            return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }
        
        public static function generateCSRFToken() {
            if (!isset($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            return $_SESSION['csrf_token'];
        }
        
        public static function verifyCSRFToken($token) {
            return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
        }
    }
}
?>