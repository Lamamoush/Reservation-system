<?php
if (!class_exists('Functions')) {
    class Functions {
        
        public static function redirect($url, $message = null) {
            if ($message) {
                $_SESSION['flash_message'] = $message;
            }
            header("Location: $url");
            exit();
        }
        
        public static function showFlashMessage() {
            if (isset($_SESSION['flash_message'])) {
                $message = $_SESSION['flash_message'];
                unset($_SESSION['flash_message']);
                return "<div class='alert alert-info alert-dismissible fade show'>
                            {$message}
                            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                        </div>";
            }
            return '';
        }
        
        public static function formatDate($date, $format = 'Y-m-d H:i:s') {
            try {
                $datetime = new DateTime($date);
                return $datetime->format($format);
            } catch (Exception $e) {
                return $date;
            }
        }
        
        public static function generateBookingNumber() {
            return 'BK' . date('Ymd') . strtoupper(substr(uniqid(), -6));
        }
    }
}
?>