<?php
namespace DACS\Controllers;

// 1. Nhúng file cấu hình Database
require_once __DIR__ . '/../Config/db.php';

// 2. Nhúng Model User để lấy dữ liệu người dùng cho trang Profile
require_once __DIR__ . '/../Models/User.php';
use DACS\Models\UserModel;

class PageController {
    private $conn;

    /**
     * HÀM KHỞI TẠO (__construct)
     * ---------------------------
     * - Nhận biến kết nối $db từ index.php truyền vào (Dependency Injection).
     * - Khởi tạo Session để kiểm tra trạng thái đăng nhập.
     */
    public function __construct($db) {
        $this->conn = $db; // Lưu kết nối DB vào biến của class để dùng lại ở các hàm dưới

        // Kiểm tra xem session đã bật chưa, nếu chưa thì bật lên
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * TRANG LIÊN HỆ
     * - Trang này là trang tĩnh, chỉ hiển thị form, không cần gọi Database.
     */
    public function contact() {
        require_once __DIR__ . '/../../views/pages/contact_index.php';
    }

    /**
     * TRANG KHUYẾN MÃI
     * - Trang tĩnh, hiển thị thông tin banner/promo.
     */
    public function promo() {
        require_once __DIR__ . '/../../views/pages/promo_index.php';
    }

    /**
     * TRANG HỒ SƠ CÁ NHÂN (PROFILE)
     * -----------------------------
     * Logic:
     * 1. Kiểm tra đăng nhập (Bảo mật).
     * 2. Nếu đã đăng nhập -> Gọi Model để lấy thông tin chi tiết từ DB.
     * 3. Truyền dữ liệu sang View để hiển thị.
     */
    public function profile() {
        // BƯỚC 1: Kiểm tra bảo mật (Authentication Check)
        // Nếu không tìm thấy user_id trong Session -> Nghĩa là chưa đăng nhập.
        if (!isset($_SESSION['user_id'])) {
            // Chuyển hướng người dùng về trang đăng nhập
            header('Location: /DACS/public/index.php?page=auth&action=login');
            exit; // Dừng code ngay lập tức để chặn truy cập trái phép
        }

        // BƯỚC 2: Lấy dữ liệu người dùng (Data Fetching)
        // Khởi tạo UserModel và truyền kết nối DB vào
        $userModel = new UserModel($this->conn);
        
        // Gọi hàm getUserById để lấy toàn bộ thông tin (Email, Tên, Ngày tạo...)
        // Biến $user này sẽ được dùng trực tiếp bên trong file view 'profile.php'
        $user = $userModel->getUserById($_SESSION['user_id']);

        // BƯỚC 3: Hiển thị giao diện (View Rendering)
        require_once __DIR__ . '/../../views/pages/profile.php';
    }
    
    /**
     * [DEPRECATED - KHÔNG DÙNG NỮA]
     * Hàm này trước đây dùng để xem chi tiết sản phẩm, nhưng giờ chức năng đó
     * đã được chuyển sang ProductController -> detail().
     * Giữ lại để tránh lỗi Fatal Error nếu lỡ còn link cũ nào trỏ tới, 
     * nhưng nên xóa dần trong tương lai.
     */
    public function productDetail() {
        // Có thể redirect về trang chủ hoặc hiển thị lỗi 404
        require_once __DIR__ . '/../../views/pages/product.php';
    }
}
?>