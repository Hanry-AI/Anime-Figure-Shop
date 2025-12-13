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
        
        // Khởi động session nếu chưa có
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Đảm bảo biến cart luôn là mảng
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    /**
     * 1. TRANG GIỎ HÀNG (View)
     * Hiển thị trang cart.php đầy đủ
     */
    public function index() {
        $cart = $_SESSION['cart'];
        $finalCart = [];
        $totalAmount = 0;

        if (!empty($cart)) {
            $ids = array_keys($cart);
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

    /**
     * 2. API INFO (Cho Sidebar Giỏ hàng)
     * Trả về JSON để Javascript vẽ lại sidebar mà không cần reload trang
     */
    public function apiInfo() {
        $cart = $_SESSION['cart'];
        $items = [];
        $totalAmount = 0;

        if (!empty($cart)) {
            $ids = array_keys($cart);
            foreach ($ids as $id) {
                $product = $this->productModel->getProductById($id);
                if ($product) {
                    $qty = (int)$cart[$id];
                    $lineTotal = (float)$product['price'] * $qty;
                    $totalAmount += $lineTotal;

                    $items[] = [
                        'id' => $product['id'],
                        'name' => $product['name'],
                        'img' => ImageHelper::normalizeUrl($product['image_url']),
                        'price_formatted' => number_format($product['price'], 0, ',', '.') . '₫',
                        'quantity' => $qty,
                        'line_total' => $lineTotal
                    ];
                }
            }
        }

        // Trả về JSON
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'items' => $items,
            'total_formatted' => number_format($totalAmount, 0, ',', '.') . '₫',
            'count' => array_sum($cart)
        ]);
        exit;
    }

    /**
     * 3. THÊM VÀO GIỎ (AJAX)
     */
    public function add(Request $request) {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = isset($data['id']) ? (int)$data['id'] : 0;
        $qty = isset($data['quantity']) ? (int)$data['quantity'] : 1;

        if ($id > 0) {
            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id] += $qty;
            } else {
                $_SESSION['cart'][$id] = $qty;
            }
            
            // [QUAN TRỌNG] Lưu session ngay lập tức
            session_write_close();

            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success', 
                'message' => 'Đã thêm vào giỏ!',
                'total_count' => array_sum($_SESSION['cart']) // Đếm lại từ session mới nhất
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Sản phẩm không hợp lệ']);
        }
        exit;
    }

    /**
     * 4. CẬP NHẬT SỐ LƯỢNG (AJAX +/-)
     */
    public function update(Request $request) {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)($data['id'] ?? 0);
        $qty = (int)($data['quantity'] ?? 1);
        
        if ($id > 0) {
            if ($qty <= 0) {
                unset($_SESSION['cart'][$id]); // Nếu số lượng <= 0 thì xóa luôn
            } else {
                $_SESSION['cart'][$id] = $qty;
            }
            
            // [QUAN TRỌNG] Lưu session ngay lập tức
            session_write_close();
        }
        
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success']);
        exit;
    }

    /**
     * 5. XÓA SẢN PHẨM (AJAX)
     */
    public function delete(Request $request) {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)($data['id'] ?? 0);

        if (isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
            
            // [QUAN TRỌNG] Lưu session ngay lập tức
            session_write_close();
        }

        header('Content-Type: application/json');
        echo json_encode(['status' => 'success']);
        exit;
    }
}
?>