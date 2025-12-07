<?php
session_start();

// Định nghĩa đường dẫn gốc
define('PROJECT_ROOT', dirname(__DIR__));

// Nhúng các file cấu hình và Controller
require_once PROJECT_ROOT . '/src/Config/db.php';
require_once PROJECT_ROOT . '/src/Helpers/image_helper.php';
require_once PROJECT_ROOT . '/src/Controllers/AuthController.php';

use DACS\Controllers\AuthController;

// --- ĐÂY LÀ LỄ TÂN (ROUTER) ---
// Lấy yêu cầu của khách: khách muốn đi 'page' nào?
// Nếu khách không nói gì (không có ?page=...), mặc định cho vào 'home'
$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? 'index';

switch ($page) {
    // Nếu khách muốn vào khu Đăng nhập/Đăng ký
    case 'auth':
    case 'login':
    case 'register':
        $controller = new AuthController();
        if ($action === 'logout') {
            $controller->logout();
        } else {
            $controller->index();
        }
        break;

    // Nếu khách muốn vào Trang Chủ (hoặc đi lung tung)
    case 'home':
    default:
        // Lễ tân mời khách vào "Phòng Khách" mà bạn vừa dọn đồ sang
        require_once PROJECT_ROOT . '/views/pages/home.php'; 
        break;
}
?>