<?php
// بداية الجلسة
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// قائمة بالملفات التي تم تحميلها
$loaded_files = [];

// دالة لتحميل الملفات مرة واحدة فقط
function load_once($file) {
    global $loaded_files;
    
    if (!in_array($file, $loaded_files)) {
        if (file_exists($file)) {
            require_once $file;
            $loaded_files[] = $file;
            return true;
        } else {
            error_log("UNFOUND File" . $file);
            return false;
        }
    }
    return true;
}

// تحميل الملفات الأساسية
load_once('includes/database.php');
load_once('includes/security.php');
load_once('includes/functions.php');
load_once('includes/auth.php');
?>