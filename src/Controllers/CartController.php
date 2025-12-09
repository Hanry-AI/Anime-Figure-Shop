<?php
namespace DACS\Controllers;

// Nhúng các file cấu hình và helper (hỗ trợ xử lý ảnh, định dạng tiền)
require_once __DIR__ . '/../Config/db.php';
require_once __DIR__ . '/../Helpers/image_helper.php';
require_once __DIR__ . '/../Helpers/format_helper.php';

class CartController {
    private $conn;

    /**
     * HÀM KHỞI TẠO (__construct)
     * - Nhận kết nối DB từ index.php truyền vào (Dependency Injection).
     * - Thay thế hoàn toàn cho "global $conn".
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * HÀM HIỂN THỊ GIỎ HÀNG
     * Logic: Lấy danh sách ID sản phẩm từ Javascript gửi lên -> Query Database lấy giá tiền -> Tính tổng.
     */
    public function index() {
        // 1. Lấy dữ liệu giỏ hàng gửi từ trình duyệt (Client)
        // Dữ liệu này thường được gửi qua form ẩn hoặc Ajax dưới dạng chuỗi JSON
        $cartJson = $_POST['cart'] ?? '[]';
        $items    = json_decode($cartJson, true); // Chuyển chuỗi JSON thành mảng PHP để xử lý

        // Biến lưu kết quả cuối cùng để đưa ra View
        $finalCart   = [];
        $totalAmount = 0; // Tổng tiền hóa đơn

        // Kiểm tra: Nếu có sản phẩm trong giỏ hàng thì mới xử lý
        if (is_array($items) && !empty($items)) {
            
            // a. Lọc lấy danh sách ID các sản phẩm
            $ids = [];
            foreach ($items as $item) {
                // Ép kiểu int để an toàn
                $id = isset($item['id']) ? (int)$item['id'] : 0;
                if ($id > 0) $ids[] = $id;
            }
            $ids = array_unique($ids); // Loại bỏ các ID trùng nhau (nếu có)

            if (!empty($ids)) {
                // b. Query Database để lấy thông tin sản phẩm (Tên, Giá, Ảnh)
                // [LƯU Ý]: Phải lấy giá từ Database, KHÔNG ĐƯỢC tin giá từ Client gửi lên (tránh hack sửa giá)
                
                // Tạo chuỗi dấu hỏi chấm cho câu lệnh IN (?,?,?) tương ứng số lượng ID
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                
                // Tạo chuỗi kiểu dữ liệu cho bind_param (ví dụ: "iii" nếu có 3 ID)
                $types = str_repeat('i', count($ids));
                
                $sql = "SELECT id, name, price, image_url FROM products WHERE id IN ($placeholders)";
                
                $stmt = $this->conn->prepare($sql);
                // Dùng toán tử ... (spread operator) để rải mảng $ids vào làm tham số
                $stmt->bind_param($types, ...$ids);
                $stmt->execute();
                $result = $stmt->get_result();

                // Lưu kết quả DB vào một mảng tạm (Key là ID sản phẩm) để dễ tra cứu
                $dbProducts = [];
                while ($row = $result->fetch_assoc()) {
                    $dbProducts[$row['id']] = $row;
                }
                $stmt->close();

                // c. Tính toán tổng tiền
                foreach ($items as $item) {
                    $id  = (int)($item['id'] ?? 0);
                    $qty = (int)($item['quantity'] ?? 0); // Số lượng khách mua
                    
                    // Chỉ tính nếu sản phẩm thực sự tồn tại trong DB
                    if ($id > 0 && $qty > 0 && isset($dbProducts[$id])) {
                        $product = $dbProducts[$id];
                        
                        // Thành tiền = Giá gốc (từ DB) * Số lượng
                        $lineTotal = $product['price'] * $qty;
                        $totalAmount += $lineTotal;

                        // Thêm vào danh sách hiển thị
                        $finalCart[] = [
                            'id'         => $id,
                            'name'       => $product['name'],
                            'img'        => normalizeImageUrl($product['image_url']), // Xử lý link ảnh
                            'price'      => $product['price'],
                            'qty'        => $qty,
                            'line_total' => $lineTotal,
                        ];
                    }
                }
            }
        }

        // 2. Gọi View hiển thị
        // Truyền biến $finalCart (danh sách hàng) và $totalAmount (tổng tiền) sang view
        require_once __DIR__ . '/../../views/pages/cart.php';
    }
}
?>