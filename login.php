<?php
session_start();
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

$host = 'sql207.infinityfree.com';
$user = 'if0_40113975';
$password = 'WZ1ZxQ8ghVo';
$database = 'if0_40113975_academy';
$connection = new mysqli($host, $user, $password, $database);

if ($connection->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

$login_value = $_POST['username'] ?? '';
$password_value = $_POST['password'] ?? '';

// البحث عن المستخدم بالبريد أو الاسم
$query = $connection->prepare('SELECT id, username, email, password, verified, token FROM teachers WHERE email = ? OR username = ?');
$query->bind_param('ss', $login_value, $login_value);
$query->execute();
$result = $query->get_result();

if ($row = $result->fetch_assoc()) {

    if ($row['verified'] != 1) {
        // إنشاء توكن جديد للتأكيد
        $token = bin2hex(random_bytes(16));
        $update = $connection->prepare("UPDATE teachers SET token = ? WHERE id = ?");
        $update->bind_param("si", $token, $row['id']);
        $update->execute();

        // إرسال بريد التفعيل
        $mail = require __DIR__ . "/mailer.php";
        $mail->isHTML(true);
        $mail->setFrom("academysystem@gmail.com", "Academy System");
        $mail->addAddress($row['email'], $row['username']);
        $mail->Subject = "Confirm Your Account";
        $mail->AddEmbeddedImage(__DIR__ . "/badge.png", "badge_cid");
        $mail->Body = <<<END
<div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f9fafb; padding: 30px; border-radius: 12px; max-width: 600px; margin: auto; border: 1px solid #e5e7eb;">
    <div style="text-align: center;">
        <img src="cid:badge_cid" alt="Academy Badge" style="width: 120px; height: auto; margin-bottom: 20px;">
        <h2 style="color: #1e293b; margin-bottom: 10px;">Confirm Your Email</h2>
        <p style="color: #475569; font-size: 15px;">
            Welcome <strong>{$row['username']}</strong>! Please verify your email address to activate your <strong>Academy System</strong> account.
        </p>
        <a href="https://academy-system.page.gd/verify.php?token=$token"
           style="display: inline-block; margin-top: 20px; padding: 12px 28px; background-color: #22c55e; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600;">
           Confirm Email
        </a>
        <p style="color: #64748b; font-size: 14px; margin-top: 25px;">
            If you didn't register, please ignore this email.<br>
            This link will expire soon for your security.
        </p>
        <hr style="margin: 25px 0; border: none; border-top: 1px solid #e2e8f0;">
        <p style="color: #94a3b8; font-size: 12px;">
            © 2025 Academy System. All rights reserved.
        </p>
    </div>
</div>
END;

        try {
            $mail->send();
            echo json_encode([
                "status" => "unverified",
                "message" => "Your account is not verified. A confirmation email has been resent."
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => "Failed to send confirmation email."
            ]);
        }

        exit;
    }

    // تحقق كلمة المرور
    if (password_verify($password_value, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        echo json_encode([
            "status" => "success",
            "username" => $row['username']
        ]);
        exit;
    } else {
        echo json_encode([
            "status" => "invalid",
            "message" => "Incorrect password."
        ]);
        exit;
    }
} else {
    echo json_encode([
        "status" => "notfound",
        "message" => "No account found with this username or email."
    ]);
    exit;
}

$connection->close();
