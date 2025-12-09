<?php
session_start();

// Định nghĩa đường dẫn gốc
define('PROJECT_ROOT', dirname(__DIR__));

// --- 1. LOAD FILE CẤU HÌNH & CONTROLLER ---
require_once PROJECT_ROOT . '/src/Config/db.php';
require_once PROJECT_ROOT . '/src/Helpers/image_helper.php';
require_once PROJECT_ROOT . '/src/Helpers/format_helper.php';

// Load các Controller thủ công (Sau này dùng Composer Autoload sẽ bỏ đoạn này)
require_once PROJECT_ROOT . '/src/Controllers/AuthController.php';
require_once PROJECT_ROOT . '/src/Controllers/HomeController.php';
require_once PROJECT_ROOT . '/src/Controllers/PageController.php';
require_once PROJECT_ROOT . '/src/Controllers/ProductController.php';
require_once PROJECT_ROOT . '/src/Controllers/CartController.php';

// --- 2. KHAI BÁO SỬ DỤNG NAMESPACE ---
use DACS\Controllers\AuthController;
use DACS\Controllers\ProductController;
use DACS\Controllers\HomeController;
use DACS\Controllers\PageController;
use DACS\Controllers\CartController;

// --- 3. ĐIỀU HƯỚNG (ROUTER) ---
$page   = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? 'index';

switch ($page) {
    // --- KHU VỰC TÀI KHOẢN ---
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

    // --- CÁC TRANG SẢN PHẨM (Đã có Controller) ---
    case 'anime':
        $controller = new ProductController();
        $controller->indexAnime();
        break;
        
    case 'gundam':
        $controller = new ProductController();
        $controller->indexGundam();
        break;
        
    case 'marvel':
        $controller = new ProductController();
        $controller->indexMarvel();
        break;

    // --- CÁC TRANG KHÁC (Bây giờ dùng PageController) ---
    
    case 'product':
        $controller = new ProductController();
        $controller->detail(); // Gọi hàm detail vừa tạo
        break;

    case 'contact':
        $controller = new PageController();
        $controller->contact();
        break;

    case 'promo':
        $controller = new PageController();
        $controller->promo();
        break;
        
    case 'profile':
        $controller = new PageController();
        $controller->profile();
        break;

    // --- TRANG CHỦ ---
    case 'home':
    default:
        $controller = new HomeController();
        $controller->index();
        break;
        
    case 'cart':
        $controller = new CartController();
        $controller->index();
        break;
}
?>