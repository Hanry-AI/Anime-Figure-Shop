<?php
/**
 * TRANG QUẢN LÝ SẢN PHẨM (ADMIN DASHBOARD)
 */

session_start();

// Định nghĩa đường dẫn gốc (để trỏ về vendor/autoload)
define('PROJECT_ROOT', dirname(dirname(__DIR__)));

// 1. Load Composer Autoload (Thay thế cho các lệnh require thủ công)
require_once PROJECT_ROOT . '/vendor/autoload.php';

// 2. Sử dụng Namespace chuẩn
use DACS\Config\Database;
use DACS\Models\ProductModel;
use DACS\Helpers\ImageHelper;
use DACS\Helpers\FormatHelper;

// 3. Kiểm tra quyền Admin (Bảo mật)
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /DACS/public/index.php');
    exit;
}

try {
    // 4. Kết nối Database & Khởi tạo Model (Chuẩn OOP)
    $db = new Database();
    $conn = $db->getConnection();
    $productModel = new ProductModel($conn);

    // 5. Lấy danh sách sản phẩm
    $products = $productModel->getAllProducts();

} catch (Exception $e) {
    die("Lỗi hệ thống: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm - Admin</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/DACS/public/assets/css/styles.css">
    
    <style>
        /* CSS Nội bộ cho trang Admin (Giữ nguyên style cũ của bạn) */
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: #f8fafc; color: #0f172a; }
        .admin-container { max-width: 1200px; margin: 100px auto 30px; padding: 0 20px; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .admin-header h1 { font-size: 1.6rem; font-weight: 700; color: #0f172a; margin: 0; }
        
        .btn-add {
            background: #10b981; color: #ffffff; padding: 10px 20px;
            text-decoration: none; border-radius: 8px; font-weight: 600;
            display: inline-flex; align-items: center; gap: 8px; font-size: 0.95rem;
            transition: all 0.2s; box-shadow: 0 2px 5px rgba(16, 185, 129, 0.3);
        }
        .btn-add:hover { background: #059669; transform: translateY(-2px); }

        .product-table {
            width: 100%; border-collapse: separate; border-spacing: 0; 
            background: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); 
            border-radius: 12px; overflow: hidden;
        }
        .product-table th, .product-table td {
            padding: 16px 20px; text-align: left; 
            border-bottom: 1px solid #f1f5f9; vertical-align: middle;
        }
        .product-table th {
            background-color: #f8fafc; color: #64748b; font-weight: 600; 
            text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em;
        }
        .thumb-img {
            width: 50px; height: 50px; object-fit: contain;
            border-radius: 6px; border: 1px solid #e2e8f0; background: #fff; padding: 2px;
        }
        .action-btn {
            border: none; padding: 8px; border-radius: 6px; cursor: pointer;
            font-size: 0.9rem; margin-right: 5px; text-decoration: none; color: white;
            display: inline-flex; align-items: center; justify-content: center;
            width: 32px; height: 32px; transition: 0.2s;
        }
        .btn-edit { background: #f59e0b; }
        .btn-delete { background: #ef4444; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 8px; color: #fff; font-weight: 500; display: flex; align-items: center; gap: 10px; }
        .alert.success { background-color: #10b981; }
        .alert.error { background-color: #ef4444; }
    </style>
</head>
<body>

<?php 
// Kiểm tra file header có tồn tại không trước khi include để tránh lỗi
if(file_exists(__DIR__ . '/../layouts/header.php')) {
    include __DIR__ . '/../layouts/header.php'; 
}
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>📦 Quản Lý Sản Phẩm</h1>
        <a href="add_product.php" class="btn-add">
            <i class="fas fa-plus"></i> Thêm Sản Phẩm
        </a>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert <?= htmlspecialchars($_SESSION['flash_type'] ?? 'success'); ?>">
            <i class="<?= ($_SESSION['flash_type'] == 'success') ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'; ?>"></i>
            <?= htmlspecialchars($_SESSION['flash_message']); ?>
            <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
        </div>
    <?php endif; ?>

    <table class="product-table">
        <thead>
        <tr>
            <th style="width: 50px;">ID</th>
            <th style="width: 80px;">Hình ảnh</th>
            <th>Tên sản phẩm</th>
            <th>Danh mục</th>
            <th>Giá bán</th>
            <th style="width: 120px; text-align: center;">Thao tác</th>
        </tr>
        </thead>
        <tbody>
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $row): ?>
                <tr>
                    <td style="color: #64748b; font-weight: 500;">#<?= (int)$row['id']; ?></td>
                    
                    <td>
                        <img src="<?= htmlspecialchars(ImageHelper::normalizeUrl($row['image_url'])); ?>" 
                             class="thumb-img" alt="Product Image">
                    </td>
                    
                    <td style="font-weight: 600; color: #334155;">
                        <?= htmlspecialchars($row['name']); ?>
                    </td>
                    
                    <td>
                        <span style="background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">
                            <?= htmlspecialchars($row['category']); ?>
                        </span>
                    </td>
                    
                    <td style="font-weight: 700; color: #dc2626;">
                        <?= FormatHelper::formatPrice($row['price']); ?>
                    </td>
                    
                    <td style="text-align: center;">
                        <a href="edit_product.php?id=<?= (int)$row['id']; ?>" class="action-btn btn-edit" title="Sửa">
                            <i class="fas fa-pencil-alt"></i>
                        </a>

                        <form action="delete_product.php" method="POST" style="display:inline;" onsubmit="return confirm('⚠️ Bạn có chắc chắn muốn xóa sản phẩm này không? Hành động này không thể hoàn tác!');">
                            <input type="hidden" name="id" value="<?= (int)$row['id']; ?>">
                            <button type="submit" class="action-btn btn-delete" title="Xóa">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 60px; color: #94a3b8;">
                        <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                        <p>Kho hàng đang trống. Hãy thêm sản phẩm mới!</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>