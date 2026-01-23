<?php
session_start();
header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

$dsn = "mysql:host=sql207.infinityfree.com;dbname=if0_40113975_academy;charset=utf8";
$user = "if0_40113975";
$password = "WZ1ZxQ8ghVo";

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => '⚠️ Database connection failed.']);
    exit;
}

if (!isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => '⚠️ You must be logged in.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentUsername = $_SESSION['username'];
    $newUsername = trim($_POST['username'] ?? '');
    $newEmail = trim($_POST['email'] ?? '');
    $oldPassword = trim($_POST['oldpassword'] ?? '');
    $newPassword = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    $stmt = $pdo->prepare("SELECT id, username, email, password FROM teachers WHERE username = ?");
    $stmt->execute([$currentUsername]);
    $teacher = $stmt->fetch();

    if (!$teacher) {
        echo json_encode(['status' => 'error', 'message' => '⚠️ User not found.']);
        exit;
    }

    $fields = [];
    $params = [];
    $logoutRequired = false;

    // ================== التحقق من التغييرات ==================
    $isChanging = ($newUsername && $newUsername !== $teacher['username']) || 
                  ($newEmail && $newEmail !== $teacher['email']) || 
                  $newPassword;

    // ================== التحقق من كلمة السر القديمة ==================
    if ($isChanging) {
        if (empty($oldPassword)) {
            echo json_encode(['status' => 'error', 'message' => '⚠️ Old password is required to make any changes.']);
            exit;
        }
        if (!password_verify($oldPassword, $teacher['password'])) {
            echo json_encode(['status' => 'error', 'message' => '⚠️ Old password is incorrect. Changes not applied.']);
            exit;
        }
    }

    // ================== تعديل اسم المستخدم ==================
    if (!empty($newUsername) && $newUsername !== $teacher['username']) {
        $check = $pdo->prepare("SELECT id FROM teachers WHERE username = ?");
        $check->execute([$newUsername]);
        if ($check->fetch()) {
            echo json_encode(['status' => 'error', 'message' => '⚠️ Username already exists.']);
            exit;
        }
        $fields[] = "username = ?";
        $params[] = $newUsername;
    }

    // ================== تعديل البريد ==================
    if (!empty($newEmail) && $newEmail !== $teacher['email']) {
        $check = $pdo->prepare("SELECT id FROM teachers WHERE email = ?");
        $check->execute([$newEmail]);
        if ($check->fetch()) {
            echo json_encode(['status' => 'error', 'message' => '⚠️ Email already exists.']);
            exit;
        }
        $fields[] = "email = ?";
        $params[] = $newEmail;

        $fields[] = "verified = 0";
        $logoutRequired = true;
    }

    // ================== تعديل كلمة السر ==================
    if (!empty($newPassword)) {
        if ($newPassword !== $confirm) {
            echo json_encode(['status' => 'error', 'message' => '⚠️ Passwords do not match!']);
            exit;
        }
        if (preg_match('/[\p{Arabic}]/u', $newPassword)) {
            echo json_encode(['status' => 'error', 'message' => '⚠️ Password must not contain Arabic letters.']);
            exit;
        }
        if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $newPassword)) {
            echo json_encode(['status' => 'error', 'message' => '⚠️ Password must contain at least 8 characters, including letters and numbers.']);
            exit;
        }
        if (password_verify($newPassword, $teacher['password'])) {
            echo json_encode(['status' => 'error', 'message' => '⚠️ New password cannot be the same as the old one.']);
            exit;
        }

        $fields[] = "password = ?";
        $params[] = password_hash($newPassword, PASSWORD_DEFAULT);
    }

    if (empty($fields)) {
        echo json_encode(['status' => 'error', 'message' => '⚠️ No changes detected.']);
        exit;
    }

    $sql = "UPDATE teachers SET " . implode(", ", $fields) . " WHERE username = ?";
    $params[] = $currentUsername;

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        if (!empty($newUsername)) $_SESSION['username'] = $newUsername;
        if (!empty($newEmail)) $_SESSION['email'] = $newEmail;

        echo json_encode([
            'status' => 'success',
            'message' => '✅ Account updated successfully!',
            'logout' => $logoutRequired
        ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => '⚠️ Update failed. Please try again later.']);
    }
    exit;
}
