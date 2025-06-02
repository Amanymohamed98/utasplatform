<?php
require_once 'config.php';
require_once 'functions.php';

if(!is_logged_in()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = get_user_data($conn, $user_id);

$tutor_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$tutor = null;

if($tutor_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND is_tutor = 1");
    $stmt->execute([$tutor_id]);
    $tutor = $stmt->fetch(PDO::FETCH_ASSOC);
}

if(!$tutor) {
    header("Location: services.php?type=tutors");
    exit();
}

// تعريف أسعار الجلسات بالريال العماني
$online_rate = 3.85; // 3.85 ريال عماني للساعة للجلسات الاونلاين (ما يعادل ~10 دولار)
$in_person_rate = 5.78; // 5.78 ريال عماني للساعة للجلسات الحضورية (ما يعادل ~15 دولار)

$errors = [];
$success = false;

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_name = clean_input($_POST['course_name']);
    $session_type = clean_input($_POST['session_type']);
    $meeting_time = clean_input($_POST['meeting_time']);
    $duration = (int)$_POST['duration'];
    $notes = clean_input($_POST['notes']);
    $payment_method = clean_input($_POST['payment_method']);
    
    // Validate input data
    if(empty($course_name)) $errors[] = "Course name is required";
    if(empty($meeting_time)) $errors[] = "Meeting time is required";
    if($duration < 30 || $duration > 180) $errors[] = "Duration must be between 30 and 180 minutes";
    if(empty($payment_method)) $errors[] = "Payment method is required";
    
    if(empty($errors)) {
        $meeting_location = ($session_type == 'in_person') ? clean_input($_POST['meeting_location']) : 'Online';
        
        // حساب السعر بناء على نوع الجلسة والمدة
        $hourly_rate = ($session_type == 'online') ? $online_rate : $in_person_rate;
        $total_price = round(($duration / 60) * $hourly_rate, 2);
        $currency = 'OMR'; // تغيير العملة إلى الريال العماني
        
        // بدء transaction
        $conn->beginTransaction();
        
        try {
            // إدخال بيانات الطلب
            $stmt = $conn->prepare("INSERT INTO tutoring_requests 
                                  (student_id, tutor_id, course_name, session_type, meeting_location, 
                                  meeting_time, duration, student_notes) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $tutor_id, $course_name, $session_type, $meeting_location, 
                          $meeting_time, $duration, $notes]);
            
            $request_id = $conn->lastInsertId();
            
            // إضافة سجل الدفع في جدول payments
            $stmt = $conn->prepare("INSERT INTO payments 
                                  (request_id, student_id, tutor_id, amount, currency, payment_method, status) 
                                  VALUES (?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$request_id, $user_id, $tutor_id, $total_price, $currency, $payment_method]);
            
            // Send notification to tutor
            $notification = "You have a new tutoring request from student " . $user['full_name'] . " for " . $total_price . " OMR";
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, related_url) VALUES (?, ?, ?, ?)");
            $stmt->execute([$tutor_id, 'New Tutoring Request', $notification, 'tutor_requests.php']);
            
            // إتمام العملية
            $conn->commit();
            $success = true;
            
        } catch (PDOException $e) {
            // التراجع عن العملية في حالة خطأ
            $conn->rollBack();
            $errors[] = "An error occurred: " . $e->getMessage();
        }
    }
}

$page_title = "Private Tutor Request - " . SITE_NAME;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <style>
        :root {
    --utas-primary: #002664; /* Navy Blue */
    --utas-secondary: #D40000; /* Red */
    --utas-accent: #00AEEF; /* Light Blue */
    --utas-light: #FFFFFF; /* White */
}


.tutor-request {
    padding: 40px 0;
    background-color: #f8f9fa;
}

.container {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 20px;
}

h2 {
    color: var(--utas-primary);
    text-align: center;
    margin-bottom: 30px;
    position: relative;
}

h2:after {
    content: '';
    display: block;
    width: 80px;
    height: 3px;
    background: var(--utas-secondary);
    margin: 15px auto 0;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    text-align: center;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-danger ul {
    margin: 0;
    padding-left: 20px;
}

.request-form {
    background-color: var(--utas-light);
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.tutor-info {
    background-color: #f1f5fd;
    padding: 20px;
    border-radius: 5px;
    margin-bottom: 25px;
    border-left: 4px solid var(--utas-primary);
}

.tutor-info h3 {
    color: var(--utas-primary);
    margin-top: 0;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--utas-primary);
}

.form-group input[type="text"],
.form-group input[type="datetime-local"],
.form-group input[type="number"],
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

.form-group input:focus,
.form-group textarea:focus {
    border-color: var(--utas-accent);
    outline: none;
    box-shadow: 0 0 0 2px rgba(0, 174, 239, 0.2);
}

.radio-group {
    display: flex;
    gap: 20px;
    margin-top: 10px;
}

.radio-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: normal;
    cursor: pointer;
}

.btn {
    display: inline-block;
    padding: 12px 24px;
    background-color: var(--utas-primary);
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s;
    text-decoration: none;
    text-align: center;
}

