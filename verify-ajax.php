<?php
session_start();
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['token'])) {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

$token = $_POST['token'];

$host = 'sql207.infinityfree.com';
$user = 'if0_40113975';
$password = 'WZ1ZxQ8ghVo';
$database = 'if0_40113975_academy';

$connection = new mysqli($host, $user, $password, $database);
if ($connection->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

$query = $connection->prepare("SELECT id, verified FROM teachers WHERE token=?");
$query->bind_param("s", $token);
$query->execute();
$result = $query->get_result();

if ($row = $result->fetch_assoc()) {
    if ($row['verified'] == 1) {
        echo json_encode(["status" => "success", "message" => "Your account is already verified."]);
        exit;
    }

    $update = $connection->prepare("UPDATE teachers SET verified=1 WHERE id=?");
    $update->bind_param("i", $row['id']);
    if ($update->execute()) {
        echo json_encode(["status" => "success", "message" => "Your account has been verified successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to verify your account."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid or expired token."]);
}

$connection->close();

