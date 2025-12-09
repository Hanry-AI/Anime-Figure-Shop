<?php
session_start();
require_once __DIR__ . '/../../src/Models/Product.php';

// Thay thế đoạn SQL cũ bằng hàm này
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
        body { font-family: sans-serif; background-color: #f8fafc; }
        .admin-container { max-width: 1200px; margin: 130px auto 30px; padding: 0 20px; }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .admin-header h1 {
            font-size: 1.6rem;
            font-weight: 700;
            color: #0f172a;
        }

        /* Nút thêm sản phẩm */
        .btn-add {
            background: #10b981;
            color: #ffffff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
        }
        .btn-add i { font-size: 0.9rem; }
        .btn-add:hover { background: #059669; }

        /* Table Styles */
        .product-table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        .product-table th,
        .product-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .product-table th {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
        }
        .product-table tr:hover { background-color: #f8fafc; }

        .thumb-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .action-btn {
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            margin-right: 5px;
        }
        .btn-delete { background: #ef4444; color: #ffffff; }
        .btn-delete:hover { background: #dc2626; }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: #ffffff;
            font-size: 0.9rem;
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

        <!-- NÚT THÊM SẢN PHẨM MỚI -->
        <a href="add_product.php" class="btn-add">
            <i class="fas fa-plus"></i>
            Thêm Sản Phẩm Mới
        </a>

    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert <?= htmlspecialchars($_SESSION['flash_type'] ?? 'success', ENT_QUOTES, 'UTF-8'); ?>">
            <?= htmlspecialchars($_SESSION['flash_message'], ENT_QUOTES, 'UTF-8'); ?>
            <?php
            unset($_SESSION['flash_message'], $_SESSION['flash_type']);
            ?>
        </div>
    <?php endif; ?>

    <table class="product-table">
        <thead>
        <tr>
            <th style="width: 50px;">ID</th>
            <th style="width: 100px;">Hình ảnh</th>
            <th>Tên sản phẩm</th>
            <th>Danh mục</th>
            <th>Giá</th>
            <th style="width: 150px;">Hành động</th>
        </tr>
        </thead>
        <tbody>
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $row): ?>
                <tr>
                    <td>#<?= (int)$row['id']; ?></td>
                    <td>
                        <?php
                        $imgUrl = !empty($row['image_url'])
                            ? normalizeImageUrl($row['image_url'])
                            : 'https://via.placeholder.com/60';
                        ?>
                        <img src="<?= htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8'); ?>"
                             class="thumb-img" alt="Img">
                    </td>
                    <td style="font-weight: 500; color: #334155;">
                        <?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?>
                    </td>
                    <td>
                        <span style="
                            background: #e0f2fe;
                            color: #0369a1;
                            padding: 2px 8px;
                            border-radius: 10px;
                            font-size: 12px;">
                            <?= htmlspecialchars($row['category'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    </td>
                    <td style="font-weight: bold; color: #dc2626;">
                        <?= number_format((float)$row['price'], 0, ',', '.'); ?>đ
                    </td>
                    <td>
                        <a href="edit_product.php?id=<?= (int)$row['id']; ?>" 
                            class="action-btn" 
                            style="background: #eab308; color: white; text-decoration: none; display: inline-block;">
                            <i class="fas fa-edit"></i> Sửa
                        </a>

                        <form action="delete_product.php" method="POST"
                              onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này không?');"
                              style="display:inline;">
                            <input type="hidden" name="id" value="<?= (int)$row['id']; ?>">
                            <button type="submit" class="action-btn btn-delete">
                                <i class="fas fa-trash-alt"></i> Xóa
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" style="text-align: center; padding: 30px; color: #64748b;">
                    Chưa có sản phẩm nào. Hãy thêm mới!
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
