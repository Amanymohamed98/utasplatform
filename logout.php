<?php
// ملف includes/logout.php

require_once 'config.php';
require_once 'functions.php';

// بدء الجلسة إذا لم تكن بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تسجيل حدث الخروج إذا كان المستخدم مسجل الدخول
if (isset($_SESSION['user_id'])) {
    log_logout($conn, $_SESSION['user_id']);
}

// تدمير جميع بيانات الجلسة
$_SESSION = array();

// حذف كوكي الجلسة
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// تدمير الجلسة
session_destroy();

// التوجيه إلى صفحة تسجيل الدخول مع رسالة
header("Location:login.php");
exit();
?>