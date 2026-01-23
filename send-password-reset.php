<?php
header("Content-Type: application/json");

$input = $_POST["forgotEmail"] ?? '';

if (empty($input)) {
    echo json_encode(["status" => "error", "message" => "Please provide your email or username."]);
    exit;
}

// الاتصال بقاعدة البيانات
$mysqli = require __DIR__ . "/database.php";

// البحث عن المستخدم
$sql = "SELECT * FROM teachers WHERE email = ? OR username = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ss", $input, $input);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode(["status" => "error", "message" => "Email or username not registered."]);
    exit;
}

$email = $user["email"];
$username = $user["username"];
$displayName = htmlspecialchars($username ?? explode('@', $email)[0], ENT_QUOTES | ENT_SUBSTITUTE);
$cleanNameForHeader = str_replace(["\r", "\n"], '', $displayName);

// إذا لم يكن الحساب مفعّلًا بعد
if ($user['verified'] != 1) {
    $token = bin2hex(random_bytes(16));

    $update = $mysqli->prepare("UPDATE teachers SET token = ? WHERE id = ?");
    $update->bind_param("si", $token, $user['id']);
    $update->execute();

    // إعداد البريد
    $mail = require __DIR__ . "/mailer.php";
    $mail->isHTML(true);
    $mail->setFrom("academysystem@gmail.com", "Academy System");
    $mail->addAddress($email, $displayName);
    $mail->Subject = "Confirm Your Account";
    $mail->AddEmbeddedImage(__DIR__ . "/badge.png", "badge_cid");
    $mail->Body = <<<END
<div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; padding: 30px; border-radius: 12px; max-width: 600px; margin: auto; border: 1px solid #e2e8f0;">
  <div style="text-align: center;">
    <img src="cid:badge_cid" alt="Academy Badge" style="width: 100px; height: auto; margin-bottom: 20px;">
    <h2 style="color: #1e293b; margin-bottom: 10px;">Hello, {$displayName} 👋</h2>
    <p style="color: #475569; font-size: 15px; line-height: 1.6;">
      You tried to log in, but your account is not yet verified.<br>
      Please verify your email to activate your <strong>Academy System</strong> account.
    </p>
    <a href="https://academy-system.page.gd/verify.php?token={$token}"
       style="display: inline-block; margin-top: 20px; padding: 12px 28px; background-color: #2563eb; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 15px;">
       ✅ Confirm My Email
    </a>
    <p style="color: #64748b; font-size: 14px; margin-top: 25px;">
      If you didn’t try to log in, please ignore this email.<br>
      This link will expire soon for your security.
    </p>
    <hr style="margin: 25px 0; border: none; border-top: 1px solid #e2e8f0;">
    <p style="color: #94a3b8; font-size: 12px;">
      © 2025 Academy System — All rights reserved.
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

// ======================= Reset Password Logic =======================
$token = bin2hex(random_bytes(16));
$token_hash = hash("sha256", $token);
$expiry = date("Y-m-d H:i:s", time() + 1800); // 30 دقيقة

$sql = "UPDATE teachers SET reset_token_hash = ?, reset_token_expires_at = ? WHERE email = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("sss", $token_hash, $expiry, $email);
$stmt->execute();

if ($mysqli->affected_rows > 0) {
    $mail = require __DIR__ . "/mailer.php";
    $mail->isHTML(true);
    $mail->setFrom("academysystem@gmail.com", "Academy System");
    $mail->addAddress($email, $displayName);
    $mail->Subject = "Password Reset for " . $cleanNameForHeader;
    $mail->addEmbeddedImage(__DIR__ . "/badge.png", "badge_cid");

    $mail->Body = <<<END
<div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f9fafb; padding: 30px; border-radius: 12px; max-width: 600px; margin: auto; border: 1px solid #e5e7eb;">
    <div style="text-align: center;">
        <img src="cid:badge_cid" alt="Academy Badge" style="width: 120px; height: auto; margin-bottom: 20px;">
        <h2 style="color: #1e293b; margin-bottom: 10px;">Password Reset Request</h2>
        <p style="color: #475569; font-size: 15px;">
            Hello <strong>{$displayName}</strong>,<br>
            You recently requested to reset your password for your <strong>Academy System</strong> account.
            Click the button below to choose a new password.
        </p>
        <a href="https://academy-system.page.gd/reset-password.php?token={$token}"
           style="display: inline-block; margin-top: 20px; padding: 12px 28px; background-color: #2563eb; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600;">
           Reset Password
        </a>
        <p style="color: #64748b; font-size: 14px; margin-top: 25px;">
            If you didn't request this password reset, please ignore this email.<br>
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
        echo json_encode(["status" => "success", "message" => "A reset link has been sent to your email."]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Mail could not be sent."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Database update failed."]);
}

