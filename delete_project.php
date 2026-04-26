<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    
    $project_id = intval($_GET['id']);

    // Fetch all files associated with the project to delete them from the server
    $sql_files = "SELECT image_url, pdf_file, project_poster, project_poster_pdf FROM projects WHERE id = $project_id";
    $result = $conn->query($sql_files);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // 1. Delete main project image
        if (!empty($row['image_url']) && $row['image_url'] != 'default.jpg') {
            $image_path = "uploads/" . $row['image_url'];
            if (file_exists($image_path)) unlink($image_path);
        }

        // 2. Delete poster image
        if (!empty($row['project_poster'])) {
            $poster_image_path = "uploads/" . $row['project_poster'];
            if (file_exists($poster_image_path)) unlink($poster_image_path);
        }

        // 3. Delete main documentation PDF
        if (!empty($row['pdf_file'])) {
            $pdf_path = "uploads/documents/" . $row['pdf_file'];
            if (file_exists($pdf_path)) unlink($pdf_path);
        }

        // 4. Delete poster PDF
        if (!empty($row['project_poster_pdf'])) {
            $poster_pdf_path = "uploads/documents/" . $row['project_poster_pdf'];
            if (file_exists($poster_pdf_path)) unlink($poster_pdf_path);
        }

        // Delete the project record from the database
        $delete_sql = "DELETE FROM projects WHERE id = $project_id";
        
        if ($conn->query($delete_sql)) {
            header("Location: manage_projects.php?msg=deleted");
            exit();
        } else {
            echo "حدث خطأ أثناء محاولة حذف المشروع: " . $conn->error;
        }

    } else {
        echo "عذراً، المشروع غير موجود أو تم حذفه مسبقاً.";
    }

} else {
    header("Location: manage_projects.php");
    exit();
}

$conn->close();
?>