.btn:hover {
    background-color: var(--utas-secondary);
}

.btn-primary {
    background-color: var(--utas-primary);
    width: 100%;
    margin-top: 10px;
}

.price-display {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin: 20px 0;
    border-left: 4px solid var(--utas-accent);
}

.price-display p {
    margin: 5px 0;
    font-size: 16px;
}

.price-display .total-price {
    font-weight: bold;
    font-size: 18px;
    color: var(--utas-primary);
}

@media (max-width: 768px) {
    .request-form {
        padding: 20px;
    }
    
    .radio-group {
        flex-direction: column;
        gap: 10px;
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
</head>
<body>
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

<section class="tutor-request">
    <div class="container">
        <h2>Private Tutoring Request</h2>
        
        <?php if($success): ?>
            <div class="alert alert-success">
                <p>Your request has been submitted successfully! The tutor will contact you soon.</p>
                <a href="services.php?type=tutors" class="btn">Back to Tutors List</a>
            </div>
        <?php else: ?>
            <div class="request-form">
                <div class="tutor-info">
                    <h3>Tutor: <?php echo $tutor['full_name']; ?></h3>
                    <p><strong>Specialization:</strong> <?php echo ($tutor['specialization'] == 'IT') ? 'Information Technology' : 'Engineering'; ?></p>
                    <p><strong>GPA:</strong> <?php echo $tutor['gpa']; ?></p>
                    <p><strong>Online Rate:</strong> <?php echo $online_rate; ?> OMR per hour</p>
                    <p><strong>In-Person Rate:</strong> <?php echo $in_person_rate; ?> OMR per hour</p>
                </div>
                
                <?php if(!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="post" id="tutorRequestForm">
                    <div class="form-group">
                        <label for="course_name">Course Name</label>
                        <input type="text" id="course_name" name="course_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Session Type</label>
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="session_type" value="online" checked> Online (<?php echo $online_rate; ?> OMR/hour)
                            </label>
                            <label>
                                <input type="radio" name="session_type" value="in_person"> In Person (<?php echo $in_person_rate; ?> OMR/hour)
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group" id="location-field" style="display:none;">
                        <label for="meeting_location">Meeting Location</label>
                        <input type="text" id="meeting_location" name="meeting_location" placeholder="Specify meeting location">
                    </div>
                    
                    <div class="form-group">
                        <label for="meeting_time">Meeting Time</label>
                        <input type="datetime-local" id="meeting_time" name="meeting_time" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="duration">Session Duration (minutes)</label>
                        <input type="number" id="duration" name="duration" min="30" max="180" value="60" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="">Select Payment Method</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="paypal">PayPal</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Additional Notes</label>
                        <textarea id="notes" name="notes" rows="3"></textarea>
                    </div>
                    
                    <div class="price-display">
                        <p>Price Calculation:</p>
                        <p id="rate-display">Rate: <?php echo $online_rate; ?> OMR/hour (Online)</p>
                        <p id="duration-display">Duration: 60 minutes</p>
                        <p class="total-price" id="total-price">Total Price: <?php echo $online_rate; ?> OMR</p>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </form>
            </div>
            
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const sessionTypeRadios = document.querySelectorAll('input[name="session_type"]');
                const locationField = document.getElementById('location-field');
                const durationInput = document.getElementById('duration');
                const rateDisplay = document.getElementById('rate-display');
                const durationDisplay = document.getElementById('duration-display');
                const totalPriceDisplay = document.getElementById('total-price');
                
                const onlineRate = <?php echo $online_rate; ?>;
                const inPersonRate = <?php echo $in_person_rate; ?>;
                
                // تحديث حقل الموقع بناء على نوع الجلسة
                sessionTypeRadios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        if(this.value === 'in_person') {
                            locationField.style.display = 'block';
                            rateDisplay.textContent = `Rate: ${inPersonRate} OMR/hour (In Person)`;
                        } else {
                            locationField.style.display = 'none';
                            rateDisplay.textContent = `Rate: ${onlineRate} OMR/hour (Online)`;
                        }
                        calculateTotalPrice();
                    });
                });
                
                // تحديث السعر عند تغيير المدة
                durationInput.addEventListener('input', function() {
                    durationDisplay.textContent = `Duration: ${this.value} minutes`;
                    calculateTotalPrice();
                });
                
                // دالة حساب السعر الإجمالي
                function calculateTotalPrice() {
                    const duration = parseInt(durationInput.value) || 60;
                    const sessionType = document.querySelector('input[name="session_type"]:checked').value;
                    const hourlyRate = (sessionType === 'online') ? onlineRate : inPersonRate;
                    const totalPrice = ((duration / 60) * hourlyRate).toFixed(2);
                    
                    totalPriceDisplay.textContent = `Total Price: ${totalPrice} OMR`;
                }
                
                // حساب السعر الأولي عند تحميل الصفحة
                calculateTotalPrice();
            });
            </script>
        <?php endif; ?>
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
</body>
</html>