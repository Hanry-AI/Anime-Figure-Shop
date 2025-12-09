<?php
session_start();

// 1. Load file cấu hình và Model
// Dùng __DIR__ để đường dẫn luôn đúng tuyệt đối
require_once __DIR__ . '/../../src/Config/db.php';
require_once __DIR__ . '/../../src/Models/Product.php';
require_once __DIR__ . '/../../src/Helpers/image_helper.php';

// 2. Kiểm tra quyền Admin (BẮT BUỘC PHẢI CÓ)
// Nếu chưa đăng nhập HOẶC không phải admin -> Đuổi về trang chủ ngay
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /DACS/public/index.php');
    exit; // Dừng code ngay lập tức
}

// 3. Lấy dữ liệu
// Biến $conn được tạo ra từ file db.php (đã require ở trên)
$products = getAllProducts($conn); 
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
        body { font-family: sans-serif; background-color: #f8fafc; color: #0f172a; }
        .admin-container { max-width: 1200px; margin: 130px auto 30px; padding: 0 20px; }

        .admin-header {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;
        }
        .admin-header h1 { font-size: 1.6rem; font-weight: 700; color: #0f172a; }

        /* Button Styles */
        .btn-add {
            background: #10b981; color: #ffffff; padding: 10px 20px;
            text-decoration: none; border-radius: 5px; font-weight: bold;
            display: inline-flex; align-items: center; gap: 8px; font-size: 0.95rem;
            transition: background 0.2s;
        }
        .btn-add:hover { background: #059669; }

        /* Table Styles */
        .product-table {
            width: 100%; border-collapse: collapse; background: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden;
        }
        .product-table th, .product-table td {
            padding: 12px 15px; text-align: left; border-bottom: 1px solid #e2e8f0;
        }
        .product-table th {
            background-color: #f1f5f9; color: #475569; font-weight: 600; text-transform: uppercase; font-size: 0.85rem;
        }
        .product-table tr:hover { background-color: #f8fafc; }

        .thumb-img {
            width: 60px; height: 60px; object-fit: cover;
            border-radius: 4px; border: 1px solid #ddd;
        }

        .action-btn {
            border: none; padding: 6px 12px; border-radius: 4px;
            cursor: pointer; font-size: 0.9rem; margin-right: 5px; text-decoration: none; color: white;
            display: inline-block;
        }
        .btn-edit { background: #eab308; }
        .btn-edit:hover { background: #ca8a04; }
        .btn-delete { background: #ef4444; }
        .btn-delete:hover { background: #dc2626; }

        .alert {
            padding: 15px; margin-bottom: 20px; border-radius: 4px; color: #ffffff; font-size: 0.9rem;
        }
        .alert.success { background-color: #10b981; }
        .alert.error   { background-color: #ef4444; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="admin-container">
    <div class="admin-header">
        <h1>📦 Quản Lý Sản Phẩm</h1>
        <a href="add_product.php" class="btn-add">
            <i class="fas fa-plus"></i> Thêm Sản Phẩm Mới
        </a>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert <?= htmlspecialchars($_SESSION['flash_type'] ?? 'success'); ?>">
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
            <th>Giá</th>
            <th style="width: 160px;">Hành động</th>
        </tr>
        </thead>
        <tbody>
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $row): ?>
                <tr>
                    <td>#<?= (int)$row['id']; ?></td>
                    <td>
                        <?php
                        // Xử lý đường dẫn ảnh an toàn
                        $imgUrl = !empty($row['image_url']) 
                            ? normalizeImageUrl($row['image_url']) 
                            : '/DACS/public/assets/img/no-image.jpg';
                        ?>
                        <img src="<?= htmlspecialchars($imgUrl); ?>" class="thumb-img" alt="Product Image">
                    </td>
                    <td style="font-weight: 500;">
                        <?= htmlspecialchars($row['name']); ?>
                    </td>
                    <td>
                        <span style="background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 10px; font-size: 12px; font-weight: 600;">
                            <?= htmlspecialchars(ucfirst($row['category'])); ?>
                        </span>
                    </td>
                    <td style="font-weight: bold; color: #dc2626;">
                        <?= number_format((float)$row['price'], 0, ',', '.'); ?>đ
                    </td>
                    <td>
                        <a href="edit_product.php?id=<?= (int)$row['id']; ?>" class="action-btn btn-edit">
                            <i class="fas fa-edit"></i>
                        </a>

                        <form action="delete_product.php" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa không?');">
                            <input type="hidden" name="id" value="<?= (int)$row['id']; ?>">
                            <button type="submit" class="action-btn btn-delete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" style="text-align: center; padding: 40px; color: #64748b;">
                    <i class="fas fa-box-open" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                    Chưa có sản phẩm nào.
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>