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
    <link rel="stylesheet" href="<?php echo htmlspecialchars(style_css_href(), ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body class="login-body">
    <div class="login-container">
        <h2>تسجيل دخول الإدارة</h2>
        
        <?php if ($error): ?><div class="auth-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>اسم المستخدم</label>
                <input type="text" name="username" required autocomplete="username">
            </div>
            <div class="form-group">
                <label>كلمة المرور</label>
                <input type="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary btn-block">تسجيل الدخول</button>
            <a href="index.php" class="login-back-link">العودة للرئيسية</a>
        </form>
    </div>
</body>
</html>