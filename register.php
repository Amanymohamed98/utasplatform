<?php
require_once 'config.php';
require_once 'functions.php';

if(is_logged_in()) {
    header("Location: dashboard.php");
    exit();
}

$errors = [];
$success = false;
$show_tutor_form = false;

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle tutor registration form submission
    if(isset($_POST['register_tutor'])) {
        $user_id = clean_input($_POST['user_id']);
        $course_name = clean_input($_POST['course_name']);
        $course_id = clean_input($_POST['course_id']);
        $bank_account = clean_input($_POST['bank_account']);
        $teaching_method = clean_input($_POST['teaching_method']);
        $available_days = isset($_POST['available_days']) ? $_POST['available_days'] : [];
        $available_hours = clean_input($_POST['available_hours']);
        
        // Validate tutor data
        if(empty($course_name)) $errors[] = "Course name is required";
        if(empty($course_id)) $errors[] = "Course ID is required";
        if(empty($bank_account)) $errors[] = "Bank account is required";
        if(empty($teaching_method)) $errors[] = "Teaching method is required";
        if(empty($available_days)) $errors[] = "Please select available days";
        if(empty($available_hours)) $errors[] = "Available hours are required";
        
        if(empty($errors)) {
            $available_days_str = implode(",", $available_days);
            
            $stmt = $conn->prepare("UPDATE tutor_profiles SET 
                course_name = ?, 
                course_id = ?, 
                bank_account = ?, 
                teaching_method = ?, 
                available_days = ?, 
                available_hours = ?,
                is_approved = 0 
                WHERE user_id = ?");
            
            if($stmt->execute([
                $course_name, 
                $course_id, 
                $bank_account, 
                $teaching_method, 
                $available_days_str, 
                $available_hours,
                $user_id
            ])) {
                $success = true;
                header("Location: dashboard.php");
                exit();
            } else {
                $errors[] = "An error occurred during tutor registration, please try again later";
            }
        }
    }
    // Handle main registration form submission
    else {
        // Clean inputs
        $university_id = clean_input($_POST['university_id']);
        $email = clean_input($_POST['email']);
        $password = clean_input($_POST['password']);
        $confirm_password = clean_input($_POST['confirm_password']);
        $specialization = clean_input($_POST['specialization']);
        $phone = clean_input($_POST['phone']);
        $gpa = isset($_POST['gpa']) && !empty($_POST['gpa']) ? clean_input($_POST['gpa']) : null;
        $full_name = clean_input($_POST['full_name']);
        $user_type = clean_input($_POST['user_type']);
        $register_as_tutor = isset($_POST['register_as_tutor']) ? clean_input($_POST['register_as_tutor']) : 0;

        // Validate data
        if(empty($university_id)) $errors[] = "University ID is required";
        if(empty($email)) $errors[] = "Email is required";
        elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
        if(empty($password)) $errors[] = "Password is required";
        elseif(strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
        if($password !== $confirm_password) $errors[] = "Passwords do not match";
        if(empty($specialization)) $errors[] = "Specialization is required";
        if(empty($full_name)) $errors[] = "Full name is required";
        if(empty($user_type)) $errors[] = "Please select user type";

        // Additional validation for students
        if($user_type == 'student' && $gpa === null) {
            $errors[] = "GPA is required for students";
        }
        elseif($user_type == 'student' && $register_as_tutor && $gpa < 3.3) {
            $errors[] = "To register as a tutor, your GPA must be 3.3 or higher";
        }

        // Teachers shouldn't have GPA
        if($user_type == 'teacher' && isset($_POST['gpa']) && !empty($_POST['gpa'])) {
            $errors[] = "Teachers should not have a GPA";
        }

        // Check if user with same university ID or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE university_id = ? OR email = ?");
        $stmt->execute([$university_id, $email]);
        if($stmt->rowCount() > 0) {
            $errors[] = "University ID or email already registered";
        }

        // If no errors, register the user
        if(empty($errors)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $is_tutor = ($user_type == 'student' && $register_as_tutor && $gpa >= 3.3) ? 1 : 0;

            $stmt = $conn->prepare("INSERT INTO users 
                (university_id, email, password, specialization, phone, gpa, full_name, is_tutor, user_type) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if($stmt->execute([
                $university_id, 
                $email, 
                $hashed_password, 
                $specialization, 
                $phone, 
                $user_type == 'student' ? $gpa : NULL,
                $full_name, 
                $is_tutor,
                $user_type
            ])) {
                $user_id = $conn->lastInsertId();
                
                // Create tutor profile if user is tutor
                if($is_tutor) {
                    $stmt = $conn->prepare("INSERT INTO tutor_profiles (user_id) VALUES (?)");
                    $stmt->execute([$user_id]);
                    
                    // Set flag to show tutor registration form
                    $show_tutor_form = true;
                    $_SESSION['new_tutor_id'] = $user_id;
                } else {
                    $success = true;
                }
            } else {
                $errors[] = "An error occurred during registration, please try again later";
            }
        }
    }
}

$page_title = "New Registration - " . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <style>
        /* UTAS Color Scheme */
        :root {
            --utas-primary: #002664; /* Navy Blue */
            --utas-secondary: #D40000; /* Red */
            --utas-accent: #00AEEF; /* Light Blue */
            --utas-light: #FFFFFF; /* White */
            --utas-gray: #f5f5f5;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--utas-gray);
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        header {
            background-color: var(--utas-light);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
        }

        .logo img {
            height: 50px;
        }

        .auth-form {
            padding: 40px 0;
        }

        .login-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 30px;
            background-color: var(--utas-light);
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
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

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--utas-primary);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .form-group input:focus,
        .form-group select:focus {
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

        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .checkbox-group label {
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
            width: 100%;
        }

        .btn-secondary {
            background-color: var(--utas-secondary);
        }

        .btn:hover {
            opacity: 0.9;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
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

        .auth-link {
            text-align: center;
            margin-top: 20px;
        }

        .auth-link a {
            color: var(--utas-primary);
            font-weight: 500;
        }

        footer {
            background-color: var(--utas-primary);
            color: white;
            padding: 30px 0;
            text-align: center;
        }

        .tutor-form {
            display: <?php echo $show_tutor_form ? 'block' : 'none'; ?>;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #ddd;
        }

        @media (max-width: 768px) {
            .radio-group, .checkbox-group {
                flex-direction: column;
                gap: 10px;
            }
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

    <section class="auth-form">
        <div class="login-container">
            <?php if($show_tutor_form): ?>
                <h2>Complete Tutor Registration</h2>
                
                <?php if(!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                    <input type="hidden" name="user_id" value="<?php echo $_SESSION['new_tutor_id']; ?>">
                    
                    <div class="form-group">
                        <label for="course_name">Course Name</label>
                        <input type="text" id="course_name" name="course_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="course_id">Course ID</label>
                        <input type="text" id="course_id" name="course_id" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="bank_account">Bank Account Number</label>
                        <input type="text" id="bank_account" name="bank_account" required>
                        <small>This will be used to send your monthly salary</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Teaching Method</label>
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="teaching_method" value="online" required> Online
                            </label>
                            <label>
                                <input type="radio" name="teaching_method" value="in_person"> In Person
                            </label>
                            <label>
                                <input type="radio" name="teaching_method" value="both"> Both
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Available Days</label>
                        <div class="checkbox-group">
                            <?php 
                            $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                            foreach($days as $day): ?>
                                <label>
                                    <input type="checkbox" name="available_days[]" value="<?php echo $day; ?>"> <?php echo $day; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="available_hours">Available Hours</label>
                        <input type="text" id="available_hours" name="available_hours" placeholder="e.g. 9:00 AM - 5:00 PM" required>
                    </div>
                    
                    <button type="submit" name="register_tutor" class="btn">Complete Tutor Registration</button>
                </form>
                
            <?php elseif($success): ?>
                <div class="alert alert-success">
                    <p>Registration successful! You can now <a href="login.php">login</a>.</p>
                </div>
            <?php else: ?>
                <h2>Create New Account</h2>
                
                <?php if(!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" id="registrationForm">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="university_id">University ID</label>
                        <input type="text" id="university_id" name="university_id" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">University Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="specialization">Specialization</label>
                        <select id="specialization" name="specialization" required>
                            <option value="">Select Specialization</option>
                            <option value="IT">Information Technology</option>
                            <option value="Engineering">Engineering</option>
                            <option value="Business">Business</option>
                            <option value="Science">Science</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Register As</label>
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="user_type" value="student" checked> Student
                            </label>
                            <label>
                                <input type="radio" name="user_type" value="teacher"> Teacher
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group" id="gpa-field">
                        <label for="gpa">GPA <span id="gpa-required">(Required for student)</span></label>
                        <input type="number" id="gpa" name="gpa" step="0.01" min="0" max="4">
                    </div>
                    
                    <div class="form-group" id="tutor-option">
                        <label>
                            <input type="checkbox" id="register_as_tutor" name="register_as_tutor" value="1"> 
                            Register as Tutor (GPA 3.3 or higher required)
                        </label>
                    </div>
                    
                    <button type="submit" class="btn">Register Account</button>
                    
                    <p class="auth-link">Already have an account? <a href="login.php">Login here</a></p>
                </form>
            <?php endif; ?>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> University of Technology and Applied Sciences. All rights reserved.</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userTypeRadios = document.querySelectorAll('input[name="user_type"]');
            const gpaField = document.getElementById('gpa-field');
            const tutorOption = document.getElementById('tutor-option');
            const gpaInput = document.getElementById('gpa');
            const registrationForm = document.getElementById('registrationForm');
            
            // Initial state
            updateFieldsVisibility();
            
            userTypeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    updateFieldsVisibility();
                });
            });
            
            // GPA change listener for tutor option
            if(gpaInput) {
                gpaInput.addEventListener('input', function() {
                    const gpa = parseFloat(this.value) || 0;
                    const tutorCheckbox = document.getElementById('register_as_tutor');
                    
                    if(gpa >= 3.3) {
                        tutorCheckbox.disabled = false;
                    } else {
                        tutorCheckbox.checked = false;
                        tutorCheckbox.disabled = true;
                    }
                });
            }
            
            // Form submission validation
            if(registrationForm) {
                registrationForm.addEventListener('submit', function(e) {
                    const userType = document.querySelector('input[name="user_type"]:checked').value;
                    const gpaValue = gpaInput ? gpaInput.value : '';
                    
                    if(userType === 'teacher' && gpaValue) {
                        e.preventDefault();
                        alert('Teachers should not have a GPA. Please remove the GPA value.');
                        return false;
                    }
                    
                    if(userType === 'student' && !gpaValue) {
                        e.preventDefault();
                        alert('GPA is required for students');
                        return false;
                    }
                    
                    return true;
                });
            }
            
            function updateFieldsVisibility() {
                const isStudent = document.querySelector('input[name="user_type"]:checked').value === 'student';
                
                gpaField.style.display = isStudent ? 'block' : 'none';
                tutorOption.style.display = isStudent ? 'block' : 'none';
                
                if(!isStudent) {
                    if(gpaInput) gpaInput.value = '';
                    const tutorCheckbox = document.getElementById('register_as_tutor');
                    if(tutorCheckbox) {
                        tutorCheckbox.checked = false;
                        tutorCheckbox.disabled = true;
                    }
                }
            }
        });
    </script>
</body>
</html>