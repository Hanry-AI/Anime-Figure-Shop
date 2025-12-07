<?php
namespace DACS\Controllers;

// Nạp Model để lấy dữ liệu (nếu chưa autoload)
require_once __DIR__ . '/../Models/Product.php';

class HomeController {
    public function index() {
        global $conn; // Lấy kết nối DB
        
        // Lấy 10 sản phẩm nổi bật
        $featuredProducts = getFeaturedProducts($conn, 10);
        
        // Gọi View và truyền biến $featuredProducts sang
        require_once __DIR__ . '/../../views/pages/index.php';
    }
}