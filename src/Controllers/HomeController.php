<?php
namespace DACS\Controllers;

// Nhúng Model Product để dùng hàm getFeaturedProducts()
// (Dùng __DIR__ để đường dẫn luôn chính xác)
require_once __DIR__ . '/../Models/Product.php';

class HomeController {
    private $conn;

    /**
     * HÀM KHỞI TẠO (Constructor)
     * - Nhận kết nối $db từ bên ngoài (Dependency Injection)
     * - Giúp loại bỏ hoàn toàn "global $conn" (code sạch hơn)
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    public function index() {
        // 1. Lấy 10 sản phẩm nổi bật
        // Truyền $this->conn (kết nối nội bộ) vào hàm model
        $featuredProducts = getFeaturedProducts($this->conn, 10);
        
        // 2. Gọi View và truyền biến $featuredProducts sang để hiển thị
        require_once __DIR__ . '/../../views/pages/index.php';
    }
}
?>