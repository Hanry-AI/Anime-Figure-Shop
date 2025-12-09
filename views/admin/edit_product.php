<?php
session_start();

// 1. Cấu hình & Kết nối
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', __DIR__ . '/../..');
}
require_once PROJECT_ROOT . '/src/Config/db.php';
require_once PROJECT_ROOT . '/src/Models/Product.php';
require_once PROJECT_ROOT . '/src/Helpers/image_helper.php';

// Định nghĩa thư mục upload
if (!defined('UPLOAD_DIR')) define('UPLOAD_DIR', PROJECT_ROOT . '/public/assets/img/');
if (!defined('DB_IMG_PATH')) define('DB_IMG_PATH', '/DACS/public/assets/img/');

// Hàm upload file (Helper nội bộ)
function processUpload($fileInput) {
    if (!isset($fileInput['name']) || $fileInput['error'] !== UPLOAD_ERR_OK) return null;
    
    $ext = pathinfo($fileInput['name'], PATHINFO_EXTENSION);
    $filename = time() . '_' . uniqid() . '.' . $ext; // Tên file ngẫu nhiên tránh trùng
    
    if (move_uploaded_file($fileInput['tmp_name'], UPLOAD_DIR . $filename)) {
        return DB_IMG_PATH . $filename;
    }
    return null;
}

// Lấy ID sản phẩm
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = getProductById($conn, $id);

// Nếu không tìm thấy sản phẩm -> Về trang quản lý
if (!$product) {
    header('Location: manage_products.php');
    exit;
}

$error = '';
$success = '';

// ==========================================
// XỬ LÝ POST (Cả xóa ảnh và cập nhật)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- TRƯỜNG HỢP 1: XÓA ẢNH PHỤ ---
    if (isset($_POST['delete_image_id'])) {
        $imgId = (int)$_POST['delete_image_id'];
        
        // Gọi hàm xóa trong Model
        if (deleteProductImageById($conn, $imgId)) {
            // Redirect lại chính trang này để refresh và tránh form resubmission
            header("Location: edit_product.php?id=$id&msg=deleted");
            exit;
        } else {
            $error = "Không thể xóa ảnh. Vui lòng thử lại.";
        }
    }
    
    // --- TRƯỜNG HỢP 2: CẬP NHẬT SẢN PHẨM (Nút Lưu) ---
    else {
        $name = trim($_POST['name'] ?? '');
        $category = $_POST['category'] ?? 'anime';
        $price = (int)preg_replace('/[^\d]/', '', $_POST['price']); // Chỉ lấy số

        if ($name === '') {
            $error = "Tên sản phẩm không được để trống.";
        } else {
            // 1. Xử lý Ảnh đại diện (Main Image)
            $mainImgPath = null;
            if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
                $mainImgPath = processUpload($_FILES['main_image']);
            }

            // Gọi Model cập nhật thông tin chính
            if (updateProduct($conn, $id, $name, $category, $price, $mainImgPath)) {
                
                // 2. Xử lý thêm ảnh phụ (Extra Images)
                if (isset($_FILES['new_extra_images'])) {
                    $newExtraUrls = [];
                    $count = count($_FILES['new_extra_images']['name']);
                    
                    for ($i = 0; $i < $count; $i++) {
                        $singleFile = [
                            'name'     => $_FILES['new_extra_images']['name'][$i],
                            'type'     => $_FILES['new_extra_images']['type'][$i],
                            'tmp_name' => $_FILES['new_extra_images']['tmp_name'][$i],
                            'error'    => $_FILES['new_extra_images']['error'][$i],
                            'size'     => $_FILES['new_extra_images']['size'][$i]
                        ];
                        
                        $url = processUpload($singleFile);
                        if ($url) {
                            $newExtraUrls[] = $url;
                        }
                    }
                    
                    // Gọi Model thêm ảnh phụ
                    addProductExtraImages($conn, $id, $newExtraUrls);
                }

                $success = "Cập nhật sản phẩm thành công!";
                // Refresh lại dữ liệu mới nhất từ DB
                $product = getProductById($conn, $id);
            } else {
                $error = "Lỗi SQL: " . $conn->error;
            }
        }
    }
}

// Kiểm tra thông báo từ URL (sau khi redirect xóa ảnh)
if (isset($_GET['msg']) && $_GET['msg'] == 'deleted') {
    $success = "Đã xóa ảnh thành công!";
}

