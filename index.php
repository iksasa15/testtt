<?php 
session_start();
include 'db_connect.php'; 

$limit = 6;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$search_query = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';
$dept_filter = isset($_GET['dept']) ? $conn->real_escape_string($_GET['dept']) : '';

$where_clauses = [];
if (!empty($search_query)) {
    $where_clauses[] = "(title LIKE '%$search_query%' OR tech_stack LIKE '%$search_query%')";
}
if (!empty($dept_filter)) {
    $where_clauses[] = "department = '$dept_filter'";
}
$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

$count_sql = "SELECT COUNT(*) as total FROM projects $where_sql";
$total_result = $conn->query($count_sql);
$total_projects = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_projects / $limit);

$total_students = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_departments = $conn->query("SELECT COUNT(DISTINCT department) as count FROM projects")->fetch_assoc()['count'];
$years_row = $conn->query("SELECT COUNT(DISTINCT grad_year) as c FROM projects WHERE grad_year IS NOT NULL AND grad_year != '' AND grad_year != 0")->fetch_assoc();
$distinct_year_count = (int) ($years_row['c'] ?? 0);

function index_build_query(array $params): string
{
    $filtered = [];
    foreach ($params as $k => $v) {
        if ($v === null || $v === '') {
            continue;
        }
        if ($k === 'page' && (int) $v < 2) {
            continue;
        }
        $filtered[$k] = $v;
    }
    return http_build_query($filtered);
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>منصة مشاريع التخرج | الرئيسية</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(style_css_href(), ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body class="site-home">

    <header class="site-header">
        <div>
            <div class="brand-logo-wrap"><img src="logo.png" alt="Company Logo" width="150" height="60"></div>
            <h1><a href="index.php">UT | GradSource</a></h1>
        </div>
        
        <div class="menu-toggle" onclick="toggleMenu()">☰</div>

        <div class="nav-links" id="navLinks">
            <a href="index.php">الرئيسية</a>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="user-header-info">
                    <a href="profile.php">
                        <img src="uploads/profiles/<?php echo $_SESSION['user_pic'] ? $_SESSION['user_pic'] : 'default_avatar.png'; ?>" alt="صورتي">
                    </a>
                    <div>
                        <a href="profile.php"><?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
                        <a href="logout_user.php" class="logout-link">تسجيل الخروج</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login_user.php" class="btn-outline btn-outline--header">دخول الطلاب</a>
                <a href="login_admin.php" class="login-btn">دخول الإدارة</a>
            <?php endif; ?>

        </div>
    </header>

    <section class="hero-section hero-section--landing" aria-labelledby="hero-heading">
        <div class="hero-layout">
            <div class="hero-copy">
                <p class="hero-kicker">منصة أكاديمية</p>
                <h2 id="hero-heading" class="hero-headline">
                    <span class="hero-headline__main">منصة مشاريع التخرج</span>
                    <span class="hero-headline__tags" aria-hidden="true">
                        <span>متميزة</span><span>مبتكرة</span><span>ملهمة</span><span>شاملة</span><span>عصرية</span>
                    </span>
                </h2>
                <p class="hero-lead">منصة تجمع طموح الطلاب وإبداعهم</p>
                <p class="hero-desc">اكتشف مشاريع الطلاب من تخصصات متعددة، مع تصفية ذكية وتجربة عصرية تركز على المحتوى.</p>
                <div class="hero-actions">
                    <a href="#projects-board" class="btn btn-primary hero-actions__btn">استكشف المشاريع</a>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="register.php" class="btn btn-outline hero-actions__btn hero-actions__btn--ghost">إنشاء حساب</a>
                    <?php else: ?>
                        <a href="profile.php" class="btn btn-outline hero-actions__btn hero-actions__btn--ghost">ملفي الشخصي</a>
                    <?php endif; ?>
                </div>
                <form method="GET" action="index.php" class="hero-search-bar" role="search">
                    <input type="hidden" name="dept" value="<?php echo htmlspecialchars($dept_filter, ENT_QUOTES, 'UTF-8'); ?>">
                    <label class="visually-hidden" for="heroSearchInput">بحث في المشاريع</label>
                    <input id="heroSearchInput" type="search" name="q" placeholder="ابحث بالاسم أو التقنية..." value="<?php echo htmlspecialchars($search_query, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off">
                    <button type="submit" class="hero-search-bar__submit">بحث</button>
                    <?php if (!empty($search_query) || !empty($dept_filter)): ?>
                        <a href="index.php" class="hero-search-bar__clear">إلغاء التصفية</a>
                    <?php endif; ?>
                </form>
                <nav class="home-dept-pills" aria-label="تصفية حسب القسم">
                    <?php
                    $pill_q = $search_query;
                    $all_href = 'index.php?' . index_build_query(['q' => $pill_q, 'dept' => '', 'page' => 1]);
                    $cs_href = 'index.php?' . index_build_query(['q' => $pill_q, 'dept' => 'علوم حاسوب', 'page' => 1]);
                    $se_href = 'index.php?' . index_build_query(['q' => $pill_q, 'dept' => 'هندسة برمجيات', 'page' => 1]);
                    ?>
                    <a class="dept-pill<?php echo $dept_filter === '' ? ' dept-pill--active' : ''; ?>" href="<?php echo htmlspecialchars($all_href, ENT_QUOTES, 'UTF-8'); ?>">الكل</a>
                    <a class="dept-pill<?php echo $dept_filter === 'علوم حاسوب' ? ' dept-pill--active' : ''; ?>" href="<?php echo htmlspecialchars($cs_href, ENT_QUOTES, 'UTF-8'); ?>">علوم حاسوب</a>
                    <a class="dept-pill<?php echo $dept_filter === 'هندسة برمجيات' ? ' dept-pill--active' : ''; ?>" href="<?php echo htmlspecialchars($se_href, ENT_QUOTES, 'UTF-8'); ?>">هندسة برمجيات</a>
                </nav>
            </div>
            <div class="hero-visual" aria-hidden="true">
                <img src="pplbg.png" alt="" loading="lazy">
            </div>
        </div>
    </section>

    <div class="stats-bar stats-bar--landing">
        <div class="stat-item">
            <span class="stat-item-icon" aria-hidden="true">📋</span>
            <div>
                <h3><?php echo (int) $total_projects; ?></h3>
                <p class="stat-item__label">مشروعاً منشوراً</p>
                <p class="stat-item__hint">متاح للاستكشاف</p>
            </div>
        </div>
        <div class="stat-item">
            <span class="stat-item-icon" aria-hidden="true">👤</span>
            <div>
                <h3><?php echo number_format((int) $total_students); ?></h3>
                <p class="stat-item__label">مستخدم مسجّل</p>
                <p class="stat-item__hint">طلاب ومشرفون</p>
            </div>
        </div>
        <div class="stat-item">
            <span class="stat-item-icon" aria-hidden="true">🏷️</span>
            <div>
                <h3><?php echo (int) $total_departments; ?></h3>
                <p class="stat-item__label">تخصصاً أكاديمياً</p>
                <p class="stat-item__hint">تصنيفات المشاريع</p>
            </div>
        </div>
        <div class="stat-item">
            <span class="stat-item-icon" aria-hidden="true">⭐</span>
            <div>
                <h3>50+</h3>
                <p class="stat-item__label">مشروعاً مميزاً</p>
                <p class="stat-item__hint">في الأرشيف</p>
            </div>
        </div>
        <div class="stat-item">
            <span class="stat-item-icon" aria-hidden="true">📅</span>
            <div>
                <h3><?php echo (int) $distinct_year_count; ?></h3>
                <p class="stat-item__label">سنوات تخرج</p>
                <p class="stat-item__hint">في الأرشيف</p>
            </div>
        </div>
    </div>

    <div class="container container--projects" id="projects-board">
        <header class="projects-section-head">
            <h2 class="projects-section-head__title">المشاريع</h2>
            <p class="projects-section-head__meta">
                <?php
                $list_sql = "SELECT * FROM projects $where_sql ORDER BY id DESC LIMIT $limit OFFSET $offset";
                $list_result = $conn->query($list_sql);
                $shown_on_page = $list_result ? $list_result->num_rows : 0;
                ?>
                عرض <?php echo (int) $shown_on_page; ?> من أصل <?php echo (int) $total_projects; ?> مشروعاً
            </p>
        </header>
        <div class="projects-grid projects-grid--landing">
            <?php
            if ($list_result && $list_result->num_rows > 0) {
                while ($row = $list_result->fetch_assoc()) {
                    $img_src = project_image_src($row['image_url'] ?? null, 'https://via.placeholder.com/400x200?text=No+image');
                    $tech_raw = isset($row['tech_stack']) ? $row['tech_stack'] : '';
                    $tech_parts = array_filter(array_map('trim', explode(',', $tech_raw)));
                    $tech_show = array_slice($tech_parts, 0, 6);
                    ?>
                    <article class="card project-card">
                        <div class="card-media">
                            <img src="<?php echo htmlspecialchars($img_src, ENT_QUOTES, 'UTF-8'); ?>" class="card-img" alt="">
                        </div>
                        <div class="card-content">
                            <div class="card-badges">
                                <span class="badge-soft"><?php echo htmlspecialchars($row['department'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="badge-soft badge-soft--muted"><?php echo htmlspecialchars((string) $row['grad_year'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <h3><?php echo htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p class="card-excerpt"><?php echo mb_strimwidth(htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8'), 0, 160, '…'); ?></p>
                            <?php if (!empty($tech_show)): ?>
                                <div class="card-tech">
                                    <span class="card-tech__label">التقنيات</span>
                                    <div class="card-tech__chips">
                                        <?php foreach ($tech_show as $t): ?>
                                            <span class="tech-chip"><?php echo htmlspecialchars($t, ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="card-footer">
                                <?php if (!empty($row['owner_linkedin'])): ?>
                                    <a href="<?php echo htmlspecialchars($row['owner_linkedin'], ENT_QUOTES, 'UTF-8'); ?>" class="card-footer__link" target="_blank" rel="noopener noreferrer">تواصل على لينكد إن</a>
                                <?php endif; ?>
                                <a href="project_details.php?id=<?php echo (int) $row['id']; ?>" class="btn btn-primary btn-block card-footer__cta">عرض التفاصيل</a>
                            </div>
                        </div>
                    </article>
                    <?php
                }
            } else {
                echo "<div class='projects-empty'><h3>لم نجد أي مشاريع تطابق بحثك.</h3></div>";
            }
            ?>
        </div>

        <?php if($total_pages > 1): ?>
        <div class="pagination">
            <?php 
            for($i = 1; $i <= $total_pages; $i++): 
                $link = "?page=$i&q=" . urlencode($search_query) . "&dept=" . urlencode($dept_filter);
            ?>
                <a href="<?php echo $link; ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

    </div>

     <footer class="site-footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>منصة مشاريع التخرج</h3>
                <p>منصة أكاديمية تهدف إلى أرشفة وعرض المشاريع المتميزة للطلاب، لتكون مرجعاً وإلهاماً للدفعات القادمة في مختلف التخصصات والكليات.</p>
            </div>
            
            <div class="footer-section">
                <h3>روابط سريعة</h3>
                <ul class="footer-links">
                    <li><a href="index.php">الرئيسية</a></li>
                    <li><a href="#">تصفح الأقسام</a></li>
                    <?php if(!isset($_SESSION['user_id'])): ?>
                        <li><a href="register.php">إنشاء حساب طالب</a></li>
                    <?php else: ?>
                        <li><a href="profile.php">ملفي الشخصي</a></li>
                    <?php endif; ?>
                    <li><a href="login_admin.php">دخول الإدارة</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="#" onclick="openContactModal(); return false;">تواصل معنا</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>تواصل معنا</h3>
                <ul class="footer-links">
                    <li>📧 البريد: info@projects-portal.edu</li>
                    <li>📞 الهاتف: 966-XXXXXXXXX+</li>
                    <li>📍 الموقع: الحرم الجامعي، عمادة شؤون الطلاب</li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>جميع الحقوق محفوظة &copy; <?php echo date('Y'); ?> | منصة مشاريع التخرج</p>
        </div>
    </footer>

    <div class="modal-overlay" id="contactModal">
        <div class="contact-modal">
            <button class="close-modal" onclick="closeContactModal()">✖</button>
            <h3>تواصل معنا</h3>
            <form id="contactForm" onsubmit="sendEmail(event)">
                <div class="contact-form-group">
                    <label>الاسم الكامل</label>
                    <input type="text" id="contactName" required placeholder="ادخل اسمك هنا">
                </div>
                <div class="contact-form-group">
                    <label>عنوان الرسالة</label>
                    <input type="text" id="contactSubject" required placeholder="مثال: استفسار عن رفع مشروع">
                </div>
                <div class="contact-form-group">
                    <label>رسالتك للإدارة</label>
                    <textarea id="contactMessage" required placeholder="اكتب تفاصيل رسالتك أو استفسارك هنا..."></textarea>
                </div>
                <button type="submit" class="btn-send">إرسال عبر البريد الإلكتروني ✉️</button>
            </form>
        </div>
    </div>

    <?php if(isset($_SESSION['user_id'])): ?>
    <div class="chat-widget" id="chatWidget">
        <div class="chat-header" onclick="toggleChat()">
            <span class="chat-header-title">
                <span class="chat-emoji" aria-hidden="true">💬</span> مجتمع الطلاب
            </span>
            <span id="chatToggleIcon">▲</span>
        </div>
        
        <div class="chat-body" id="chatBody">
        </div>
        
        <div class="chat-footer">
            <input type="text" id="chatMsg" placeholder="اكتب رسالتك هنا..." required onkeypress="handleKeyPress(event)">
            <button onclick="sendMessage()">إرسال</button>
        </div>
    </div>

    <script>
        const currentUserName = "<?php echo addslashes($_SESSION['user_name']); ?>";
        const currentUserPic = "<?php echo addslashes($_SESSION['user_pic'] ? $_SESSION['user_pic'] : 'default_avatar.png'); ?>";

        function toggleChat() {
            const widget = document.getElementById('chatWidget');
            const icon = document.getElementById('chatToggleIcon');
            widget.classList.toggle('open');
            icon.innerText = widget.classList.contains('open') ? '▼' : '▲';
            if(widget.classList.contains('open')) {
                loadMessages();
            }
        }

        function loadMessages() {
            fetch('chat_api.php?action=get')
                .then(res => res.json())
                .then(data => {
                    const chatBody = document.getElementById('chatBody');
                    const isScrolledToBottom = chatBody.scrollHeight - chatBody.clientHeight <= chatBody.scrollTop + 10;
                    
                    chatBody.innerHTML = '';
                    data.forEach(msg => {
                        const picSrc = msg.profile_pic ? `uploads/profiles/${msg.profile_pic}` : 'uploads/profiles/default_avatar.png';
                        chatBody.innerHTML += `
                            <div class="chat-message">
                                <img src="${picSrc}" alt="Avatar" onerror="this.src='https://via.placeholder.com/35'">
                                <div class="chat-message-content">
                                    <strong>${msg.sender_name}</strong>
                                    <p>${msg.message}</p>
                                </div>
                            </div>
                        `;
                    });

                    if(isScrolledToBottom) {
                        chatBody.scrollTop = chatBody.scrollHeight;
                    }
                });
        }

        function sendMessage() {
            const msgInput = document.getElementById('chatMsg');
            const msg = msgInput.value.trim();
            
            if(!msg) return;

            const formData = new FormData();
            formData.append('name', currentUserName);
            formData.append('pic', currentUserPic);
            formData.append('message', msg);

            msgInput.value = ''; 

            fetch('chat_api.php?action=send', { method: 'POST', body: formData })
                .then(() => {
                    loadMessages();
                    setTimeout(() => {
                        const chatBody = document.getElementById('chatBody');
                        chatBody.scrollTop = chatBody.scrollHeight;
                    }, 100);
                });
        }

        function handleKeyPress(event) {
            if (event.key === "Enter") {
                sendMessage();
            }
        }

        setInterval(() => {
            if(document.getElementById('chatWidget').classList.contains('open')) {
                loadMessages();
            }
        }, 3000);
    </script>
    <?php endif; ?>

    <div class="ai-chat-widget" id="aiChatWidget">
        <div class="ai-chat-header" onclick="toggleAiChat()" role="button" tabindex="0" onkeydown="if(event.key==='Enter'||(event.key===' '||event.code==='Space')){event.preventDefault();toggleAiChat();}">
            <span class="ai-chat-header__title">
                <span class="ai-chat-header__ico" aria-hidden="true">✨</span>
                مساعد المشاريع (Gemini)
            </span>
            <span class="ai-chat-header__toggle" id="aiChatToggleIcon">▲</span>
        </div>
        <div class="ai-chat-body" id="aiChatBody" aria-live="polite"></div>
        <div class="ai-chat-footer">
            <input type="text" id="aiChatInput" placeholder="اسأل عن المشاريع أو التقنيات..." maxlength="2000" autocomplete="off" onkeypress="aiChatKeyPress(event)">
            <button type="button" class="ai-chat-send" id="aiChatSend" onclick="sendAiChat()">إرسال</button>
        </div>
    </div>

    <script>
        (function () {
            var aiHistory = [];

            function escapeHtml(s) {
                var d = document.createElement('div');
                d.textContent = s;
                return d.innerHTML;
            }

            function appendBubble(role, text) {
                var body = document.getElementById('aiChatBody');
                if (!body) return;
                var wrap = document.createElement('div');
                wrap.className = 'ai-chat-msg ai-chat-msg--' + role;
                var inner = document.createElement('div');
                inner.className = 'ai-chat-msg__bubble';
                inner.innerHTML = '<p>' + escapeHtml(text).replace(/\n/g, '<br>') + '</p>';
                wrap.appendChild(inner);
                body.appendChild(wrap);
                body.scrollTop = body.scrollHeight;
            }

            function appendTyping() {
                var body = document.getElementById('aiChatBody');
                if (!body) return null;
                var el = document.createElement('div');
                el.className = 'ai-chat-msg ai-chat-msg--model ai-chat-msg--typing';
                el.id = 'aiChatTyping';
                el.innerHTML = '<div class="ai-chat-msg__bubble"><span class="ai-chat-typing">جاري الكتابة…</span></div>';
                body.appendChild(el);
                body.scrollTop = body.scrollHeight;
                return el;
            }

            window.toggleAiChat = function () {
                var w = document.getElementById('aiChatWidget');
                var icon = document.getElementById('aiChatToggleIcon');
                if (!w || !icon) return;
                w.classList.toggle('open');
                icon.textContent = w.classList.contains('open') ? '▼' : '▲';
            };

            window.aiChatKeyPress = function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    sendAiChat();
                }
            };

            window.sendAiChat = function () {
                var input = document.getElementById('aiChatInput');
                var btn = document.getElementById('aiChatSend');
                if (!input || !btn) return;
                var msg = input.value.trim();
                if (!msg) return;

                input.value = '';
                appendBubble('user', msg);
                aiHistory.push({ role: 'user', text: msg });

                btn.disabled = true;
                input.disabled = true;
                var typing = appendTyping();

                fetch('gemini_chat.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json; charset=utf-8' },
                    body: JSON.stringify({ message: msg, history: aiHistory.slice(0, -1) })
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (typing && typing.parentNode) typing.parentNode.removeChild(typing);
                        if (data.ok && data.reply) {
                            appendBubble('model', data.reply);
                            aiHistory.push({ role: 'model', text: data.reply });
                            if (aiHistory.length > 40) {
                                aiHistory = aiHistory.slice(-40);
                            }
                        } else {
                            if (aiHistory.length && aiHistory[aiHistory.length - 1].role === 'user') {
                                aiHistory.pop();
                                var uMsgs = document.querySelectorAll('#aiChatBody .ai-chat-msg--user');
                                var lastU = uMsgs[uMsgs.length - 1];
                                if (lastU && lastU.parentNode) lastU.parentNode.removeChild(lastU);
                            }
                            appendBubble('model', data.error || 'حدث خطأ غير متوقع.');
                        }
                    })
                    .catch(function () {
                        if (typing && typing.parentNode) typing.parentNode.removeChild(typing);
                        if (aiHistory.length && aiHistory[aiHistory.length - 1].role === 'user') {
                            aiHistory.pop();
                            var uMsgs2 = document.querySelectorAll('#aiChatBody .ai-chat-msg--user');
                            var lastU2 = uMsgs2[uMsgs2.length - 1];
                            if (lastU2 && lastU2.parentNode) lastU2.parentNode.removeChild(lastU2);
                        }
                        appendBubble('model', 'تعذر الاتصال بالخادم. تحقق من الشبكة أو من ضبط GEMINI_API_KEY.');
                    })
                    .finally(function () {
                        btn.disabled = false;
                        input.disabled = false;
                        input.focus();
                    });
            };

            var first = true;
            var origToggle = window.toggleAiChat;
            window.toggleAiChat = function () {
                origToggle();
                var w = document.getElementById('aiChatWidget');
                var body = document.getElementById('aiChatBody');
                if (first && w && w.classList.contains('open') && body && body.children.length === 0) {
                    first = false;
                    appendBubble('model', 'مرحباً! أنا مساعد منصة مشاريع التخرج. يمكنك سؤالي عن أفكار مشاريع، تقنيات، أو كيفية استخدام المنصة بشكل عام.');
                }
            };
        })();
    </script>

    <script>
        // سكربت إظهار وإخفاء القائمة في الجوال
        function toggleMenu() {
            var menu = document.getElementById("navLinks");
            menu.classList.toggle("active");
        }

        // سكربت التحكم بنافذة "تواصل معنا"
        function openContactModal() {
            document.getElementById('contactModal').classList.add('active');
        }

        function closeContactModal() {
            document.getElementById('contactModal').classList.remove('active');
        }

        // إغلاق النافذة عند الضغط خارجها
        document.getElementById('contactModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeContactModal();
            }
        });

        // إرسال البريد الإلكتروني عبر تطبيق البريد الافتراضي
        function sendEmail(e) {
            e.preventDefault();
            
            const name = document.getElementById('contactName').value;
            const subject = document.getElementById('contactSubject').value;
            const message = document.getElementById('contactMessage').value;
            const universityEmail = "info@projects-portal.edu"; // إيميل الجامعة
            
            const bodyText = `المرسل: ${name}\n\nالرسالة:\n${message}`;
            
            // إنشاء رابط mailto
            const mailtoLink = `mailto:${universityEmail}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(bodyText)}`;
            
            // فتح تطبيق البريد
            window.location.href = mailtoLink;
            
            // إعادة تعيين النموذج وإغلاق النافذة
            document.getElementById('contactForm').reset();
            closeContactModal();
        }
    </script>
</body>
</html>