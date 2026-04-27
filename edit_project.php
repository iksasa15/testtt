<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login_admin.php");
    exit();
}

$message = '';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>خطأ</title><link rel="stylesheet" href="' . htmlspecialchars(style_css_href(), ENT_QUOTES, 'UTF-8') . '"></head><body class="login-body"><div class="simple-die-page is-error"><h2>عذراً، لم يتم تحديد المشروع المطلوب تعديله!</h2><p><a href="manage_projects.php">العودة للوحة التحكم</a></p></div></body></html>');
}

$project_id = intval($_GET['id']);

$sql = "SELECT * FROM projects WHERE id = $project_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die('<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>غير موجود</title><link rel="stylesheet" href="' . htmlspecialchars(style_css_href(), ENT_QUOTES, 'UTF-8') . '"></head><body class="login-body"><div class="simple-die-page"><h2>المشروع غير موجود.</h2><p><a href="manage_projects.php">العودة للوحة التحكم</a></p></div></body></html>');
}

$project = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $department = $conn->real_escape_string($_POST['department']);
    $grad_year = intval($_POST['grad_year']);
    $tech_stack = $conn->real_escape_string($_POST['tech_stack']);

    $update_sql = "UPDATE projects SET 
                    title='$title', 
                    description='$description', 
                    department='$department', 
                    grad_year=$grad_year, 
                    tech_stack='$tech_stack'";

    $linkedin_raw = trim($_POST['owner_linkedin'] ?? '');
    if ($linkedin_raw === '') {
        $update_sql .= ', owner_linkedin=NULL';
    } else {
        $linkedin_url = preg_match('#^https?://#i', $linkedin_raw) ? $linkedin_raw : 'https://' . ltrim($linkedin_raw, '/');
        if (stripos($linkedin_url, 'linkedin.com') === false) {
            $message = "<div class='auth-error'>رابط LinkedIn غير صالح (يجب أن يحتوي على linkedin.com).</div>";
        } else {
            $update_sql .= ", owner_linkedin='" . $conn->real_escape_string($linkedin_url) . "'";
        }
    }

    // 1. Update Project Image
    if (isset($_FILES['project_image']) && $_FILES['project_image']['error'] == 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_name = $_FILES['project_image']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed_ext)) {
            $new_image_name = "project_" . time() . "_" . rand(100,999) . "." . $file_ext;
            $upload_path = "uploads/" . $new_image_name;

            if (move_uploaded_file($_FILES['project_image']['tmp_name'], $upload_path)) {
                $update_sql .= ", image_url='$new_image_name'";
                
                if (!empty($project['image_url']) && $project['image_url'] != 'default.jpg') {
                    $old_img_path = "uploads/" . $project['image_url'];
                    if (file_exists($old_img_path)) unlink($old_img_path);
                }
            }
        } else {
            $message = "<div class='auth-error'>صيغة الصورة غير مدعومة. يرجى رفع ملف بصيغة JPG, PNG أو WEBP.</div>";
        }
    }

    // 2. Update Poster Image
    if (isset($_FILES['poster_image']) && $_FILES['poster_image']['error'] == 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_name = $_FILES['poster_image']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed_ext)) {
            $new_poster_name = "poster_" . time() . "_" . rand(100,999) . "." . $file_ext;
            $upload_path = "uploads/" . $new_poster_name;

            if (move_uploaded_file($_FILES['poster_image']['tmp_name'], $upload_path)) {
                $update_sql .= ", project_poster='$new_poster_name'";
                
                if (!empty($project['project_poster'])) {
                    $old_poster_path = "uploads/" . $project['project_poster'];
                    if (file_exists($old_poster_path)) unlink($old_poster_path);
                }
            }
        } else {
            $message = "<div class='auth-error'>صيغة صورة البوستر غير مدعومة. يرجى رفع ملف بصيغة JPG, PNG أو WEBP.</div>";
        }
    }

    // 3. Update Project Documentation PDF
    if (isset($_FILES['project_pdf']) && $_FILES['project_pdf']['error'] == 0) {
        $pdf_name = $_FILES['project_pdf']['name'];
        $pdf_ext = strtolower(pathinfo($pdf_name, PATHINFO_EXTENSION));

        if ($pdf_ext == 'pdf') {
            $new_pdf_name = "doc_" . time() . "_" . rand(1000, 9999) . ".pdf";
            $pdf_upload_path = "uploads/documents/" . $new_pdf_name;

            if (move_uploaded_file($_FILES['project_pdf']['tmp_name'], $pdf_upload_path)) {
                $update_sql .= ", pdf_file='$new_pdf_name'";

                if (!empty($project['pdf_file'])) {
                    $old_pdf_path = "uploads/documents/" . $project['pdf_file'];
                    if (file_exists($old_pdf_path)) unlink($old_pdf_path);
                }
            } else {
                $message = "<div class='auth-error'>حدث خطأ أثناء رفع ملف الـ PDF.</div>";
            }
        } else {
            $message = "<div class='auth-error'>صيغة الملف غير مدعومة. يرجى رفع ملف بصيغة PDF فقط.</div>";
        }
    }

    // 4. Update Poster PDF
    if (isset($_FILES['poster_pdf']) && $_FILES['poster_pdf']['error'] == 0) {
        $pdf_name = $_FILES['poster_pdf']['name'];
        $pdf_ext = strtolower(pathinfo($pdf_name, PATHINFO_EXTENSION));

        if ($pdf_ext == 'pdf') {
            $new_poster_pdf_name = "poster_doc_" . time() . "_" . rand(1000, 9999) . ".pdf";
            $pdf_upload_path = "uploads/documents/" . $new_poster_pdf_name;

            if (move_uploaded_file($_FILES['poster_pdf']['tmp_name'], $pdf_upload_path)) {
                $update_sql .= ", project_poster_pdf='$new_poster_pdf_name'";

                if (!empty($project['project_poster_pdf'])) {
                    $old_poster_pdf_path = "uploads/documents/" . $project['project_poster_pdf'];
                    if (file_exists($old_poster_pdf_path)) unlink($old_poster_pdf_path);
                }
            } else {
                $message = "<div class='auth-error'>حدث خطأ أثناء رفع ملف بوستر الـ PDF.</div>";
            }
        } else {
            $message = "<div class='auth-error'>صيغة ملف البوستر غير مدعومة. يرجى رفع ملف بصيغة PDF فقط.</div>";
        }
    }

    $update_sql .= " WHERE id = $project_id";

    if (empty($message)) {
        if ($conn->query($update_sql)) {
            $message = "<div class='flash-success'>تم تحديث بيانات المشروع بنجاح!</div>";
            $project = $conn->query("SELECT * FROM projects WHERE id = $project_id")->fetch_assoc();
        } else {
            $message = "<div class='auth-error'>حدث خطأ أثناء التحديث: " . htmlspecialchars($conn->error) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل المشروع | لوحة التحكم</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(style_css_href(), ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body class="admin-body">

    <div class="admin-layout">

        <?php include 'admin_side_bar.php'; ?>

        <div class="admin-content">

            <header class="admin-topbar">
                <h3>تعديل مشروع</h3>
            </header>

        <div class="form-container">
            <h2><?php echo htmlspecialchars($project['title']); ?></h2>
            
            <?php echo $message; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label>عنوان المشروع</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($project['title']); ?>" required>
                </div>

                <div class="form-group">
                    <label>وصف المشروع وملخصه</label>
                    <textarea name="description" required><?php echo htmlspecialchars($project['description']); ?></textarea>
                </div>

                <div class="row">
                    <div class="col form-group">
                        <label>القسم / الكلية</label>
                        <select name="department" required>
                            <option value="علوم حاسوب" <?php if($project['department'] == 'علوم حاسوب') echo 'selected'; ?>>علوم حاسوب</option>
                            <option value="هندسة برمجيات" <?php if($project['department'] == 'هندسة برمجيات') echo 'selected'; ?>>هندسة برمجيات</option>
                        </select>
                    </div>
                    <div class="col form-group">
                        <label>سنة التخرج</label>
                        <input type="number" name="grad_year" value="<?php echo htmlspecialchars($project['grad_year']); ?>" required min="2000" max="2100">
                    </div>
                </div>

                <div class="form-group">
                    <label>التقنيات المستخدمة (افصل بينها بفاصلة)</label>
                    <input type="text" name="tech_stack" value="<?php echo htmlspecialchars($project['tech_stack']); ?>" required>
                </div>

                <div class="form-group">
                    <label>رابط LinkedIn لصاحب المشروع (اختياري)</label>
                    <input type="url" name="owner_linkedin" class="input-ltr" value="<?php echo htmlspecialchars($project['owner_linkedin'] ?? ''); ?>" placeholder="https://www.linkedin.com/in/username">
                    <small class="form-hint">يُعرض في صفحة تفاصيل المشروع. اتركه فارغاً لإخفاء الرابط.</small>
                </div>

                <div class="form-group upload-zone">
                    <label>صورة واجهة المشروع الحالية</label>
                    <?php if(!empty($project['image_url']) && $project['image_url'] != 'default.jpg'): ?>
                        <img src="uploads/<?php echo $project['image_url']; ?>" alt="صورة المشروع" class="current-image-preview">
                    <?php else: ?>
                        <p class="form-hint">يستخدم هذا المشروع الصورة الافتراضية.</p>
                    <?php endif; ?>
                    
                    <label class="upload-sub-label">تغيير الصورة (اختياري)</label>
                    <input type="file" name="project_image" accept="image/*">
                    <small class="form-hint">اترك هذا الحقل فارغاً إذا كنت لا ترغب بتغيير الصورة الحالية.</small>
                </div>

                <div class="form-group upload-zone">
                    <label>صورة بوستر المشروع الحالية</label>
                    <?php if(!empty($project['project_poster'])): ?>
                        <img src="uploads/<?php echo $project['project_poster']; ?>" alt="بوستر المشروع" class="current-image-preview">
                    <?php else: ?>
                        <p class="form-hint">لا توجد صورة بوستر مرفوعة لهذا المشروع.</p>
                    <?php endif; ?>
                    
                    <label class="upload-sub-label">إضافة أو تغيير صورة البوستر (اختياري)</label>
                    <input type="file" name="poster_image" accept="image/*">
                    <small class="form-hint">اترك هذا الحقل فارغاً إذا كنت لا ترغب بتغيير الصورة.</small>
                </div>

                <div class="row">
                    <div class="col form-group upload-zone upload-zone-accent">
                        <label>ملف توثيق المشروع (PDF)</label>
                        
                        <?php if(!empty($project['pdf_file'])): ?>
                            <div class="pdf-preview-box">
                                <span class="pdf-status-ok">✓ يوجد ملف مرفوع:</span> 
                                <a href="uploads/documents/<?php echo $project['pdf_file']; ?>" target="_blank" rel="noopener noreferrer">معاينة التوثيق الحالي</a>
                            </div>
                        <?php else: ?>
                            <p class="form-hint">لا يوجد ملف توثيق مرفق حالياً.</p>
                        <?php endif; ?>

                        <label>تغيير أو إضافة ملف توثيق (اختياري)</label>
                        <input type="file" name="project_pdf" accept=".pdf">
                        <small class="form-hint">اترك الحقل فارغاً للإبقاء على الملف الحالي.</small>
                    </div>

                    <div class="col form-group upload-zone upload-zone-accent">
                        <label>ملف بوستر المشروع (PDF)</label>
                        
                        <?php if(!empty($project['project_poster_pdf'])): ?>
                            <div class="pdf-preview-box">
                                <span class="pdf-status-ok">✓ يوجد ملف مرفوع:</span> 
                                <a href="uploads/documents/<?php echo $project['project_poster_pdf']; ?>" target="_blank" rel="noopener noreferrer">معاينة البوستر الحالي</a>
                            </div>
                        <?php else: ?>
                            <p class="form-hint">لا يوجد ملف بوستر PDF مرفق حالياً.</p>
                        <?php endif; ?>

                        <label>تغيير أو إضافة بوستر PDF (اختياري)</label>
                        <input type="file" name="poster_pdf" accept=".pdf">
                        <small class="form-hint">اترك الحقل فارغاً للإبقاء على الملف الحالي.</small>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                    <a href="manage_projects.php" class="btn btn-outline">إلغاء والعودة</a>
                </div>

            </form>
        </div>
        </div>
    </div>

</body>
</html>