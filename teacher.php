<?php
require_once 'config.php';
require_once 'functions.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = get_user_data($conn, $user_id);

// التحقق من أن المستخدم معلم
if ($user['user_type'] !== 'teacher') {
    header("Location: dashboard.php");
    exit();
}

// جلب المواد التعليمية للمعلم
try {
    $stmt = $conn->prepare("SELECT id, teacher_id, course_id, title, description, file_path, uploaded_at FROM teacher_materials WHERE teacher_id = ? ORDER BY uploaded_at DESC");
    $stmt->execute([$user_id]);
    $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("خطأ في قاعدة البيانات: " . $e->getMessage());
}

// جلب قائمة المواد الدراسية
try {
    $courses = $conn->query("SELECT id, code, name FROM courses WHERE teacher_id = $user_id")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $courses = [];
    $upload_error = "خطأ في جلب قائمة المواد الدراسية";
}

// جلب رسائل الطلاب
try {
    $stmt = $conn->prepare("
        SELECT m.*, u.full_name AS student_name, u.university_id AS student_id
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.teacher_id = ?
        ORDER BY m.sent_at DESC
    ");
    $stmt->execute([$user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $messages = [];
    $upload_error = "خطأ في جلب الرسائل";
}

// معالجة رفع الملفات
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['course_id'])) {
    try {
        // التحقق من البيانات المطلوبة
        $required_fields = ['course_id', 'material_type', 'title'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("حقل '{$field}' مطلوب");
            }
        }

        // التحقق من وجود ملف مرفوع
        if (empty($_FILES['material']['name'][0])) {
            throw new Exception("يجب رفع ملف واحد على الأقل");
        }

        // إنشاء مجلد التخزين إذا لم يكن موجوداً
        $uploadDir = 'uploads/materials/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // معالجة كل الملفات المرفوعة
        $uploadedFiles = [];
        foreach ($_FILES['material']['tmp_name'] as $key => $tmpName) {
            $fileName = $_FILES['material']['name'][$key];
            $fileSize = $_FILES['material']['size'][$key];
            $fileError = $_FILES['material']['error'][$key];
            
            // التحقق من أخطاء الرفع
            if ($fileError !== UPLOAD_ERR_OK) {
                throw new Exception("حدث خطأ أثناء رفع الملف '{$fileName}'");
            }
            
            // التحقق من حجم الملف (25MB كحد أقصى)
            if ($fileSize > 25 * 1024 * 1024) {
                throw new Exception("حجم الملف '{$fileName}' كبير جداً (الحد الأقصى 25MB)");
            }
            
            // التحقق من امتداد الملف
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExts = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'zip', 'txt'];
            if (!in_array($fileExt, $allowedExts)) {
                throw new Exception("امتداد الملف '{$fileName}' غير مسموح به");
            }
            
            // إنشاء اسم فريد للملف
            $newFileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '', $fileName);
            $destination = $uploadDir . $newFileName;
            
            // نقل الملف إلى مجلد التخزين
            if (move_uploaded_file($tmpName, $destination)) {
                $uploadedFiles[] = $destination;
            } else {
                throw new Exception("فشل في حفظ الملف '{$fileName}'");
            }
        }

        // حفظ البيانات في قاعدة البيانات لكل ملف
        $stmt = $conn->prepare("
            INSERT INTO teacher_materials 
            (teacher_id, course_id, title, description, file_path, uploaded_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        foreach ($uploadedFiles as $filePath) {
            $stmt->execute([
                $user_id,
                clean_input($_POST['course_id']),
                clean_input($_POST['title']),
                clean_input($_POST['description'] ?? ''),
                $filePath
            ]);
        }

        $_SESSION['success'] = "تم رفع المادة بنجاح!";
        header("Location: teacher_dashboard.php");
        exit();

    } catch (Exception $e) {
        $upload_error = $e->getMessage();
        
        // حذف أي ملفات تم رفعها في حالة حدوث خطأ
        if (!empty($uploadedFiles)) {
            foreach ($uploadedFiles as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }
}

// معالجة حذف المادة التعليمية
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_material'])) {
    try {
        $material_id = (int)$_POST['material_id'];
        
        // التحقق من أن المادة تخص المعلم الحالي
        $stmt = $conn->prepare("SELECT file_path FROM teacher_materials WHERE id = ? AND teacher_id = ?");
        $stmt->execute([$material_id, $user_id]);
        $material = $stmt->fetch();
        
        if ($material) {
            // حذف المادة من قاعدة البيانات
            $stmt = $conn->prepare("DELETE FROM teacher_materials WHERE id = ?");
            if ($stmt->execute([$material_id])) {
                // حذف الملف من السيرفر
                if (file_exists($material['file_path'])) {
                    unlink($material['file_path']);
                }
                $_SESSION['success'] = "تم حذف المادة بنجاح";
                header("Location: teacher_dashboard.php");
                exit();
            }
        }
    } catch (Exception $e) {
        $upload_error = "خطأ في حذف المادة: " . $e->getMessage();
    }
}

// معالجة الرد على الرسائل
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply_message'])) {
    try {
        $message_id = (int)$_POST['message_id'];
        $reply = clean_input($_POST['reply']);
        
        $stmt = $conn->prepare("
            UPDATE messages 
            SET reply = ?, replied_at = NOW() 
            WHERE id = ? AND teacher_id = ?
        ");
        
        if ($stmt->execute([$reply, $message_id, $user_id])) {
            $_SESSION['success'] = "تم إرسال الرد بنجاح";
            header("Location: teacher_dashboard.php");
            exit();
        }
    } catch (Exception $e) {
        $upload_error = "خطأ في إرسال الرد: " . $e->getMessage();
    }
}

$page_title = "لوحة تحكم المعلم - " . SITE_NAME;
?>

<!-- Teacher Dashboard Design -->
<div class="teacher-dashboard">
    <!-- Sidebar -->
    <div class="teacher-sidebar">
        <div class="teacher-profile">
            <div class="profile-image">
                <i class="fas fa-user-circle"></i>
            </div>
            <h3><?php echo $user['full_name']; ?></h3>
            <p>Teacher of <?php echo $user['specialization'] == 'IT' ? 'Information Technology' : 'Engineering'; ?></p>
        </div>
        
        <nav class="teacher-menu">
            <ul>
                <li><a href="#overview" class="active"><i class="fas fa-tachometer-alt"></i> Overview</a></li>
                <li><a href="#upload"><i class="fas fa-upload"></i> Upload Materials</a></li>
                <li><a href="#materials"><i class="fas fa-book"></i> Educational Materials</a></li>
                <li><a href="#messages"><i class="fas fa-envelope"></i> Student Messages</a></li>
                <li><a href="#courses"><i class="fas fa-graduation-cap"></i> Courses</a></li>
                      <li>
    <a href="logout.php" id="logout-link">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</li>
                    
            </ul>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="teacher-main-content">
        <!-- Overview Section -->
        <section id="overview" class="dashboard-section active">
            <h2><i class="fas fa-tachometer-alt"></i> Overview</h2>
            
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($materials); ?></h3>
                        <p>Uploaded Materials</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($messages); ?></h3>
                        <p>Student Messages</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo count($courses); ?></h3>
                        <p>Courses</p>
                    </div>
                </div>
            </div>
            
            <div class="recent-activities">
                <h3><i class="fas fa-history"></i> Recent Activities</h3>
                <ul>
                    <?php if(count($materials) > 0): ?>
                        <li>
                            <i class="fas fa-file-upload"></i>
                            <span>Last uploaded material: <?php echo $materials[0]['title']; ?></span>
                            <small><?php echo date('Y/m/d', strtotime($materials[0]['uploaded_at'])); ?></small>
                        </li>
                    <?php endif; ?>
                    
                    <?php if(count($messages) > 0): ?>
                        <li>
                            <i class="fas fa-comment-alt"></i>
                            <span>Last message from: <?php echo $messages[0]['student_name']; ?></span>
                            <small><?php echo date('Y/m/d', strtotime($messages[0]['sent_at'])); ?></small>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </section>
        
      <!-- Upload Section -->
<!-- Upload Section -->
<section id="upload" class="dashboard-section">
    <h2><i class="fas fa-upload"></i> Upload New Educational Materials</h2>
    
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <?php if(isset($upload_error)): ?>
        <div class="alert alert-danger"><?php echo $upload_error; ?></div>
    <?php endif; ?>
    
    <form method="post" enctype="multipart/form-data" class="upload-form">
        <div class="form-row">
            <div class="form-group">
                <label for="course_id">Course</label>
                <select id="course_id" name="course_id" required class="select2">
                    <option value="">Select Course</option>
                    <!-- Sample Computer Science Courses -->
                    <optgroup label="Computer Science">
                        <option value="cs101">CS101 - Introduction to Programming</option>
                        <option value="cs201">CS201 - Data Structures</option>
                        <option value="cs301">CS301 - Algorithms</option>
                        <option value="cs401">CS401 - Database Systems</option>
                        <option value="cs501">CS501 - Operating Systems</option>
                        <option value="cs601">CS601 - Computer Networks</option>
                        <option value="cs701">CS701 - Artificial Intelligence</option>
                    </optgroup>
                    
                    <!-- Sample Engineering Courses -->
                    <optgroup label="Engineering">
                        <option value="eng101">ENG101 - Introduction to Engineering</option>
                        <option value="eng201">ENG201 - Thermodynamics</option>
                        <option value="eng301">ENG301 - Fluid Mechanics</option>
                        <option value="eng401">ENG401 - Control Systems</option>
                    </optgroup>
                    
                    <!-- Sample Mathematics Courses -->
                    <optgroup label="Mathematics">
                        <option value="math101">MATH101 - Calculus I</option>
                        <option value="math201">MATH201 - Linear Algebra</option>
                        <option value="math301">MATH301 - Probability & Statistics</option>
                        <option value="math401">MATH401 - Differential Equations</option>
                    </optgroup>
                    
                    <!-- Sample Business Courses -->
                    <optgroup label="Business">
                        <option value="bus101">BUS101 - Principles of Management</option>
                        <option value="bus201">BUS201 - Financial Accounting</option>
                        <option value="bus301">BUS301 - Marketing Principles</option>
                    </optgroup>
                    
                    <!-- If you're using PHP to fetch real courses from database -->
                    <?php if(isset($courses) && is_array($courses)): ?>
                        <optgroup label="Your Courses">
                        <?php foreach($courses as $course): ?>
                            <option value="<?php echo $course['id']; ?>">
                                <?php echo htmlspecialchars($course['code'] . ' - ' . $course['name']); ?>
                            </option>
                        <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="material_type">Material Type</label>
                <select id="material_type" name="material_type" required>
                    <option value="">Select Material Type</option>
                    <option value="lecture">Lecture</option>
                    <option value="assignment">Assignment</option>
                    <option value="exam">Exam</option>
                    <option value="summary">Summary</option>
                    <option value="reading">Reading Material</option>
                    <option value="lab">Lab Manual</option>
                    <option value="solution">Solution</option>
                    <option value="other">Other</option>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="title">Material Title</label>
                <input type="text" id="title" name="title" required placeholder="Example: Unit 1 Summary">
            </div>
            
            <div class="form-group">
                <label for="week">Week (Optional)</label>
                <input type="number" id="week" name="week" min="1" max="16" placeholder="Week number">
            </div>
        </div>
        
        <div class="form-group">
            <label for="description">Material Description</label>
            <textarea id="description" name="description" rows="3" placeholder="Brief description of the material..."></textarea>
        </div>
        
        <div class="form-group file-upload-container">
            <label>Upload Files</label>
            <div class="file-upload-box" id="dropZone">
                <div class="file-upload-content">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <h4>Drag & Drop Files Here</h4>
                    <p>or <span class="browse-link">Browse Files</span></p>
                    <small>Allowed files: PDF, DOC, DOCX, PPT, PPTX, ZIP (Max size 25MB)</small>
                </div>
                <input type="file" id="material" name="material" required multiple class="file-input">
            </div>
            <div id="filePreview" class="file-preview-container"></div>
        </div>
        
        <div class="form-group">
            <div class="form-check">
                <input type="checkbox" id="publish" name="publish" checked class="form-check-input">
                <label for="publish" class="form-check-label">Publish material to students immediately</label>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-upload"></i> Upload Material
            </button>
            <button type="reset" class="btn btn-secondary">
                <i class="fas fa-undo"></i> Reset Form
            </button>
        </div>
    </form>
</section>
        <!-- Educational Materials Section -->
        <section id="materials" class="dashboard-section">
            <h2><i class="fas fa-book"></i> Educational Materials</h2>
            
            <?php if(empty($materials)): ?>
                <div class="empty-state">
                    <i class="fas fa-book-open"></i>
                    <p>No materials uploaded yet</p>
                    <a href="#upload" class="btn btn-primary">Upload First Material</a>
                </div>
            <?php else: ?>
                <div class="materials-grid">
                    <?php foreach($materials as $material): ?>
                        <div class="material-card">
                            <div class="material-icon">
                                <?php 
                                $icon = 'fa-file';
                                if($material['file_type'] == 'pdf') $icon = 'fa-file-pdf';
                                elseif(in_array($material['file_type'], ['doc', 'docx'])) $icon = 'fa-file-word';
                                elseif(in_array($material['file_type'], ['ppt', 'pptx'])) $icon = 'fa-file-powerpoint';
                                ?>
                                <i class="fas <?php echo $icon; ?>"></i>
                            </div>
                            <div class="material-info">
                                <h3><?php echo $material['title']; ?></h3>
                                <p><?php echo $material['description']; ?></p>
                                <div class="material-meta">
                                    <span><i class="fas fa-book"></i> <?php echo $material['course_id']; ?></span>
                                    <span><i class="fas fa-calendar-alt"></i> <?php echo date('Y/m/d', strtotime($material['uploaded_at'])); ?></span>
                                </div>
                            </div>
                            <div class="material-actions">
                                <a href="<?php echo $material['file_path']; ?>" class="btn btn-download" download>
                                    <i class="fas fa-download"></i> Download
                                </a>
                                <form method="post" class="delete-form">
                                    <input type="hidden" name="material_id" value="<?php echo $material['id']; ?>">
                                    <button type="submit" name="delete_material" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this material?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- Student Messages Section -->
        <section id="messages" class="dashboard-section">
            <h2><i class="fas fa-envelope"></i> Student Messages</h2>
            
            <?php if(empty($messages)): ?>
                <div class="empty-state">
                    <i class="fas fa-comment-slash"></i>
                    <p>No messages from students</p>
                </div>
            <?php else: ?>
                <div class="messages-container">
                    <?php foreach($messages as $message): ?>
                        <div class="message-card <?php echo $message['replied_at'] ? 'replied' : ''; ?>">
                            <div class="message-header">
                                <div class="student-info">
                                    <div class="student-avatar">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <div>
                                        <h4><?php echo $message['student_name']; ?></h4>
                                        <small>Student ID: <?php echo $message['student_id']; ?></small>
                                    </div>
                                </div>
                                <div class="message-time">
                                    <i class="fas fa-clock"></i> <?php echo date('Y/m/d h:i A', strtotime($message['sent_at'])); ?>
                                </div>
                            </div>
                            
                            <div class="message-content">
                                <p><?php echo $message['message']; ?></p>
                            </div>
                            
                            <?php if($message['reply']): ?>
                                <div class="message-reply">
                                    <div class="reply-header">
                                        <i class="fas fa-reply"></i> Teacher's Reply
                                        <small><?php echo date('Y/m/d h:i A', strtotime($message['replied_at'])); ?></small>
                                    </div>
                                    <p><?php echo $message['reply']; ?></p>
                                </div>
                            <?php else: ?>
                                <form method="post" class="reply-form">
                                    <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                    <div class="form-group">
                                        <label for="reply-<?php echo $message['id']; ?>">Write Your Reply</label>
                                        <textarea id="reply-<?php echo $message['id']; ?>" name="reply" rows="3" required></textarea>
                                    </div>
                                    <button type="submit" name="reply_message" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> Send Reply
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- Courses Section -->
        <section id="courses" class="dashboard-section">
            <h2><i class="fas fa-graduation-cap"></i> Courses</h2>
            
            <?php if(empty($courses)): ?>
                <div class="empty-state">
                    <i class="fas fa-book"></i>
                    <p>No registered courses for you</p>
                </div>
            <?php else: ?>
                <div class="courses-grid">
                    <?php foreach($courses as $course): ?>
                        <div class="course-card">
                            <div class="course-icon">
                                <i class="fas fa-book-open"></i>
                            </div>
                            <div class="course-info">
                                <h3><?php echo $course['name']; ?></h3>
                                <p><?php echo $course['code']; ?></p>
                                <div class="course-stats">
                                    <span><i class="fas fa-file-alt"></i> <?php echo $conn->query("SELECT COUNT(*) FROM teacher_materials WHERE course_id = '{$course['id']}'")->fetchColumn(); ?> Materials</span>
                                    <span><i class="fas fa-users"></i> <?php echo $conn->query("SELECT COUNT(*) FROM student_courses WHERE course_id = '{$course['id']}'")->fetchColumn(); ?> Students</span>
                                </div>
                            </div>
                            <div class="course-actions">
                                <a href="course.php?id=<?php echo $course['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<!-- CSS الإضافي -->
<style>
    /* التصميم العام */
    .teacher-dashboard {
        display: flex;
        min-height: calc(100vh - 70px);
        background-color: #f8f9fa;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    /* الشريط الجانبي */
    .teacher-sidebar {
        width: 280px;
        background-color: #002664;
        color: white;
        padding: 20px 0;
    }
    
    .teacher-profile {
        text-align: center;
        padding: 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    .profile-image {
        width: 80px;
        height: 80px;
        margin: 0 auto 15px;
        border-radius: 50%;
        background-color: rgba(255,255,255,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .profile-image i {
        font-size: 40px;
        color: white;
    }
    
    .teacher-profile h3 {
        margin: 10px 0 5px;
        font-size: 18px;
    }
    
    .teacher-profile p {
        margin: 0;
        font-size: 14px;
        opacity: 0.8;
    }
    
    .teacher-menu ul {
        list-style: none;
        padding: 0;
        margin: 20px 0;
    }
    
    .teacher-menu li {
        margin-bottom: 5px;
    }
    
    .teacher-menu a {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        color: white;
        text-decoration: none;
        transition: all 0.3s;
    }
    
    .teacher-menu a i {
        margin-left: 10px;
        width: 20px;
        text-align: center;
    }
    
    .teacher-menu a:hover, .teacher-menu a.active {
        background-color: rgba(255,255,255,0.1);
        border-right: 3px solid #00AEEF;
    }
    
    /* المحتوى الرئيسي */
    .teacher-main-content {
        flex: 1;
        padding: 30px;
        overflow-y: auto;
    }
    
    .dashboard-section {
        display: none;
        animation: fadeIn 0.5s;
    }
    
    .dashboard-section.active {
        display: block;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    /* بطاقات الإحصائيات */
    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background-color: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        background-color: #f0f7ff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 15px;
    }
    
    .stat-icon i {
        font-size: 20px;
        color: #002664;
    }
    
    .stat-info h3 {
        margin: 0;
        font-size: 24px;
        color: #002664;
    }
    
    .stat-info p {
        margin: 5px 0 0;
        font-size: 14px;
        color: #666;
    }
    
    /* النشاطات الأخيرة */
    .recent-activities {
        background-color: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .recent-activities h3 {
        margin-top: 0;
        color: #002664;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }
    
    .recent-activities ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .recent-activities li {
        padding: 15px 0;
        border-bottom: 1px solid #f5f5f5;
        display: flex;
        align-items: center;
    }
    
    .recent-activities li:last-child {
        border-bottom: none;
    }
    
    .recent-activities li i {
        width: 30px;
        height: 30px;
        background-color: #f0f7ff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 15px;
        color: #002664;
    }
    
    .recent-activities li span {
        flex: 1;
    }
    
    .recent-activities li small {
        color: #999;
        font-size: 13px;
    }
    
    /* نموذج رفع المادة */
    .upload-form {
        background-color: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        transition: all 0.3s;
    }
    
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        border-color: #00AEEF;
        box-shadow: 0 0 0 3px rgba(0, 174, 239, 0.2);
        outline: none;
    }
    
    .file-upload {
        position: relative;
    }
    
    .file-upload input[type="file"] {
        position: absolute;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }
    
    .file-info {
        border: 2px dashed #ddd;
        border-radius: 6px;
        padding: 30px;
        text-align: center;
        transition: all 0.3s;
    }
    
    .file-upload:hover .file-info {
        border-color: #00AEEF;
        background-color: #f8fdff;
    }
    
    .file-info i {
        font-size: 40px;
        color: #00AEEF;
        margin-bottom: 10px;
    }
    
    .file-info p {
        margin: 0 0 5px;
        font-size: 16px;
        color: #333;
    }
    
    .file-info small {
        color: #999;
        font-size: 13px;
    }
    
    /* الأزرار */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
        border: none;
        text-decoration: none;
    }
    
    .btn i {
        margin-left: 8px;
    }
    
    .btn-primary {
        background-color: #002664;
        color: white;
    }
    
    .btn-primary:hover {
        background-color: #001a4a;
    }
    
    .btn-danger {
        background-color: #D40000;
        color: white;
    }
    
    .btn-danger:hover {
        background-color: #b30000;
    }
    
    .btn-download {
        background-color: #00AEEF;
        color: white;
    }
    
    .btn-download:hover {
        background-color: #0098d1;
    }
    
    /* بطاقات المواد التعليمية */
    .materials-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }
    
    .material-card {
        background-color: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: transform 0.3s;
    }
    
    .material-card:hover {
        transform: translateY(-5px);
    }
    
    .material-icon {
        background-color: #f0f7ff;
        padding: 20px;
        text-align: center;
    }
    
    .material-icon i {
        font-size: 40px;
        color: #002664;
    }
    
    .material-info {
        padding: 15px;
    }
    
    .material-info h3 {
        margin: 0 0 10px;
        color: #002664;
    }
    
    .material-info p {
        margin: 0 0 15px;
        color: #666;
        font-size: 14px;
    }
    
    .material-meta {
        display: flex;
        gap: 15px;
        font-size: 13px;
        color: #999;
    }
    
    .material-meta i {
        margin-left: 5px;
    }
    
    .material-actions {
        display: flex;
        padding: 0 15px 15px;
        gap: 10px;
    }
    
    .material-actions .btn {
        flex: 1;
    }
    
    /* رسائل الطلاب */
    .messages-container {
        display: grid;
        gap: 15px;
    }
    
    .message-card {
        background-color: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .message-card.replied {
        border-left: 3px solid #00AEEF;
    }
    
    .message-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .student-info {
        display: flex;
        align-items: center;
    }
    
    .student-avatar {
        width: 40px;
        height: 40px;
        background-color: #f0f7ff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 15px;
    }
    
    .student-avatar i {
        color: #002664;
    }
    
    .student-info h4 {
        margin: 0;
        font-size: 16px;
    }
    
    .student-info small {
        color: #999;
        font-size: 13px;
    }
    
    .message-time {
        color: #999;
        font-size: 13px;
    }
    
    .message-time i {
        margin-left: 5px;
    }
    
    .message-content {
        margin-bottom: 15px;
    }
    
    .message-content p {
        margin: 0;
        color: #333;
    }
    
    .message-reply {
        background-color: #f8fdff;
        border-radius: 6px;
        padding: 15px;
        margin-top: 15px;
        border-left: 3px solid #00AEEF;
    }
    
    .reply-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        color: #00AEEF;
        font-weight: 500;
    }
    
    .reply-header i {
        margin-left: 5px;
    }
    
    .reply-header small {
        color: #999;
        font-size: 13px;
    }
    
    .message-reply p {
        margin: 0;
        color: #333;
    }
    
    .reply-form {
        margin-top: 15px;
    }
    
    /* بطاقات المواد الدراسية */
    .courses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .course-card {
        background-color: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
    }
    
    .course-icon {
        background-color: #f0f7ff;
        padding: 20px;
        text-align: center;
    }
    
    .course-icon i {
        font-size: 40px;
        color: #002664;
    }
    
    .course-info {
        padding: 15px;
        flex: 1;
    }
    
    .course-info h3 {
        margin: 0 0 5px;
        color: #002664;
    }
    
    .course-info p {
        margin: 0 0 15px;
        color: #666;
        font-size: 14px;
    }
    
    .course-stats {
        display: flex;
        gap: 15px;
        font-size: 13px;
        color: #999;
        margin-bottom: 15px;
    }
    
    .course-stats i {
        margin-left: 5px;
    }
    
    .course-actions {
        padding: 0 15px 15px;
    }
    
    .course-actions .btn {
        width: 100%;
    }
    
    /* الحالة الفارغة */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .empty-state i {
        font-size: 50px;
        color: #ddd;
        margin-bottom: 15px;
    }
    
    .empty-state p {
        margin: 0 0 20px;
        color: #666;
        font-size: 16px;
    }
    
    /* التجاوب مع أحجام الشاشات المختلفة */
    @media (max-width: 992px) {
        .teacher-dashboard {
            flex-direction: column;
        }
        
        .teacher-sidebar {
            width: 100%;
            padding: 10px 0;
        }
        
        .teacher-profile {
            display: flex;
            align-items: center;
            text-align: right;
            padding: 10px 20px;
        }
        
        .profile-image {
            width: 40px;
            height: 40px;
            margin: 0 0 0 15px;
        }
        
        .profile-image i {
            font-size: 20px;
        }
        
        .teacher-profile h3 {
            margin: 0;
            font-size: 16px;
        }
        
        .teacher-profile p {
            display: none;
        }
        
        .teacher-menu ul {
            display: flex;
            overflow-x: auto;
            margin: 0;
            padding: 0 10px;
        }
        
        .teacher-menu li {
            margin: 0;
            flex-shrink: 0;
        }
        
        .teacher-menu a {
            padding: 10px 15px;
            font-size: 14px;
        }
        
        .teacher-menu a i {
            margin-left: 5px;
        }
        
        .teacher-main-content {
            padding: 20px;
        }
        
        .form-row {
            grid-template-columns: 1fr;
        }
    }
    
    @media (max-width: 768px) {
        .materials-grid, .courses-grid {
            grid-template-columns: 1fr;
        }
        
        .stats-cards {
            grid-template-columns: 1fr;
        }
    }
    
</style>

<!-- JavaScript للتحكم بين الأقسام -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // التحكم بين أقسام لوحة التحكم
    const menuLinks = document.querySelectorAll('.teacher-menu a:not(#logout-link)');
    const sections = document.querySelectorAll('.dashboard-section');
    
    // وظيفة لتغيير القسم النشط
    function setActiveSection(sectionId) {
        // إزالة النشاط من جميع الروابط والأقسام
        menuLinks.forEach(link => link.classList.remove('active'));
        sections.forEach(section => section.classList.remove('active'));
        
        // إضافة النشاط للرابط والقسم المحدد
        const activeLink = document.querySelector(`.teacher-menu a[href="${sectionId}"]`);
        const activeSection = document.querySelector(sectionId);
        
        if (activeLink && activeSection) {
            activeLink.classList.add('active');
            activeSection.classList.add('active');
            
            // حفظ القسم النشط في sessionStorage
            sessionStorage.setItem('activeSection', sectionId);
        }
    }
    
    // تحديد القسم النشط الأولي
    let initialSection = '#overview';
    
    // التحقق من وجود قسم محفوظ في sessionStorage
    const savedSection = sessionStorage.getItem('activeSection');
    if (savedSection && document.querySelector(savedSection)) {
        initialSection = savedSection;
    }
    
    // التحقق من وجود هاش في الرابط
    if (window.location.hash && document.querySelector(window.location.hash)) {
        initialSection = window.location.hash;
    }
    
    // تعيين القسم النشط الأولي
    setActiveSection(initialSection);
    
    // إضافة event listeners للروابط
    menuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // منع السلوك الافتراضي فقط للروابط التي تبدأ بـ #
            if (this.getAttribute('href').startsWith('#')) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                setActiveSection(targetId);
                
                // تحديث عنوان URL بدون إعادة تحميل الصفحة
                history.pushState(null, null, targetId);
                
                // Scroll إلى القسم المطلوب
                setTimeout(() => {
                    const targetSection = document.querySelector(targetId);
                    if (targetSection) {
                        targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }, 100);
            }
        });
    });
    
    // التعامل مع زر الرجوع/التقدم في المتصفح
    window.addEventListener('popstate', function() {
        if (window.location.hash) {
            setActiveSection(window.location.hash);
        }
    });
    
    // ... باقي الكود الحالي ...
});

document.addEventListener('DOMContentLoaded', function() {
    // ... الكود الحالي ...
    
    // إضافة معالج حدث لتسجيل الخروج
    const logoutLink = document.getElementById('logout-link');
    if (logoutLink) {
        logoutLink.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                // إضافة طابع زمني لمنع التخزين المؤقت
                window.location.href = 'logout.php?t=' + new Date().getTime();
            }
        });
    }


});
</script>