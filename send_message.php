<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $subject = htmlspecialchars(trim($_POST['subject']));
    $message = htmlspecialchars(trim($_POST['message']));
    
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'academysystem11@gmail.com';
        $mail->Password = 'vdnfqxdiqunilytu';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($email, $name);
        $mail->addAddress('ahmedelsayed6179@gmail.com', 'Ahmed Elsayed');

        $mail->isHTML(true);
        $mail->Subject = 'New Message from Academy System Contact Form';
        $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #e0e0e0; border-radius: 10px; padding: 20px; background: #f9f9f9;'>
        <h2 style='color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px;'>New Contact Form Message</h2>
        <p style='margin: 10px 0;'><strong>Name:</strong> <span style='color: #34495e;'>$name</span></p>
        <p style='margin: 10px 0;'><strong>Email:</strong> <span style='color: #34495e;'>$email</span></p>
        <p style='margin: 10px 0;'><strong>Subject:</strong> <span style='color: #34495e;'>$subject</span></p>
        <p style='margin: 10px 0;'><strong>Message:</strong><br><span style='color: #34495e; line-height: 1.5;'>$message</span></p>
        <hr style='border: none; border-top: 1px solid #ccc; margin: 20px 0;'/>
        <p style='font-size: 12px; color: #7f8c8d;'>This message was sent from the Academy System contact form.</p>
       </div>
       ";

        $mail->send();
        echo json_encode(["status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}
