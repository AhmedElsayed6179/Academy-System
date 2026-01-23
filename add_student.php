<?php
session_start();

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
} 

catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// التأكد من تسجيل الدخول
if (!isset($_SESSION['username'])) {
    header('Location: login.html');
    exit;
}

// جلب teacher_id
$username = $_SESSION['username'];
$stmt = $pdo->prepare("SELECT id FROM teachers WHERE username = ?");
$stmt->execute([$username]);
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$teacher) {
    $_SESSION['error'] = "Teacher not found!";
    header("Location: dashboard.php");
    exit;
}

$teacher_id = $teacher['id'];

// معالجة الفورم عند الإرسال
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $class = trim($_POST['class'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $grade = trim($_POST['grade'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    date_default_timezone_set('Africa/Cairo');
    $created_at = date('Y-m-d H:i:s');

    // التحقق من المدخلات
    if ($name === '') {
        $_SESSION['error'] = "Student name is required!";
        header('Location: dashboard.php');
        exit;
    }

    // التحقق من وجود الطالب بنفس الاسم أو رقم الهاتف لنفس المدرس فقط
    $checkStmt = $pdo->prepare("
    SELECT id FROM students 
    WHERE teacher_id = ? 
    AND (student_name = ? OR student_phone = ?)
");
    $checkStmt->execute([$teacher_id, $name, $phone]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $_SESSION['error'] = "Student with this name and phone already exists!";
        header('Location: dashboard.php');
        exit;
    }

    // إدخال الطالب
    $insertStmt = $pdo->prepare("INSERT INTO students (teacher_id, student_name, student_class, student_course, student_grade, student_phone, student_notes, created_at) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $insertStmt->execute([$teacher_id, $name, $class, $course, $grade, $phone, $notes, $created_at]);

    $_SESSION['success'] = "Student added successfully!";
    header('Location: dashboard.php');
    exit;
}
