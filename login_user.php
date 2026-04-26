<?php
session_start();
include 'db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_pic'] = $user['profile_pic'];
        header("Location: index.php");
        exit();
    } else {
            $error = "كلمة المرور غير صحيحة.";
        }
    } else {
        $error = "البريد الإلكتروني غير مسجل.";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل دخول الطلاب</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">
    <div class="login-container">
        <h2>تسجيل دخول الطلاب</h2>
        <?php if($error) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label>البريد الإلكتروني</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>كلمة المرور</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary submit-btn">دخول</button>
            <div style="margin-top: 15px; display:flex; justify-content:space-between;">
                <a href="forgot_password.php" style="color:#e74c3c;">نسيت كلمة المرور؟</a>
                <a href="register.php">إنشاء حساب</a>
            </div>
        </form>
    </div>
</body>
</html>