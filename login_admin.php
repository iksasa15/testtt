<?php
session_start();
include 'db_connect.php';

/** يطابق graduation_projects.sql — كلمة المرور: ١٢٣٤ أو 1234 (بعد التحويل الصحيح UTF-8). */
const ADMIN_DEFAULT_PASSWORD_HASH = '$2y$12$A0WscPn4FxKtYh380dY.IOcARz0fr4DV0U8a4U6/DWjuu/JK1pt7K';

/** تحويل 0-9 إلى أرقام عربية هندية (U+0660–U+0669). strtr لا يصلح مع UTF-8. */
function western_digits_to_arabic_indic(string $s): string
{
    $out = '';
    $len = strlen($s);
    for ($i = 0; $i < $len; $i++) {
        $b = $s[$i];
        if ($b >= '0' && $b <= '9') {
            $out .= mb_chr(0x0660 + (ord($b) - 48), 'UTF-8');
        } else {
            $out .= $b;
        }
    }

    return $out;
}

function arabic_indic_digits_to_western(string $s): string
{
    $out = '';
    foreach (preg_split('//u', $s, -1, PREG_SPLIT_NO_EMPTY) as $ch) {
        $cp = mb_ord($ch, 'UTF-8');
        if ($cp >= 0x0660 && $cp <= 0x0669) {
            $out .= chr(48 + ($cp - 0x0660));
        } else {
            $out .= $ch;
        }
    }

    return $out;
}

/**
 * يقبل كلمة مرور من أرقام فقط: إنجليزي (1234) أو عربي هندي (١٢٣٤).
 */
function admin_digit_password_variants(string $password): array
{
    $variants = [$password];
    if (preg_match('/^[0-9]+$/', $password)) {
        $variants[] = western_digits_to_arabic_indic($password);
    }
    if (preg_match('/^[\x{0660}-\x{0669}]+$/u', $password)) {
        $variants[] = arabic_indic_digits_to_western($password);
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

/** يُنشئ صف admin إن لم يكن موجوداً (مثلاً بعد نشر Railway دون إعادة استيراد SQL). */
function ensure_default_admin_exists(mysqli $conn): void
{
    $stmt = $conn->prepare(
        'INSERT INTO admins (id, username, password) SELECT 1, ?, ? WHERE NOT EXISTS (SELECT 1 FROM admins WHERE username = ? LIMIT 1)'
    );
    if ($stmt === false) {
        return;
    }
    $u = 'admin';
    $h = ADMIN_DEFAULT_PASSWORD_HASH;
    $stmt->bind_param('sss', $u, $h, $u);
    $stmt->execute();
    $stmt->close();
}

/**
 * مرة واحدة: ضع في Railway على خدمة testtt المتغير FIX_ADMIN_PASSWORD=1 ثم افتح صفحة تسجيل الدخول، ثم احذف المتغير.
 * يُعاد ضبط hash كلمة مرور admin للقيمة الافتراضية (١٢٣٤ / 1234).
 */
function maybe_reset_admin_password_from_env(mysqli $conn): void
{
    if (getenv('FIX_ADMIN_PASSWORD') !== '1') {
        return;
    }
    $h = ADMIN_DEFAULT_PASSWORD_HASH;
    $u = 'admin';
    $stmt = $conn->prepare('UPDATE admins SET password = ? WHERE username = ?');
    if ($stmt === false) {
        return;
    }
    $stmt->bind_param('ss', $h, $u);
    $stmt->execute();
    $stmt->close();
}

ensure_default_admin_exists($conn);
maybe_reset_admin_password_from_env($conn);

$error = '';

if (isset($_SESSION['admin_logged_in'])) {
    header('Location: admin.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $stmt = $conn->prepare('SELECT id, username, password FROM admins WHERE username = ? LIMIT 1');
    if ($stmt === false) {
        $error = 'تعذر التحقق من الحساب.';
    } else {
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();

            if (admin_password_matches_hash($password, $admin['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $admin['username'];

                header('Location: admin.php');
                exit();
            }
            $error = 'كلمة المرور غير صحيحة.';
        } else {
            $error = 'اسم المستخدم غير موجود.';
        }
        $stmt->close();
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
