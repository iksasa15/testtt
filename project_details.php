<?php
session_start();
include 'db_connect.php';

if (isset($_SESSION['admin_logged_in'])) {
} elseif (!isset($_SESSION['user_id'])) {
    header("Location: login_user.php");
    exit();
}
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$sql = "SELECT * FROM projects WHERE id = $id";
$result = $conn->query($sql);
$project = $result->fetch_assoc();

if (!$project) {
    die("<!DOCTYPE html><html lang='ar' dir='rtl'><head><meta charset='UTF-8'><link rel='stylesheet' href='" . htmlspecialchars(style_css_href(), ENT_QUOTES, 'UTF-8') . "'></head><body class='page-project-detail'><h2 class='project-not-found-title'>عذراً، المشروع غير موجود.</h2><p style='text-align:center;margin-top:1rem;'><a href='index.php' class='btn btn-primary'>العودة للرئيسية</a></p></body></html>");
}

$img_main = project_image_src($project['image_url'] ?? null, 'https://via.placeholder.com/960x540?text=Project');
$techs_raw = isset($project['tech_stack']) ? $project['tech_stack'] : '';
$techs_list = array_filter(array_map('trim', explode(',', $techs_raw)));
$has_files = !empty($project['pdf_file']) || !empty($project['project_poster_pdf']);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل المشروع | <?php echo htmlspecialchars($project['title'], ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(style_css_href(), ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body class="page-project-detail">

    <header class="main-header project-detail-header">
        <div class="container navbar project-detail-header__inner">
            <a href="index.php" class="project-detail-brand">أرشيف المشاريع</a>
            <nav class="project-detail-nav">
                <a href="index.php">الرئيسية</a>
            </nav>
        </div>
    </header>

    <main class="project-detail-main">
        <div class="container project-detail-wrap">
            <a href="index.php" class="detail-back-link">
                <span class="detail-back-link__icon" aria-hidden="true">›</span>
                العودة لقائمة المشاريع
            </a>

            <header class="detail-page-head">
                <h1 class="detail-page-head__title"><?php echo htmlspecialchars($project['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
                <div class="detail-badges">
                    <span class="detail-badge"><?php echo htmlspecialchars($project['department'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="detail-badge detail-badge--muted">تخرج <?php echo htmlspecialchars((string) $project['grad_year'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </header>

            <div class="detail-layout">
                <div class="detail-main">
                    <figure class="detail-figure">
                        <img
                            src="<?php echo htmlspecialchars($img_main, ENT_QUOTES, 'UTF-8'); ?>"
                            alt="صورة المشروع"
                            class="detail-hero-img"
                            onerror="this.src='https://via.placeholder.com/960x540?text=صورة+المشروع'">
                    </figure>

                    <section class="detail-panel">
                        <h2 class="detail-panel__title">ملخص المشروع</h2>
                        <div class="detail-panel__body detail-prose">
                            <?php echo nl2br(htmlspecialchars($project['description'], ENT_QUOTES, 'UTF-8')); ?>
                        </div>
                    </section>

                    <?php if (!empty($techs_list)): ?>
                    <section class="detail-panel">
                        <h2 class="detail-panel__title">التقنيات المستخدمة</h2>
                        <div class="detail-tech-row">
                            <?php foreach ($techs_list as $tech): ?>
                                <span class="detail-tech-chip"><?php echo htmlspecialchars($tech, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <?php endif; ?>

                    <?php if (!empty($project['project_poster'])): ?>
                    <section class="detail-panel">
                        <h2 class="detail-panel__title">بوستر المشروع</h2>
                        <div class="detail-poster">
                            <img src="uploads/<?php echo htmlspecialchars($project['project_poster'], ENT_QUOTES, 'UTF-8'); ?>" alt="بوستر المشروع">
                        </div>
                    </section>
                    <?php endif; ?>

                    <section class="detail-panel detail-panel--files detail-panel--files-main">
                        <h2 class="detail-panel__title">ملفات المشروع</h2>
                        <?php if ($has_files): ?>
                            <p class="detail-files-lead">يمكنك تحميل ملفات المشروع المتوفرة للاستفادة من تفاصيل البحث والتصميم.</p>
                            <div class="detail-files-actions detail-files-actions--inline">
                                <?php if (!empty($project['pdf_file'])): ?>
                                    <a href="uploads/documents/<?php echo htmlspecialchars($project['pdf_file'], ENT_QUOTES, 'UTF-8'); ?>" download class="btn btn-primary btn-download-doc">
                                        تحميل وثيقة المشروع
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($project['project_poster_pdf'])): ?>
                                    <a href="uploads/documents/<?php echo htmlspecialchars($project['project_poster_pdf'], ENT_QUOTES, 'UTF-8'); ?>" download class="btn btn-primary btn-download-poster">
                                        تحميل بوستر المشروع (PDF)
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <p class="detail-files-empty">لا توجد ملفات توثيق أو بوسترات (PDF) مرفقة مع هذا المشروع حالياً.</p>
                        <?php endif; ?>
                    </section>
                </div>

                <aside class="detail-sidebar" aria-label="معلومات جانبية">
                    <div class="detail-side-card">
                        <h3 class="detail-side-card__title">معلومات سريعة</h3>
                        <dl class="detail-spec-list">
                            <div class="detail-spec-row">
                                <dt>القسم</dt>
                                <dd><?php echo htmlspecialchars($project['department'], ENT_QUOTES, 'UTF-8'); ?></dd>
                            </div>
                            <div class="detail-spec-row">
                                <dt>سنة التخرج</dt>
                                <dd><?php echo htmlspecialchars((string) $project['grad_year'], ENT_QUOTES, 'UTF-8'); ?></dd>
                            </div>
                        </dl>
                    </div>

                    <?php if (!empty($project['owner_linkedin'])): ?>
                        <a href="<?php echo htmlspecialchars($project['owner_linkedin'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline detail-sidebar__linkedin">
                            تواصل على لينكد إن
                        </a>
                    <?php endif; ?>

                </aside>
            </div>
        </div>
    </main>
</body>
</html>
