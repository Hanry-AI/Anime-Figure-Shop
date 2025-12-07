<?php
namespace DACS\Controllers;

// Nhúng file cấu hình và Model Product
require_once __DIR__ . '/../Config/db.php';
require_once __DIR__ . '/../Models/Product.php';

class ProductController {
    private $conn;

    public function __construct() {
        // Lấy biến kết nối $conn từ global (được tạo trong db.php)
        global $conn;
        $this->conn = $conn;
    }

    // Hàm xử lý trang Anime
    public function indexAnime() {
        // 1. Lấy dữ liệu từ Model
        $products = getProductsByCategory($this->conn, 'anime');

        // 2. Gọi View hiển thị (biến $products sẽ tự động truyền sang view)
        require_once __DIR__ . '/../../views/pages/anime_index.php';
    }

    // Hàm xử lý trang Gundam
    public function indexGundam() {
        $products = getProductsByCategory($this->conn, 'gundam');
        require_once __DIR__ . '/../../views/pages/gundam_index.php';
    }

    // Hàm xử lý trang Marvel
    public function indexMarvel() {
        $products = getProductsByCategory($this->conn, 'marvel');
        require_once __DIR__ . '/../../views/pages/marvel_index.php';
    }
}