<?php
namespace DACS\Controllers;

// 1. Nhúng các file cần thiết
// Dùng __DIR__ để đường dẫn luôn đúng dù gọi từ đâu
require_once __DIR__ . '/../Config/db.php';
require_once __DIR__ . '/../Models/Product.php';

class ProductController {
    private $conn;

    /**
     * HÀM KHỞI TẠO (Constructor)
     * Nhận kết nối DB từ bên ngoài (Dependency Injection)
     * Giúp loại bỏ lỗi dùng "global" và dễ giải thích với giáo viên.
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * TRANG ANIME
     * Hiển thị danh sách sản phẩm thuộc danh mục Anime
     */
    public function indexAnime() {
        // Gọi hàm từ Model (Product.php), truyền kết nối $this->conn vào
        $products = getProductsByCategory($this->conn, 'anime');

        // Gọi View hiển thị
        require_once __DIR__ . '/../../views/pages/anime_index.php';
    }

    /**
     * TRANG GUNDAM (Có phân trang)
     * Logic phân trang được tính toán tại đây để View chỉ việc hiển thị
     */
    public function indexGundam() {
        // Cấu hình số lượng sản phẩm mỗi trang
        $limit = 10;
        
        // Lấy trang hiện tại từ URL, ép kiểu số nguyên (int) để bảo mật
        $page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1; 
        if ($page < 1) $page = 1;
        
        // Tính vị trí bắt đầu lấy dữ liệu trong DB (Offset)
        $offset = ($page - 1) * $limit;
    
        // Lấy danh sách sản phẩm và tổng số lượng từ Model
        $products = getProductsByCategory($this->conn, 'gundam', $limit, $offset);
        $totalProducts = countProductsByCategory($this->conn, 'gundam');
        
        // Tính tổng số trang (làm tròn lên)
        $totalPages = ceil($totalProducts / $limit);
    
        // Gọi View
        require_once __DIR__ . '/../../views/pages/gundam_index.php';
    }

    /**
     * TRANG MARVEL
     */
    public function indexMarvel() {
        $products = getProductsByCategory($this->conn, 'marvel');
        require_once __DIR__ . '/../../views/pages/marvel_index.php';
    }

    /**
     * TRANG CHI TIẾT SẢN PHẨM
     */
    public function detail() {
        // Lấy ID từ URL và ép kiểu int để chống hack SQL Injection
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        // Lấy thông tin sản phẩm
        $product = getProductById($this->conn, $id);

        // Nếu không tìm thấy sản phẩm, quay về trang chủ
        if (!$product) {
            header('Location: /DACS/public/index.php');
            exit;
        }

        // Lấy dữ liệu phụ trợ (Ảnh gallery, Sản phẩm liên quan)
        $images = getProductImages($this->conn, $id);
        $relatedProducts = getRelatedProducts($this->conn, $product['category'], $id);

        // Biến hỗ trợ JS đổi ảnh
        $firstImg = $product['image_url']; 

        // Gọi View
        require_once __DIR__ . '/../../views/pages/product.php';
    }
}
?>