<?php
session_start();
header("Content-Type: application/json");

// التحقق من طريقة الطلب
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

// إعدادات قاعدة البيانات
$host = 'sql207.infinityfree.com';
$user = 'if0_40113975';
$password = 'WZ1ZxQ8ghVo';
$database = 'if0_40113975_academy';
$connection = new mysqli($host, $user, $password, $database);
if ($connection->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

// بيانات المستخدم
$username_value = $_POST['username'] ?? '';
$email_value = $_POST['email'] ?? '';
$password_value_raw = $_POST['password'] ?? '';

// البحث عن المستخدم الحالي
$checkUser = $connection->prepare('SELECT id, verified, token FROM teachers WHERE username=? OR email=?');
$checkUser->bind_param('ss', $username_value, $email_value);
$checkUser->execute();
$result = $checkUser->get_result();

// الحالة 1: المستخدم موجود
if ($row = $result->fetch_assoc()) {
    if ($row['verified'] == 0) {
        // إعادة إرسال رابط التأكيد
        $token = bin2hex(random_bytes(16)); // حفظ التوكن نفسه
        $update = $connection->prepare("UPDATE teachers SET token=? WHERE id=?");
        $update->bind_param("si", $token, $row['id']);
        $update->execute();

        // إرسال البريد
        $mail = require __DIR__ . "/mailer.php";
        $mail->isHTML(true);
        $mail->setFrom("academysystem@gmail.com", "Academy System");
        $mail->addAddress($email_value, $username_value);
        $mail->Subject = "Confirm Your Account (Resent)";
        $mail->AddEmbeddedImage(__DIR__ . "/badge.png", "badge_cid");
        $mail->Body = <<<END
<div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#f9fafb; padding:30px; border-radius:12px; max-width:600px; margin:auto; border:1px solid #e5e7eb;">
    <div style="text-align:center;">
        <img src="cid:badge_cid" alt="Academy Badge" style="width:120px; height:auto; margin-bottom:20px;">
        <h2>Confirm Your Email</h2>
        <p>Welcome <strong>$username_value</strong>! You haven't verified your email yet. Click below to activate your account.</p>
        <a href="https://academy-system.page.gd/verify.php?token=$token" style="display:inline-block; padding:12px 28px; background-color:#22c55e; color:#fff; text-decoration:none; border-radius:8px; font-weight:600;">Confirm Email</a>
        <p>If you didn't register, ignore this email.</p>
    </div>
</div>
END;
        try {
            $mail->send();
            echo json_encode(["status" => "pending_verification", "message" => "Your account is not verified yet. A new confirmation email has been sent."]);
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Mail could not be sent."]);
        }
        exit;
    } else {
        // المستخدم موجود ومفعل مسبقًا
        echo json_encode(["status" => "error", "type" => "both", "message" => "Account already exists and is verified."]);
        exit;
    }
}

// الحالة 2: مستخدم جديد → التحقق من قوة كلمة المرور
if (preg_match('/^[0-9]+$/', $password_value_raw)) {
    echo json_encode(["status" => "error", "type" => "weak_password"]);
    exit;
}
if (strlen($password_value_raw) < 8) {
    echo json_encode(["status" => "error", "type" => "short_password"]);
    exit;
}
if (!preg_match("/[a-z]/i", $password_value_raw)) {
    echo json_encode(["status" => "error", "type" => "Password must contain at least one letter."]);
    exit;
}

$password_value = password_hash($password_value_raw, PASSWORD_DEFAULT);

// إنشاء توكن جديد
$token = bin2hex(random_bytes(16)); // حفظ التوكن نفسه

// إدخال المستخدم الجديد في قاعدة البيانات
$query = $connection->prepare('INSERT INTO teachers(username, email, password, token, verified) VALUES (?, ?, ?, ?, 0)');
$query->bind_param('ssss', $username_value, $email_value, $password_value, $token);
if (!$query->execute()) {
    echo json_encode(["status" => "error", "type" => "both"]);
    exit;
}

// إرسال بريد التأكيد للمستخدم الجديد
$mail = require __DIR__ . "/mailer.php";
$mail->isHTML(true);
$mail->setFrom("academysystem@gmail.com", "Academy System");
$mail->addAddress($email_value, $username_value);
$mail->Subject = "Confirm Your Account";
$mail->AddEmbeddedImage(__DIR__ . "/badge.png", "badge_cid");
$mail->Body = <<<END
<div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#f9fafb; padding:30px; border-radius:12px; max-width:600px; margin:auto; border:1px solid #e5e7eb;">
    <div style="text-align:center;">
        <img src="cid:badge_cid" alt="Academy Badge" style="width:120px; height:auto; margin-bottom:20px;">
        <h2>Confirm Your Email</h2>
        <p>Welcome <strong>$username_value</strong>! Please verify your email address to activate your <strong>Academy System</strong> account.</p>
        <a href="https://academy-system.page.gd/verify.php?token=$token" style="display:inline-block; padding:12px 28px; background-color:#22c55e; color:#fff; text-decoration:none; border-radius:8px; font-weight:600;">Confirm Email</a>
        <p>If you didn't register, ignore this email.</p>
    </div>
</div>
END;

try {
    $mail->send();
    $_SESSION['username'] = $username_value;
    echo json_encode(["status" => "success", "message" => "Account created. Please check your email to verify."]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Mail could not be sent."]);
}

$connection->close();
