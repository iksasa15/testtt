<?php
session_start();
include 'db_connect.php';

$error = '';

if (isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM admins WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin['username'];
            
            header("Location: admin.php");
            exit();
        } else {
            $error = "كلمة المرور غير صحيحة.";
        }
    } else {
        $error = "اسم المستخدم غير موجود.";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل دخول الإدارة</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">
    <div class="login-container">
        <h2>تسجيل دخول الإدارة</h2>
        
        <?php if($error): ?>
            <div style="background-color: #fee2e2; color: #dc2626; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>اسم المستخدم</label>
                <input type="text" name="username" required autocomplete="off">
            </div>
            <div class="form-group">
                <label>كلمة المرور</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block" style="padding: 12px; font-size: 16px;">تسجيل الدخول</button>
            <a href="index.php" style="display:block; margin-top:15px; color:#6b7280; text-align:center;">العودة للرئيسية</a>
        </form>
    </div>
</body>
</html>