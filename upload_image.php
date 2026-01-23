<?php
session_start();

if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'You must be logged in.']);
    exit;
}

$username = $_SESSION['username'];

// الاتصال بقاعدة البيانات
try {
    $pdo = new PDO(
        "mysql:host=sql207.infinityfree.com;dbname=if0_40113975_academy;charset=utf8",
        "if0_40113975",
        "WZ1ZxQ8ghVo",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection error.']);
    exit;
}

$uploadDir = __DIR__ . "/uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// رفع الصورة
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['profile_image'];
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $mime = mime_content_type($file['tmp_name']);
    $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];

    if (!in_array($ext, $allowedExt) || !in_array($mime, $allowedMime)) {
        echo json_encode(['error' => 'Only JPG, PNG, GIF, or WEBP images are allowed.']);
        exit;
    }

    // حذف الصورة القديمة
    $stmt = $pdo->prepare("SELECT profile_image FROM teachers WHERE username = ?");
    $stmt->execute([$username]);
    $oldImage = $stmt->fetchColumn();

    if ($oldImage && file_exists($uploadDir . $oldImage)) {
        unlink($uploadDir . $oldImage);
    }

    // حفظ الصورة الجديدة
    $newName = uniqid("teacher_", true) . "." . $ext;
    $target = $uploadDir . $newName;

    if (move_uploaded_file($file['tmp_name'], $target)) {
        $stmt = $pdo->prepare("UPDATE teachers SET profile_image = ? WHERE username = ?");
        $stmt->execute([$newName, $username]);
        echo json_encode(['success' => 'Profile picture updated successfully.']);
    } else {
        echo json_encode(['error' => 'Failed to save uploaded image.']);
    }
    exit;
}

// حذف الصورة
if (isset($_POST['delete'])) {
    $stmt = $pdo->prepare("SELECT profile_image FROM teachers WHERE username = ?");
    $stmt->execute([$username]);
    $currentImage = $stmt->fetchColumn();

    if ($currentImage && file_exists($uploadDir . $currentImage)) {
        unlink($uploadDir . $currentImage);
    }

    $stmt = $pdo->prepare("UPDATE teachers SET profile_image = NULL WHERE username = ?");
    $stmt->execute([$username]);

    echo json_encode(['success' => 'Profile picture deleted successfully.']);
    exit;
}

// لو مفيش عملية
echo json_encode(['error' => 'No action detected.']);


