<?php
/**
 * TRANG CHỦ (HOME CONTROLLER)
 * ---------------------------
 * Nhiệm vụ:
 * 1. Khởi tạo kết nối và Model Sản phẩm.
 * 2. Lấy dữ liệu sản phẩm nổi bật từ Database.
 * 3. Gọi giao diện (View) để hiển thị cho người dùng.
 */

// 1. Khai báo Namespace (Không gian tên)
// Giúp PHP phân biệt Class này với các Class trùng tên ở thư viện khác.
namespace DACS\Controllers;

// 2. Nhúng các file cấu hình và Model cần thiết
// __DIR__ giúp đường dẫn luôn chính xác dù file này được gọi từ đâu.
require_once __DIR__ . '/../Config/db.php';
require_once __DIR__ . '/../Models/Product.php'; // Đây là file chứa Class ProductModel

// Sử dụng từ khóa 'use' để gọi Model cho ngắn gọn (thay vì viết dài dòng DACS\Models\ProductModel)
use DACS\Models\ProductModel;

class HomeController {
    // Thuộc tính để lưu kết nối Database (nếu cần dùng trực tiếp)
    private $conn;
    
    // Thuộc tính để chứa đối tượng ProductModel
    // Đây là "cánh tay phải" giúp Controller lấy dữ liệu từ DB mà không cần viết SQL ở đây.
    private $productModel;

    /**
     * HÀM KHỞI TẠO (__construct)
     * ---------------------------
     * Chạy ngay lập tức khi Controller được gọi (new HomeController).
     * @param mysqli $db Biến kết nối Database được truyền từ bên ngoài vào.
     * Kỹ thuật này gọi là "Dependency Injection" (Tiêm phụ thuộc).
     * Lợi ích: Giúp code linh hoạt, dễ kiểm thử và không phụ thuộc vào biến global.
     */
    public function __construct($db) {
        $this->conn = $db;

        // Khởi tạo đối tượng ProductModel
        // Thay vì gọi hàm lẻ tẻ (procedural), ta tạo một "đối tượng" để quản lý sản phẩm.
        // Truyền $db vào để Model có thể kết nối CSDL.
        $this->productModel = new ProductModel($db);
    }

    /**
     * HÀM INDEX (Trang chính)
     * -----------------------
     * Được gọi khi người dùng truy cập vào trang chủ.
     */
    public function index() {
        // BƯỚC 1: Lấy dữ liệu từ Model
        // ----------------------------
        // Thay vì gọi hàm getFeaturedProducts($conn, 10) bị lỗi "undefined function",
        // Ta gọi phương thức của ĐỐI TƯỢNG: $this->productModel->getFeaturedProducts(10)
        // Đây là cách viết chuẩn Hướng Đối Tượng (OOP).
        
        // Lưu ý: Không cần truyền biến $conn vào nữa, vì Model đã giữ kết nối rồi.
        $featuredProducts = $this->productModel->getFeaturedProducts(10);
        
        // BƯỚC 2: Gọi View để hiển thị
        // ----------------------------
        // Biến $featuredProducts ở trên sẽ tự động được truyền sang file view bên dưới.
        // View chỉ việc foreach biến này để hiển thị danh sách sản phẩm.
        require_once __DIR__ . '/../../views/pages/index.php';
    }
}
?>