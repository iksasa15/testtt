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

    <section class="hero-section">
        <div class="hero-content">
            <h2>اكتشف مشاريع الطلاب المتميزة</h2>
            <p>شارك مشروع تخرجك مع العالم واستكشف مشاريع مبتكرة من زملائك الخريجين. ابحث في أرشيفنا المكون من <?php echo $total_projects; ?> مشروعاً للإلهام والاستفادة.</p>
            
            <form method="GET" action="index.php" class="advanced-search-form">
                <input type="text" name="q" placeholder="ابحث باسم المشروع ..." value="<?php echo htmlspecialchars($search_query); ?>">
                
                <select name="dept">
                    <option value="">جميع الأقسام</option>
                    <option value="علوم حاسوب" <?php if($dept_filter == 'علوم حاسوب') echo 'selected'; ?>>علوم حاسوب</option>
                    <option value="هندسة برمجيات" <?php if($dept_filter == 'هندسة برمجيات') echo 'selected'; ?>>هندسة برمجيات</option>
                </select>
                
                <button type="submit">بحث</button>
                
                <?php if(!empty($search_query) || !empty($dept_filter)): ?>
                    <a href="index.php" class="btn-search-clear">إلغاء</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="hero-image">
            <img src="pplbg.png" alt="طلاب مشاريع التخرج">
        </div>
    </section>

    <div class="stats-bar">
        <div class="stat-item">
            <span class="stat-item-icon" aria-hidden="true">📋</span>
            <div>
                <h3><?php echo $total_projects; ?></h3>
                <p>مشروع متاح</p>
            </div>
        </div>
        <div class="stat-item">
            <span class="stat-item-icon" aria-hidden="true">🎓</span>
            <div>
                <h3><?php echo $total_students; ?></h3>
                <p>طالب مستفيد</p>
            </div>
        </div>
        <div class="stat-item">
            <span class="stat-item-icon" aria-hidden="true">⭐</span>
            <div>
                <h3>50+</h3>
                <p>مشروع مميز</p>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="projects-grid">
            <?php
            $sql = "SELECT * FROM projects $where_sql ORDER BY id DESC LIMIT $limit OFFSET $offset";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $img_src = (!empty($row['image_url']) && $row['image_url'] != 'default.jpg') ? "uploads/".$row['image_url'] : "https://via.placeholder.com/400x200?text=بدون+صورة";
                    ?>
                    <div class="card">
                        <img src="<?php echo $img_src; ?>" class="card-img" alt="صورة المشروع">
                        <div class="card-content">
                            <div class="tags">
                                <span class="tag"><?php echo htmlspecialchars($row['department']); ?></span>
                                <span class="tag"><?php echo htmlspecialchars($row['grad_year']); ?></span>
                            </div>
                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p><?php echo mb_strimwidth(htmlspecialchars($row['description']), 0, 90, "..."); ?></p>
                            <a href="project_details.php?id=<?php echo $row['id']; ?>" class="btn btn-outline btn-block">عرض التفاصيل</a>
                        </div>
                    </div>
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