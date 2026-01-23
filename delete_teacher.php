<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "You must be logged in."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
    exit;
}

$password_input = $_POST['password'] ?? '';

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
    echo json_encode(["status" => "error", "message" => "Database connection failed."]);
    exit;
}

$username = $_SESSION['username'];

// جلب الباسورد المخزن
$stmt = $pdo->prepare("SELECT password FROM teachers WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password_input, $user['password'])) {
    echo json_encode(["status" => "error", "message" => "Incorrect password."]);
    exit;
}

// حذف الطلاب المرتبطين
$stmt = $pdo->prepare("DELETE FROM students WHERE teacher_id = (SELECT id FROM teachers WHERE username = ?)");
$stmt->execute([$username]);

// حذف المدرس
$stmt = $pdo->prepare("DELETE FROM teachers WHERE username = ?");
$stmt->execute([$username]);

session_destroy();

echo json_encode(["status" => "success"]);
exit;
