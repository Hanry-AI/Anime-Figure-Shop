<?php
session_start();

// Định nghĩa đường dẫn gốc
define('PROJECT_ROOT', dirname(__DIR__));

// --- 1. LOAD COMPOSER (QUAN TRỌNG) ---
// Dòng này sẽ tự động nạp tất cả các file Class (Model, Controller, Helper...)
// Bạn không cần require thủ công từng file nữa.
require_once PROJECT_ROOT . '/vendor/autoload.php';

// --- 2. SỬ DỤNG NAMESPACE ---
use DACS\Config\Database;
use DACS\Core\Router;

try {
    // --- 3. KHỞI TẠO APP ---
    
    // Khởi tạo kết nối Database (từ file src/Config/db.php hoặc Database.php)
    $db = new Database();
    $conn = $db->getConnection();

    // Khởi chạy Router để điều hướng (từ file src/Core/Router.php)
    $router = new Router($conn);
    $router->run();

} catch (Exception $e) {
    // Hiển thị lỗi thân thiện nếu có sự cố
    echo "<div style='color:red; font-weight:bold;'>Lỗi hệ thống: " . $e->getMessage() . "</div>";
    
    // Gợi ý debug:
    echo "<br><em>Gợi ý: Kiểm tra xem bạn đã chạy lệnh 'composer dump-autoload' chưa?</em>";
}
?>