<?php
/**
 * NAMESPACE (KHÔNG GIAN TÊN):
 * Giúp quản lý code gọn gàng, tránh bị trùng tên hàm/class với các file khác.
 * Ví dụ: Class ProductModel của bạn sẽ không bị nhầm với Product của thư viện khác.
 */
namespace DACS\Models;

// Nhúng file cấu hình DB và Helper xử lý ảnh
require_once __DIR__ . '/../Config/db.php';
require_once __DIR__ . '/../Helpers/image_helper.php';

/**
 * CLASS ProductModel (MÔ HÌNH SẢN PHẨM)
 * -------------------------------------
 * Thay vì viết các hàm rời rạc (thủ tục), ta gom tất cả vào một Class (lớp).
 * Đại diện cho "Đối tượng" xử lý mọi thứ liên quan đến bảng 'products' trong Database.
 */
class ProductModel {
    
    // Thuộc tính private: Chỉ nội bộ class này mới dùng được biến $conn.
    // Giúp bảo mật kết nối, bên ngoài không thể can thiệp lung tung.
    private $conn;

    /**
     * CONSTRUCTOR (HÀM KHỞI TẠO)
     * --------------------------
     * Hàm này tự động chạy khi bạn viết: $model = new ProductModel($db);
     * * @param object $db Biến kết nối CSDL được truyền từ bên ngoài vào.
     * Kỹ thuật này gọi là "Dependency Injection" (Tiêm phụ thuộc).
     * -> Giúp Code linh hoạt: Class không tự tạo kết nối mà "nhận" kết nối để dùng.
     */
    public function __construct($db) {
        $this->conn = $db; 
    }

    /* ==========================================================================
       KHU VỰC 1: FRONTEND (DÀNH CHO KHÁCH HÀNG)
       Các phương thức lấy dữ liệu để hiển thị ra trang web
       ========================================================================== */

    /**
     * 1. Lấy danh sách sản phẩm theo danh mục
     * Lưu ý: Không cần tham số $conn nữa vì đã dùng $this->conn
     */
    public function getProductsByCategory($category, $limit = 10, $offset = 0) {
        // Câu lệnh SQL lấy dữ liệu
        $sql = "SELECT id, name, price, image_url, category, overview AS series, details AS brand, note AS scale
                FROM products
                WHERE category = ?
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?";
        
        // Dùng $this->conn để gọi prepare
        $stmt = $this->conn->prepare($sql);
        
        // Gán giá trị vào dấu ? (s = string, i = integer)
        $stmt->bind_param("sii", $category, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        // Kiểm tra nếu có dữ liệu trả về
        if ($result && $result->num_rows > 0) {
            $products = $result->fetch_all(MYSQLI_ASSOC);
            
            // Xử lý chuẩn hóa đường dẫn ảnh (thêm http/https nếu thiếu)
            foreach ($products as &$p) {
                if (isset($p['image_url'])) {
                    // Gọi hàm helper (giả sử hàm này nằm ở file global helper)
                    $p['image_url'] = normalizeImageUrl($p['image_url']);
                    // Tạo thêm key 'img_url' để tương thích với nhiều view khác nhau
                    $p['img_url'] = $p['image_url']; 
                }
            }
        }
        $stmt->close(); // Đóng statement để giải phóng tài nguyên
        return $products;
    }

    /**
     * 2. Đếm tổng số sản phẩm trong danh mục
     * Dùng để tính toán phân trang (Pagination)
     */
    public function countProductsByCategory($category) {
        $sql = "SELECT COUNT(*) as total FROM products WHERE category = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $category);
        $stmt->execute();
        // Lấy 1 dòng kết quả duy nhất
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        // Trả về số lượng (nếu null thì trả về 0)
        return $result['total'] ?? 0;
    }

    /**
     * 3. Lấy sản phẩm nổi bật/mới nhất (Featured)
     */
    public function getFeaturedProducts($limit = 10) {
        $sql = "SELECT id, name, price, image_url AS img_url, category 
                FROM products 
                ORDER BY id DESC 
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $row['img_url'] = normalizeImageUrl($row['img_url']);
            $row['image_url'] = $row['img_url']; // Đồng bộ key ảnh
            $products[] = $row;
        }
        $stmt->close();
        return $products;
    }

