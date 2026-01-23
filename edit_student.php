<?php
session_start();

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

if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit;
}

$username = $_SESSION['username'];
$stmt = $pdo->prepare("SELECT id FROM teachers WHERE username=?");
$stmt->execute([$username]);
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);
$teacher_id = $teacher['id'] ?? 0;

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$student_id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM students WHERE id=? AND teacher_id=?");
$stmt->execute([$student_id, $teacher_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    $_SESSION['error'] = "Student not found!";
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $class = trim($_POST['class'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $grade = trim($_POST['grade'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if ($name === '') {
        $_SESSION['error'] = "Student name is required!";
        header("Location: edit_student.php?id=$student_id");
        exit;
    }

    // التحقق إذا لم تتغير البيانات
    if (
        $name === $student['student_name'] &&
        $class === $student['student_class'] &&
        $course === $student['student_course'] &&
        $grade === $student['student_grade'] &&
        $phone === $student['student_phone'] &&
        $notes === $student['student_notes']
    ) {
        $_SESSION['error'] = "No changes detected!";
        header("Location: edit_student.php?id=$student_id");
        exit;
    }

    // ✅ التحقق من أن الاسم أو الهاتف غير مستخدم من طالب آخر لنفس المدرس
    $checkStmt = $pdo->prepare("
        SELECT id FROM students 
        WHERE teacher_id = ? 
        AND (student_name = ? OR student_phone = ?)
        AND id != ?
    ");
    $checkStmt->execute([$teacher_id, $name, $phone, $student_id]);
    $duplicate = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($duplicate) {
        $_SESSION['error'] = "A student with this name or phone already exists!";
        header("Location: edit_student.php?id=$student_id");
        exit;
    }

    $stmt = $pdo->prepare("UPDATE students 
                           SET student_name=?, student_class=?, student_course=?, student_grade=?, student_phone=?, student_notes=? 
                           WHERE id=? AND teacher_id=?");
    $stmt->execute([$name, $class, $course, $grade, $phone, $notes, $student_id, $teacher_id]);

    $_SESSION['success'] = "Student updated successfully!";
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="badge.png" type="image/x-icon">
    <style>
        * {
            margin: 0;
            padding: 0;
            font-family: 'Roboto', sans-serif;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #306ABF 60%, #ffffff 40%);
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        #preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(26, 32, 44, 0.95);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loader {
            position: relative;
            width: 80px;
            height: 80px;
            border: 6px solid transparent;
            border-top-color: #306ABF;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .loader::before,
        .loader::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            background: #023c93;
            border-radius: 50%;
            top: -10px;
            left: -10px;
            animation: orbit 1s linear infinite;
        }

        .loader::after {
            background: #fff;
            animation-delay: 0.5s;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes orbit {
            0% {
                transform: rotate(0deg) translateX(50px) rotate(0deg);
            }

            100% {
                transform: rotate(360deg) translateX(50px) rotate(-360deg);
            }
        }

        .card {
            background: #fff;
            padding: 30px;
            border-radius: 14px;
            box-shadow: 0px 6px 20px rgba(0, 0, 0, 0.1);
            width: 600px;
            position: relative;
        }

        h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #306ABF;
        }

        .message {
            padding: 12px 15px;
            border-radius: 10px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 15px;
        }

        .error {
            background: #ffe6e6;
            color: #b10000;
            border: 1px solid #ffb3b3;
        }

        .success {
            background: #e6ffe6;
            color: #007a00;
            border: 1px solid #b3ffb3;
        }

        form {
            display: grid;
            gap: 15px;
        }

        input {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
        }

        button {
            background: #306ABF;
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #244c8a;
        }

        .back {
            display: inline-block;
            margin-top: 15px;
            text-decoration: none;
            color: #306ABF;
            font-weight: 500;
            text-align: center;
        }

        .back:hover {
            text-decoration: underline;
        }

        .icon {
            margin-right: 6px;
            vertical-align: middle;
        }
    </style>
</head>

<body>
    <div id="preloader">
        <div class="loader"></div>
    </div>
    <div class="card">
        <h2><i class='bx bx-edit icon'></i> Edit Student</h2>

        <!-- عرض الرسائل -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php elseif (isset($_SESSION['success'])): ?>
            <div class="message success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <form method="post">
            <input type="text" name="name" value="<?= htmlspecialchars($student['student_name']) ?>" placeholder="Student Name">
            <input type="text" name="class" value="<?= htmlspecialchars($student['student_class']) ?>" placeholder="Class">
            <input type="text" name="course" value="<?= htmlspecialchars($student['student_course']) ?>" placeholder="Course">
            <input type="text" name="grade" value="<?= htmlspecialchars($student['student_grade']) ?>" placeholder="Grade">
            <input type="tel" class="phoneNum" name="phone" value="<?= htmlspecialchars($student['student_phone']) ?>" placeholder="Phone (11 digits only)" maxlength="11">
            <input type="text" name="notes" value="<?= htmlspecialchars($student['student_notes']) ?>" placeholder="Notes">
            <button type="submit"><i class='bx bx-save icon'></i> Save</button>
        </form>
        <a class="back" href="dashboard.php"><i class='bx bx-arrow-back'></i> Back to Dashboard</a>
    </div>
    <script>
        // ===================== PRELOADER =====================
        window.addEventListener("load", () => {
            const preloader = document.getElementById("preloader");
            if (!preloader) return;
            setTimeout(() => {
                preloader.style.opacity = "0";
                preloader.style.transition = "opacity 0.5s ease";
                setTimeout(() => (preloader.style.display = "none"), 500);
            }, 500);
        });
        
        document.querySelectorAll('.phoneNum').forEach(input => {
    input.addEventListener('input', () => {
        const cleanValue = input.value.replace(/\D/g, '');
        if (input.value !== cleanValue) {
            input.value = cleanValue;
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Only numbers are allowed!',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
});
    </script>
</body>

</html>