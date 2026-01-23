<?php
session_start();

// ✅ التحقق من تسجيل الدخول قبل أي استعلام
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit;
}

// ✅ إعداد اتصال آمن بقاعدة البيانات
try {
    $pdo = new PDO(
        "mysql:host=sql207.infinityfree.com;dbname=if0_40113975_academy;charset=utf8",
        "if0_40113975",
        "WZ1ZxQ8ghVo",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    // عرض رسالة خطأ واضحة ومحمية
    die("<p style='color:red; text-align:center;'>⚠️ Database Connection Failed: " . htmlspecialchars($e->getMessage()) . "</p>");
}

$username = $_SESSION['username'];

// ✅ التأكد من وجود المستخدم في قاعدة البيانات
$stmt = $pdo->prepare("SELECT id, email FROM teachers WHERE username = ?");
$stmt->execute([$username]);
$teacher = $stmt->fetch();

if (!$teacher) {
    // لو المستخدم مش موجود، نخرج بأمان
    session_destroy();
    die("<p style='color:red; text-align:center;'>⚠️ Account not found in database. Please log in again.</p>");
}

$teacher_id = $teacher['id'];
$email = $teacher['email'];
$_SESSION['email'] = $email;

// ✅ جلب الطلاب المرتبطين بالمدرس
$studentsStmt = $pdo->prepare("SELECT * FROM students WHERE teacher_id = ?");
$studentsStmt->execute([$teacher_id]);
$students = $studentsStmt->fetchAll();

// ✅ معالجة الرسائل من الجلسة
$errorMsg = $_SESSION['error'] ?? '';
$successMsg = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

$settingsError = $_SESSION['settings_error'] ?? '';
$settingsSuccess = $_SESSION['settings_success'] ?? '';
unset($_SESSION['settings_error'], $_SESSION['settings_success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($username) ?>’s Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;600&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="shortcut icon" href="badge.png" type="image/x-icon">
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css" rel="stylesheet">
</head>
<body>
    <!-- ✅ Preloader -->
    <div id="preloader">
        <div class="loader"></div>
    </div>

    <!-- ✅ Particles Background -->
    <div id="particles-js" class="particles"></div>

    <div class="bg-wrapper">
        <div class="bg-gradient"></div>
        <div class="bg-pattern"></div>
        <div class="bg-shapes">
            <div class="bg-shape bg-shape-1"></div>
            <div class="bg-shape bg-shape-2"></div>
            <div class="bg-shape bg-shape-3"></div>
        </div>
    </div>

    <header>
   <?php
if ($teacher_id) {
$stmt = $pdo->prepare("SELECT id, email, profile_image FROM teachers WHERE username = ?");
$stmt->execute([$username]);
$teacher = $stmt->fetch();

if (!empty($teacher['profile_image']) && $teacher['profile_image'] !== 'NULL') {
    $profileImage = "uploads/" . htmlspecialchars($teacher['profile_image']);
} else {
    $profileImage = "uploads/default.png";
}

}
?>
       <div class="profile">
            <img src="<?= $profileImage ?>" alt="Profile Picture" class="profile-img">
            <h1>Welcome, <?= htmlspecialchars($username) ?></h1>
        </div>

        <div class="hamburger-menu">
            <div id="hamburgerIcon" class="hamburger-icon">
                <i class='bx bx-menu'></i>
            </div>

            <div id="hamburgerLinks" class="hamburger-links">
               <!-- Change Picture Button -->
               <button type="button" id="changePicBtn" class="menu-btn settings-btn">
               <i class='bx bx-pencil'></i> Change Picture
               </button>
               <input type="file" id="profileUpload" name="profile_image" accept="image/*" style="display:none;">
                <button id="settingsBtn" class="menu-btn settings-btn">
                    <i class='bx bx-cog'></i> Settings
                </button>
                <a href="logout.php" class="menu-btn logout-link">
                    <i class='bx bx-log-out'></i> Logout
                </a>
                <!-- Delete Picture Button -->
                <button type="button" id="deletePicBtn" class="menu-btn delete-link">
                <i class='bx bx-trash-alt'></i> Delete Picture
                 </button>
                <button type="button" id="deleteAllStudentsBtn" class="menu-btn delete-link">
                    <i class='bx bx-trash'></i> Clear students
                </button>
                <button type="button" id="deleteAccountBtn" class="menu-btn delete-link">
                    <i class='bx bx-user-x'></i> Remove Account
                </button>
                <div class="settings-icon">
                    <img src="badge.png" alt="Academy">
                </div>
            </div>
        </div>

        <!-- ✅ نافذة الإعدادات -->
        <div id="settingsModal" class="modal">
            <div class="modal-content">
                <span class="close-btn">&times;</span>
                <h2>Account Settings</h2>
                <p>You can easily edit your information</p>

                <form id="settingsForm" action="update_teacher.php" 
                    data-current-username="<?= htmlspecialchars($_SESSION['username']) ?>" 
                    data-current-email="<?= htmlspecialchars($_SESSION['email']) ?>" 
                    method="post">
                    
                    <div class="input-box">
                        <input type="text" id="username" name="username"
                            value="<?= htmlspecialchars($username) ?>"
                            placeholder="New Username" required>
                    </div>

                    <div class="input-box">
                        <input type="email" id="email" name="email"
                            value="<?= htmlspecialchars($email) ?>"
                            placeholder="New Email" required>
                    </div>
                    
                    <div class="input-box">
                        <input
                            type="password"
                            id="oldpassword"
                            name="oldpassword"
                            placeholder="Old Password" />
                        <i class="bx bx-show toggle-pass" data-target="oldpassword"></i>
                    </div>

                    <div class="input-box">
                        <input type="password" id="password" name="password" placeholder="New Password">
                        <i class="bx bx-show toggle-pass" data-target="password"></i>
                    </div>

                    <div class="input-box">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password">
                        <i class="bx bx-show toggle-pass" data-target="confirm_password"></i>
                    </div>

                    <button type="submit" class="save-btn">Save Changes</button>
                </form>
            </div>
        </div>
    </header>
    
     <?php if ($errorMsg): ?>
        <div class="message error" id="flashMessage"><?= htmlspecialchars($errorMsg) ?></div>
    <?php elseif ($successMsg): ?>
        <div class="message success" id="flashMessage"><?= htmlspecialchars($successMsg) ?></div>
    <?php endif; ?>

    <div class="intro">
        <h2><?= htmlspecialchars($username) ?>’s Dashboard</h2>
          <p class="intro-text">
            You can now
            <span class="words">
                <span class="word">add</span>
                <span class="word">edit</span>
                <span class="word">delete</span>
            </span>
            students easily and manage your class efficiently
        </p>
    </div>
    
  <!-- ✅ Dashboard Stats -->
<div class="dashboard-stats first-row">
    <div class="stat-card">
        <div class="stat-icon"><i class='bx bx-user'></i></div>
        <h3>Total Students</h3>
        <p><?= count($students) ?></p>
    </div>

    <div class="stat-card">
        <div class="stat-icon"><i class='bx bx-book'></i></div>
        <h3>Courses Offered</h3>
        <?php
        $courses = array_unique(array_column($students, 'student_course'));
        ?>
        <p><?= count($courses) ?></p>
    </div>

    <div class="stat-card">
        <div class="stat-icon"><i class='bx bx-user-plus'></i></div>
        <h3>Latest Student</h3>
        <?php
        $latestStudent = end($students);
        ?>
        <p><?= $latestStudent ? htmlspecialchars($latestStudent['student_name']) : "N/A" ?></p>
    </div>
    
    <?php
$grades = array_column($students, 'student_grade'); // جميع الدرجات

$highestGrade = count($grades) ? max($grades) : 0; // أعلى درجة
$lowestGrade  = count($grades) ? min($grades) : 0; // أقل درجة
?>
    
<div class="stat-card">
    <div class="stat-icon"><i class='bx bx-trending-up'></i></div>
    <h3>Highest Grade</h3>
    <p><?= htmlspecialchars($lowestGrade) ?></p>
</div>

<div class="stat-card">
    <div class="stat-icon"><i class='bx bx-trending-down'></i></div>
    <h3>Lowest Grade</h3>
    <p><?= htmlspecialchars($highestGrade) ?></p>
</div>

    <div class="stat-card">
        <div class="stat-icon"><i class='bx bx-note'></i></div>
        <h3>Students with Notes</h3>
        <?php
        $studentsWithNotes = array_filter($students, fn($s) => !empty($s['student_notes']));
        ?>
        <p><?= count($studentsWithNotes) ?></p>
    </div>
</div>

<!-- ✅ Second Row -->
<div class="dashboard-stats second-row">
    <!-- Students per Course -->
    <div class="stat-card">
        <div class="stat-icon"><i class='bx bx-book'></i></div>
        <h3>Students per Course</h3>
        <?php
        $coursesCount = array_count_values(array_column($students,'student_course'));
        ?>
        <ul style="list-style:none; padding:0; margin:0;">
            <?php 
        if (!empty($coursesCount)) {
        foreach($coursesCount as $course => $count): ?>
            <li><?= htmlspecialchars($course) ?>: <?= $count ?></li>
        <?php endforeach;
        } else { ?>
        <li>0</li>
           <?php } ?>
        </ul>
    </div>

    <!-- Total Teachers -->
    <div class="stat-card">
        <div class="stat-icon"><i class='bx bx-user-circle'></i></div>
        <h3>Total Teachers</h3>
        <?php
        $teachersStmt = $pdo->query("SELECT COUNT(*) as total FROM teachers");
        $totalTeachers = $teachersStmt->fetch()['total'];
        ?>
        <p><?= $totalTeachers ?></p>
    </div>
</div>

    <!-- ✅ إضافة طالب -->
    <button class="add-btn" id="showFormBtn"><i class='bx bx-plus'></i> Add Student</button>

    <div class="form-container" id="studentForm">
        <h2>Add Student</h2>
        <form method="post" action="add_student.php">
            <input type="text" name="name" placeholder="Name" required>
            <input type="text" name="course" placeholder="Course" required>
            <input type="text" name="grade" placeholder="Grade" required>
            <input type="text" name="class" placeholder="Class" required>
            <input type="tel" class="phoneNum" name="phone" placeholder="Phone (11 digits only)" maxlength="11" required>
            <input type="text" name="notes" placeholder="Notes">
            <button type="submit"><i class='bx bx-plus'></i> Add Student</button>
        </form>
        <button class="hide-btn" id="hideFormBtn"><i class='bx bx-hide'></i> Hide Details</button>
    </div>

    <!-- ✅ جدول الطلاب -->
    <button class="add-btn" id="toggleTableBtn"><i class='bx bx-list-ul'></i> Show Students</button>
    <div id="studentsTable" style="display: none;">
        <h2 class="students-title">Your Students</h2>

        <div class="search-container">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="ID or Name...">
                <button id="searchBtn"><i class='bx bx-search'></i></button>
            </div>
            <div class="search-box">
                <input type="text" id="searchCourseInput" placeholder="Course...">
                <button id="searchCourseBtn"><i class='bx bx-search'></i></button>
            </div>
            <div id="noResults" class="no-results" style="display:none;">
                No matching students found
            </div>
        </div>

        <table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Class</th>
        <th>Course</th>
        <th>Grade</th>
        <th>Phone</th>
        <th>Notes</th>
        <th>Created at</th>
        <th>Manage</th>
    </tr>

    <?php if (!empty($students)): ?>
        <?php foreach ($students as $row): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['student_name']) ?></td>
                <td><?= htmlspecialchars($row['student_class']) ?></td>
                <td><?= htmlspecialchars($row['student_course']) ?></td>
                <td><?= htmlspecialchars($row['student_grade']) ?></td>
                <td><?= htmlspecialchars($row['student_phone']) ?></td>
                <td><?= htmlspecialchars($row['student_notes']) ?></td>
                <td><?= date('Y-m-d • h:i:s A', strtotime($row['created_at'])) ?></td>
                <td>
                    <a href="edit_student.php?id=<?= $row['id'] ?>"><i class='bx bx-edit'></i> Edit</a> |
                    <a href="delete_student.php?id=<?= $row['id'] ?>"
                       onclick="return confirm('Please confirm that you want to permanently delete <?= htmlspecialchars($row['student_name']) ?> from your records.')">
                       <i class='bx bx-trash'></i> Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="9" style="text-align:center; color:#777; font-style:italic; padding:12px;">
                No students have been registered for you yet
            </td>
        </tr>
    <?php endif; ?>
        </table>

    </div>

    <!-- ✅ Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
    <script src="dashboard.js"></script>
</body>
</html>
