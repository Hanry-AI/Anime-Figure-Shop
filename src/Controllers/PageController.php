<?php
namespace DACS\Controllers;

// Nhúng file config (để đảm bảo có kết nối nếu cần dùng sau này)
require_once __DIR__ . '/../Config/db.php';

class PageController {
    private $conn;

    /**
     * HÀM KHỞI TẠO (__construct)
     * - Nhận kết nối $db từ index.php (cho đồng bộ với các Controller khác)
     * - Khởi tạo Session để kiểm tra đăng nhập ở trang Profile
     */
    public function __construct($db) {
        $this->conn = $db;

        // Kiểm tra session đã bật chưa, nếu chưa thì bật lên
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * TRANG LIÊN HỆ
     */
    public function contact() {
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
     * - Cần kiểm tra đăng nhập trước khi cho vào xem
     */
    public function profile() {
        // Nếu chưa có user_id trong session -> Chưa đăng nhập -> Đá về trang login
        if (!isset($_SESSION['user_id'])) {
            // Chuyển hướng sang trang đăng nhập
            header('Location: /DACS/public/index.php?page=auth&action=login');
            exit;
        }

        // (Sau này nếu muốn lấy thông tin chi tiết user từ DB thì dùng $this->conn ở đây)
        
        require_once __DIR__ . '/../../views/pages/profile.php';
    }
    
    /**
     * [KHÔNG DÙNG NỮA]
     * Hàm này đã được chuyển sang ProductController->detail() rồi.
     * Để lại đây cho đỡ lỗi nếu lỡ có chỗ nào gọi nhầm, nhưng về cơ bản là thừa.
     */
    public function productDetail() {
        require_once __DIR__ . '/../../views/pages/product.php';
    }
}
?>