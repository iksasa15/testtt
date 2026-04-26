<?php
include 'db_connect.php';

$action = $_GET['action'] ?? '';

if ($action == 'get') {
    $sql = "SELECT * FROM chat_messages ORDER BY created_at ASC";
    $result = $conn->query($sql);
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    echo json_encode($messages);
} 
elseif ($action == 'send' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $msg = $conn->real_escape_string($_POST['message']);
    $pic = $conn->real_escape_string($_POST['pic']); // استقبال الصورة
    
    if (!empty($name) && !empty($msg)) {
        $sql = "INSERT INTO chat_messages (sender_name, message, profile_pic) VALUES ('$name', '$msg', '$pic')";
        $conn->query($sql);
    }
}
$conn->close();
?>