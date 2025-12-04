<?php
if (!class_exists('Auth')) {
    class Auth {
        
        public static function login($email, $password) {
            $db = new Database();
            $conn = $db->getConnection();
            
            if (!$conn) {
                throw new Exception("Cannot connect to database");
            }
            
            $email = Security::cleanInput($email);
            
            $sql = "SELECT * FROM users WHERE email = :email AND status = 'active'";
            $stmt = Security::safeQuery($conn, $sql, [':email' => $email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                return true;
            }
            
            return false;
        }
        
        public static function register($name, $email, $password) {
            $db = new Database();
            $conn = $db->getConnection();
            
            if (!$conn) {
                throw new Exception("Cannot connect to database");
            }
            
            $name = Security::cleanInput($name);
            $email = Security::cleanInput($email);
            
            // Check if email already exists
            $sql = "SELECT id FROM users WHERE email = :email";
            $stmt = Security::safeQuery($conn, $sql, [':email' => $email]);
            
            if ($stmt->fetch()) {
                throw new Exception("Email already registered");
            }
            
            // Register new user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (name, email, password, role, created_at) 
                    VALUES (:name, :email, :password, 'user', NOW())";
            
            Security::safeQuery($conn, $sql, [
                ':name' => $name,
                ':email' => $email,
                ':password' => $hashedPassword
            ]);
            
            return true;
        }
        
        public static function isLoggedIn() {
            return isset($_SESSION['user_id']);
        }
        
        public static function logout() {
            session_destroy();
            header("Location: login.php");
            exit();
        }
        
        public static function getUserInfo() {
            if (self::isLoggedIn()) {
                return [
                    'id' => $_SESSION['user_id'],
                    'name' => $_SESSION['user_name'],
                    'email' => $_SESSION['user_email'],
                    'role' => $_SESSION['user_role']
                ];
            }
            return null;
        }
    }
}
?>