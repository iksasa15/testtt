<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$message = '';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("<h2 style='text-align:center; margin-top:50px; color:red;'>عذراً، لم يتم تحديد المشروع المطلوب تعديله! <a href='manage_projects.php'>العودة للوحة التحكم</a></h2>");
}

$project_id = intval($_GET['id']);

$sql = "SELECT * FROM projects WHERE id = $project_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("<h2 style='text-align:center; margin-top:50px;'>المشروع غير موجود. <a href='manage_projects.php'>العودة للوحة التحكم</a></h2>");
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
            $message = "<div style='color:red; margin-bottom:15px;'>رابط LinkedIn غير صالح (يجب أن يحتوي على linkedin.com).</div>";
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
            $message = "<div style='color:red; margin-bottom:15px;'>صيغة الصورة غير مدعومة. يرجى رفع ملف بصيغة JPG, PNG أو WEBP.</div>";
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
            $message = "<div style='color:red; margin-bottom:15px;'>صيغة صورة البوستر غير مدعومة. يرجى رفع ملف بصيغة JPG, PNG أو WEBP.</div>";
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
                $message = "<div style='color:red; margin-bottom:15px;'>حدث خطأ أثناء رفع ملف الـ PDF.</div>";
            }
        } else {
            $message = "<div style='color:red; margin-bottom:15px;'>صيغة الملف غير مدعومة. يرجى رفع ملف بصيغة PDF فقط.</div>";
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
                $message = "<div style='color:red; margin-bottom:15px;'>حدث خطأ أثناء رفع ملف بوستر الـ PDF.</div>";
            }
        } else {
            $message = "<div style='color:red; margin-bottom:15px;'>صيغة ملف البوستر غير مدعومة. يرجى رفع ملف بصيغة PDF فقط.</div>";
        }
    }

    $update_sql .= " WHERE id = $project_id";

    if (empty($message)) {
        if ($conn->query($update_sql)) {
            $message = "<div style='color:green; margin-bottom:15px; text-align:center; font-weight:bold; background:#d4edda; padding:10px; border-radius:5px;'>تم تحديث بيانات المشروع بنجاح!</div>";
            $project = $conn->query("SELECT * FROM projects WHERE id = $project_id")->fetch_assoc();
        } else {
            $message = "<div style='color:red; margin-bottom:15px;'>حدث خطأ أثناء التحديث: " . $conn->error . "</div>";
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
    <link rel="stylesheet" href="style.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 40px auto;
            background: var(--card-bg);
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }
        .form-container h2 { color: var(--primary-color); border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px;}
        .form-group label { display: block; font-weight: bold; margin-bottom: 8px; color: #374151;}
        .form-group input[type="text"], .form-group input[type="number"], .form-group textarea, .form-group select {
            width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 5px; font-family: inherit; font-size: 15px; margin-bottom: 15px; box-sizing: border-box;
        }
        .form-group textarea { height: 150px; resize: vertical; }
        .current-image-preview { width: 100%; max-width: 250px; height: auto; border-radius: 8px; margin-bottom: 10px; border: 1px solid #ddd; }
        .row { display: flex; gap: 20px; }
        .col { flex: 1; }
        @media(max-width: 768px) { .row { flex-direction: column; gap: 0; } }
    </style>
</head>
<body style="background-color: #f8f9fa;">

    <div class="container">
        <div class="form-container">
            <h2>تعديل مشروع: <?php echo htmlspecialchars($project['title']); ?></h2>
            
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
                    <input type="url" name="owner_linkedin" dir="ltr" style="text-align:left;" value="<?php echo htmlspecialchars($project['owner_linkedin'] ?? ''); ?>" placeholder="https://www.linkedin.com/in/username">
                    <small style="display:block; color:#666; margin-top:6px;">يُعرض في صفحة تفاصيل المشروع. اتركه فارغاً لإخفاء الرابط.</small>
                </div>

                <div class="form-group" style="background: #f9fafb; padding: 15px; border-radius: 8px; border: 1px dashed #ccc; margin-bottom: 15px;">
                    <label>صورة واجهة المشروع الحالية</label>
                    <?php if(!empty($project['image_url']) && $project['image_url'] != 'default.jpg'): ?>
                        <img src="uploads/<?php echo $project['image_url']; ?>" alt="صورة المشروع" class="current-image-preview">
                    <?php else: ?>
                        <p style="color: #666; font-size: 14px;">يستخدم هذا المشروع الصورة الافتراضية.</p>
                    <?php endif; ?>
                    
                    <label style="margin-top: 10px;">تغيير الصورة (اختياري)</label>
                    <input type="file" name="project_image" accept="image/*">
                    <small style="display:block; color: #666; margin-top:5px;">اترك هذا الحقل فارغاً إذا كنت لا ترغب بتغيير الصورة الحالية.</small>
                </div>

                <div class="form-group" style="background: #f9fafb; padding: 15px; border-radius: 8px; border: 1px dashed #ccc; margin-bottom: 15px;">
                    <label>صورة بوستر المشروع الحالية</label>
                    <?php if(!empty($project['project_poster'])): ?>
                        <img src="uploads/<?php echo $project['project_poster']; ?>" alt="بوستر المشروع" class="current-image-preview">
                    <?php else: ?>
                        <p style="color: #666; font-size: 14px;">لا توجد صورة بوستر مرفوعة لهذا المشروع.</p>
                    <?php endif; ?>
                    
                    <label style="margin-top: 10px;">إضافة أو تغيير صورة البوستر (اختياري)</label>
                    <input type="file" name="poster_image" accept="image/*">
                    <small style="display:block; color: #666; margin-top:5px;">اترك هذا الحقل فارغاً إذا كنت لا ترغب بتغيير الصورة.</small>
                </div>

                <div class="row">
                    <div class="col form-group" style="background: #eef2ff; padding: 15px; border-radius: 8px; border: 1px dashed #6366f1; margin-bottom: 15px;">
                        <label style="color: #4f46e5;">ملف توثيق المشروع (PDF)</label>
                        
                        <?php if(!empty($project['pdf_file'])): ?>
                            <div style="margin-bottom: 10px; padding: 10px; background: white; border-radius: 5px; border: 1px solid #c7d2fe;">
                                <span style="color: #10b981; font-weight: bold;">✓ يوجد ملف مرفوع:</span> 
                                <a href="uploads/documents/<?php echo $project['pdf_file']; ?>" target="_blank" style="color: #2563eb; text-decoration: underline;">معاينة التوثيق الحالي</a>
                            </div>
                        <?php else: ?>
                            <p style="color: #666; font-size: 14px; margin-bottom: 10px;">لا يوجد ملف توثيق مرفق حالياً.</p>
                        <?php endif; ?>

                        <label style="margin-top: 10px; color: #4f46e5;">تغيير أو إضافة ملف توثيق (اختياري)</label>
                        <input type="file" name="project_pdf" accept=".pdf">
                        <small style="display:block; color: #666; margin-top:5px;">اترك الحقل فارغاً للإبقاء على الملف الحالي.</small>
                    </div>

                    <div class="col form-group" style="background: #eef2ff; padding: 15px; border-radius: 8px; border: 1px dashed #6366f1; margin-bottom: 15px;">
                        <label style="color: #4f46e5;">ملف بوستر المشروع (PDF)</label>
                        
                        <?php if(!empty($project['project_poster_pdf'])): ?>
                            <div style="margin-bottom: 10px; padding: 10px; background: white; border-radius: 5px; border: 1px solid #c7d2fe;">
                                <span style="color: #10b981; font-weight: bold;">✓ يوجد ملف مرفوع:</span> 
                                <a href="uploads/documents/<?php echo $project['project_poster_pdf']; ?>" target="_blank" style="color: #2563eb; text-decoration: underline;">معاينة البوستر الحالي</a>
                            </div>
                        <?php else: ?>
                            <p style="color: #666; font-size: 14px; margin-bottom: 10px;">لا يوجد ملف بوستر PDF مرفق حالياً.</p>
                        <?php endif; ?>

                        <label style="margin-top: 10px; color: #4f46e5;">تغيير أو إضافة بوستر PDF (اختياري)</label>
                        <input type="file" name="poster_pdf" accept=".pdf">
                        <small style="display:block; color: #666; margin-top:5px;">اترك الحقل فارغاً للإبقاء على الملف الحالي.</small>
                    </div>
                </div>

                <div style="display: flex; gap: 15px; margin-top: 25px;">
                    <button type="submit" class="btn btn-primary" style="flex: 2;">حفظ التعديلات</button>
                    <a href="manage_projects.php" class="btn btn-outline" style="flex: 1; text-align: center;">إلغاء والعودة</a>
                </div>

            </form>
        </div>
    </div>

</body>
</html>