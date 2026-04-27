<div class="admin-sidebar-overlay" id="adminSidebarOverlay" hidden aria-hidden="true"></div>

<aside class="sidebar-menu" id="adminSidebar">
    <button type="button" class="admin-menu-fab" id="adminMenuFab" aria-controls="adminSidebar" aria-expanded="false" aria-label="فتح قائمة التنقل">
        <span class="admin-menu-fab__icon" aria-hidden="true">☰</span>
    </button>
    <div class="sidebar-header">
        <p class="sidebar-kicker">منصة المشاريع</p>
        <h2>لوحة التحكم</h2>
    </div>
    <nav class="sidebar-nav" aria-label="القائمة الرئيسية">
        <ul class="sidebar-links">
            <li>
                <a href="admin.php" class="sidebar-link<?php echo basename($_SERVER['PHP_SELF']) === 'admin.php' ? ' is-active' : ''; ?>">
                    <span class="sidebar-link__ico" aria-hidden="true">▣</span>
                    <span>الرئيسية والإحصائيات</span>
                </a>
            </li>
            <li>
                <a href="add_project.php" class="sidebar-link<?php echo basename($_SERVER['PHP_SELF']) === 'add_project.php' ? ' is-active' : ''; ?>">
                    <span class="sidebar-link__ico" aria-hidden="true">＋</span>
                    <span>إضافة مشروع</span>
                </a>
            </li>
            <li>
                <a href="manage_projects.php" class="sidebar-link<?php echo basename($_SERVER['PHP_SELF']) === 'manage_projects.php' ? ' is-active' : ''; ?>">
                    <span class="sidebar-link__ico" aria-hidden="true">≡</span>
                    <span>إدارة المشاريع</span>
                </a>
            </li>
            <li>
                <a href="manage_students.php" class="sidebar-link<?php echo basename($_SERVER['PHP_SELF']) === 'manage_students.php' ? ' is-active' : ''; ?>">
                    <span class="sidebar-link__ico" aria-hidden="true">👥</span>
                    <span>إدارة الطلاب</span>
                </a>
            </li>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <a href="index.php" class="sidebar-footer__link">عرض الموقع العام</a>
        <a href="logout_user.php" class="sidebar-link sidebar-link--logout nav-link-logout">تسجيل الخروج</a>
    </div>
</aside>

<script>
(function () {
    var fab = document.getElementById('adminMenuFab');
    var aside = document.getElementById('adminSidebar');
    var overlay = document.getElementById('adminSidebarOverlay');
    if (!fab || !aside || !overlay) return;

    function setOpen(open) {
        aside.classList.toggle('sidebar-menu--open', open);
        overlay.classList.toggle('is-visible', open);
        overlay.hidden = !open;
        overlay.setAttribute('aria-hidden', open ? 'false' : 'true');
        document.body.classList.toggle('admin-nav-open', open);
        fab.setAttribute('aria-expanded', open ? 'true' : 'false');
        fab.setAttribute('aria-label', open ? 'إغلاق قائمة التنقل' : 'فتح قائمة التنقل');
    }

    fab.addEventListener('click', function () {
        setOpen(!aside.classList.contains('sidebar-menu--open'));
    });
    overlay.addEventListener('click', function () {
        setOpen(false);
    });
    window.addEventListener('resize', function () {
        if (window.matchMedia('(min-width: 769px)').matches) {
            setOpen(false);
        }
    });
})();
</script>
