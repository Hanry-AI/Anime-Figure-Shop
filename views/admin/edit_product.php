<?php
session_start();

// 1. Load Config & Model
require_once __DIR__ . '/../../src/Config/db.php';
require_once __DIR__ . '/../../src/Models/Product.php';

// 2. [BẢO MẬT] Chặn người lạ
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /DACS/public/index.php');
    exit;
}

// 3. Lấy kết nối DB
$products = getAllProducts($conn); 

// 4. Định nghĩa đường dẫn (Nếu chưa có)
if (!defined('PROJECT_ROOT')) define('PROJECT_ROOT', dirname(dirname(__DIR__)));
if (!defined('UPLOAD_DIR')) define('UPLOAD_DIR', PROJECT_ROOT . '/public/assets/img/');
if (!defined('DB_IMG_PATH')) define('DB_IMG_PATH', '/DACS/public/assets/img/');

// Hàm Helper Upload (Nội bộ)
function processUpload($fileInput) {
    if (!isset($fileInput['name']) || $fileInput['error'] !== UPLOAD_ERR_OK) return null;
    
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($fileInput['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) return null;

    $filename = time() . '_' . uniqid() . '.' . $ext;
    if (move_uploaded_file($fileInput['tmp_name'], UPLOAD_DIR . $filename)) {
        return DB_IMG_PATH . $filename;
    }
    return null;
}

// --- LẤY DỮ LIỆU SẢN PHẨM ---
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = getProductById($conn, $id);

if (!$product) {
    header('Location: manage_products.php');
    exit;
}

$error = '';
$success = '';

// --- XỬ LÝ SUBMIT FORM ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // A. Xử lý Xóa ảnh phụ
    if (isset($_POST['delete_image_id'])) {
        $imgId = (int)$_POST['delete_image_id'];
        if (deleteProductImageById($conn, $imgId)) {
            // Redirect để refresh trang (PRG Pattern)
            header("Location: edit_product.php?id=$id&msg=deleted");
            exit;
        } else {
            $error = "Lỗi: Không thể xóa ảnh.";
        }
    } 
    // B. Xử lý Cập nhật thông tin
    else {
        $name     = trim($_POST['name'] ?? '');
        $category = $_POST['category'] ?? 'anime';
        $price    = (int)preg_replace('/[^\d]/', '', $_POST['price']); // Chỉ lấy số

        if ($name === '') {
            $error = "Tên sản phẩm không được để trống.";
        } else {
            // 1. Upload ảnh đại diện mới (nếu có)
            $mainImgPath = null;
            if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
                $mainImgPath = processUpload($_FILES['main_image']);
            }

            // 2. Cập nhật bảng products
            if (updateProduct($conn, $id, $name, $category, $price, $mainImgPath)) {
                
                // 3. Upload thêm ảnh phụ (nếu có)
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
                        if ($url) $newExtraUrls[] = $url;
                    }
                    
                    if (!empty($newExtraUrls)) {
                        addProductExtraImages($conn, $id, $newExtraUrls);
                    }
                }

                $success = "Cập nhật thành công!";
                $product = getProductById($conn, $id); // Lấy lại dữ liệu mới nhất
            } else {
                $error = "Lỗi SQL: Không thể cập nhật sản phẩm.";
            }
        }
    }
}

if (isset($_GET['msg']) && $_GET['msg'] == 'deleted') {
    $success = "Đã xóa ảnh thành công!";
}

// Lấy danh sách ảnh phụ
$extraImages = getProductImages($conn, $id);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa sản phẩm - Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../layouts/header.css">
    <style>
        body { font-family: sans-serif; background: #f8fafc; color: #0f172a; padding-bottom: 50px; }
        .edit-container { max-width: 900px; margin: 120px auto 0; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        h2 { border-bottom: 2px solid #e2e8f0; padding-bottom: 15px; margin-top: 0; color: #1e293b; }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; }
        input[type="text"], input[type="number"], select { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; }
        
        /* Gallery */
        .gallery-grid { display: flex; flex-wrap: wrap; gap: 15px; margin-top: 10px; }
        .gallery-item { position: relative; width: 120px; height: 120px; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .gallery-item img { width: 100%; height: 100%; object-fit: cover; }
        .btn-delete-img {
            position: absolute; top: 5px; right: 5px;
            background: rgba(220, 38, 38, 0.9); color: white; border: none;
            width: 28px; height: 28px; border-radius: 50%; cursor: pointer;
            display: flex; align-items: center; justify-content: center; transition: 0.2s;
        }
        .btn-delete-img:hover { transform: scale(1.1); background: #dc2626; }

        .btn-save { background: #2563eb; color: white; padding: 12px 25px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 1rem; }
        .btn-save:hover { background: #1d4ed8; }
        .btn-back { margin-left: 15px; text-decoration: none; color: #64748b; font-weight: 500; }
        .btn-back:hover { color: #0f172a; }

        .alert { padding: 15px; margin-bottom: 20px; border-radius: 6px; }
        .alert-success { background: #dcfce7; color: #166534; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .preview-main { width: 100px; height: 100px; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5e1; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <div class="edit-container">
        <h2><i class="fas fa-edit"></i> Chỉnh sửa: <?= htmlspecialchars($product['name']) ?></h2>
        
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>

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
                <div style="display: flex; gap: 20px; align-items: center;">
                    <img src="<?= htmlspecialchars($product['image_url']) ?>" class="preview-main">
                    <div>
                        <input type="file" name="main_image" accept="image/*">
                        <p style="font-size: 0.9em; color: #64748b; margin-top: 5px;">Chọn ảnh mới để thay thế (để trống nếu không đổi).</p>
                    </div>
                </div>
            </div>

            <hr style="margin: 30px 0; border: 0; border-top: 1px dashed #cbd5e1;">

            <div class="form-group">
                <label>Thư viện ảnh phụ (Gallery)</label>
                <div class="gallery-grid">
                    <?php if (!empty($extraImages)): ?>
                        <?php foreach ($extraImages as $img): ?>
                            <div class="gallery-item">
                                <img src="<?= htmlspecialchars($img['image_url']) ?>">
                                <button type="submit" name="delete_image_id" value="<?= $img['id'] ?>" class="btn-delete-img" title="Xóa ảnh này" onclick="return confirm('Xóa ảnh này?');">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #64748b; font-style: italic;">Chưa có ảnh phụ.</p>
                    <?php endif; ?>
                </div>

                <div style="margin-top: 20px; background: #f1f5f9; padding: 15px; border-radius: 6px;">
                    <label style="margin-bottom: 8px;"><i class="fas fa-plus-circle"></i> Thêm ảnh phụ mới</label>
                    <input type="file" name="new_extra_images[]" multiple accept="image/*">
                </div>
            </div>

            <div style="margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> Lưu Thay Đổi</button>
                <a href="manage_products.php" class="btn-back">Hủy bỏ</a>
            </div>
        </form>
    </div>
</body>
</html>