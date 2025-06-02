<?php
require_once 'config.php';
require_once 'functions.php';

$page_title = "Home - " . SITE_NAME;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Main Colors - UTAS University Brand Colors */
        :root {
            --utas-blue: #003366;
            --utas-light-blue: #0066cc;
            --utas-orange: #ff6600;
            --utas-white: #ffffff;
            --utas-light-gray: #f5f5f5;
            --utas-dark-gray: #333333;
        }

        /* Base Styles */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: var(--utas-dark-gray);
            line-height: 1.6;
        }

        .container {
            width: 85%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

       /* أنماط الهيدر المعدلة */
    /* أنماط الهيدر الجديدة */
/* أنماط الهيدر الجديدة */
.main-header {
    background-color: #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 10px 0;
    position: sticky;
    top: 0;
    z-index: 1000;
}

.header-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 20px;
    flex-wrap: wrap;
}

.logo-and-title {
    display: flex;
    align-items: center;
    gap: 15px;
}

.main-logo {
    height: 80px;
    width: auto;
    transition: all 0.3s ease;
}

.title-container {
    display: flex;
    flex-direction: column;
}

.site-main-title {
    color: #002664;
    font-size: 24px;
    margin: 0;
    font-weight: 700;
    line-height: 1.2;
}

.university-name {
    color: #555;
    font-size: 14px;
    margin: 5px 0 0 0;
}

.main-nav ul.nav-list {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 15px;
}

.main-nav .nav-link {
    color: #002664;
    text-decoration: none;
    font-weight: 500;
    padding: 8px 15px;
    border-radius: 4px;
    transition: all 0.3s ease;
    display: block;
}

.main-nav .nav-link:hover {
    background-color: rgba(0, 38, 100, 0.1);
    color: #0066cc;
}

