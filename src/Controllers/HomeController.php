<?php
/**
 * TRANG CHỦ (HOME CONTROLLER)
 */
namespace DACS\Controllers;

// [QUAN TRỌNG] Bỏ require_once thủ công.
// Composer sẽ tự động nạp ProductModel.php khi thấy dòng 'use' bên dưới.
use DACS\Models\ProductModel;

class HomeController {
    private $conn;
    private $productModel;

    public function __construct($db) {
        $this->conn = $db;

        // Khởi tạo Model
        // Composer tự tìm file ProductModel.php để chạy dòng này
        $this->productModel = new ProductModel($db);
    }

    public function index() {
        // Lấy danh sách sản phẩm nổi bật
        $featuredProducts = $this->productModel->getFeaturedProducts(10);
        
        // Gọi View để hiển thị
        require_once __DIR__ . '/../../views/pages/index.php';
    }
}
?>