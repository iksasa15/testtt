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
    die("<!DOCTYPE html><html lang='ar' dir='rtl'><head><meta charset='UTF-8'><link rel='stylesheet' href='" . htmlspecialchars(style_css_href(), ENT_QUOTES, 'UTF-8') . "'></head><body><h2 class='project-not-found-title'>عذراً، المشروع غير موجود.</h2></body></html>");
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تفاصيل المشروع | <?php echo htmlspecialchars($project['title']); ?></title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(style_css_href(), ENT_QUOTES, 'UTF-8'); ?>">
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
                <?php if (!empty($project['owner_linkedin'])): ?>
                    <span>
                        <a href="<?php echo htmlspecialchars($project['owner_linkedin'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline btn-linkedin-inline">LinkedIn — صاحب المشروع</a>
                    </span>
                <?php endif; ?>
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
                <section class="project-section project-section-spaced">
                    <h2>بوستر المشروع</h2>
                    <div class="poster-frame">
                        <img src="uploads/<?php echo htmlspecialchars($project['project_poster']); ?>" alt="بوستر المشروع">
                    </div>
                </section>
                <?php endif; ?>
                
                <div class="project-files-block">
                    <h3>ملفات المشروع</h3>
                
                    <?php if(!empty($project['pdf_file']) || !empty($project['project_poster_pdf'])): ?>
                        <p class="project-files-intro">يمكنك تحميل ملفات المشروع المتوفرة للاستفادة من تفاصيل البحث والتصميم.</p>
                        
                        <div class="project-files-actions">
                            <?php if(!empty($project['pdf_file'])): ?>
                                <a href="uploads/documents/<?php echo htmlspecialchars($project['pdf_file']); ?>" download class="btn btn-primary btn-download-doc">
                                    📥 تحميل وثيقة المشروع
                                </a>
                            <?php endif; ?>

                            <?php if(!empty($project['project_poster_pdf'])): ?>
                                <a href="uploads/documents/<?php echo htmlspecialchars($project['project_poster_pdf']); ?>" download class="btn btn-primary btn-download-poster">
                                    🖼️ تحميل بوستر المشروع (PDF)
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p class="project-files-empty">
                            لا توجد ملفات توثيق أو بوسترات (PDF) مرفقة مع هذا المشروع حالياً.
                        </p>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </main>
</body>
</html>