    /**
     * 4. Lấy chi tiết 1 sản phẩm theo ID
     */
    public function getProductById($id) {
        $sql = "SELECT * FROM products WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($product) {
            $product['image_url'] = normalizeImageUrl($product['image_url']);
            $product['img_url'] = $product['image_url']; 
        }
        return $product;
    }

    /**
     * 5. Lấy sản phẩm liên quan (Random)
     * Trừ chính sản phẩm đang xem ($excludeId)
     */
    public function getRelatedProducts($category, $excludeId, $limit = 4) {
        $sql = "SELECT id, name, price, image_url, category 
                FROM products 
                WHERE category = ? AND id != ? 
                ORDER BY RAND() 
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $category, $excludeId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $row['image_url'] = normalizeImageUrl($row['image_url']);
            $row['img_url'] = $row['image_url'];
            $products[] = $row;
        }
        $stmt->close();
        return $products;
    }

    /**
     * 6. Lấy danh sách ảnh phụ (Gallery) từ bảng product_images
     */
    public function getProductImages($productId) {
        // Kiểm tra bảng tồn tại (phòng trường hợp chưa migrate DB)
        $check = $this->conn->query("SHOW TABLES LIKE 'product_images'");
        if ($check->num_rows == 0) return [];

        $sql = "SELECT id, image_url FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $images = [];
        while ($row = $result->fetch_assoc()) {
            $row['image_url'] = normalizeImageUrl($row['image_url']);
            $images[] = $row;
        }
        $stmt->close();
        return $images;
    }

    /* ==========================================================================
       KHU VỰC 2: ADMIN (QUẢN TRỊ VIÊN) - CÁC HÀM CRUD
       ========================================================================== */

    /**
     * A. Lấy toàn bộ sản phẩm (Cho trang quản lý Admin)
     */
    public function getAllProducts() {
        $sql = "SELECT * FROM products ORDER BY id DESC";
        $result = $this->conn->query($sql);
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    /**
     * B. Thêm sản phẩm mới
     * Bao gồm cả việc thêm ảnh chính và danh sách ảnh phụ
     */
    public function addProduct($name, $category, $price, $mainImg, $extraImgs = []) {
        // Bước 1: Insert thông tin chính vào bảng products
        $sql = "INSERT INTO products (name, category, price, image_url) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssis', $name, $category, $price, $mainImg);
        
        if ($stmt->execute()) {
            // Lấy ID của sản phẩm vừa tạo
            $newId = $stmt->insert_id;
            $stmt->close();

            // Bước 2: Insert ảnh phụ (nếu có) bằng hàm nội bộ
            if (!empty($extraImgs)) {
                $this->addProductExtraImages($newId, $extraImgs);
            }
            return $newId;
        }
        return false;
    }

    /**
     * C. Cập nhật sản phẩm
     */
    public function updateProduct($id, $name, $category, $price, $newImage = null) {
        if ($newImage) {
            // Trường hợp có cập nhật ảnh đại diện mới
            $sql = "UPDATE products SET name = ?, category = ?, price = ?, image_url = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssisi", $name, $category, $price, $newImage, $id);
        } else {
            // Trường hợp giữ nguyên ảnh cũ
            $sql = "UPDATE products SET name = ?, category = ?, price = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssii", $name, $category, $price, $id);
        }
        return $stmt->execute();
    }

    /**
     * D. Xóa sản phẩm
     * Lưu ý: Phải xóa ảnh phụ trước để tránh rác dữ liệu (Data Integrity)
     */
    public function deleteProduct($id) {
        // 1. Xóa ảnh phụ liên quan trong bảng product_images
        $this->conn->query("DELETE FROM product_images WHERE product_id = $id");

        // 2. Xóa sản phẩm chính
        $sql = "DELETE FROM products WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /* ==========================================================================
       KHU VỰC 3: CÁC HÀM HELPER NỘI BỘ (PRIVATE/PUBLIC)
       ========================================================================== */

    /**
     * Helper: Thêm nhiều ảnh phụ vào DB
     */
    public function addProductExtraImages($productId, $extraImgs) {
        if (empty($extraImgs)) return true;

        $sql = "INSERT INTO product_images (product_id, image_url, sort_order) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $order = 1;
        
        foreach ($extraImgs as $img) {
            $stmt->bind_param("isi", $productId, $img, $order);
            $stmt->execute();
            $order++;
        }
        $stmt->close();
        return true;
    }

    /**
     * Helper: Xóa 1 ảnh phụ cụ thể
     */
    public function deleteProductImageById($imageId) {
        $sql = "DELETE FROM product_images WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $imageId);
        return $stmt->execute();
    }
}
?>