<?php
session_start();

// Định nghĩa đường dẫn gốc
define('PROJECT_ROOT', dirname(__DIR__));

// Nhúng các file cấu hình và Controller
require_once PROJECT_ROOT . '/src/Config/db.php';
require_once PROJECT_ROOT . '/src/Helpers/image_helper.php';
require_once PROJECT_ROOT . '/src/Controllers/AuthController.php';

use DACS\Controllers\ProductController;
use DACS\Controllers\AuthController;

// --- ĐÂY LÀ LỄ TÂN (ROUTER) ---
$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? 'index';

switch ($page) {
    // 1. Khu vực Tài khoản
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

    // 2. Các trang danh mục
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

    // 3. Trang chi tiết sản phẩm (QUAN TRỌNG: Đây là phần bạn đang thiếu)
    case 'product':
        require_once PROJECT_ROOT . '/views/pages/product.php';
        break;

    // 4. Các trang khác
    case 'contact':
        require_once PROJECT_ROOT . '/views/pages/contact_index.php';
        break;

    case 'promo':
        require_once PROJECT_ROOT . '/views/pages/promo_index.php';
        break;
        
    case 'profile':
        require_once PROJECT_ROOT . '/views/pages/profile.php';
        break;

    // 5. Trang chủ (Mặc định)
    case 'home':
    default:
        require_once PROJECT_ROOT . '/views/pages/index.php'; 
        break;
}
?>