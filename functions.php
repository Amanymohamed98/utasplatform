<?php
// دالة لتنظيف المدخلات
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// التحقق من تسجيل الدخول
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// جلب معلومات المستخدم
function get_user_data($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// جلب المواد الدراسية حسب التخصص
function get_materials($conn, $specialization) {
    $stmt = $conn->prepare("SELECT * FROM materials WHERE specialization = ?");
    $stmt->execute([$specialization]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// جلب المدرسين حسب التخصص
function get_tutors($conn, $specialization) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE specialization = ? AND gpa >= 3.3 AND is_tutor = 1");
    $stmt->execute([$specialization]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// جلب معرف الطالب من طلب معين
function get_request_student_id($conn, $request_id) {
    $stmt = $conn->prepare("SELECT student_id FROM tutoring_requests WHERE id = ?");
    $stmt->execute([$request_id]);
    return $stmt->fetchColumn();
}

// جلب طلبات الدروس لمدرس معين
function get_tutor_requests($conn, $tutor_id, $status = null) {
    $sql = "SELECT tr.*, u.full_name as student_name, u.university_id as student_id 
            FROM tutoring_requests tr
            JOIN users u ON tr.student_id = u.id
            WHERE tr.tutor_id = ?";
    
    if($status) {
        $sql .= " AND tr.status = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$tutor_id, $status]);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->execute([$tutor_id]);
    }
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function log_logout($conn, $user_id) {
    // لا تفعل أي شيء
    return true;
}
?>