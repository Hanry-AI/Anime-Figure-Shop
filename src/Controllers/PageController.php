<?php
namespace DACS\Controllers;

// [QUAN TRỌNG] Bỏ hết các dòng require_once thủ công (Config, Model...)
// Composer sẽ tự động nạp class UserModel khi bạn dùng dòng 'use' bên dưới.

use DACS\Models\UserModel;

class PageController {
    private $conn;

    /**
     * HÀM KHỞI TẠO (__construct)
     */
    public function __construct($db) {
        $this->conn = $db;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * TRANG LIÊN HỆ
     */
    public function contact() {
        // Gọi View (View vẫn phải require thủ công vì nó không phải là Class)
        require_once __DIR__ . '/../../views/pages/contact_index.php';
    }

    /**
     * TRANG KHUYẾN MÃI
     */
    public function promo() {
        require_once __DIR__ . '/../../views/pages/promo_index.php';
    }

    /**
     * TRANG HỒ SƠ CÁ NHÂN (PROFILE)
     */
    public function profile() {
        // 1. Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            header('Location: /DACS/public/index.php?page=auth&action=login');
            exit;
        }

        // 2. Lấy dữ liệu user từ Database
        // Nhờ Composer, dòng new UserModel() này sẽ tự tìm đúng file UserModel.php
        $userModel = new UserModel($this->conn);
        $user = $userModel->getUserById($_SESSION['user_id']);

        // 3. Hiển thị View
        require_once __DIR__ . '/../../views/pages/profile.php';
    }
}
?>