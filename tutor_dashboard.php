<?php
require_once 'config.php';
require_once 'functions.php';

if(!is_logged_in()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = get_user_data($conn, $user_id);

// Verify user is an approved tutor
if(!$user['is_tutor']) {
    header("Location: dashboard.php");
    exit();
}

// Get tutor's tutoring requests
$stmt = $conn->prepare("SELECT tr.*, u.full_name as student_name, u.university_id as student_id 
                       FROM tutoring_requests tr
                       JOIN users u ON tr.student_id = u.id
                       WHERE tr.tutor_id = ?
                       ORDER BY tr.meeting_time DESC");
$stmt->execute([$user_id]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Process request accept/reject/complete actions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['accept_request'])) {
        $request_id = (int)$_POST['request_id'];
        $notes = clean_input($_POST['tutor_notes']);
        
        $stmt = $conn->prepare("UPDATE tutoring_requests 
                               SET status = 'accepted', tutor_notes = ?, updated_at = NOW() 
                               WHERE id = ? AND tutor_id = ?");
        if($stmt->execute([$notes, $request_id, $user_id])) {
            // Send notification to student
            $notification = "Your tutoring request has been accepted by tutor " . $user['full_name'];
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, related_url) 
                                   VALUES (?, ?, ?, ?)");
            $student_id = get_request_student_id($conn, $request_id);
            $stmt->execute([$student_id, 'Tutoring Request Accepted', $notification, 'my_requests.php']);
            
            $_SESSION['success'] = "Request accepted successfully and student notified";
        }
    } 
    elseif(isset($_POST['reject_request'])) {
        $request_id = (int)$_POST['request_id'];
        $reason = clean_input($_POST['reject_reason']);
        
        $stmt = $conn->prepare("UPDATE tutoring_requests 
                               SET status = 'rejected', tutor_notes = ?, updated_at = NOW() 
                               WHERE id = ? AND tutor_id = ?");
        if($stmt->execute([$reason, $request_id, $user_id])) {
            // Send notification to student
            $notification = "Your tutoring request has been rejected by tutor " . $user['full_name'] . " with reason: " . $reason;
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, related_url) 
                                   VALUES (?, ?, ?, ?)");
            $student_id = get_request_student_id($conn, $request_id);
            $stmt->execute([$student_id, 'Tutoring Request Rejected', $notification, 'my_requests.php']);
            
            $_SESSION['success'] = "Request rejected successfully and student notified";
        }
    }
    elseif(isset($_POST['complete_request'])) {
        $request_id = (int)$_POST['request_id'];
        
        $stmt = $conn->prepare("UPDATE tutoring_requests 
                               SET status = 'completed', updated_at = NOW() 
                               WHERE id = ? AND tutor_id = ?");
        if($stmt->execute([$request_id, $user_id])) {
            $_SESSION['success'] = "Session marked as completed successfully";
        }
    }
    
    header("Location: tutor_dashboard.php");
    exit();
}

$page_title = "Tutor Dashboard - " . SITE_NAME;

?>
<style>
     :root {
            --utas-blue: #003366;
            --utas-light-blue: #0066cc;
            --utas-gold: #ff6600;
            --utas-white: #ffffff;
            --utas-light-gray: #f5f5f5;
            --utas-dark-gray: #333333;
        }
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: var(--utas-light-gray);
        color: var(--utas-dark-gray);
        line-height: 1.6;
    }
    
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1rem;
    }
    
    h2, h3 {
        color: var(--utas-blue);
    }
    
    .tutor-dashboard {
        padding: 2rem 0;
    }
    
    .dashboard-header {
        background-color: var(--utas-white);
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 2rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-left: 5px solid var(--utas-gold);
    }
    
    .dashboard-header h2 {
        margin-top: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .tutor-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1rem;
    }
    
    .tutor-badge {
        background-color: var(--utas-light-blue);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
    }
    
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        padding: 1rem;
        border-radius: 4px;
        margin-bottom: 1.5rem;
        border-left: 4px solid var(--utas-green);
    }
    
    .dashboard-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background-color: var(--utas-white);
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 5px;
        height: 100%;
        background-color: var(--utas-gold);
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: bold;
        color: var(--utas-blue);
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        color: var(--utas-dark-gray);
        margin-bottom: 0.5rem;
    }
    
    .stat-icon {
        font-size: 1.5rem;
        color: var(--utas-light-blue);
    }
    
    .requests-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }
    
    .tab-btn {
        padding: 0.5rem 1rem;
        background-color: var(--utas-light-gray);
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .tab-btn.active {
        background-color: var(--utas-blue);
        color: white;
    }
    
    .tab-btn:hover:not(.active) {
        background-color: #e0e0e0;
    }
    
    .requests-container {
        background-color: var(--utas-white);
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .no-requests {
        text-align: center;
        padding: 2rem;
        color: var(--utas-dark-gray);
    }
    
    .no-requests i {
        font-size: 3rem;
        color: var(--utas-light-blue);
        margin-bottom: 1rem;
    }
    
    .requests-list {
        display: none;
    }
    
    .requests-list.active {
        display: block;
    }
    
    .request-card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }
    
    .request-card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .request-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .request-date {
        color: #666;
        font-size: 0.9rem;
    }
    
    .request-body {
        display: flex;
        gap: 2rem;
    }
    
    .request-info {
        flex: 2;
    }
    
    .request-info p {
        margin-bottom: 0.5rem;
    }
    
    .request-actions {
        flex: 1;
    }
    
    .form-group {
        margin-bottom: 1rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }
    
    .form-group textarea {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        resize: vertical;
        min-height: 60px;
    }
    
    .btn {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .btn-primary {
        background-color: var(--utas-blue);
        color: white;
    }
    
    .btn-primary:hover {
        background-color: #002244;
    }
    
    .btn-secondary {
        background-color: var(--utas-light-blue);
        color: white;
    }
    
    .btn-secondary:hover {
        background-color: #0055aa;
    }
    
    .btn-success {
        background-color: var(--utas-green);
        color: white;
    }
    
    .btn-success:hover {
        background-color: #3d8b40;
    }
    
    .btn-danger {
        background-color: var(--utas-red);
        color: white;
    }
    
    .btn-danger:hover {
        background-color: #d32f2f;
    }
    
    .status-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .status-badge.accepted {
        background-color: #d4edda;
        color: #155724;
    }
    
    .status-badge.completed {
        background-color: #cce5ff;
        color: #004085;
    }
    
    .status-badge.rejected {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    @media (max-width: 768px) {
        .request-body {
            flex-direction: column;
            gap: 1rem;
        }
        
        .dashboard-stats {
            grid-template-columns: 1fr;
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
<section class="tutor-dashboard">
    <div class="container">
        <div class="dashboard-header">
            <h2><i class="fas fa-chalkboard-teacher"></i> Tutor Dashboard</h2>
            <div class="tutor-info">
                <div class="tutor-badge">
                    <i class="fas fa-user-tie"></i>
                    <span>Approved Tutor</span>
                </div>
                <p>Welcome <?php echo $user['full_name']; ?>, here you can manage your tutoring requests.</p>
            </div>
        </div>
        
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <p><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
            </div>
        <?php endif; ?>
        
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-value"><?php echo count(array_filter($requests, function($r) { return $r['status'] == 'pending'; })); ?></div>
                <div class="stat-label">New Requests</div>
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo count(array_filter($requests, function($r) { return $r['status'] == 'accepted'; })); ?></div>
                <div class="stat-label">Accepted Sessions</div>
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo count(array_filter($requests, function($r) { return $r['status'] == 'completed'; })); ?></div>
                <div class="stat-label">Completed Sessions</div>
                <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
            </div>
        </div>
        
        <div class="requests-tabs">
            <button class="tab-btn active" data-tab="pending">New Requests</button>
            <button class="tab-btn" data-tab="accepted">Accepted</button>
            <button class="tab-btn" data-tab="completed">Completed</button>
            <button class="tab-btn" data-tab="rejected">Rejected</button>
        </div>
        
        <div class="requests-container">
            <?php if(empty($requests)): ?>
                <div class="no-requests">
                    <i class="fas fa-calendar-times"></i>
                    <p>You don't have any tutoring requests at this time.</p>
                </div>
            <?php else: ?>
                <!-- Pending Requests -->
                <div class="requests-list active" id="pending-tab">
                    <?php foreach($requests as $request): ?>
                        <?php if($request['status'] == 'pending'): ?>
                            <div class="request-card">
                                <div class="request-header">
                                    <h3>Request for <?php echo $request['course_name']; ?></h3>
                                    <span class="request-date"><?php echo date('Y/m/d H:i', strtotime($request['created_at'])); ?></span>
                                </div>
                                <div class="request-body">
                                    <div class="request-info">
                                        <p><strong>Student:</strong> <?php echo $request['student_name']; ?> (<?php echo $request['student_id']; ?>)</p>
                                        <p><strong>Session Type:</strong> <?php echo ($request['session_type'] == 'online') ? 'Online' : 'In-Person'; ?></p>
                                        <?php if($request['session_type'] == 'in_person'): ?>
                                            <p><strong>Meeting Location:</strong> <?php echo $request['meeting_location']; ?></p>
                                        <?php endif; ?>
                                        <p><strong>Proposed Time:</strong> <?php echo date('Y/m/d H:i', strtotime($request['meeting_time'])); ?></p>
                                        <p><strong>Duration:</strong> <?php echo $request['duration']; ?> minutes</p>
                                        <?php if(!empty($request['student_notes'])): ?>
                                            <p><strong>Student Notes:</strong> <?php echo $request['student_notes']; ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="request-actions">
                                        <form method="post" class="accept-form">
                                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                            <div class="form-group">
                                                <label for="tutor_notes_<?php echo $request['id']; ?>">Notes for student (optional)</label>
                                                <textarea id="tutor_notes_<?php echo $request['id']; ?>" name="tutor_notes" rows="2"></textarea>
                                            </div>
                                            <button type="submit" name="accept_request" class="btn btn-primary">
                                                <i class="fas fa-check"></i> Accept Request
                                            </button>
                                        </form>
                                        <form method="post" class="reject-form">
                                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                            <div class="form-group">
                                                <label for="reject_reason_<?php echo $request['id']; ?>">Rejection Reason</label>
                                                <textarea id="reject_reason_<?php echo $request['id']; ?>" name="reject_reason" rows="2" required></textarea>
                                            </div>
                                            <button type="submit" name="reject_request" class="btn btn-danger">
                                                <i class="fas fa-times"></i> Reject Request
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                
                <!-- Accepted Requests -->
                <div class="requests-list" id="accepted-tab">
                    <?php foreach($requests as $request): ?>
                        <?php if($request['status'] == 'accepted'): ?>
                            <div class="request-card">
                                <div class="request-header">
                                    <h3>Session for <?php echo $request['course_name']; ?></h3>
                                    <span class="request-date"><?php echo date('Y/m/d H:i', strtotime($request['meeting_time'])); ?></span>
                                </div>
                                <div class="request-body">
                                    <div class="request-info">
                                        <p><strong>Student:</strong> <?php echo $request['student_name']; ?> (<?php echo $request['student_id']; ?>)</p>
                                        <p><strong>Session Type:</strong> <?php echo ($request['session_type'] == 'online') ? 'Online' : 'In-Person'; ?></p>
                                        <?php if($request['session_type'] == 'in_person'): ?>
                                            <p><strong>Meeting Location:</strong> <?php echo $request['meeting_location']; ?></p>
                                        <?php endif; ?>
                                        <p><strong>Duration:</strong> <?php echo $request['duration']; ?> minutes</p>
                                        <?php if(!empty($request['student_notes'])): ?>
                                            <p><strong>Student Notes:</strong> <?php echo $request['student_notes']; ?></p>
                                        <?php endif; ?>
                                        <?php if(!empty($request['tutor_notes'])): ?>
                                            <p><strong>Your Notes:</strong> <?php echo $request['tutor_notes']; ?></p>
                                        <?php endif; ?>
                                        <p><strong>Status:</strong> <span class="status-badge accepted">Accepted</span></p>
                                    </div>
                                    <div class="request-actions">
                                        <?php if(strtotime($request['meeting_time']) > time()): ?>
                                            <a href="contact_student.php?student_id=<?php echo $request['student_id']; ?>" class="btn btn-secondary">
                                                <i class="fas fa-envelope"></i> Contact Student
                                            </a>
                                        <?php else: ?>
                                            <form method="post">
                                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                <button type="submit" name="complete_request" class="btn btn-success">
                                                    <i class="fas fa-check-circle"></i> Mark as Completed
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                
                <!-- Completed Sessions -->
                <div class="requests-list" id="completed-tab">
                    <?php foreach($requests as $request): ?>
                        <?php if($request['status'] == 'completed'): ?>
                            <div class="request-card">
                                <div class="request-header">
                                    <h3>Session for <?php echo $request['course_name']; ?></h3>
                                    <span class="request-date">Completed on <?php echo date('Y/m/d', strtotime($request['meeting_time'])); ?></span>
                                </div>
                                <div class="request-body">
                                    <div class="request-info">
                                        <p><strong>Student:</strong> <?php echo $request['student_name']; ?> (<?php echo $request['student_id']; ?>)</p>
                                        <p><strong>Session Type:</strong> <?php echo ($request['session_type'] == 'online') ? 'Online' : 'In-Person'; ?></p>
                                        <p><strong>Duration:</strong> <?php echo $request['duration']; ?> minutes</p>
                                        <?php if(!empty($request['tutor_notes'])): ?>
                                            <p><strong>Your Notes:</strong> <?php echo $request['tutor_notes']; ?></p>
                                        <?php endif; ?>
                                        <p><strong>Status:</strong> <span class="status-badge completed">Completed</span></p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                
                <!-- Rejected Requests -->
                <div class="requests-list" id="rejected-tab">
                    <?php foreach($requests as $request): ?>
                        <?php if($request['status'] == 'rejected'): ?>
                            <div class="request-card">
                                <div class="request-header">
                                    <h3>Request for <?php echo $request['course_name']; ?></h3>
                                    <span class="request-date"><?php echo date('Y/m/d H:i', strtotime($request['created_at'])); ?></span>
                                </div>
                                <div class="request-body">
                                    <div class="request-info">
                                        <p><strong>Student:</strong> <?php echo $request['student_name']; ?> (<?php echo $request['student_id']; ?>)</p>
                                        <p><strong>Rejection Reason:</strong> <?php echo $request['tutor_notes']; ?></p>
                                        <p><strong>Status:</strong> <span class="status-badge rejected">Rejected</span></p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Request display tabs
    const tabBtns = document.querySelectorAll('.tab-btn');
    const requestLists = document.querySelectorAll('.requests-list');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Update active tabs
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Update displayed content
            requestLists.forEach(list => {
                list.classList.remove('active');
                if(list.id === `${tabId}-tab`) {
                    list.classList.add('active');
                }
            });
        });
    });
});
</script>

