<?php
session_start();
include 'db_connect.php';

/**
 * يقبل كلمة مرور مكوّنة من أرقام فقط سواء كُتبت بالإنجليزي (1234) أو العربي (١٢٣٤).
 */
function admin_digit_password_variants(string $password): array
{
    $variants = [$password];
    $western = '0123456789';
    $arabic = '';
    for ($i = 0; $i < 10; $i++) {
        $arabic .= mb_chr(0x0660 + $i, 'UTF-8');
    }
    if (preg_match('/^[0-9]+$/', $password)) {
        $variants[] = strtr($password, $western, $arabic);
    }
    if (preg_match('/^[\x{0660}-\x{0669}]+$/u', $password)) {
        $variants[] = strtr($password, $arabic, $western);
    }

    return array_values(array_unique($variants));
}

function admin_password_matches_hash(string $password, string $hash): bool
{
    foreach (admin_digit_password_variants($password) as $candidate) {
        if (password_verify($candidate, $hash)) {
            return true;
        }
    }

    return false;
}

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
        
        if (admin_password_matches_hash($password, $admin['password'])) {
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