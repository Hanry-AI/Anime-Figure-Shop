<?php
/**
 * TRANG QUẢN LÝ SẢN PHẨM (ADMIN DASHBOARD)
 * ----------------------------------------
 * Nhiệm vụ: Hiển thị danh sách toàn bộ sản phẩm, cung cấp nút Thêm, Sửa, Xóa.
 */

session_start();

// 1. Nhúng các file cấu hình và Model cần thiết
// Sử dụng __DIR__ để đường dẫn luôn chính xác, không phụ thuộc vào vị trí gọi file
require_once __DIR__ . '/../../src/Config/db.php';
require_once __DIR__ . '/../../src/Models/Product.php';     // Nhúng Class ProductModel
require_once __DIR__ . '/../../src/Helpers/image_helper.php'; // Nhúng hàm xử lý ảnh

// Sử dụng namespace của ProductModel (Vì file Model đã có namespace DACS\Models)
use DACS\Models\ProductModel;

// 2. Kiểm tra quyền Admin (BẢO MẬT)
// Logic: Nếu chưa đăng nhập HOẶC vai trò không phải 'admin' -> Chặn truy cập.
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // Chuyển hướng về trang chủ
    header('Location: /DACS/public/index.php');
    exit; // Lệnh này rất quan trọng: Dừng chạy code ngay lập tức để hacker không xem được nội dung bên dưới
}

// 3. Lấy dữ liệu từ Database (theo chuẩn OOP)
// Thay vì gọi hàm lẻ tẻ getAllProducts($conn), ta làm như sau:

// BƯỚC A: Khởi tạo đối tượng Model và truyền kết nối DB vào (Dependency Injection)
$productModel = new ProductModel($conn);

// BƯỚC B: Gọi phương thức getAllProducts() từ đối tượng vừa tạo
$products = $productModel->getAllProducts();

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm - Admin</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="../partials/header.css">
    
    <style>
        /* CSS Nội bộ cho trang Admin */
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: #f8fafc; color: #0f172a; }
        
        .admin-container { 
            max-width: 1200px; 
            margin: 130px auto 30px; /* Cách top 130px để tránh đè header */
            padding: 0 20px; 
        }

        .admin-header {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;
        }
        .admin-header h1 { font-size: 1.6rem; font-weight: 700; color: #0f172a; margin: 0; }

        /* Button Thêm mới */
        .btn-add {
            background: #10b981; color: #ffffff; padding: 10px 20px;
            text-decoration: none; border-radius: 8px; font-weight: 600;
            display: inline-flex; align-items: center; gap: 8px; font-size: 0.95rem;
            transition: all 0.2s; box-shadow: 0 2px 5px rgba(16, 185, 129, 0.3);
        }
        .btn-add:hover { background: #059669; transform: translateY(-2px); }

        /* Bảng dữ liệu */
        .product-table {
            width: 100%; border-collapse: separate; border-spacing: 0; 
            background: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); 
            border-radius: 12px; overflow: hidden;
        }
        
        .product-table th, .product-table td {
            padding: 16px 20px; text-align: left; 
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        
        .product-table th {
            background-color: #f8fafc; 
            color: #64748b; 
            font-weight: 600; 
            text-transform: uppercase; 
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }
        
        /* Hiệu ứng hover dòng */
        .product-table tr:hover { background-color: #f1f5f9; }
        .product-table tr:last-child td { border-bottom: none; }

        /* Ảnh thumbnail */
        .thumb-img {
            width: 50px; height: 50px; object-fit: contain;
            border-radius: 6px; border: 1px solid #e2e8f0;
            background: #fff; padding: 2px;
        }

        /* Nút hành động (Sửa/Xóa) */
        .action-btn {
            border: none; padding: 8px; border-radius: 6px;
            cursor: pointer; font-size: 0.9rem; margin-right: 5px; 
            text-decoration: none; color: white;
            display: inline-flex; align-items: center; justify-content: center;
            width: 32px; height: 32px; transition: 0.2s;
        }
        .btn-edit { background: #f59e0b; box-shadow: 0 2px 4px rgba(245, 158, 11, 0.2); }
        .btn-edit:hover { background: #d97706; transform: translateY(-2px); }
        
        .btn-delete { background: #ef4444; box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2); }
        .btn-delete:hover { background: #dc2626; transform: translateY(-2px); }

        /* Thông báo Alert */
        .alert {
            padding: 15px; margin-bottom: 20px; border-radius: 8px; 
            color: #ffffff; font-size: 0.95rem; font-weight: 500;
            display: flex; align-items: center; gap: 10px;
            animation: slideIn 0.3s ease;
        }
        .alert.success { background-color: #10b981; }
        .alert.error   { background-color: #ef4444; }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="admin-container">
    <div class="admin-header">
        <h1>📦 Quản Lý Sản Phẩm</h1>
        <a href="add_product.php" class="btn-add">
            <i class="fas fa-plus"></i> Thêm Sản Phẩm
        </a>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert <?= htmlspecialchars($_SESSION['flash_type'] ?? 'success'); ?>">
            <?php if(($_SESSION['flash_type'] ?? '') == 'success'): ?>
                <i class="fas fa-check-circle"></i>
            <?php else: ?>
                <i class="fas fa-exclamation-circle"></i>
            <?php endif; ?>
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
                        <?php
                        // Xử lý đường dẫn ảnh bằng hàm helper để tránh lỗi ảnh chết
                        $imgUrl = !empty($row['image_url']) 
                            ? normalizeImageUrl($row['image_url']) 
                            : '/DACS/public/assets/img/no-image.jpg';
                        ?>
                        <img src="<?= htmlspecialchars($imgUrl); ?>" class="thumb-img" alt="Product Image">
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
                        <?= number_format((float)$row['price'], 0, ',', '.'); ?>đ
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