@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        gap: 15px;
        padding: 15px;
    }
    
    .logo-and-title {
        flex-direction: column;
        text-align: center;
    }
    
    .main-nav ul.nav-list {
        justify-content: center;
        flex-wrap: wrap;
    }
}
        /* Hero Section with Animated Background */
        .hero {
            position: relative;
            padding: 100px 0;
            color: var(--utas-white);
            text-align: center;
            overflow: hidden;
            background-color: var(--utas-blue);
        }

        /* Animated Background for Hero */
        .hero::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://www.utas.edu.om/Portals/0/Images/new.about.jpg') center/cover;
            opacity: 0.7;
            z-index: 0;
            animation: slide 30s linear infinite;
        }

        @keyframes slide {
            0% { background-position: 0 0; }
            50% { background-position: 100% 100%; }
            100% { background-position: 0 0; }
        }

        .hero .container {
            position: relative;
            z-index: 1;
        }

        .hero h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        .hero p {
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto 30px;
        }

        /* Button Styles */
        .btn {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
            margin: 0 10px;
        }

        .btn-primary {
            background-color: var(--utas-orange);
            color: var(--utas-white);
            border: 2px solid var(--utas-orange);
        }

        .btn-primary:hover {
            background-color: transparent;
            color: var(--utas-orange);
        }

        .btn-secondary {
            background-color: transparent;
            color: var(--utas-white);
            border: 2px solid var(--utas-white);
        }

        .btn-secondary:hover {
            background-color: var(--utas-white);
            color: var(--utas-blue);
        }

        /* About Section */
        .about-section {
            padding: 80px 0;
            background-color: var(--utas-light-gray);
        }

        .about-section h2 {
            text-align: center;
            color: var(--utas-blue);
            margin-bottom: 50px;
            font-size: 2rem;
        }

        .about-content {
            display: flex;
            align-items: center;
            gap: 40px;
        }

        .about-text {
            flex: 1;
        }

        .about-image {
            flex: 1;
        }

        .about-image img {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        /* Features Section */
        .features {
            padding: 80px 0;
            background-color: var(--utas-white);
        }

        .features h2 {
            text-align: center;
            color: var(--utas-blue);
            margin-bottom: 50px;
            font-size: 2rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .feature-card {
            background-color: var(--utas-light-gray);
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .feature-card i {
            font-size: 3rem;
            color: var(--utas-orange);
            margin-bottom: 20px;
        }

        .feature-card h3 {
            color: var(--utas-blue);
            margin-bottom: 15px;
        }

        /* Footer Styles */
        footer {
            background-color: var(--utas-blue);
            color: var(--utas-white);
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .about-content {
                flex-direction: column;
            }
            
            .hero h2 {
                font-size: 1.8rem;
            }
            
            .header-container {
                flex-direction: column;
                text-align: center;
            }
            
            nav ul {
                margin-top: 15px;
                justify-content: center;
            }
        }
        .about-section {
    background-color: var(--utas-light);
    padding: 60px 0;
}

.about-section h2, 
.campus-gallery h2 {
    color: var(--utas-primary);
    text-align: center;
    margin-bottom: 40px;
    position: relative;
}

.about-section h2:after,
.campus-gallery h2:after {
    content: '';
    display: block;
    width: 80px;
    height: 3px;
    background: var(--utas-secondary);
    margin: 15px auto 0;
}

.about-content {
    display: flex;
    align-items: center;
    gap: 40px;
    flex-wrap: wrap;
}

.about-text {
    flex: 1;
    min-width: 300px;
}

.about-image {
    flex: 1;
    min-width: 300px;
}

.about-image img {
    width: 100%;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.university-leaders {
    margin-top: 30px;
}

/* أنماط القسم الجديدة */
    .university-leaders {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
        margin-top: 30px;
        justify-content: center;
    }
    
    .leader-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        width: 280px;
        text-align: center;
    }
    
    .leader-card:hover {
        transform: translateY(-10px);
    }
    
    .leader-image {
        height: 280px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f5f5f5;
    }
    
    .leader-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .leader-card:hover .leader-image img {
        transform: scale(1.05);
    }
    
    .leader-info {
        padding: 20px;
        background: #002664;
        color: white;
    }
    
    .leader-info h4 {
        margin: 0 0 5px 0;
        font-size: 1.3rem;
        color: #ffd700; /* اللون الذهبي */
    }
    
    .leader-info p {
        margin: 0;
        font-size: 1rem;
        color: white;
    }
    
    @media (max-width: 768px) {
        .university-leaders {
            flex-direction: column;
            align-items: center;
        }
        
        .leader-card {
            width: 100%;
            max-width: 350px;
        }
        
        .leader-image {
            height: 350px;
        }
    }

.campus-gallery {
    padding: 60px 0;
    background-color: #f5f7fa;
}

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.gallery-item {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.gallery-item:hover {
    transform: translateY(-5px);
}

.gallery-item img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    display: block;
}

.caption {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0, 38, 100, 0.8);
    color: white;
    padding: 10px;
    text-align: center;
}

@media (max-width: 768px) {
    .about-content {
        flex-direction: column;
    }
    
    .leader-card {
        flex-direction: column;
        text-align: center;
    }
    
    .leader-image {
        margin-right: 0;
        margin-bottom: 15px;
    }
    
    .gallery-grid {
        grid-template-columns: 1fr;
    }
}
    </style>
</head>
<body>
  <header class="main-header">
    <div class="header-content">
        <div class="logo-and-title">
            <img src="data:image/png;base64,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" alt="UTAS Logo" class="main-logo">
            <div class="title-container">
                <h1 class="site-main-title">UTAS Student HUB</h1>
                <p class="university-name">University of Technology and Applied Sciences</p>
            </div>
        </div>
        
        <nav class="main-nav">
            <ul class="nav-list">
                <li><a href="dashboard.php" class="nav-link">Home</a></li>
                <li><a href="profile.php" class="nav-link">Profile</a></li>
                <li><a href="services.php" class="nav-link">Services</a></li>
                <li><a href="about.php" class="nav-link">AboutUs</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="logout.php" class="nav-link">Logout</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

    <section class="hero">
        <div class="container">
            <h2>Welcome to the University of Technology and Applied Sciences Student Platform</h2>
            <p>A comprehensive platform for students of IT and Engineering specialties</p>
            <?php if(!is_logged_in()): ?>
                <div class="cta-buttons">
                    <a href="register.php" class="btn btn-primary">Register</a>
                    <a href="login.php" class="btn btn-secondary">Login</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

   <section class="about-section">
    <div class="container">
        <h2>About the Platform</h2>
        <div class="about-content">
            <div class="about-text">
                <p>This platform was established to support students at the University of Technology and Applied Sciences (UTAS) and provide all the educational resources they need in one place.</p>
                <p><strong>Under the supervision of:</strong></p>
                <div class="university-leaders">
                    <div class="leader-card">
                        <div class="leader-image">
                            <img src="https://www.utas.edu.om/portals/11/Images/hod-image-hanifa.png" alt="Dr. Hanifa Al-Qasimi - Dean of the University">
                        </div>
                        <div class="leader-info">
                            <h4>Dr. Hanifa Al-Qasimi</h4>
                            <p>Dean of the University</p>
                        </div>
                    </div>
                    <div class="leader-card">
                        <div class="leader-image">
                            <img src="https://www.utas.edu.om/portals/0/Images/VCH.png" alt="Saeed Al-Rubaie - University President">
                        </div>
                        <div class="leader-info">
                            <h4>Saeed Al-Rubaie</h4>
                            <p>University President</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="campus-gallery">
    <div class="container">
        <h2>UTAS Campus Gallery</h2>
        <div class="gallery-grid">
            <div class="gallery-item">
                <img src="data:image/jpeg;base64,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" alt="UTAS Main Building">
                <div class="caption">Main University Building</div>
            </div>
            <div class="gallery-item">
                <img src="data:image/jpeg;base64,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" alt="UTAS Library">
                <div class="caption">University Library</div>
            </div>
            <div class="gallery-item">
                <img src="https://www.utas.edu.om/portals/0/Images/mission.jpg?ver=7Ap4GMHKnkmzt6IT5ZT2eg%3d%3d" alt="UTAS Laboratories">
                <div class="caption">Science Laboratories</div>
            </div>
           
        </div>
    </div>
</section>

    <section class="features">
        <div class="container">
            <h2>Our Services</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-book-open"></i>
                    <h3>Free Materials</h3>
                    <p>Summaries, past exams, and free study materials for all specialties</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <h3>Private Tutoring</h3>
                    <p>Enhancement lessons provided by outstanding students with GPA 3.3 and above</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-comments"></i>
                    <h3>Live Chat</h3>
                    <p>Direct communication with course instructors at the university</p>
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
</body>
</html>



