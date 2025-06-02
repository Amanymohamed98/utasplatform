<?php
require_once 'config.php';
$page_title = "About Us - " . SITE_NAME;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* أنماط الهيدر الموجودة */
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .logo img {
            height: 50px;
        }
        
        nav ul {
            display: flex;
            list-style: none;
            gap: 20px;
            margin: 0;
            padding: 0;
        }
        
        nav a {
            color: #002664;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        nav a:hover {
            color: #0066cc;
            background-color: rgba(0, 86, 179, 0.1);
        }
        
        /* أنماط الصفحة الجديدة */
        .about-section {
            padding: 60px 0;
            background-color: #f8f9fa;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .about-content {
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
            align-items: center;
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
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            color: #002664;
            margin-bottom: 20px;
        }
        
        h2 {
            color: #0066cc;
            margin: 30px 0 15px;
        }
        
        p {
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .mission-vision {
            display: flex;
            gap: 30px;
            margin-top: 40px;
            flex-wrap: wrap;
        }
        
        .mission, .vision {
            flex: 1;
            min-width: 300px;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .team-members {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .team-member {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .team-member img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
        }
        
        /* أنماط قسم Contact Us بألوان شعار UTAS */
        .contact-section {
            background-color: #002664; /* اللون الأزرق الداكن لشعار UTAS */
            color: white;
            padding: 60px 0;
            margin-top: 40px;
        }
        
        .contact-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .contact-title {
            color: white;
            text-align: center;
            margin-bottom: 40px;
        }
        
        .contact-content {
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
        }
        
        .contact-info {
            flex: 1;
            min-width: 300px;
        }
        
        .contact-form {
            flex: 1;
            min-width: 300px;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .contact-info h3 {
            color: #ffd700; /* اللون الذهبي لشعار UTAS */
            margin-bottom: 20px;
        }
        
        .contact-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        
        .contact-icon {
            color: #ffd700; /* اللون الذهبي لشعار UTAS */
            font-size: 20px;
            margin-right: 15px;
            margin-top: 5px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #002664;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .form-group textarea {
            min-height: 120px;
        }
        
        .submit-btn {
            background-color: #002664;
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .submit-btn:hover {
            background-color: #0066cc;
        }
        
        @media (max-width: 768px) {
            .about-content {
                flex-direction: column;
            }
            
            .mission-vision {
                flex-direction: column;
            }
            
            .contact-content {
                flex-direction: column;
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
        
                        
                    
                </ul>
            </nav>
        </div>
    </header>
    
    <section class="about-section">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h1>About Our University</h1>
                    <p>Welcome to the University of Technology and Applied Sciences (UTAS), a leading institution committed to excellence in education, research, and innovation.</p>
                    
                    <h2>Our History</h2>
                    <p>Founded in 1995, UTAS has grown from a small technical college to a comprehensive university with over 10,000 students across multiple campuses.</p>
                    
                    <h2>Our Values</h2>
                    <ul>
                        <li>Excellence in Education</li>
                        <li>Innovation and Creativity</li>
                        <li>Student-Centered Approach</li>
                        <li>Community Engagement</li>
                    </ul>
                </div>
                
                <div class="about-image">
                    <img src="https://www.utas.edu.om/portals/6/Images/main%20building%20about%20us.jpg" alt="University Campus">
                </div>
            </div>
            
            <div class="mission-vision">
                <div class="mission">
                    <h2><i class="fas fa-bullseye"></i> Our Mission</h2>
                    <p>To provide high-quality education that prepares students for successful careers through innovative teaching, applied research, and strong industry partnerships.</p>
                </div>
                
                <div class="vision">
                    <h2><i class="fas fa-eye"></i> Our Vision</h2>
                    <p>To be a globally recognized leader in applied sciences and technology education, driving regional development and innovation.</p>
                </div>
            </div>
            
            
    </section>
    
   
    <section class="contact-section">
        <div class="contact-container">
            <h2 class="contact-title">Contact Us</h2>
            <div class="contact-content">
                <div class="contact-info">
                    <h3>Get in Touch</h3>
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <h4>Address</h4>
                            <p>University of Technology and Applied Sciences<br>P.O. Box 123, Muscat, Oman</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div>
                            <h4>Phone</h4>
                            <p>+968 1234 5678</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h4>Email</h4>
                            <p>info@utas.edu.om</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <h4>Working Hours</h4>
                            <p>Sunday - Thursday: 7:30 AM - 2:30 PM</p>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form">
                    <h3 style="color: #002664;">Send Us a Message</h3>
                    <form action="#" method="POST">
                        <div class="form-group">
                            <label for="name">Your Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Your Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" required></textarea>
                        </div>
                        
                        <button type="submit" class="submit-btn">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
    
   
</body>
</html>