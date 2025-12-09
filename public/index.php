<?php
session_start();

// Định nghĩa đường dẫn gốc
define('PROJECT_ROOT', dirname(__DIR__));

// --- 1. LOAD FILE CẤU HÌNH & CONTROLLER ---
// Khi require file db.php này, biến $conn sẽ được tạo ra và có thể dùng ngay bên dưới
require_once PROJECT_ROOT . '/src/Config/db.php';

require_once PROJECT_ROOT . '/src/Helpers/image_helper.php';
require_once PROJECT_ROOT . '/src/Helpers/format_helper.php';

// Load các Controller thủ công
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
        // [CẬP NHẬT] Truyền $conn vào đây
        $controller = new AuthController($conn); 
        
        if ($action === 'logout') {
            $controller->logout();
        } else {
            $controller->index();
        }
        break;

    // --- CÁC TRANG SẢN PHẨM (Đã sửa Controller) ---
    case 'anime':
        // [QUAN TRỌNG] Truyền $conn vào đây vì ProductController đã sửa __construct($db)
        $controller = new ProductController($conn);
        $controller->indexAnime();
        break;
        
    case 'gundam':
        // [QUAN TRỌNG] Truyền $conn vào
        $controller = new ProductController($conn);
        $controller->indexGundam();
        break;
        
    case 'marvel':
        // [QUAN TRỌNG] Truyền $conn vào
        $controller = new ProductController($conn);
        $controller->indexMarvel();
        break;

    // --- CÁC TRANG KHÁC ---
    
    case 'product':
        // [QUAN TRỌNG] Truyền $conn vào (Trang chi tiết cũng dùng ProductController)
        $controller = new ProductController($conn);
        $controller->detail(); 
        break;

    case 'contact':
        // [CẬP NHẬT] Truyền $conn vào
        $controller = new PageController($conn);
        $controller->contact();
        break;

    case 'promo':
        // [CẬP NHẬT] Truyền $conn vào
        $controller = new PageController($conn);
        $controller->promo();
        break;
        
    case 'profile':
        // [CẬP NHẬT] Truyền $conn vào
        $controller = new PageController($conn);
        $controller->profile();
        break;

    // --- TRANG CHỦ ---
    case 'home':
    default:
        // [CẬP NHẬT] Truyền thêm $conn vào đây
        // Vì HomeController vừa được sửa hàm __construct($db)
        $controller = new HomeController($conn); 
        $controller->index();
        break;
        
    case 'cart':
        // [CẬP NHẬT] Truyền $conn vào CartController
        $controller = new CartController($conn);
        $controller->index();
        break;
}
?>