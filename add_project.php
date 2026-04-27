<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login_admin.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $department = $conn->real_escape_string($_POST['department']);
    $grad_year = intval($_POST['grad_year']);
    $tech_stack = $conn->real_escape_string($_POST['tech_stack']);
    
    // Default values
    $image_url = 'default.jpg';
    $pdf_file = NULL;
    $project_poster = NULL;
    $project_poster_pdf = NULL;

    // 1. Upload Project Image
    if (isset($_FILES['project_image']) && $_FILES['project_image']['error'] == 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_name = $_FILES['project_image']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed_ext)) {
            $new_image_name = "project_" . time() . "_" . rand(1000, 9999) . "." . $file_ext;
            $upload_path = "uploads/" . $new_image_name;

            if (move_uploaded_file($_FILES['project_image']['tmp_name'], $upload_path)) {
                $image_url = $new_image_name;
            } else {
                $message = "<div class='auth-error'>حدث خطأ أثناء رفع الصورة إلى المجلد.</div>";
            }
        } else {
            $message = "<div class='auth-error'>صيغة الصورة غير مدعومة. يرجى رفع ملف بصيغة JPG, PNG أو WEBP.</div>";
        }
    }

    // 2. Upload Project Poster Image
    if (isset($_FILES['poster_image']) && $_FILES['poster_image']['error'] == 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_name = $_FILES['poster_image']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed_ext)) {
            $new_poster_name = "poster_" . time() . "_" . rand(1000, 9999) . "." . $file_ext;
            $upload_path = "uploads/" . $new_poster_name;

            if (move_uploaded_file($_FILES['poster_image']['tmp_name'], $upload_path)) {
                $project_poster = $new_poster_name;
            } else {
                $message = "<div class='auth-error'>حدث خطأ أثناء رفع صورة البوستر.</div>";
            }
        } else {
            $message = "<div class='auth-error'>صيغة صورة البوستر غير مدعومة.</div>";
        }
    }

    // 3. Upload Project Documentation PDF
    if (isset($_FILES['project_pdf']) && $_FILES['project_pdf']['error'] == 0) {
        $pdf_name = $_FILES['project_pdf']['name'];
        $pdf_ext = strtolower(pathinfo($pdf_name, PATHINFO_EXTENSION));

        if ($pdf_ext == 'pdf') {
            $new_pdf_name = "doc_" . time() . "_" . rand(1000, 9999) . ".pdf";
            $pdf_upload_path = "uploads/documents/" . $new_pdf_name;

            if (move_uploaded_file($_FILES['project_pdf']['tmp_name'], $pdf_upload_path)) {
                $pdf_file = $new_pdf_name;
            } else {
                $message = "<div class='auth-error'>حدث خطأ أثناء رفع ملف الـ PDF الخاص بالمشروع.</div>";
            }
        } else {
            $message = "<div class='auth-error'>صيغة الملف غير مدعومة. يرجى رفع ملف بصيغة PDF فقط للتوثيق.</div>";
        }
    }

    // 4. Upload Poster PDF
    if (isset($_FILES['poster_pdf']) && $_FILES['poster_pdf']['error'] == 0) {
        $pdf_name = $_FILES['poster_pdf']['name'];
        $pdf_ext = strtolower(pathinfo($pdf_name, PATHINFO_EXTENSION));

        if ($pdf_ext == 'pdf') {
            $new_poster_pdf_name = "poster_doc_" . time() . "_" . rand(1000, 9999) . ".pdf";
            $pdf_upload_path = "uploads/documents/" . $new_poster_pdf_name;

            if (move_uploaded_file($_FILES['poster_pdf']['tmp_name'], $pdf_upload_path)) {
                $project_poster_pdf = $new_poster_pdf_name;
            } else {
                $message = "<div class='auth-error'>حدث خطأ أثناء رفع ملف الـ PDF الخاص بالبوستر.</div>";
            }
        } else {
            $message = "<div class='auth-error'>صيغة ملف البوستر غير مدعومة. يرجى رفع ملف بصيغة PDF فقط.</div>";
        }
    }

    // Insert into database if no errors
    if (empty($message)) {
        $owner_linkedin_sql = 'NULL';
        $linkedin_raw = trim($_POST['owner_linkedin'] ?? '');
        if ($linkedin_raw !== '') {
            $linkedin_url = preg_match('#^https?://#i', $linkedin_raw) ? $linkedin_raw : 'https://' . ltrim($linkedin_raw, '/');
            if (stripos($linkedin_url, 'linkedin.com') === false) {
                $message = "<div class='auth-error'>رابط LinkedIn غير صالح (يجب أن يكون من نطاق linkedin.com).</div>";
            } else {
                $owner_linkedin_sql = "'" . $conn->real_escape_string($linkedin_url) . "'";
            }
        }
    }

    if (empty($message)) {
        $insert_sql = "INSERT INTO projects (title, description, department, grad_year, tech_stack, owner_linkedin, project_poster, project_poster_pdf, image_url, pdf_file) 
                       VALUES ('$title', '$description', '$department', $grad_year, '$tech_stack', $owner_linkedin_sql, " . 
                       ($project_poster ? "'$project_poster'" : "NULL") . ", " . 
                       ($project_poster_pdf ? "'$project_poster_pdf'" : "NULL") . ", " .
                       "'$image_url', " . 
                       ($pdf_file ? "'$pdf_file'" : "NULL") . ")";

        if ($conn->query($insert_sql)) {
            $message = "<div class='flash-success'>تمت إضافة المشروع بنجاح! <a href='manage_projects.php'>العودة للمشاريع</a></div>";
        } else {
            $message = "<div class='auth-error'>حدث خطأ أثناء الحفظ: " . htmlspecialchars($conn->error) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>إضافة مشروع جديد | لوحة التحكم</title>
        <link rel="stylesheet" href="<?php echo htmlspecialchars(style_css_href(), ENT_QUOTES, 'UTF-8'); ?>">
    </head>
    <body class="admin-body">

        <div class="admin-layout">

        <?php include 'admin_side_bar.php' ?>

        <main class="admin-content">
            
            <header class="admin-topbar">
                <div class="admin-topbar__titles">
                    <h3>إضافة مشروع تخرج</h3>
                    <p class="admin-topbar__subtitle">املأ الحقول وارفع الصور والملفات المطلوبة ثم أرسل النموذج لإدراج المشروع في الأرشيف.</p>
                </div>
            </header>

            <div class="form-container">

                <?php echo $message; ?>

                <form method="POST" action="" enctype="multipart/form-data">
                    
                    <div class="form-group">
                        <label>عنوان المشروع</label>
                        <input type="text" name="title" placeholder="اسم المشروع..." required>
                    </div>

                    <div class="form-group">
                        <label>وصف المشروع وملخصه</label>
                        <textarea name="description" placeholder="اكتب نبذة تفصيلية عن فكرة المشروع والهدف منه..." required></textarea>
                    </div>

                    <div class="row">
                        <div class="col form-group">
                            <label>القسم / الكلية</label>
                            <select name="department" required>
                                <option value="" disabled selected>-- اختر القسم --</option>
                                <option value="علوم حاسوب">علوم حاسوب</option>
                                <option value="هندسة برمجيات">هندسة برمجيات</option>
                            </select>
                        </div>
                        <div class="col form-group">
                            <label>سنة التخرج</label>
                            <input type="number" name="grad_year" value="<?php echo date('Y'); ?>" required min="2000" max="2100">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>التقنيات المستخدمة (افصل بينها بفاصلة)</label>
                        <input type="text" name="tech_stack" placeholder="مثال: React.js, Node.js, MongoDB" required>
                    </div>

                    <div class="form-group">
                        <label>رابط LinkedIn لصاحب المشروع (اختياري)</label>
                        <input type="url" name="owner_linkedin" class="input-ltr" placeholder="https://www.linkedin.com/in/username">
                        <small class="form-hint">يُعرض في صفحة تفاصيل المشروع للزوار.</small>
                    </div>

                    <div class="form-group upload-zone">
                        <label>صورة واجهة المشروع (اختياري ولكن مفضل)</label>
                        <input type="file" name="project_image" accept="image/*">
                        <p class="form-hint">سيتم استخدام صورة افتراضية في حال لم تقم برفع صورة.</p>
                    </div>

                    <div class="form-group upload-zone">
                        <label>صورة بوستر المشروع (اختياري)</label>
                        <input type="file" name="poster_image" accept="image/*">
                        <p class="form-hint">ارفع تصميم البوستر كصورة (JPG, PNG).</p>
                    </div>

                    <div class="row">
                        <div class="col form-group upload-zone">
                            <label>ملف توثيق المشروع (PDF)</label>
                            <input type="file" name="project_pdf" accept=".pdf">
                        </div>

                        <div class="col form-group upload-zone">
                            <label>ملف بوستر المشروع (PDF)</label>
                            <input type="file" name="poster_pdf" accept=".pdf">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">رفع وإضافة المشروع</button>
                        <a href="admin.php" class="btn btn-outline">إلغاء والعودة</a>
                    </div>

                </form>
            </div>
        </main>
        </div>
    </body>
</html>