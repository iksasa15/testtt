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
    <link rel="stylesheet" href="style.css">
    <style>
        /* --- تنسيق قسم المستخدم في الهيدر --- */
        .user-header-info { display: flex; align-items: center; gap: 10px; }
        .user-header-info img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #e5e7eb; }
        .user-header-info a { font-weight: bold; color: #1f2937; text-decoration: none; display: block; line-height: 1.2;}
        .logout-link { font-size: 12px; color: #e74c3c !important; font-weight: normal;}

        /* --- تنسيق نافذة الدردشة --- */
        .chat-widget {
            position: fixed; bottom: 20px; left: 20px;
            width: 320px; background: #fff; border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15); z-index: 9999;
            display: flex; flex-direction: column; overflow: hidden;
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            transform: translateY(calc(100% - 45px));
            border: 1px solid #e5e7eb;
        }
        .chat-widget.open { transform: translateY(0); }
        .chat-header {
            background: #4A6CF7; color: #fff; padding: 12px 15px; cursor: pointer;
            font-weight: bold; display: flex; justify-content: space-between; align-items: center;
        }
        .chat-body { height: 300px; overflow-y: auto; padding: 15px; background: #f9fafb; display: flex; flex-direction: column; gap: 10px;}
        .chat-message { display: flex; gap: 10px; align-items: flex-start; }
        .chat-message img { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 1px solid #ddd;}
        .chat-message-content { background: #fff; padding: 10px 12px; border-radius: 10px; border: 1px solid #e5e7eb; width: 100%; box-shadow: 0 1px 2px rgba(0,0,0,0.05);}
        .chat-message strong { display: block; color: #2563eb; font-size: 13px; margin-bottom: 4px;}
        .chat-message p { margin: 0; font-size: 14px; color: #374151;}
        .chat-footer { padding: 12px; border-top: 1px solid #e5e7eb; background: #fff; display: flex; gap: 8px;}
        .chat-footer input { flex-grow: 1; padding: 10px; border: 1px solid #d1d5db; border-radius: 20px; outline: none;}
        .chat-footer input:focus { border-color: #2563eb;}
        .chat-footer button { padding: 8px 15px; background: #2563eb; color: white; border: none; border-radius: 20px; cursor: pointer; font-weight: bold;}
        .chat-footer button:hover { background: #1e40af;}

        /* --- تنسيق قسم البحث الجديد (Hero Section) --- */
        .hero-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 60px 5%;
            background-color: #d81d1d00;
            gap: 40px;
            margin-bottom: 40px;
        }

        .hero-content {
            flex: 1;
            max-width: 600px;
        }

        .hero-content h2 {
            font-size: 2.5rem;
            color: #1e3a8a;
            margin-bottom: 15px;
            line-height: 1.3;
        }

        .hero-content p {
            font-size: 1.1rem;
            color: #4b5563;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .hero-image {
            flex: 1;
            display: flex;
            justify-content: center;
        }

        .hero-image img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
        }

        .advanced-search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            background: #ffffff;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }

        .advanced-search-form input, 
        .advanced-search-form select {
            flex: 1;
            min-width: 200px;
            padding: 12px 15px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            outline: none;
            font-family: inherit;
        }

        .advanced-search-form input:focus, 
        .advanced-search-form select:focus {
            border-color: #2563eb;
        }

        .advanced-search-form button {
            padding: 12px 25px;
            background-color: #4A6CF7;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }

        .advanced-search-form button:hover {
            background-color: #1d4ed8;
        }

        .stats-bar {
            display: flex;
            justify-content: center;
            gap: 50px;
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            margin: -60px 5% 40px 5%;
            position: relative;
            z-index: 10;
        }

        .stat-item {
            text-align: center;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-item h3 {
            font-size: 1.8rem;
            color: #1e3a8a;
            margin: 0;
        }

        .stat-item p {
            margin: 0;
            color: #6b7280;
            font-size: 0.9rem;
        }

        /* --- تنسيق نافذة (تواصل معنا) المنبثقة --- */
        .modal-overlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6); z-index: 100000; align-items: center; justify-content: center;
            backdrop-filter: blur(4px);
        }
        .modal-overlay.active { display: flex; }
        .contact-modal {
            background: #fff; padding: 35px; border-radius: 16px; width: 90%; max-width: 450px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.2); position: relative;
        }
        .contact-modal h3 { margin-top: 0; color: #1e3a8a; font-size: 1.6rem; margin-bottom: 25px; text-align: center;}
        .close-modal {
            position: absolute; top: 15px; left: 15px; background: none; border: none;
            font-size: 22px; cursor: pointer; color: #9ca3af; transition: 0.2s;
        }
        .close-modal:hover { color: #ef4444; }
        .contact-form-group { margin-bottom: 18px; }
        .contact-form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #4b5563; font-size: 0.95rem; }
        .contact-form-group input, .contact-form-group textarea {
            width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-family: inherit; box-sizing: border-box; background: #f9fafb;
        }
        .contact-form-group input:focus, .contact-form-group textarea:focus {
            outline: none; border-color: #4A6CF7; background: #fff;
        }
        .contact-form-group textarea { resize: vertical; height: 120px; }
        .btn-send {
            background: #4A6CF7; color: white; border: none; padding: 14px; width: 100%;
            border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: bold; transition: 0.3s; margin-top: 10px;
        }
        .btn-send:hover { background: #1d4ed8; transform: translateY(-2px);}

        @media (max-width: 900px) {
            .hero-section {
                flex-direction: column;
                text-align: center;
                padding: 40px 5% 80px 5%;
            }
            .stats-bar {
                flex-direction: column;
                gap: 20px;
            }
        }
    </style>
</head>
<body>

    <header>
        <div>
            <div style="padding-right: 15px;"><img src="logo.png" alt="Company Logo" width="150" height="60"></div>
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
                <a href="login_user.php" class="btn-outline" style="padding: 5px 15px; border-radius: 20px; margin-left:10px;">دخول الطلاب</a>
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
                    <a href="index.php" style="display:flex; align-items:center; justify-content:center; padding: 12px 20px; background: #fee2e2; color: #ef4444; border-radius: 8px; text-decoration: none; font-weight: bold;">إلغاء</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="hero-image">
            <img src="pplbg.png" alt="طلاب مشاريع التخرج">
        </div>
    </section>

    <div class="stats-bar">
        <div class="stat-item">
            <span style="font-size: 2rem;">📋</span>
            <div>
                <h3><?php echo $total_projects; ?></h3>
                <p>مشروع متاح</p>
            </div>
        </div>
        <div class="stat-item">
            <span style="font-size: 2rem;">🎓</span>
            <div>
                <h3><?php echo $total_students; ?></h3>
                <p>طالب مستفيد</p>
            </div>
        </div>
        <div class="stat-item">
            <span style="font-size: 2rem;">⭐</span>
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
                echo "<div style='text-align:center; width:100%; padding:40px;'><h3>لم نجد أي مشاريع تطابق بحثك.</h3></div>";
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
            <span style="display:flex; align-items:center; gap:8px;">
                <span style="font-size:18px;">💬</span> مجتمع الطلاب
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