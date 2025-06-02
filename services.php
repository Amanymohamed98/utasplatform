<?php
require_once 'config.php';
require_once 'functions.php';

if(!is_logged_in()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = get_user_data($conn, $user_id);
$specialization = $user['specialization'];

$type = isset($_GET['type']) ? $_GET['type'] : 'materials';

$page_title = "Services - " . SITE_NAME;

?>
<style>
    /* UTAS Color Scheme */
    :root {
            --utas-navy: #003366;
            --utas-light-blue: #0066cc;
            --utas-gold: #ff6600;
            --utas-white: #ffffff;
            --utas-light-gray: #f5f5f5;
            --utas-dark-gray: #333333;
        }
    body {
        font-family: 'Arial', sans-serif;
        background-color: var(--utas-light-gray);
        color: var(--utas-dark-gray);
    }
    
    .services {
        padding: 30px 0;
    }
    
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
    }
    
    h2, h3, h4 {
        color: var(--utas-navy);
    }
    
    h2 {
        text-align: center;
        margin-bottom: 30px;
        font-size: 2.2rem;
        border-bottom: 2px solid var(--utas-gold);
        padding-bottom: 10px;
    }
    
    h3 {
        margin-bottom: 20px;
        font-size: 1.8rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .services-tabs {
        display: flex;
        justify-content: center;
        margin-bottom: 30px;
        border-bottom: 1px solid #ddd;
    }
    
    .services-tabs a {
        padding: 12px 20px;
        margin: 0 5px;
        text-decoration: none;
        color: var(--utas-dark-gray);
        font-weight: 600;
        border-radius: 5px 5px 0 0;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .services-tabs a:hover {
        background-color: rgba(0, 33, 71, 0.1);
    }
    
    .services-tabs a.active {
        background-color: var(--utas-navy);
        color: var(--utas-white);
    }
    
    .services-content {
        background-color: var(--utas-white);
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    /* Materials Section */
    .materials-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .material-card {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease;
        background-color: var(--utas-white);
    }
    
    .material-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .material-icon {
        font-size: 2.5rem;
        color: var(--utas-navy);
        margin-bottom: 10px;
    }
    
    .material-info {
        flex: 1;
    }
    
    .material-info h4 {
        margin-bottom: 10px;
        color: var(--utas-navy);
    }
    
    .material-info p {
        margin-bottom: 10px;
        color: var(--utas-dark-gray);
    }
    
    .material-date {
        font-size: 0.8rem;
        color: #777;
    }
    
    .download-btn {
        display: inline-block;
        background-color: var(--utas-navy);
        color: var(--utas-white);
        padding: 8px 15px;
        border-radius: 5px;
        text-decoration: none;
        margin-top: 15px;
        text-align: center;
        transition: background-color 0.3s ease;
    }
    
    .download-btn:hover {
        background-color: #003366;
    }
    
    .no-materials, .no-tutors {
        text-align: center;
        padding: 40px 20px;
        color: #777;
    }
    
    .no-materials i, .no-tutors i {
        font-size: 3rem;
        color: #ddd;
        margin-bottom: 15px;
    }
    
    /* Tutors Section */
    .tutors-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .tutor-card {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        display: flex;
        flex-direction: column;
        background-color: var(--utas-white);
    }
    
    .tutor-avatar {
        font-size: 3rem;
        color: var(--utas-navy);
        text-align: center;
        margin-bottom: 15px;
    }
    
    .tutor-info h4 {
        margin-bottom: 10px;
        color: var(--utas-navy);
    }
    
    .tutor-info p {
        margin-bottom: 8px;
        color: var(--utas-dark-gray);
    }
    
    .tutor-info p strong {
        color: var(--utas-navy);
    }
    
    .tutor-actions {
        margin-top: 15px;
    }
    
    .btn {
        display: inline-block;
        padding: 8px 15px;
        border-radius: 5px;
        text-decoration: none;
        transition: background-color 0.3s ease;
    }
    
    .btn-primary {
        background-color: var(--utas-navy);
        color: var(--utas-white);
    }
    
    .btn-primary:hover {
        background-color: #003366;
    }
    
    /* Chat Section */
    .chat-container {
        display: flex;
        height: 500px;
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
        margin-top: 20px;
    }
    
    .chat-sidebar {
        width: 250px;
        background-color: var(--utas-navy);
        color: var(--utas-white);
        padding: 15px;
    }
    
    .chat-sidebar h4 {
        color: var(--utas-white);
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .teacher-item {
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 5px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .teacher-item:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }
    
    .teacher-item.active {
        background-color: var(--utas-gold);
        color: var(--utas-navy);
    }
    
    .teacher-item.active i {
        color: var(--utas-navy);
    }
    
    .chat-messages {
        flex: 1;
        display: flex;
        flex-direction: column;
        background-color: var(--utas-white);
    }
    
    .messages-header {
        padding: 15px;
        border-bottom: 1px solid #ddd;
        background-color: var(--utas-light-gray);
    }
    
    .messages-header h4 {
        margin: 0;
    }
    
    .messages-body {
        flex: 1;
        padding: 15px;
        overflow-y: auto;
    }
    
    .message {
        margin-bottom: 15px;
        max-width: 70%;
    }
    
    .message.received {
        align-self: flex-start;
    }
    
    .message.sent {
        align-self: flex-end;
    }
    
    .message-content {
        padding: 10px 15px;
        border-radius: 18px;
        position: relative;
    }
    
    .message.received .message-content {
        background-color: #f1f1f1;
        color: var(--utas-dark-gray);
    }
    
    .message.sent .message-content {
        background-color: var(--utas-gold);
        color: var(--utas-dark-gray);
    }
    
    .message-time {
        font-size: 0.7rem;
        color: #777;
        display: block;
        margin-top: 5px;
        text-align: right;
    }
    
    .message-input {
        padding: 15px;
        border-top: 1px solid #ddd;
        background-color: var(--utas-light-gray);
    }
    
    .message-input form {
        display: flex;
        gap: 10px;
    }
    
    .message-input input {
        flex: 1;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
    
    .message-input button {
        padding: 10px 20px;
        background-color: var(--utas-navy);
        color: var(--utas-white);
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    
    .message-input button:hover {
        background-color: #003366;
    }
    
    @media (max-width: 768px) {
        .chat-container {
            flex-direction: column;
            height: auto;
        }
        
        .chat-sidebar {
            width: 100%;
        }
        
        .materials-grid, .tutors-grid {
            grid-template-columns: 1fr;
        }
        
        .services-tabs {
            flex-direction: column;
            align-items: center;
        }
        
        .services-tabs a {
            width: 100%;
            text-align: center;
            margin: 5px 0;
            border-radius: 5px;
        }
    }
    /* Footer Styles */
        footer {
            background-color: #003366;
            color: white;
            padding: 30px 0;
            text-align: center;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        .footer-links a {
            color: var(--utas-white);
            text-decoration: none;
        }

        .footer-links a:hover {
            color: var(--utas-orange);
        }

        .copyright {
            font-size: 0.9rem;
            opacity: 0.8;
        }
/* Header Styles */
header {
    background-color: var(--white);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    padding: 15px 0;
    position: sticky;
    top: 0;
    z-index: 100;
}

.header-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.logo img {
    height: 50px;
}

nav ul {
    display: flex;
    list-style: none;
    gap: 20px;
}

nav ul li a {
    color: var(--dark-color);
    font-weight: 500;
    padding: 8px 12px;
    border-radius: 5px;
    transition: all 0.3s ease;
}

nav ul li a:hover {
    color: var(--primary-color);
    background-color: rgba(0, 86, 179, 0.1);
}
</style>
<header>
        <div class="container header-container">
            <div class="logo">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT13qZq8ttUi44qMBaoT4-aloxfJL712OeWyQ&s" alt="UTAS Logo">
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Home</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="services.php">Services</a></li>
                    <?php if(is_logged_in()): ?>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
<section class="services">
    <div class="container">
        <h2>Available Services</h2>
        
        <div class="services-tabs">
            <a href="?type=materials" class="<?php echo ($type == 'materials') ? 'active' : ''; ?>">
                <i class="fas fa-book-open"></i> Free Materials
            </a>
            <a href="?type=tutors" class="<?php echo ($type == 'tutors') ? 'active' : ''; ?>">
                <i class="fas fa-chalkboard-teacher"></i> Private Tutors
            </a>
            <a href="?type=chat" class="<?php echo ($type == 'chat') ? 'active' : ''; ?>">
                <i class="fas fa-comments"></i> Chat
            </a>
        </div>
        
        <div class="services-content">
            <?php if($type == 'materials'): ?>
                <h3><i class="fas fa-book-open"></i> Free Study Materials</h3>
                <p>You can download study materials, summaries, and past exam questions for <?php echo ($specialization == 'IT') ? 'Information Technology' : 'Engineering'; ?> specialization</p>
                
                <div class="materials-list">
                    <?php
                    $materials = get_materials($conn, $specialization);
                    if(count($materials) > 0): ?>
                        <div class="materials-grid">
                            <?php foreach($materials as $material): ?>
                                <div class="material-card">
                                    <div class="material-icon">
                                        <i class="fas fa-file-<?php echo ($material['type'] == 'pdf') ? 'pdf' : 'word'; ?>"></i>
                                    </div>
                                    <div class="material-info">
                                        <h4><?php echo $material['title']; ?></h4>
                                        <p><?php echo $material['description']; ?></p>
                                        <span class="material-date"><?php echo date('Y/m/d', strtotime($material['uploaded_at'])); ?></span>
                                    </div>
                                    <a href="download.php?id=<?php echo $material['id']; ?>" class="download-btn">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-materials">
                            <i class="fas fa-book"></i>
                            <p>No materials available for your specialization at the moment.</p>
                        </div>
                        <?php if($type == 'materials'): ?>
    <div style="text-align: right; margin-bottom: 20px;">
        <a href="upload_material.php" class="btn btn-primary">
            <i class="fas fa-upload"></i> Upload Material
        </a>
    </div>
    
    <!-- بقية كود عرض المواد -->
<?php endif; ?>
                    <?php endif; ?>
                </div>
                
            <?php elseif($type == 'tutors'): ?>
                <h3><i class="fas fa-chalkboard-teacher"></i> Private Tutors</h3>
                <p>You can choose a private tutor from outstanding students in <?php echo ($specialization == 'IT') ? 'Information Technology' : 'Engineering'; ?> specialization</p>
                
                <div class="tutors-list">
                    <?php
                    $tutors = get_tutors($conn, $specialization);
                    if(count($tutors) > 0): ?>
                        <div class="tutors-grid">
                            <?php foreach($tutors as $tutor): ?>
                                <div class="tutor-card">
                                    <div class="tutor-avatar">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                    <div class="tutor-info">
                                        <h4><?php echo $tutor['full_name']; ?></h4>
                                        <p><strong>Student ID:</strong> <?php echo $tutor['university_id']; ?></p>
                                        <p><strong>GPA:</strong> <?php echo $tutor['gpa']; ?></p>
                                        <p><strong>Specialization:</strong> <?php echo ($tutor['specialization'] == 'IT') ? 'Information Technology' : 'Engineering'; ?></p>
                                    </div>
                                    <div class="tutor-actions">
                                        <a href="request_tutor.php?id=<?php echo $tutor['id']; ?>" class="btn btn-primary">
                                            <i class="fas fa-envelope"></i> Request Session
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-tutors">
                            <i class="fas fa-user-graduate"></i>
                            <p>No tutors available for your specialization at the moment.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
            <?php elseif($type == 'chat'): ?>
                <h3><i class="fas fa-comments"></i> Chat with Course Instructors</h3>
                <p>You can communicate directly with university course instructors regarding your academic inquiries.</p>
                
                <div class="chat-container">
                    <div class="chat-sidebar">
                        <h4>Course Instructors</h4>
                        <div class="course-teachers">
                            <div class="teacher-item active">
                                <i class="fas fa-user-tie"></i>
                                <span>Dr. Ahmed Mohamed - Programming 1</span>
                            </div>
                            <div class="teacher-item">
                                <i class="fas fa-user-tie"></i>
                                <span>Dr. Sara Khalid - Database Systems</span>
                            </div>
                            <div class="teacher-item">
                                <i class="fas fa-user-tie"></i>
                                <span>Dr. Omar Ali - Computer Networks</span>
                            </div>
                        </div>
                    </div>
                    <div class="chat-messages">
                        <div class="messages-header">
                            <h4>Dr. Ahmed Mohamed - Programming 1</h4>
                        </div>
                        <div class="messages-body">
                            <div class="message received">
                                <div class="message-content">
                                    <p>Hello <?php echo $user['full_name']; ?>, how can I help you today?</p>
                                    <span class="message-time">10:30 AM</span>
                                </div>
                            </div>
                        </div>
                        <div class="message-input">
                            <form id="chat-form" method="post">
                                <input type="text" name="message" placeholder="Type your message here..." required>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Send
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- PHP code for message processing -->
                <?php
                if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
                    $message = clean_input($_POST['message']);
                    $teacher_id = 1; // This can be changed based on the selected teacher
                    
                    if (!empty($message)) {
                        // Code to insert message into database
                        try {
                            $stmt = $conn->prepare("INSERT INTO messages (conversation_id, sender_id, message) 
                                                   VALUES (?, ?, ?)");
                            // Assuming conversation_id is 1 for this example
                            $stmt->execute([1, $user_id, $message]);
                            
                            echo '<script>alert("Message sent successfully");</script>';
                        } catch(PDOException $e) {
                            echo '<script>alert("Error sending message");</script>';
                        }
                    }
                }
                ?>
                
                <!-- JavaScript for chat management -->
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Update chat every 5 seconds
                    setInterval(updateChat, 5000);
                    
                    // Handle message submission
                    document.getElementById('chat-form').addEventListener('submit', function(e) {
                        e.preventDefault();
                        const messageInput = this.querySelector('input[name="message"]');
                        const message = messageInput.value.trim();
                        
                        if (message) {
                            fetch(this.action, {
                                method: 'POST',
                                body: new URLSearchParams(new FormData(this)),
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                }
                            })
                            .then(response => {
                                if (response.ok) {
                                    messageInput.value = '';
                                    updateChat();
                                }
                            });
                        }
                    });
                    
                    // Update chat messages
                    function updateChat() {
                        fetch('get_messages.php?conversation_id=1')
                            .then(response => response.text())
                            .then(data => {
                                document.querySelector('.messages-body').innerHTML = data;
                                scrollToBottom();
                            });
                    }
                    
                    // Scroll to bottom of chat
                    function scrollToBottom() {
                        const messagesBody = document.querySelector('.messages-body');
                        messagesBody.scrollTop = messagesBody.scrollHeight;
                    }
                    
                    // Select different teacher
                    document.querySelectorAll('.teacher-item').forEach(item => {
                        item.addEventListener('click', function() {
                            document.querySelectorAll('.teacher-item').forEach(i => {
                                i.classList.remove('active');
                            });
                            this.classList.add('active');
                            
                            const teacherName = this.querySelector('span').textContent;
                            document.querySelector('.messages-header h4').textContent = teacherName;
                            
                            // Code to fetch different conversation
                            updateChat();
                        });
                    });
                });
                </script>
                
            <?php endif; ?>
        </div>
    </div>
</section>

<footer>
        <div class="container">
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Contact Us</a>
                <a href="#">FAQ</a>
            </div>
            <div class="copyright">
                &copy; <?php echo date('Y'); ?> University of Technology and Applied Sciences. All rights reserved.
            </div>
        </div>
    </footer>