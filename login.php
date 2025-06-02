<?php
require_once 'config.php';
require_once 'functions.php';

// بدء الجلسة إذا لم تكن بدأت
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$errors = [];
$page_title = "Login - " . SITE_NAME;

// معالجة بيانات تسجيل الدخول إذا تم إرسالها
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $university_id = clean_input($_POST['university_id'] ?? '');
    $password = clean_input($_POST['password'] ?? '');

    if (empty($university_id)) $errors[] = "يجب إدخال رقم الجامعة";
    if (empty($password)) $errors[] = "يجب إدخال كلمة المرور";

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("SELECT * FROM users WHERE university_id = ?");
            $stmt->execute([$university_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_type'] = $user['user_type'];
                
                header("Location: " . ($user['user_type'] === 'teacher' ? 'teacher.php' : 'dashboard.php'));
                exit();
            } else {
                $errors[] = "رقم الجامعة أو كلمة المرور غير صحيحة";
            }
        } catch (PDOException $e) {
            $errors[] = "حدث خطأ في النظام. يرجى المحاولة لاحقاً";
            error_log("Login error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>

<style>
    /* Base Styles */
:root {
    --primary-color: #0056b3; /* UTAS Blue */
    --secondary-color: #e67e22; /* UTAS Orange */
    --dark-color: #2c3e50;
    --light-color: #ecf0f1;
    --success-color: #27ae60;
    --danger-color: #e74c3c;
    --warning-color: #f39c12;
    --info-color: #3498db;
    --white: #ffffff;
    --gray: #f5f5f5;
    --dark-gray: #333333;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
    background-color: var(--gray);
    color: var(--dark-gray);
    line-height: 1.6;
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

/* Login Container */
.login-container {
    max-width: 500px;
    margin: 50px auto;
    padding: 30px;
    background-color: var(--white);
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.login-container h2 {
    text-align: center;
    margin-bottom: 30px;
    color: var(--primary-color);
}

/* Form Styles */
.login-form-group {
    margin-bottom: 20px;
}

.login-form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
}

.login-form-group input,
.login-form-group select {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
    transition: border 0.3s ease;
}

.login-form-group input:focus,
.login-form-group select:focus {
    border-color: var(--primary-color);
    outline: none;
}

.login-form-group small {
    display: block;
    margin-top: 5px;
    font-size: 0.8rem;
    color: #666;
}

/* Button Styles */
.btn {
    display: inline-block;
    padding: 12px 20px;
    border-radius: 5px;
    font-weight: bold;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    width: 100%;
}

.btn-primary {
    background-color: var(--primary-color);
    color: var(--white);
}

.btn-primary:hover {
    background-color: #004494;
}

/* Alert Messages */
.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
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

.alert ul {
    margin-left: 20px;
}

.alert li {
    margin-bottom: 5px;
}

/* Auth Link */
.auth-link {
    text-align: center;
    margin-top: 20px;
}

.auth-link a {
    color: var(--primary-color);
    font-weight: 500;
}

.auth-link a:hover {
    text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 768px) {
    .header-container {
        flex-direction: column;
        gap: 15px;
    }
    
    nav ul {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .login-container {
        margin: 30px 15px;
        padding: 20px;
    }
}

@media (max-width: 480px) {
    nav ul {
        gap: 10px;
    }
    
    nav ul li a {
        padding: 6px 10px;
        font-size: 0.9rem;
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

</style>
<!DOCTYPE html>
<html lang="en">
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
   
</head>
<header>
        <div class="container header-container">
            <div class="logo">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT13qZq8ttUi44qMBaoT4-aloxfJL712OeWyQ&s" alt="UTAS Logo">
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="services.php">Services</a></li>
                    <?php if(is_logged_in()): ?>
                        <li><a href="dashboard.php">Dashboard</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
                    </header>
<section class="auth-form">
    <div class="login-container">
        <h2>Login</h2>
        
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
            <div class="login-form-group">
                <label for="university_id">University ID</label>
                <input type="text" id="university_id" name="university_id" required>
            </div>
            
            <div class="login-form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Login</button>
            
            <p class="auth-link">Don't have an account? <a href="register.php">Register new account</a></p>
        </form>
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