<?php
/**
 * Entry Point - Cửa ngõ ứng dụng
 */

use DACS\Core\App;
use DACS\Core\Session;

// Định nghĩa đường dẫn gốc
define('PROJECT_ROOT', dirname(__DIR__));

// Load Composer
require_once PROJECT_ROOT . '/vendor/autoload.php';

try {
    // 1. Khởi động Session (Lưu vào Database)
    new Session();

    // 2. Khởi chạy Ứng dụng
    $app = new App();
    $app->run();

} catch (Throwable $e) {
    // Bắt tất cả lỗi (Exception & Error)
    // Trong môi trường Production nên ghi log thay vì echo
    error_log($e->getMessage());
    echo "Đã có lỗi xảy ra. Vui lòng thử lại sau.";
}
?>