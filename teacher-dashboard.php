<?php
require_once 'config.php';
require_once 'functions.php';

if(!is_logged_in()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = get_user_data($conn, $user_id);

// التعديل الرئيسي: التحقق من نوع المستخدم
if($user['user_type'] !== 'teacher') {
    header("Location: teacher.php");
    exit();
}


