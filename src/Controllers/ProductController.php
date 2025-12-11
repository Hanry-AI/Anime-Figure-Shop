<?php
/**
 * NAMESPACE (KHÔNG GIAN TÊN)
 * Định danh vị trí của file này trong dự án.
 * Giúp PHP hiểu DACS\Controllers\ProductController khác với các Controller của thư viện khác.
 */
namespace DACS\Controllers;

// 1. Nhúng file cấu hình Database
require_once __DIR__ . '/../Config/db.php';

// 2. Nhúng Model Product (Đã được chuyển thành Class)
require_once __DIR__ . '/../Models/Product.php';

// Sử dụng namespace của Model để gọi cho gọn
use DACS\Models\ProductModel;

class ProductController {
    
    // Thuộc tính lưu kết nối Database
    private $conn;
    
    // Thuộc tính chứa đối tượng Model (Đây là cầu nối để lấy dữ liệu)
    private $productModel;

    /**
     * HÀM KHỞI TẠO (__construct)
     * ---------------------------
     * Chạy ngay lập tức khi Controller được gọi.
     * Nhiệm vụ:
     * 1. Nhận kết nối DB từ bên ngoài ($db) -> Kỹ thuật Dependency Injection.
     * 2. Khởi tạo đối tượng ProductModel để sẵn sàng sử dụng các hàm lấy dữ liệu.
     */
    public function __construct($db) {
        $this->conn = $db;
        
        // Khởi tạo Model và truyền kết nối DB vào cho nó
        // Từ giờ, muốn lấy dữ liệu gì thì cứ nhờ $this->productModel làm
        $this->productModel = new ProductModel($db);
    }

    /**
     * CONTROLLER: TRANG ANIME
     * Nhiệm vụ: Lấy danh sách sản phẩm Anime và hiển thị ra View.
     */
    public function indexAnime() {
        // GỌI MODEL: "Ê Model, lấy cho tao danh sách sản phẩm danh mục 'anime' nhé"
        // Thay vì gọi hàm lẻ tẻ, ta gọi phương thức của đối tượng -> Chuẩn OOP
        $products = $this->productModel->getProductsByCategory('anime');

        // GỌI VIEW: Hiển thị giao diện và đổ dữ liệu $products vào
        require_once __DIR__ . '/../../views/pages/anime_index.php';
    }

    /**
     * CONTROLLER: TRANG GUNDAM (Có phân trang)
     * Nhiệm vụ: Tính toán xem đang ở trang mấy, cần lấy bao nhiêu sản phẩm.
     */
    public function indexGundam() {
        // Cấu hình: Mỗi trang hiển thị 10 sản phẩm
        $limit = 10;
        
        // Lấy số trang hiện tại từ URL (Ví dụ: index.php?page_num=2)
        // Nếu không có thì mặc định là trang 1. Ép kiểu (int) để bảo mật.
        $page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1; 
        if ($page < 1) $page = 1;
        
        // Công thức tính vị trí bắt đầu lấy dữ liệu (Offset) trong SQL
        // Ví dụ: Trang 1 -> offset 0. Trang 2 -> offset 10.
        $offset = ($page - 1) * $limit;
    
        // GỌI MODEL:
        // 1. Lấy danh sách sản phẩm cho trang hiện tại
        $products = $this->productModel->getProductsByCategory('gundam', $limit, $offset);
        
        // 2. Đếm tổng số sản phẩm Gundam có trong kho (để biết chia được bao nhiêu trang)
        $totalProducts = $this->productModel->countProductsByCategory('gundam');
        
        // Tính tổng số trang (làm tròn lên bằng hàm ceil)
        $totalPages = ceil($totalProducts / $limit);
    
        // GỌI VIEW
        require_once __DIR__ . '/../../views/pages/gundam_index.php';
    }

    /**
     * CONTROLLER: TRANG MARVEL
     */
    public function indexMarvel() {
        // Logic đơn giản giống trang Anime
        $products = $this->productModel->getProductsByCategory('marvel');
        
        require_once __DIR__ . '/../../views/pages/marvel_index.php';
    }

    /**
     * CONTROLLER: TRANG CHI TIẾT SẢN PHẨM
     * Nhiệm vụ: Hiển thị thông tin đầy đủ của 1 sản phẩm cụ thể.
     */
    public function detail() {
        // Lấy ID sản phẩm từ URL (Ví dụ: ...&id=15)
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        // GỌI MODEL: Lấy thông tin chi tiết sản phẩm theo ID
        $product = $this->productModel->getProductById($id);

        // Kiểm tra: Nếu sản phẩm không tồn tại (do ID sai hoặc đã bị xóa)
        if (!$product) {
            // Chuyển hướng người dùng về trang chủ
            header('Location: /DACS/public/index.php');
            exit;
        }

        // GỌI MODEL: Lấy thêm các dữ liệu phụ trợ
        // 1. Lấy danh sách ảnh phụ (Gallery) để làm slide ảnh
        $images = $this->productModel->getProductImages($id);
        
        // 2. Lấy danh sách sản phẩm liên quan (Gợi ý cho khách hàng mua thêm)
        $relatedProducts = $this->productModel->getRelatedProducts($product['category'], $id);

        // Biến này dùng cho Javascript để đổi ảnh khi click vào ảnh nhỏ
        $firstImg = $product['image_url']; 

        // GỌI VIEW
        require_once __DIR__ . '/../../views/pages/product.php';
    }
}
?>