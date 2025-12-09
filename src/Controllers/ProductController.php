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
    // Hàm xử lý trang Gundam (Đã thêm phân trang)
    public function indexGundam() {
        // 1. Cấu hình phân trang
        $limit = 10;
        // Dùng 'page_num' vì 'page' đang dùng để định tuyến (page=gundam)
        $page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1; 
        if ($page < 1) $page = 1;
        
        $offset = ($page - 1) * $limit;
    
        // 2. Lấy dữ liệu
        $products = getProductsByCategory($this->conn, 'gundam', $limit, $offset);
        $totalProducts = countProductsByCategory($this->conn, 'gundam');
        $totalPages = ceil($totalProducts / $limit);
    
        // 3. Gọi View (đã sửa ở trên)
        require_once __DIR__ . '/../../views/pages/gundam_index.php';
    }

    // Hàm xử lý trang Marvel
    public function indexMarvel() {
        $products = getProductsByCategory($this->conn, 'marvel');
        require_once __DIR__ . '/../../views/pages/marvel_index.php';
    }

    public function detail() {
        // 1. Lấy ID từ URL, ép kiểu int để bảo mật
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        // 2. Lấy thông tin sản phẩm chính
        $product = getProductById($this->conn, $id);

        // Nếu không tìm thấy sản phẩm, đá về trang chủ
        if (!$product) {
            header('Location: /DACS/public/index.php');
            exit;
        }

        // 3. Chuẩn bị các dữ liệu phụ trợ cho View
        // - Lấy danh sách ảnh gallery
        $images = getProductImages($this->conn, $id);
        
        // - Lấy sản phẩm liên quan (cùng category, trừ sản phẩm hiện tại)
        $relatedProducts = getRelatedProducts($this->conn, $product['category'], $id);

        // - ĐỊNH NGHĨA BIẾN $firstImg MÀ VIEW ĐANG CẦN CHO JS
        $firstImg = $product['image_url']; 

        // 4. Gọi View hiển thị
        require_once __DIR__ . '/../../views/pages/product.php';
    }
}