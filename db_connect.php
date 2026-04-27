<?php

/**
 * Cache-bust stylesheet so browsers pick up style.css changes after deploy/edit.
 */
function style_css_href(): string
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $path = __DIR__ . '/style.css';
    $v = is_readable($path) ? (string) filemtime($path) : '1';
    $cache = 'style.css?v=' . rawurlencode($v);
    return $cache;
}

// DB_* للتشغيل المحلي؛ على Railway اربط متغيرات خدمة MySQL (MYSQLHOST وغيرها) كمرجع من نفس المشروع.
$servername = getenv('DB_HOST') ?: (getenv('MYSQLHOST') ?: 'localhost');
$port = (int) (getenv('DB_PORT') ?: (getenv('MYSQLPORT') ?: 3306));
$username = getenv('DB_USER') ?: (getenv('MYSQLUSER') ?: 'root');
if (getenv('DB_PASS') !== false) {
    $password = (string) getenv('DB_PASS');
} elseif (getenv('MYSQLPASSWORD') !== false) {
    $password = (string) getenv('MYSQLPASSWORD');
} else {
    $password = '';
}
$dbname = getenv('DB_NAME') ?: (getenv('MYSQLDATABASE') ?: 'graduation_projects');

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

require_once __DIR__ . '/includes/seed_projects.php';
ensure_sample_projects_if_empty($conn);
