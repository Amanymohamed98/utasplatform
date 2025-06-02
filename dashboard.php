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

$page_title = "Dashboard - " . SITE_NAME;

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
        color: #333;
    }
    
    .dashboard {
        padding: 2rem 0;
    }
    
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1rem;
    }
    
    h2, h3 {
        color: var(--utas-blue);
    }
    
    .dashboard-welcome {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: var(--utas-white);
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
        border-left: 5px solid var(--utas-gold);
    }
    
    .welcome-message p {
        margin-bottom: 0.5rem;
    }
    
    .user-avatar {
        font-size: 5rem;
        color: var(--utas-light-blue);
    }
    
    .dashboard-sections {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .dashboard-card {
        background-color: var(--utas-white);
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    
    .dashboard-card:hover {
        transform: translateY(-5px);
    }
    
    .dashboard-card h3 {
        margin-top: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .dashboard-card p {
        margin-bottom: 1.5rem;
    }
    
    .btn {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-primary {
        background-color: var(--utas-blue);
        color: white;
        border: 1px solid var(--utas-blue);
    }
    
    .btn-primary:hover {
        background-color: #002244;
        border-color: #002244;
    }
    
    .btn-secondary {
        background-color: var(--utas-light-blue);
        color: white;
        border: 1px solid var(--utas-light-blue);
    }
    
    .btn-secondary:hover {
        background-color: #0055aa;
        border-color: #0055aa;
    }
    
    .tutor-panel {
        background-color: var(--utas-white);
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-left: 5px solid var(--utas-gold);
        margin-top: 2rem;
    }
    
    .tutor-panel h3 {
        display: flex;
        align-items: center;
        gap: 0.5rem;
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
<section class="dashboard">
    <div class="container">
        <h2>Welcome, <?php echo $user['full_name']; ?></h2>
        
        <div class="dashboard-welcome">
            <div class="welcome-message">
                <p>Through this platform, you can access all available services for students of the University of Technology and Applied Sciences.</p>
                <p>Your current specialization: <strong><?php echo ($specialization == 'IT') ? 'Information Technology' : 'Engineering'; ?></strong></p>
            </div>
            <div class="user-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
        </div>
        
        <div class="dashboard-sections">
            <div class="dashboard-card">
                <h3><i class="fas fa-book-open"></i> Study Materials</h3>
                <p>Browse study materials, summaries, and past exam questions for your specialization.</p>
                <a href="services.php?type=materials" class="btn btn-secondary">View Materials</a>
            </div>
            
            <div class="dashboard-card">
                <h3><i class="fas fa-chalkboard-teacher"></i> Private Tutoring</h3>
                <p>Get private tutoring from outstanding students in your specialization.</p>
                <a href="services.php?type=tutors" class="btn btn-secondary">View Tutors</a>
            </div>
            
            <div class="dashboard-card">
                <h3><i class="fas fa-comments"></i> Live Chat</h3>
                <p>Direct communication with course instructors at the university.</p>
                <a href="services.php?type=chat" class="btn btn-secondary">Open Chat</a>
            </div>
        </div>
        
        <?php if($user['is_tutor']): ?>
            <div class="tutor-panel">
                <h3><i class="fas fa-user-tie"></i> Tutor Panel</h3>
                <p>As an approved tutor on the platform, you can manage your private tutoring offers.</p>
                <a href="tutor_dashboard.php" class="btn btn-primary">Go to Tutor Dashboard</a>
            </div>
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
