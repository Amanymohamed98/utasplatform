<?php
session_start();

// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'utas_platform');

// الاتصال بقاعدة البيانات
try {
    $conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// إعدادات الموقع
define('SITE_NAME', 'منصة طلاب UTAS');
define('SITE_URL', 'http://localhost/UTAS-Platform');
?>