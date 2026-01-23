<?php
session_start();
header('Content-Type: application/json');

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
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "You must be logged in"]);
    exit;
}

$password = $_POST['password'] ?? '';
$username = $_SESSION['username'];

// Get teacher info
$stmt = $pdo->prepare("SELECT id, password FROM teachers WHERE username = ?");
$stmt->execute([$username]);
$teacher = $stmt->fetch();

if (!$teacher) {
    echo json_encode(["status" => "error", "message" => "Teacher not found"]);
    exit;
}

// Verify password
if (!password_verify($password, $teacher['password'])) {
    echo json_encode(["status" => "error", "message" => "Incorrect password"]);
    exit;
}

$teacher_id = $teacher['id'];

// Check if teacher has students
$check = $pdo->prepare("SELECT COUNT(*) AS total FROM students WHERE teacher_id = ?");
$check->execute([$teacher_id]);
$count = $check->fetchColumn();

if ($count == 0) {
    echo json_encode(["status" => "error", "message" => "No students have been registered yet"]);
    exit;
}

// Delete all students for this teacher
$delete = $pdo->prepare("DELETE FROM students WHERE teacher_id = ?");
$delete->execute([$teacher_id]);

echo json_encode(["status" => "success", "message" => "All students deleted successfully ✅"]);