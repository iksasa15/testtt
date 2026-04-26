<?php
include 'db_connect.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $new_password = $_POST['new_password'];
    
    $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
    
    if ($check->num_rows > 0) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET password='$hashed_password' WHERE email='$email'");
        $message = "<p style='color:green;'>تم إعادة ضبط كلمة المرور بنجاح! <a href='login_user.php'>سجل دخولك الآن</a></p>";
    } else {
        $message = "<p style='color:red;'>هذا البريد الإلكتروني غير مسجل لدينا.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>استعادة كلمة المرور</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">
    <div class="login-container">
        <h2>إعادة ضبط كلمة المرور</h2>
        <p style="color:#666; font-size:14px; margin-bottom:20px;">أدخل بريدك الإلكتروني المسجل وكلمة المرور الجديدة.</p>
        <?php echo $message; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label>البريد الإلكتروني</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>كلمة المرور الجديدة</label>
                <input type="password" name="new_password" required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary submit-btn">حفظ كلمة المرور</button>
            <a href="login_user.php" style="display:block; margin-top:15px; text-align:center;">العودة لتسجيل الدخول</a>
        </form>
    </div>
</body>
</html>