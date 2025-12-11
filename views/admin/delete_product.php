<?php
session_start();

// 1. Cấu hình & Kết nối
// Định nghĩa PROJECT_ROOT nếu chưa có (để tránh lỗi đường dẫn)
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', dirname(dirname(__DIR__)));
}

require_once PROJECT_ROOT . '/src/Config/db.php';
require_once PROJECT_ROOT . '/src/Models/Product.php';

// Sử dụng Namespace
use DACS\Models\ProductModel;

// 2. Kiểm tra quyền Admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /DACS/public/index.php');
    exit;
}

// 3. Xử lý yêu cầu Xóa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    // Khởi tạo Model
    $productModel = new ProductModel($conn);

    // Gọi hàm xóa từ Model
    // Hàm này trong Model đã bao gồm logic xóa cả ảnh phụ trong bảng product_images
    if ($productModel->deleteProduct($id)) {
        $_SESSION['flash_message'] = "✅ Đã xóa sản phẩm (ID: $id) thành công!";
        $_SESSION['flash_type'] = 'success';
    } else {
        $_SESSION['flash_message'] = "❌ Lỗi: Không thể xóa sản phẩm.";
        $_SESSION['flash_type'] = 'error';
    }
}

// 4. Quay về trang quản lý
header('Location: manage_products.php');
exit;
?>