<?php
require_once 'config.php';
require_once 'functions.php';

if(!is_logged_in()) {
    die("غير مصرح بالدخول");
}

$conversation_id = isset($_GET['conversation_id']) ? (int)$_GET['conversation_id'] : 1;
$user_id = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("SELECT m.*, u.full_name 
                           FROM messages m
                           JOIN users u ON m.sender_id = u.id
                           WHERE m.conversation_id = ?
                           ORDER BY m.sent_at ASC");
    $stmt->execute([$conversation_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($messages as $message) {
        $messageClass = ($message['sender_id'] == $user_id) ? 'sent' : 'received';
        echo '<div class="message ' . $messageClass . '">';
        echo '    <div class="message-content">';
        if ($messageClass == 'received') {
            echo '        <div class="message-sender">' . $message['full_name'] . '</div>';
        }
        echo '        <p>' . htmlspecialchars($message['message']) . '</p>';
        echo '        <span class="message-time">' . date('h:i A', strtotime($message['sent_at'])) . '</span>';
        echo '    </div>';
        echo '</div>';
    }
} catch(PDOException $e) {
    die("حدث خطأ في جلب الرسائل");
}
?>