<?php
require_once 'config.php';
require_once 'functions.php';

if(!is_logged_in()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = get_user_data($conn, $user_id);

$errors = [];
$success = false;

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = clean_input($_POST['full_name']);
    $email = clean_input($_POST['email']);
    $phone = clean_input($_POST['phone']);
    $password = clean_input($_POST['password']);
    $new_password = clean_input($_POST['new_password']);
    $confirm_password = clean_input($_POST['confirm_password']);

    // Validate input data
    if(empty($full_name)) $errors[] = "Full name is required";
    if(empty($email)) $errors[] = "Email is required";
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";

    // If new password is provided, validate it
    if(!empty($new_password)) {
        if(empty($password)) $errors[] = "Current password is required to change password";
        elseif(!password_verify($password, $user['password'])) $errors[] = "Current password is incorrect";
        if(strlen($new_password) < 6) $errors[] = "New password must be at least 6 characters";
        if($new_password !== $confirm_password) $errors[] = "Passwords do not match";
    }

    // If no errors, update the data
    if(empty($errors)) {
        $update_data = [
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone,
            'id' => $user_id
        ];

        $sql = "UPDATE users SET full_name = :full_name, email = :email, phone = :phone";
        
        if(!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql .= ", password = :password";
            $update_data['password'] = $hashed_password;
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $conn->prepare($sql);
        if($stmt->execute($update_data)) {
            $success = true;
            $user = get_user_data($conn, $user_id); // Refresh user data
        } else {
            $errors[] = "An error occurred while updating data, please try again later";
        }
    }
}

$page_title = "Profile - " . SITE_NAME;

?>
<!-- UTAS Color Theme CSS -->
<style>
:root {
    --utas-primary: #002664; /* UTAS navy blue */
    --utas-secondary: #D40000; /* UTAS red */
    --utas-accent: #00AEEF; /* UTAS light blue */
    --utas-light: #FFFFFF; /* White */
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
    color: #333;
    background-color: #f5f5f5;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.profile {
    background-color: var(--utas-light);
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    padding: 30px;
    margin-top: 20px;
}

.profile h2 {
    color: var(--utas-primary);
    border-bottom: 2px solid var(--utas-accent);
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.profile-content {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
}

.profile-info {
    flex: 1;
    min-width: 300px;
}

.profile-status {
    flex: 0 0 300px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: var(--utas-primary);
}

.form-group input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

.form-group input:focus {
    border-color: var(--utas-accent);
    outline: none;
    box-shadow: 0 0 0 2px rgba(0, 174, 239, 0.2);
}

.btn {
    display: inline-block;
    padding: 10px 20px;
    background-color: var(--utas-primary);
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s;
}

.btn:hover {
    background-color: var(--utas-secondary);
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

.status-card {
    background-color: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid var(--utas-primary);
}

.status-card h3 {
    color: var(--utas-primary);
    margin-top: 0;
}

.tutor-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background-color: var(--utas-accent);
    color: white;
    padding: 5px 10px;
    border-radius: 20px;
    font-weight: bold;
    margin: 10px 0;
}

@media (max-width: 768px) {
    .profile-content {
        flex-direction: column;
    }
    
    .profile-status {
        margin-top: 30px;
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
<section class="profile">
    <div class="container">
        <h2>My Profile</h2>
        
        <?php if($success): ?>
            <div class="alert alert-success">
                <p>Profile updated successfully!</p>
            </div>
        <?php elseif(!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="profile-content">
            <div class="profile-info">
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo $user['full_name']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="university_id">Student ID</label>
                        <input type="text" id="university_id" value="<?php echo $user['university_id']; ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="specialization">Major</label>
                        <input type="text" id="specialization" value="<?php echo ($user['specialization'] == 'IT') ? 'Information Technology' : 'Engineering'; ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo $user['phone']; ?>">
                    </div>
                    
                    <?php if($user['gpa']): ?>
                        <div class="form-group">
                            <label for="gpa">GPA</label>
                            <input type="text" id="gpa" value="<?php echo $user['gpa']; ?>" disabled>
                        </div>
                    <?php endif; ?>
                    
                    <h3>Change Password</h3>
                    
                    <div class="form-group">
                        <label for="password">Current Password</label>
                        <input type="password" id="password" name="password">
                        <small>Leave blank if you don't want to change password</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
            
            <div class="profile-status">
                <div class="status-card">
                    <h3>Account Status</h3>
                    <p><strong>Registration Date:</strong> <?php echo date('Y/m/d', strtotime($user['created_at'])); ?></p>
                    
                    <?php if($user['is_tutor']): ?>
                        <div class="tutor-badge">
                            <i class="fas fa-user-tie"></i>
                            <span>Verified Tutor</span>
                        </div>
                        <p>You can offer tutoring services to other students.</p>
                    <?php else: ?>
                        <p>Regular Student</p>
                        <?php if($user['gpa'] && $user['gpa'] >= 3.3): ?>
                            <p>Your GPA qualifies you to become a tutor. <a href="become_tutor.php">Apply now</a></p>
                        <?php elseif($user['gpa']): ?>
                            <p>To become a tutor, your GPA must be 3.3 or higher.</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
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
