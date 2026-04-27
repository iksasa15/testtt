<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login_admin.php");
    exit();
}

$total_projects = $conn->query("SELECT COUNT(*) as count FROM projects")->fetch_assoc()['count'];
$total_students = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_departments = $conn->query("SELECT COUNT(DISTINCT department) as count FROM projects")->fetch_assoc()['count'];

$dept_labels = [];
$dept_counts = [];
$dept_query = $conn->query("SELECT department, COUNT(*) as count FROM projects GROUP BY department");
while($row = $dept_query->fetch_assoc()) {
    $dept_labels[] = $row['department'];
    $dept_counts[] = $row['count'];
}

$year_labels = [];
$year_counts = [];
$year_query = $conn->query("SELECT grad_year, COUNT(*) as count FROM projects GROUP BY grad_year ORDER BY grad_year DESC");
while($row = $year_query->fetch_assoc()) {
    $year_labels[] = $row['grad_year'];
    $year_counts[] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم الإدارة | منصة المشاريع</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(style_css_href(), ENT_QUOTES, 'UTF-8'); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="admin-body">

    <div class="admin-layout">
        
        <?php include 'admin_side_bar.php' ?>

        <main class="admin-content">
            
            <header class="admin-topbar">
                <h3>مرحباً بك، مدير النظام</h3>
            </header>

            <div class="stats-grid">
                <div class="stat-card">
                    <h4>إجمالي المشاريع</h4>
                    <p class="stat-number"><?php echo $total_projects; ?></p>
                </div>
                <div class="stat-card">
                    <h4>إجمالي الطلاب</h4>
                    <p class="stat-number"><?php echo $total_students; ?></p>
                </div>
                <div class="stat-card">
                    <h4>الأقسام المسجلة</h4>
                    <p class="stat-number"><?php echo $total_departments; ?></p>
                </div>
            </div>

            <div class="charts-container">
                <div class="chart-box">
                    <h4>المشاريع حسب القسم</h4>
                    <canvas id="deptChart"></canvas>
                </div>
                <div class="chart-box">
                    <h4>المشاريع حسب سنة التخرج</h4>
                    <canvas id="yearChart"></canvas>
                </div>
            </div>
            
        </main>
    </div>

    <script>
        const deptLabels = <?php echo json_encode($dept_labels); ?>;
        const deptData = <?php echo json_encode($dept_counts); ?>;
        
        const yearLabels = <?php echo json_encode($year_labels); ?>;
        const yearData = <?php echo json_encode($year_counts); ?>;

        const ctxDept = document.getElementById('deptChart').getContext('2d');
        new Chart(ctxDept, {
            type: 'bar',
            data: {
                labels: deptLabels,
                datasets: [{
                    label: 'عدد المشاريع',
                    data: deptData,
                    backgroundColor: '#3498db',
                    borderRadius: 5
                }]
            },
            options: { 
                responsive: true,
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });

        const ctxYear = document.getElementById('yearChart').getContext('2d');
        new Chart(ctxYear, {
            type: 'doughnut',
            data: {
                labels: yearLabels,
                datasets: [{
                    data: yearData,
                    backgroundColor: ['#2ecc71', '#f1c40f', '#e67e22', '#9b59b6', '#34495e']
                }]
            },
            options: { responsive: true }
        });
    </script>
</body>
</html>