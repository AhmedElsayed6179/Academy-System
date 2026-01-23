<?php
session_start();

// اتصال PDO بقاعدة البيانات
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

// لو مش لوجين
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit;
}

// هات teacher_id من السيشن
$username = $_SESSION['username'];
$stmt = $pdo->prepare("SELECT id FROM teachers WHERE username=?");
$stmt->execute([$username]);
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);
$teacher_id = $teacher['id'] ?? 0;

// لازم ID الطالب
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$student_id = (int)$_GET['id'];

// امسح الطالب لو فعلا ليه علاقة بالمدرس
$stmt = $pdo->prepare("DELETE FROM students WHERE id=? AND teacher_id=?");
$stmt->execute([$student_id, $teacher_id]);

$_SESSION['success'] = "Student deleted successfully!";
header("Location: dashboard.php");
exit;