<?php
$servername = getenv('DB_HOST') ?: 'localhost';
$port = (int) (getenv('DB_PORT') ?: 3306);
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') !== false ? (string) getenv('DB_PASS') : '';
$dbname = getenv('DB_NAME') ?: 'graduation_projects';

try {
    $conn = new mysqli($servername, $username, $password, $dbname, $port > 0 ? $port : 3306);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(503);
    die(
        'فشل الاتصال بقاعدة البيانات. تأكد من تشغيل MySQL ووجود قاعدة '
        . htmlspecialchars($dbname, ENT_QUOTES, 'UTF-8')
        . ' وبيانات الدخول الصحيحة.<br><small>'
        . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
        . '</small>'
    );
}
