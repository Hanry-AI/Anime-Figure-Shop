<?php
namespace DACS\Controllers;

use DACS\Models\ProductModel;
use DACS\Core\Request;
use DACS\Core\View;
use DACS\Helpers\ImageHelper; 
use DACS\Helpers\FormatHelper;

class CartController {
    private $conn;
    private $productModel;

    public function __construct($db) {
        $this->conn = $db;
        $this->productModel = new ProductModel($db);
        
        // Luôn đảm bảo session giỏ hàng tồn tại
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    }

    // 1. Xem giỏ hàng
    public function index() {
        $cart = $_SESSION['cart'];
        $finalCart = [];
        $totalAmount = 0;

        if (!empty($cart)) {
            // Lấy danh sách ID sản phẩm
            $ids = array_keys($cart);
            
            // Lấy thông tin chi tiết từ Database để đảm bảo giá đúng
            // (Lưu ý: Bạn nên viết hàm getProductsByIds trong Model để tối ưu hơn, 
            // ở đây mình dùng vòng lặp tạm thời cho dễ hiểu)
            foreach ($ids as $id) {
                $product = $this->productModel->getProductById($id);
                if ($product) {
                    $qty = (int)$cart[$id];
                    $lineTotal = (float)$product['price'] * $qty;
                    $totalAmount += $lineTotal;

                    $finalCart[] = [
                        'id' => $product['id'],
                        'name' => $product['name'],
                        'price' => $product['price'],
                        'img' => ImageHelper::normalizeUrl($product['image_url']),
                        'qty' => $qty,
                        'line_total' => $lineTotal
                    ];
                }
            }
        }

        View::render('pages/cart', [
            'finalCart' => $finalCart,
            'totalAmount' => $totalAmount
        ]);
    }

    public function apiInfo() {
        $cart = $_SESSION['cart'];
        $items = [];
        $totalAmount = 0;

        if (!empty($cart)) {
            $ids = array_keys($cart);
            // Lấy thông tin sản phẩm từ DB
            // (Lưu ý: Nếu Model chưa có getProductsByIds, ta dùng vòng lặp tạm)
            foreach ($ids as $id) {
                $product = $this->productModel->getProductById($id);
                if ($product) {
                    $qty = (int)$cart[$id];
                    $lineTotal = (float)$product['price'] * $qty;
                    $totalAmount += $lineTotal;

                    $items[] = [
                        'id' => $product['id'],
                        'name' => $product['name'],
                        'price_raw' => $product['price'],
                        // Format sẵn giá tiền để JS chỉ việc hiện
                        'price_formatted' => number_format($product['price'], 0, ',', '.') . '₫',
                        'img' => ImageHelper::normalizeUrl($product['image_url']),
                        'quantity' => $qty,
                        'line_total' => $lineTotal
                    ];
                }
            }
        }

        echo json_encode([
            'status' => 'success',
            'items' => $items,
            'total_amount' => $totalAmount,
            'total_formatted' => number_format($totalAmount, 0, ',', '.') . '₫',
            'count' => array_sum($cart)
        ]);
        exit;
    }

    // 2. Thêm vào giỏ (API)
    public function add(Request $request) {
        // Nhận dữ liệu JSON từ JS
        $data = json_decode(file_get_contents('php://input'), true);
        
        $id = isset($data['id']) ? (int)$data['id'] : 0;
        $qty = isset($data['quantity']) ? (int)$data['quantity'] : 1;

        if ($id > 0) {
            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id] += $qty;
            } else {
                $_SESSION['cart'][$id] = $qty;
            }
            
            // Tính tổng số lượng để cập nhật icon giỏ hàng
            $totalItems = array_sum($_SESSION['cart']);

            echo json_encode([
                'status' => 'success', 
                'message' => 'Đã thêm vào giỏ!',
                'total_count' => $totalItems
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Sản phẩm không hợp lệ']);
        }
        exit;
    }

    // 3. Xóa sản phẩm (API)
    public function delete(Request $request) {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)($data['id'] ?? 0);

        if (isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
        }

        echo json_encode(['status' => 'success']);
        exit;
    }
    
    // 4. Cập nhật số lượng (API - Dành cho tính năng +/- sau này)
    public function update(Request $request) {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)($data['id'] ?? 0);
        $qty = (int)($data['quantity'] ?? 1);
        
        if ($id > 0) {
            if ($qty <= 0) {
                unset($_SESSION['cart'][$id]);
            } else {
                $_SESSION['cart'][$id] = $qty;
            }
        }
        echo json_encode(['status' => 'success']);
        exit;
    }
}
?>