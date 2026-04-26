<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login_admin.php");
    exit();
}
$total_projects = $conn->query("SELECT COUNT(*) as count FROM projects")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المشاريع | لوحة التحكم</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .project-thumb { width: 60px; height: 40px; border-radius: 4px; object-fit: cover; border: 1px solid #ddd; }
        .tech-stack-small { font-size: 12px; color: #666; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; display: inline-block; margin-top: 4px; }
        .search-bar { width: 100%; max-width: 300px; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 5px; margin-bottom: 15px; }
        .table-controls { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .btn-sm { padding: 4px 8px; font-size: 12px; margin-right: 3px; }
        .view-btn { background-color: #3498db; color: white; }
        .view-btn:hover { background-color: #2980b9; }
    </style>
</head>
<body class="admin-body">

    <div class="admin-layout">
        
        <?php include 'admin_side_bar.php' ?>

        <main class="admin-content">
            
            <header class="admin-topbar">
                <h3>إدارة المشاريع التفصيلية</h3>
                <span style="background: var(--primary-color); color: white; padding: 5px 15px; border-radius: 20px; font-weight: bold;">
                    إجمالي المشاريع: <?php echo $total_projects; ?>
                </span>
            </header>

            <?php if(isset($_GET['msg'])): ?>
                <?php if($_GET['msg'] == 'deleted'): ?>
                    <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center;">تم حذف المشروع بنجاح!</div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="table-container">
                <div class="table-controls">
                    <h4>قائمة المشاريع المضافة</h4>
                    <input type="text" id="searchProject" class="search-bar" placeholder="ابحث باسم المشروع أو القسم..." onkeyup="filterTable()">
                </div>

                <table class="admin-table" id="projectsTable">
                    <thead>
                        <tr>
                            <th>الصورة</th>
                            <th>معلومات المشروع</th>
                            <th>القسم والسنة</th>
                            <th>التقنيات المستخدمة</th>
                            <th>تاريخ الإضافة</th>
                            <th style="min-width: 150px;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM projects ORDER BY id DESC";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $pic = (!empty($row['image_url']) && $row['image_url'] != 'default.jpg') ? "uploads/" . $row['image_url'] : "https://via.placeholder.com/60x40?text=بدون";
                                $date = date('Y-m-d', strtotime($row['created_at'] ?? 'now')); // افتراض وجود created_at
                                
                                echo "<tr>";
                                
                                echo "<td><img src='" . $pic . "' class='project-thumb' alt='صورة'></td>";
                                
                                echo "<td>
                                        <strong>" . htmlspecialchars($row['title']) . "</strong><br>
                                        <span style='font-size:12px; color:#888;'>رقم التسلسل: #" . $row['id'] . "</span>
                                      </td>";
                                      
                                echo "<td>
                                        " . htmlspecialchars($row['department']) . "<br>
                                        <span style='font-size:13px; font-weight:bold;'> دفعة " . $row['grad_year'] . "</span>
                                      </td>";
                                      
                                echo "<td><div style='max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;' title='" . htmlspecialchars($row['tech_stack']) . "'>
                                        <span class='tech-stack-small'>" . htmlspecialchars($row['tech_stack']) . "</span>
                                      </div></td>";
                                      
                                echo "<td>" . $date . "</td>";
                                
                                echo "<td>
                                        <a href='project_details.php?id=" . $row['id'] . "' target='_blank' class='action-btn btn-sm view-btn' title='معاينة'>معاينة</a>
                                        <a href='edit_project.php?id=" . $row['id'] . "' class='action-btn btn-sm edit-btn' title='تعديل'>تعديل</a>
                                        <a href='delete_project.php?id=" . $row['id'] . "' class='action-btn btn-sm delete-btn' onclick='return confirm(\"هل أنت متأكد من الحذف؟\")' title='حذف'>حذف</a>
                                      </td>";
                                      
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center; padding: 20px;'>لا توجد مشاريع مضافة حتى الآن.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>

    <script>
    function filterTable() {
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("searchProject");
        filter = input.value.toUpperCase();
        table = document.getElementById("projectsTable");
        tr = table.getElementsByTagName("tr");

        for (i = 1; i < tr.length; i++) {
            var tdTitle = tr[i].getElementsByTagName("td")[1];
            var tdDept = tr[i].getElementsByTagName("td")[2];
            if (tdTitle || tdDept) {
                var txtTitle = tdTitle.textContent || tdTitle.innerText;
                var txtDept = tdDept.textContent || tdDept.innerText;
                if (txtTitle.toUpperCase().indexOf(filter) > -1 || txtDept.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }       
        }
    }
    </script>

</body>
</html>