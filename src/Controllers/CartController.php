<?php
namespace DACS\Controllers;

require_once __DIR__ . '/../Config/db.php';
require_once __DIR__ . '/../Helpers/image_helper.php';
require_once __DIR__ . '/../Helpers/format_helper.php'; // Nạp helper định dạng tiền

class CartController {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function index() {
        // Lấy dữ liệu cart từ POST (do JS gửi lên)
        $cartJson = $_POST['cart'] ?? '[]';
        $items    = json_decode($cartJson, true);

        // Biến để lưu kết quả cuối cùng
        $finalCart   = [];
        $totalAmount = 0;

        // Nếu có items thì mới query DB
        if (is_array($items) && !empty($items)) {
            // Lọc ID sản phẩm
            $ids = [];
            foreach ($items as $item) {
                $id = isset($item['id']) ? (int)$item['id'] : 0;
                if ($id > 0) $ids[] = $id;
            }
            $ids = array_unique($ids);

            if (!empty($ids)) {
                // Query DB lấy thông tin sản phẩm
                $types        = str_repeat('i', count($ids));
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $sql          = "SELECT id, name, price, image_url FROM products WHERE id IN ($placeholders)";
                
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param($types, ...$ids);
                $stmt->execute();
                $result = $stmt->get_result();

                $dbProducts = [];
                while ($row = $result->fetch_assoc()) {
                    $dbProducts[$row['id']] = $row;
                }
                $stmt->close();

                // Tính toán tổng tiền và tạo danh sách hiển thị
                foreach ($items as $item) {
                    $id  = (int)($item['id'] ?? 0);
                    $qty = (int)($item['quantity'] ?? 0);
                    
                    if ($id > 0 && $qty > 0 && isset($dbProducts[$id])) {
                        $product     = $dbProducts[$id];
                        $lineTotal   = $product['price'] * $qty;
                        $totalAmount += $lineTotal;

                        $finalCart[] = [
                            'id'         => $id,
                            'name'       => $product['name'],
                            'img'        => normalizeImageUrl($product['image_url']),
                            'price'      => $product['price'],
                            'qty'        => $qty,
                            'line_total' => $lineTotal,
                        ];
                    }
                }
            }
        }

        // Gọi View hiển thị (View chỉ việc hiển thị $finalCart và $totalAmount)
        require_once __DIR__ . '/../../views/pages/cart.php';
    }
}