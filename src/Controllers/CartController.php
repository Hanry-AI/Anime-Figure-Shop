<?php
namespace DACS\Controllers;

use DACS\Models\ProductModel;
use DACS\Models\UserModel; // [QUAN TRỌNG 1] Thêm dòng này để dùng được UserModel
use DACS\Core\Request;
use DACS\Core\View;
use DACS\Helpers\ImageHelper; 

class CartController {
    private $productModel;
    private $userModel; // [QUAN TRỌNG 2] Khai báo biến userModel

    public function __construct($db) {
        // Chỉ khởi tạo những gì cần thiết
        $this->productModel = new ProductModel($db);
        $this->userModel = new UserModel($db); // [QUAN TRỌNG 3] Khởi tạo UserModel
        
        // Đảm bảo giỏ hàng tồn tại (ngắn gọn hơn)
        $_SESSION['cart'] = $_SESSION['cart'] ?? [];
    }

    /**
     * Hàm hỗ trợ (Helper): Lấy chi tiết giỏ hàng
     * Giúp code không bị lặp lại ở index và apiInfo
     */
    private function getCartDetails(): array {
        $cart = $_SESSION['cart'];
        $items = [];
        $totalAmount = 0;

        if (empty($cart)) {
            return ['items' => [], 'total' => 0, 'count' => 0];
        }

        foreach ($cart as $id => $qty) {
            $product = $this->productModel->getProductById($id);
            if ($product) {
                $lineTotal = (float)$product['price'] * (int)$qty;
                $totalAmount += $lineTotal;

                $items[] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'price' => $product['price'], // Giữ giá gốc để tính toán nếu cần
                    'price_formatted' => number_format($product['price'], 0, ',', '.') . '₫',
                    'img' => ImageHelper::normalizeUrl($product['image_url']),
                    'qty' => (int)$qty,
                    'line_total' => $lineTotal,
                    'line_total_formatted' => number_format($lineTotal, 0, ',', '.') . '₫'
                ];
            }
        }

        return [
            'items' => $items,
            'total' => $totalAmount,
            'count' => array_sum($cart)
        ];
    }

    // 1. TRANG GIỎ HÀNG
    public function index() {
        $cartData = $this->getCartDetails();

        View::render('pages/cart', [
            'finalCart' => $cartData['items'],
            'totalAmount' => $cartData['total']
        ]);
    }

    // 2. API INFO
    public function apiInfo() {
        $cartData = $this->getCartDetails();

        $this->jsonResponse([
            'status' => 'success',
            'items' => $cartData['items'],
            'total_formatted' => number_format($cartData['total'], 0, ',', '.') . '₫',
            'count' => $cartData['count']
        ]);
    }

    // 3. THÊM VÀO GIỎ
    public function add(Request $request) {
        $data = $this->getJsonInput();
        $id = (int)($data['id'] ?? 0);
        $qty = (int)($data['quantity'] ?? 1);

        if ($id <= 0) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Sản phẩm lỗi'], 400);
        }

        $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + $qty;
        $this->saveSession();

        $this->jsonResponse([
            'status' => 'success', 
            'message' => 'Đã thêm vào giỏ!',
            'total_count' => array_sum($_SESSION['cart'])
        ]);
    }

    // 4. CẬP NHẬT SỐ LƯỢNG
    public function update(Request $request) {
        $data = $this->getJsonInput();
        $id = (int)($data['id'] ?? 0);
        $qty = (int)($data['quantity'] ?? 1);
        
        if ($id > 0) {
            if ($qty <= 0) {
                unset($_SESSION['cart'][$id]);
            } else {
                $_SESSION['cart'][$id] = $qty;
            }
            $this->saveSession();
        }
        
        $this->jsonResponse(['status' => 'success']);
    }

    // 5. XÓA SẢN PHẨM
    public function delete(Request $request) {
        $data = $this->getJsonInput();
        $id = (int)($data['id'] ?? 0);

        if (isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
            $this->saveSession();
        }

        $this->jsonResponse(['status' => 'success']);
    }

    // --- CÁC HÀM PRIVATE ĐỂ CODE GỌN HƠN ---

    private function getJsonInput() {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    private function saveSession() {
        session_write_close(); // Lưu session ngay lập tức vào DB
        session_start();       // Mở lại session để các lệnh sau vẫn dùng được nếu cần

        if (isset($_SESSION['user_id'])) {
            // Bây giờ dòng này sẽ chạy ngon lành vì userModel đã được khởi tạo
            $this->userModel->updateCart($_SESSION['user_id'], $_SESSION['cart']);
        }
    }

    private function jsonResponse($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}