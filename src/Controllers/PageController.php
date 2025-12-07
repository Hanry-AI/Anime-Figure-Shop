<?php
namespace DACS\Controllers;

class PageController {
    
    // Trang liên hệ
    public function contact() {
        require_once __DIR__ . '/../../views/pages/contact_index.php';
    }

    // Trang khuyến mãi
    public function promo() {
        require_once __DIR__ . '/../../views/pages/promo_index.php';
    }

    // Trang hồ sơ cá nhân
    public function profile() {
        // Kiểm tra session (đảm bảo session đã start)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Nếu chưa đăng nhập thì đá về trang login
        if (!isset($_SESSION['user_id'])) {
            header('Location: /DACS/public/index.php?page=auth&action=login');
            exit;
        }

        require_once __DIR__ . '/../../views/pages/profile.php';
    }
    
    // Trang chi tiết sản phẩm (Tạm thời để đây, sau này nên chuyển sang ProductController)
    public function productDetail() {
        require_once __DIR__ . '/../../views/pages/product.php';
    }
}