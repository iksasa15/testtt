<?php
session_start();
include 'db_connect.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $department = $conn->real_escape_string($_POST['department']); // استقبال القسم
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // التحقق من الموافقة على الشروط
    if (!isset($_POST['terms'])) {
        $message = "<div class='alert alert-danger'>يجب الموافقة على الشروط والأحكام وسياسة الخصوصية.</div>";
    } 
    // التحقق من تطابق كلمتي المرور
    elseif ($password !== $confirm_password) {
        $message = "<div class='alert alert-danger'>كلمتا المرور غير متطابقتين!</div>";
    } 
    // التحقق من طول كلمة المرور
    elseif (strlen($password) < 8) {
        $message = "<div class='alert alert-danger'>يجب أن تحتوي كلمة المرور على 8 أحرف على الأقل.</div>";
    } 
    else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $check_email = $conn->query("SELECT id FROM users WHERE email = '$email'");
        if ($check_email->num_rows > 0) {
            $message = "<div class='alert alert-danger'>البريد الإلكتروني مسجل بالفعل!</div>";
        } else {
            // تحديث الاستعلام لإضافة القسم (department)
            $sql = "INSERT INTO users (full_name, email, password, department) VALUES ('$full_name', '$email', '$hashed_password', '$department')";
            
            if ($conn->query($sql)) {
                $message = "<div class='alert alert-success'>تم إنشاء الحساب بنجاح! <a href='login_user.php'>سجل دخولك الآن</a></div>";
            } else {
                $message = "<div class='alert alert-danger'>حدث خطأ أثناء التسجيل.</div>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب جديد | منصة المشاريع</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="main-wrapper">
        <div class="auth-container">
            
            <div class="auth-form-section">
                <h2>إنشاء حساب جديد</h2>
                <p class="subtitle">أنشئ حسابك لعرض مشاريعك والتواصل مع الزملاء</p>

                <?php echo $message; ?>

                <form method="POST" action="">
                    <div class="input-grid">
                        <div class="input-group">
                            <i class="far fa-user right-icon"></i>
                            <input type="text" name="full_name" placeholder="الاسم الكامل" required>
                        </div>
                        <div class="input-group">
                            <i class="far fa-envelope right-icon"></i>
                            <input type="email" name="email" placeholder="البريد الالكتروني" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <i class="fas fa-lock right-icon"></i>
                        <input type="password" name="password" id="password" placeholder="كلمة المرور" required minlength="8">
                        <i class="far fa-eye left-icon" onclick="togglePassword('password', this)"></i>
                    </div>
                    <small class="password-hint">يجب أن تحتوي على 8 أحرف على الأقل</small>

                    <div class="input-group">
                        <i class="fas fa-lock right-icon"></i>
                        <input type="password" name="confirm_password" id="confirm_password" placeholder="تأكيد كلمة المرور" required minlength="8">
                        <i class="far fa-eye left-icon" onclick="togglePassword('confirm_password', this)"></i>
                    </div>

                    <div class="input-group">
                        <i class="fas fa-building right-icon"></i>
                        <select name="department" required>
                            <option value="" disabled selected>الكلية / القسم</option>
                            <option value="علوم حاسوب">علوم حاسوب</option>
                            <option value="تقنية معلومات">تقنية معلومات</option>
                            <option value="هندسة برمجيات">هندسة برمجيات</option>
                            <option value="نظم معلومات">نظم معلومات</option>
                            <option value="هندسة حاسبات">هندسة حاسبات</option>
                        </select>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" name="terms" id="terms" required>
                        <label for="terms">أوافق على الشروط والأحكام وسياسة الخصوصية</label>
                    </div>

                    <button type="submit" class="submit-btn">إنشاء حساب</button>
                    
                    <div class="login-link">
                        لديك حساب بالفعل؟ <a href="login_user.php">سجل الدخول</a>
                    </div>
                </form>
            </div>

            <div class="auth-illustration">
                <div class="illustration-img"></div>
                
                <div class="features-row">
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                        <h4>امان</h4>
                        <p>بياناتك آمنة</p>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-bolt"></i></div>
                        <h4>سرعة</h4>
                        <p>إنشاء في ثوان معدودة</p>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="fas fa-star"></i></div>
                        <h4>سهل</h4>
                        <p>ابدأ بالعرض الان</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        // دالة إظهار وإخفاء كلمة المرور
        function togglePassword(inputId, iconElement) {
            const input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
                iconElement.classList.remove('fa-eye');
                iconElement.classList.add('fa-eye-slash');
            } else {
                input.type = "password";
                iconElement.classList.remove('fa-eye-slash');
                iconElement.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>