// Lấy danh sách ảnh phụ (để hiển thị)
$extraImages = getProductImages($conn, $id);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa: <?= htmlspecialchars($product['name']) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: system-ui, sans-serif; background: #f3f4f6; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #111827; border-bottom: 1px solid #e5e7eb; padding-bottom: 15px; }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #374151; }
        input[type="text"], select { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box; }
        input:focus, select:focus { outline: 2px solid #2563eb; border-color: transparent; }

        /* Gallery Styles */
        .gallery-grid { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 10px; }
        .gallery-item { position: relative; width: 100px; height: 100px; border-radius: 6px; overflow: hidden; border: 1px solid #e5e7eb; }
        .gallery-item img { width: 100%; height: 100%; object-fit: cover; }
        
        /* Nút xóa ảnh (Tuyệt chiêu: Nút submit nhưng style thành icon) */
        .btn-delete-img {
            position: absolute; top: 4px; right: 4px;
            background: rgba(239, 68, 68, 0.9); color: white;
            border: none; width: 24px; height: 24px; border-radius: 50%;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            font-size: 12px; transition: 0.2s;
        }
        .btn-delete-img:hover { background: #dc2626; transform: scale(1.1); }

        .btn-save { background: #2563eb; color: #fff; padding: 12px 24px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; transition: 0.2s; }
        .btn-save:hover { background: #1d4ed8; }
        .btn-back { display: inline-block; margin-left: 15px; color: #6b7280; text-decoration: none; }
        .btn-back:hover { color: #111827; }

        .alert { padding: 12px; margin-bottom: 20px; border-radius: 6px; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        
        .preview-main { width: 80px; height: 80px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; }
    </style>
</head>
<body>

<div class="container">
    <h2><i class="fas fa-edit"></i> Chỉnh sửa sản phẩm</h2>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        
        <div class="form-group">
            <label>Tên sản phẩm</label>
            <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label>Danh mục</label>
                <select name="category">
                    <option value="anime" <?= $product['category'] == 'anime' ? 'selected' : '' ?>>Anime Figure</option>
                    <option value="gundam" <?= $product['category'] == 'gundam' ? 'selected' : '' ?>>Gundam</option>
                    <option value="marvel" <?= $product['category'] == 'marvel' ? 'selected' : '' ?>>Marvel</option>
                </select>
            </div>

            <div class="form-group">
                <label>Giá (VNĐ)</label>
                <input type="text" name="price" value="<?= number_format($product['price'], 0, '', '') ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label>Ảnh đại diện (Main Image)</label>
            <div style="display: flex; gap: 15px; align-items: center;">
                <img src="<?= htmlspecialchars($product['image_url']) ?>" class="preview-main">
                <div>
                    <input type="file" name="main_image" accept="image/*">
                    <div style="font-size: 0.85em; color: #666; margin-top: 4px;">Chỉ chọn nếu muốn thay ảnh mới.</div>
                </div>
            </div>
        </div>

        <hr style="margin: 30px 0; border: 0; border-top: 1px dashed #e5e7eb;">

        <div class="form-group">
            <label>Bộ sưu tập ảnh phụ (Gallery)</label>
            
            <div class="gallery-grid">
                <?php if (!empty($extraImages)): ?>
                    <?php foreach ($extraImages as $img): ?>
                        <div class="gallery-item">
                            <img src="<?= htmlspecialchars($img['image_url']) ?>">
                            
                            <button type="submit" 
                                    name="delete_image_id" 
                                    value="<?= $img['id'] ?>"
                                    class="btn-delete-img"
                                    title="Xóa ảnh này"
                                    onclick="return confirm('Bạn chắc chắn muốn xóa ảnh này?');">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="font-size: 0.9em; color: #9ca3af; font-style: italic;">Chưa có ảnh phụ nào.</p>
                <?php endif; ?>
            </div>

            <div style="margin-top: 20px; background: #f9fafb; padding: 15px; border-radius: 6px; border: 1px dashed #d1d5db;">
                <label style="margin-bottom: 5px; color: #2563eb;"><i class="fas fa-plus"></i> Thêm ảnh phụ mới (Chọn nhiều)</label>
                <input type="file" name="new_extra_images[]" multiple accept="image/*">
            </div>
        </div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
            <button type="submit" class="btn-save">
                <i class="fas fa-save"></i> Lưu Thay Đổi
            </button>
            <a href="manage_products.php" class="btn-back">Hủy & Quay lại</a>
        </div>

    </form>
</div>

</body>
</html>