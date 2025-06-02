<?php
require_once 'config.php';
require_once 'functions.php';

if(!is_logged_in()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = get_user_data($conn, $user_id);
$page_title = "Upload Material - " . SITE_NAME;

$errors = [];
$success = false;

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = clean_input($_POST['title']);
    $description = clean_input($_POST['description']);
    $specialization = $user['specialization'];
    
    // التحقق من البيانات
    if(empty($title)) {
        $errors[] = "Title is required";
    }
    
    if(empty($_FILES['material_file']['name'])) {
        $errors[] = "Please select a file";
    }
    
    if(empty($errors)) {
        $file = $_FILES['material_file'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'jpg', 'png', 'zip'];
        
        if(in_array($file_ext, $allowed_ext)) {
            $upload_dir = 'uploads/materials/';
            if(!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = uniqid() . '_' . preg_replace('/[^A-Za-z0-9\-]/', '', $file['name']);
            $file_path = $upload_dir . $file_name;
            
            if(move_uploaded_file($file['tmp_name'], $file_path)) {
                $stmt = $conn->prepare("INSERT INTO materials 
                                      (title, description, type, file_path, specialization, uploaded_by) 
                                      VALUES (?, ?, ?, ?, ?, ?)");
                if($stmt->execute([$title, $description, $file_ext, $file_path, $specialization, $user_id])) {
                    $success = true;
                } else {
                    $errors[] = "Database error: Failed to save material";
                }
            } else {
                $errors[] = "Failed to upload file";
            }
        } else {
            $errors[] = "Invalid file type. Allowed types: PDF, DOC, PPT, JPG, PNG, ZIP";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #003366;
            --secondary-color: #0066cc;
            --accent-color: #ff6600;
            --light-color: #f5f5f5;
            --dark-color: #333333;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            background-color: #f8f9fa;
            color: var(--dark-color);
        }
        
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 30px;
        }
        
        .upload-form {
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .form-group input[type="text"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .form-group textarea {
            min-height: 100px;
        }
        
        .file-input {
            padding: 10px;
            border: 2px dashed #ccc;
            border-radius: 4px;
            text-align: center;
            cursor: pointer;
        }
        
        .file-input:hover {
            border-color: var(--secondary-color);
        }
        
        .btn {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: var(--secondary-color);
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
        
        .file-preview {
            margin-top: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: none;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
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
   
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    padding: 15px 0;
    position: sticky;
    top: 0;
    z-index: 100;
}

.header-container2 {
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
    
    <div class="container">
        <h1><i class="fas fa-upload"></i> Upload Study Material</h1>
        
        <?php if($success): ?>
            <div class="alert alert-success">
                <p>Your material has been uploaded successfully!</p>
                <a href="services.php?type=materials" class="btn">View All Materials</a>
            </div>
        <?php else: ?>
            <?php if(!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form class="upload-form" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Material Title</label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"></textarea>
                </div>
                
                <div class="form-group">
                    <label>File Upload</label>
                    <div class="file-input" onclick="document.getElementById('file').click()">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 40px; color: #666;"></i>
                        <p>Click to select file (PDF, DOC, PPT, JPG, PNG, ZIP)</p>
                        <input type="file" id="file" name="material_file" style="display: none;" onchange="previewFile()" required>
                    </div>
                    <div class="file-preview" id="filePreview">
                        <i class="fas fa-file" id="fileIcon"></i>
                        <span id="fileName"></span>
                    </div>
                </div>
                
                <button type="submit" class="btn"><i class="fas fa-upload"></i> Upload Material</button>
            </form>
            
            <a href="services.php?type=materials" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Materials
            </a>
        <?php endif; ?>
    </div>
    
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
    function previewFile() {
        const fileInput = document.getElementById('file');
        const filePreview = document.getElementById('filePreview');
        const fileName = document.getElementById('fileName');
        const fileIcon = document.getElementById('fileIcon');
        
        if(fileInput.files.length > 0) {
            const file = fileInput.files[0];
            fileName.textContent = file.name;
            
            // تغيير الأيقونة حسب نوع الملف
            const ext = file.name.split('.').pop().toLowerCase();
            if(ext === 'pdf') {
                fileIcon.className = 'fas fa-file-pdf';
                fileIcon.style.color = '#e74c3c';
            } else if(ext === 'doc' || ext === 'docx') {
                fileIcon.className = 'fas fa-file-word';
                fileIcon.style.color = '#2c3e50';
            } else if(ext === 'ppt' || ext === 'pptx') {
                fileIcon.className = 'fas fa-file-powerpoint';
                fileIcon.style.color = '#e67e22';
            } else if(ext === 'jpg' || ext === 'jpeg' || ext === 'png') {
                fileIcon.className = 'fas fa-file-image';
                fileIcon.style.color = '#3498db';
            } else if(ext === 'zip') {
                fileIcon.className = 'fas fa-file-archive';
                fileIcon.style.color = '#9b59b6';
            } else {
                fileIcon.className = 'fas fa-file';
                fileIcon.style.color = '#7f8c8d';
            }
            
            filePreview.style.display = 'flex';
            filePreview.style.alignItems = 'center';
            filePreview.style.gap = '10px';
        }
    }
    </script>
</body>
</html>