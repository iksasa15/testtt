<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$total_students = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الطلاب | لوحة التحكم</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .student-pic { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid #ddd; }
        .student-info { display: flex; align-items: center; gap: 10px; }
    </style>
</head>
<body class="admin-body">

    <div class="admin-layout">
        
        <?php include 'admin_side_bar.php' ?>

        <main class="admin-content">
            
            <header class="admin-topbar">
                <h3>إدارة حسابات الطلاب</h3>
                <span style="background: var(--primary-color); color: white; padding: 5px 15px; border-radius: 20px; font-weight: bold;">
                    إجمالي المسجلين: <?php echo $total_students; ?>
                </span>
            </header>

            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                    تم حذف حساب الطالب بنجاح!
                </div>
            <?php endif; ?>

            <div class="table-container">
                <div class="table-header">
                    <h4>قائمة الطلاب المسجلين في المنصة</h4>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>رقم</th>
                            <th>الطالب</th>
                            <th>البريد الإلكتروني</th>
                            <th>تاريخ التسجيل</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $students_sql = "SELECT id, full_name, email, profile_pic, created_at FROM users ORDER BY id DESC";
                        $students_result = $conn->query($students_sql);

                        if ($students_result->num_rows > 0) {
                            while($row = $students_result->fetch_assoc()) {
                                $date = date('Y-m-d', strtotime($row['created_at']));
                                $pic = $row['profile_pic'] ? $row['profile_pic'] : 'default_avatar.png';
                                
                                echo "<tr>";
                                echo "<td>" . $row['id'] . "</td>";
                                echo "<td>
                                        <div class='student-info'>
                                            <img src='uploads/profiles/" . $pic . "' class='student-pic' alt='صورة'>
                                            <span>" . htmlspecialchars($row['full_name']) . "</span>
                                        </div>
                                      </td>";
                                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                echo "<td><span style='color: #666; font-size: 14px;'>" . $date . "</span></td>";
                                echo "<td>
                                        <a href='delete_student.php?id=" . $row['id'] . "' class='action-btn delete-btn' onclick='return confirm(\"هل أنت متأكد من حذف حساب هذا الطالب نهائياً؟ لا يمكن التراجع عن هذا الإجراء.\")'>حذف الحساب</a>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' style='text-align:center; padding: 20px;'>لا يوجد طلاب مسجلين حتى الآن.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>

</body>
</html>