<?php
namespace DACS\Controllers;

// Sử dụng các Helper đã được Composer nạp
// Không cần require_once db.php thủ công nữa nếu đã có App.php khởi tạo DB
use DACS\Helpers\ImageHelper;
use DACS\Helpers\FormatHelper;

class CartController {
    private $conn;

    /**
     * HÀM KHỞI TẠO (__construct)
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * HÀM HIỂN THỊ GIỎ HÀNG
     */
    public function index() {
        // 1. Lấy dữ liệu giỏ hàng gửi từ trình duyệt
        $cartJson = $_POST['cart'] ?? '[]';
        $items    = json_decode($cartJson, true);

        $finalCart   = [];
        $totalAmount = 0;

        if (is_array($items) && !empty($items)) {
            
            // a. Lọc lấy danh sách ID
            $ids = [];
            foreach ($items as $item) {
                $id = isset($item['id']) ? (int)$item['id'] : 0;
                if ($id > 0) $ids[] = $id;
            }
            $ids = array_unique($ids);

            if (!empty($ids)) {
                // b. Query Database lấy thông tin
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $types = str_repeat('i', count($ids));
                
                $sql = "SELECT id, name, price, image_url FROM products WHERE id IN ($placeholders)";
                
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param($types, ...$ids);
                $stmt->execute();
                $result = $stmt->get_result();

                $dbProducts = [];
                while ($row = $result->fetch_assoc()) {
                    $dbProducts[$row['id']] = $row;
                }
                $stmt->close();

                // c. Tính toán tổng tiền
                foreach ($items as $item) {
                    $id  = (int)($item['id'] ?? 0);
                    $qty = (int)($item['quantity'] ?? 0);
                    
                    if ($id > 0 && $qty > 0 && isset($dbProducts[$id])) {
                        $product = $dbProducts[$id];
                        
                        $lineTotal = $product['price'] * $qty;
                        $totalAmount += $lineTotal;

                        // Thêm vào danh sách hiển thị
                        $finalCart[] = [
                            'id'         => $id,
                            'name'       => $product['name'],
                            // [SỬA LỖI TẠI ĐÂY] Dùng ImageHelper::normalizeUrl
                            'img'        => ImageHelper::normalizeUrl($product['image_url']),
                            'price'      => $product['price'],
                            'qty'        => $qty,
                            'line_total' => $lineTotal,
                        ];
                    }
                }
            }
        }

        // 2. Gọi View hiển thị
        require_once __DIR__ . '/../../views/pages/cart.php';
    }
}
?>