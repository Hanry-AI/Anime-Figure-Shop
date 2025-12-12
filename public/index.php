<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Định nghĩa đường dẫn gốc
define('PROJECT_ROOT', dirname(__DIR__));

// 1. Load Composer Autoload
require_once PROJECT_ROOT . '/vendor/autoload.php';

use DACS\Core\App;

try {
    // 2. Khởi tạo ứng dụng
    $app = new App();
    
    // 3. Chạy ứng dụng
    $app->run();

} catch (Exception $e) {
    echo "Lỗi hệ thống: " . $e->getMessage();
}
?>