<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login_admin.php");
    exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    
    $user_id = intval($_GET['id']);

    $sql_image = "SELECT profile_pic FROM users WHERE id = $user_id";
    $result = $conn->query($sql_image);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $image_to_delete = $row['profile_pic'];

        if (!empty($image_to_delete) && $image_to_delete != 'default_avatar.png') {
            $file_path = "uploads/profiles/" . $image_to_delete;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        $delete_sql = "DELETE FROM users WHERE id = $user_id";
        
        if ($conn->query($delete_sql)) {
            header("Location: manage_students.php?msg=deleted");
            exit();
        } else {
            echo "حدث خطأ أثناء محاولة حذف الحساب: " . $conn->error;
        }

    } else {
        echo "عذراً، الطالب غير موجود أو تم حذفه مسبقاً.";
    }

} else {
    header("Location: manage_students.php");
    exit();
}

$conn->close();
?>