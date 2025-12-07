<?php
session_start();
// 1. Cấu hình & Kết nối CSDL
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', __DIR__ . '/../..');
}
require_once PROJECT_ROOT . '/src/Config/db.php';

// Chỉ xử lý khi có Request POST và có ID gửi lên
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    // Chuẩn bị câu lệnh xóa
    // Lưu ý: Nếu bạn có thiết lập khóa ngoại (Foreign Key) với bảng product_images,
    // bạn cần xóa ảnh trong bảng product_images trước hoặc thiết lập ON DELETE CASCADE trong MySQL.
    
    // Ở đây mình xóa bảng products (Logic đơn giản nhất)
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $_SESSION['flash_message'] = "Đã xóa sản phẩm (ID: $id) thành công!";
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = "Lỗi xóa: " . $stmt->error;
            $_SESSION['flash_type'] = 'error';
        }
        $stmt->close();
    }
}

// Quay về trang quản lý
header('Location: manage_products.php');
exit;
?>