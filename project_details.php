<?php
session_start();
include 'db_connect.php';

if (isset($_SESSION['admin_logged_in'])) {
} else if (!isset($_SESSION['user_id'])) {
    header("Location: login_user.php");
    exit();
}
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql = "SELECT * FROM projects WHERE id = $id";
$result = $conn->query($sql);
$project = $result->fetch_assoc();

if (!$project) {
    die("<h2 style='text-align:center; margin-top:50px;'>عذراً، المشروع غير موجود.</h2>");
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تفاصيل المشروع | <?php echo htmlspecialchars($project['title']); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="main-header">
        <div class="container navbar">
            <div class="logo">أرشيف المشاريع</div>
            <nav>
                <a href="index.php">الرئيسية</a>
            </nav>
        </div>
    </header>

    <main class="container project-details-container">
        <div class="project-header">
            <h1><?php echo htmlspecialchars($project['title']); ?></h1>
            <div class="project-meta">
                <span>القسم: <?php echo htmlspecialchars($project['department']); ?></span>
                <span>سنة التخرج: <?php echo htmlspecialchars($project['grad_year']); ?></span>
            </div>
        </div>

        <div class="details-grid">
            <div class="main-content">
                <section class="project-gallery">
                    <img src="uploads/<?php echo $project['image_url'] ? $project['image_url'] : 'placeholder.jpg'; ?>" class="main-image" onerror="this.src='https://via.placeholder.com/800x450'">
                </section>
                
                <section class="project-section">
                    <h2>ملخص المشروع</h2>
                    <p><?php echo nl2br(htmlspecialchars($project['description'])); ?></p>
                </section>
                
                <section class="project-section">
                    <h2>التقنيات المستخدمة</h2>
                    <div class="tech-tags">
                        <?php 
                        $techs = explode(',', $project['tech_stack']);
                        foreach($techs as $tech) {
                            echo '<span class="tag">'.htmlspecialchars(trim($tech)).'</span>';
                        }
                        ?>
                    </div>
                </section>

                <?php if(!empty($project['project_poster'])): ?>
                <section class="project-section" style="margin-top: 30px;">
                    <h2>بوستر المشروع</h2>
                    <div style="text-align: center; background: #f9fafb; padding: 20px; border-radius: 8px; border: 1px solid #eee;">
                        <img src="uploads/<?php echo htmlspecialchars($project['project_poster']); ?>" style="max-width: 100%; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);" alt="بوستر المشروع">
                    </div>
                </section>
                <?php endif; ?>
                
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center;">
                    <h3>ملفات المشروع</h3>
                
                    <?php if(!empty($project['pdf_file']) || !empty($project['project_poster_pdf'])): ?>
                        <p style="color: #555; margin-bottom: 20px;">يمكنك تحميل ملفات المشروع المتوفرة للاستفادة من تفاصيل البحث والتصميم.</p>
                        
                        <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                            <?php if(!empty($project['pdf_file'])): ?>
                                <a href="uploads/documents/<?php echo htmlspecialchars($project['pdf_file']); ?>" download class="btn btn-primary" style="background-color: #10b981; padding: 12px 25px; font-size: 1.1rem; border: none; color: white; text-decoration: none; border-radius: 5px;">
                                    📥 تحميل وثيقة المشروع
                                </a>
                            <?php endif; ?>

                            <?php if(!empty($project['project_poster_pdf'])): ?>
                                <a href="uploads/documents/<?php echo htmlspecialchars($project['project_poster_pdf']); ?>" download class="btn btn-primary" style="background-color: #3b82f6; padding: 12px 25px; font-size: 1.1rem; border: none; color: white; text-decoration: none; border-radius: 5px;">
                                    🖼️ تحميل بوستر المشروع (PDF)
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p style="color: #9ca3af; font-style: italic; background: #f9fafb; padding: 15px; border-radius: 8px;">
                            لا توجد ملفات توثيق أو بوسترات (PDF) مرفقة مع هذا المشروع حالياً.
                        </p>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </main>
</body>
</html>