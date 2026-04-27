<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_user.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $bio = $conn->real_escape_string($_POST['bio']);
    
    $conn->query("UPDATE users SET full_name='$full_name', bio='$bio' WHERE id=$user_id");
    $_SESSION['user_name'] = $full_name;

    if (!empty($_POST['new_password'])) {
        $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET password='$new_password' WHERE id=$user_id");
    }

    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        $file_name = $_FILES['profile_pic']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed_ext)) {
            $new_image_name = "user_" . $user_id . "_" . time() . "." . $file_ext;
            $upload_path = "uploads/profiles/" . $new_image_name;

            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_path)) {
                $conn->query("UPDATE users SET profile_pic='$new_image_name' WHERE id=$user_id");
                $_SESSION['user_pic'] = $new_image_name;
            }
        } else {
            $message = "<p class='msg-error'>صيغة الصورة غير مدعومة. يرجى رفع JPG أو PNG.</p>";
        }
    }
    
    if(empty($message)) {
        $message = "<p class='msg-success'>تم تحديث بياناتك بنجاح!</p>";
    }
}

$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>الملف الشخصي</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(style_css_href(), ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body>
    <header class="main-header">
        <div class="container navbar">
            <div class="logo">أرشيف المشاريع</div>
            <nav>
                <a href="index.php">الرئيسية</a>
                <a href="logout_user.php" class="nav-link-danger">تسجيل الخروج</a>
            </nav>
        </div>
    </header>

    <div class="profile-shell">
        <h2>ملفي الشخصي</h2>
        <?php echo $message; ?>
        
        <form method="POST" action="" enctype="multipart/form-data">
            
            <img src="uploads/profiles/<?php echo $user['profile_pic'] ? $user['profile_pic'] : 'default_avatar.png'; ?>" class="profile-pic-preview" alt="صورتي">
            
            <div class="form-group">
                <label>تغيير الصورة الشخصية</label>
                <input type="file" name="profile_pic" accept="image/png, image/jpeg">
            </div>

            <div class="form-group">
                <label>الاسم الكامل</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            </div>

            <div class="form-group">
                <label>نبذة عني (Bio)</label>
                <textarea name="bio"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label>كلمة المرور الجديدة (اختياري)</label>
                <input type="password" name="new_password" placeholder="أدخل كلمة المرور الجديدة">
            </div>

            <button type="submit" class="btn btn-primary btn-block">حفظ التعديلات</button>
        </form>
    </div>
</body>
</html>