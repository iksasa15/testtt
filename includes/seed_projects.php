<?php

declare(strict_types=1);

/**
 * روابط صور ثابتة من Unsplash CDN (أكثر موثوقية من خدمات الصور العشوائية مثل picsum).
 *
 * @return array<int, string>
 */
function sample_project_image_urls(): array
{
    return [
        1 => 'https://images.unsplash.com/photo-1461749280684-dccba630e2f6?auto=format&fit=crop&w=800&h=450&q=80',
        2 => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?auto=format&fit=crop&w=800&h=450&q=80',
        3 => 'https://images.unsplash.com/photo-1504639725590-34d0984388bd?auto=format&fit=crop&w=800&h=450&q=80',
        4 => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=800&h=450&q=80',
        5 => 'https://images.unsplash.com/photo-1516116216624-53e697fed753?auto=format&fit=crop&w=800&h=450&q=80',
        6 => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=800&h=450&q=80',
        7 => 'https://images.unsplash.com/photo-1531482615713-2afd69097998?auto=format&fit=crop&w=800&h=450&q=80',
        8 => 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?auto=format&fit=crop&w=800&h=450&q=80',
        9 => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=800&h=450&q=80',
        10 => 'https://images.unsplash.com/photo-1496171367471-9adeddcf1bf4?auto=format&fit=crop&w=800&h=450&q=80',
        11 => 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=800&h=450&q=80',
        12 => 'https://images.unsplash.com/photo-1523240795612-9a054b055fdb?auto=format&fit=crop&w=800&h=450&q=80',
    ];
}

function sample_project_image_url(int $id): string
{
    $map = sample_project_image_urls();

    return $map[$id] ?? $map[1];
}

/**
 * إن كانت جدول المشاريع فارغاً، يُدرج مشاريع تجريبية (مرة واحدة لكل بيئة).
 */
function ensure_sample_projects_if_empty(mysqli $conn): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    $r = $conn->query('SELECT COUNT(*) AS n FROM projects');
    if ($r === false) {
        return;
    }
    if ((int) $r->fetch_assoc()['n'] > 0) {
        return;
    }

    $rows = [
        [1, 'منصة إدارة مشاريع التخرج الجامعية', 'منصة ويب عربية لعرض ومشاركة مشاريع التخرج مع بحث متقدم وتصفية حسب القسم وسنة التخرج ورفع ملفات توضيحية.', 'علوم الحاسب والمعلومات', 2026, 'PHP, MySQL, HTML, CSS, JavaScript'],
        [2, 'تطبيق إرشاد أكاديمي بالذكاء الاصطناعي', 'واجهة تساعد الطالب على تلخيص المقررات واقتراح خطة مراجعة حسب الجدول الدراسي مع تذكير بالمواعيد.', 'نظم المعلومات', 2025, 'Python, FastAPI, PostgreSQL, REST'],
        [3, 'نظام مراقبة استهلاك الطاقة في المختبرات', 'لوحة تحكم لقراءة أجهزة استشعار وعرض الاستهلاك اليومي وتنبيهات عند تجاوز العتبة.', 'هندسة الحاسب', 2025, 'C++, MQTT, Node.js, Chart.js'],
        [4, 'منصة تعلم تفاعلية للبرمجة للمبتدئين', 'دروس قصيرة تمارين فورية وتتبع تقدم المتعلم مع شهادات إتمام بسيطة.', 'علوم الحاسب والمعلومات', 2024, 'React, Firebase, TypeScript'],
        [5, 'نظام حجز المواعيد للإرشاد الأكاديمي', 'يسمح للطالب بحجز موعد مع المرشد الأكاديمي وإدارة الجدول من لوحة المرشد.', 'نظم المعلومات', 2026, 'PHP, MySQL, FullCalendar'],
        [6, 'تطبيق مكتبة رقمية للمقررات', 'رفع ملخصات وملفات PDF مع تصنيف حسب المقرر والبحث النصي داخل العناوين.', 'علوم الحاسب والمعلومات', 2025, 'Laravel, MySQL, Vue.js'],
        [7, 'موقع تعريفي لقسم علوم الحاسب', 'صفحات عن الرؤية والتخصصات وروابط للمشاريع المميزة ونموذج تواصل.', 'علوم الحاسب والمعلومات', 2024, 'HTML, CSS, JavaScript'],
        [8, 'نظام إدارة فعاليات الجامعة', 'تسجيل الحضور، الجداول الزمنية، وإشعارات للمسجلين قبل الفعالية.', 'نظم المعلومات', 2026, 'PHP, MySQL, Bootstrap'],
        [9, 'تطبيق تتبع عادات الدراسة', 'مؤقت بومودورو وإحصائيات أسبوعية وتذكيرات لطيفة لزيادة التركيز.', 'هندسة البرمجيات', 2025, 'Flutter, Dart, SQLite'],
        [10, 'بوابة تقديم طلبات مشاريع التخرج', 'نموذج إلكتروني لرفع الفكرة والمشرف مع حالات الموافقة من الإدارة.', 'علوم الحاسب والمعلومات', 2026, 'PHP, MySQL, Alpine.js'],
        [11, 'نظام إدارة مخزون مختبر الحاسب', 'تسجيل الأجهزة والإعارات والصيانة مع تقارير جرد شهرية.', 'هندسة الحاسب', 2024, 'PHP, MySQL'],
        [12, 'منصة نقاش جماعي لمقرر مشروع التخرج', 'منتدى بسيط للمجموعات مع مرفقات وإشعارات عند رد المشرف.', 'هندسة البرمجيات', 2025, 'PHP, MySQL, JavaScript'],
    ];

    $sql = 'INSERT INTO `projects` (`id`, `title`, `description`, `department`, `grad_year`, `tech_stack`, `owner_linkedin`, `project_poster`, `project_poster_pdf`, `image_url`, `pdf_file`) VALUES (?, ?, ?, ?, ?, ?, NULL, NULL, NULL, ?, NULL)';
    $st = $conn->prepare($sql);
    if ($st === false) {
        return;
    }

    foreach ($rows as $row) {
        [$id, $title, $desc, $dept, $year, $tech] = $row;
        $img = sample_project_image_url($id);
        $st->bind_param(
            'isssiss',
            $id,
            $title,
            $desc,
            $dept,
            $year,
            $tech,
            $img
        );
        $st->execute();
    }
    $st->close();
}

/**
 * يحدّث المشاريع التجريبية (id 1–12) بدون صورة أو بصورة افتراضية أو بروابط picsum القديمة.
 * آمن عندما تكون هذه الصفوف من العينة الافتراضية فقط.
 */
function migrate_sample_project_remote_images(mysqli $conn): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    $sql = 'UPDATE projects SET image_url = ? WHERE id = ? AND (image_url = \'default.jpg\' OR image_url = \'\' OR image_url IS NULL OR image_url LIKE \'https://picsum.photos%\')';
    $st = $conn->prepare($sql);
    if ($st === false) {
        return;
    }

    for ($id = 1; $id <= 12; $id++) {
        $url = sample_project_image_url($id);
        $st->bind_param('si', $url, $id);
        $st->execute();
    }
    $st->close();
}
