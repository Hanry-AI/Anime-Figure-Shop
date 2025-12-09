<?php
session_start();

// 1. Cấu hình & Kết nối
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', __DIR__ . '/../..');
}
// Gọi Model Product
require_once PROJECT_ROOT . '/src/Models/Product.php';

// Định nghĩa thư mục upload nếu chưa có (thường nên để trong constants.php)
if (!defined('UPLOAD_DIR')) define('UPLOAD_DIR', PROJECT_ROOT . '/public/assets/img/');
if (!defined('DB_IMG_PATH')) define('DB_IMG_PATH', '/DACS/public/assets/img/');

// Hàm tiện ích escape output
function e($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

// Hàm xử lý upload 1 file (Giữ lại hàm helper này ở đây hoặc chuyển vào src/Helpers)
function processUpload($fileInput, $targetDir) {
    if (!isset($fileInput['name']) || $fileInput['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($fileInput['type'], $allowedTypes)) {
        return false;
    }
    $filename = basename($fileInput['name']);
    // Thêm timestamp để tránh trùng tên file
    $targetName = time() . '_' . $filename; 
    $targetFilePath = $targetDir . $targetName;
    
    if (move_uploaded_file($fileInput['tmp_name'], $targetFilePath)) {
        return $targetName;
    }
    return false;
}

$errors = [];
$successMessage = '';
$name = $category = $priceRaw = '';

// 2. Xử lý Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? 'anime');
    $priceRaw = trim($_POST['price'] ?? '0');
    
    // Validate cơ bản
    if ($name === '')     $errors[] = 'Tên sản phẩm là bắt buộc.';
    if ($category === '') $errors[] = 'Danh mục là bắt buộc.';
    if ($priceRaw === '') $errors[] = 'Giá là bắt buộc.';

    $priceDigits = preg_replace('/[^\d]/', '', $priceRaw);
    $priceValue = ($priceDigits === '') ? 0 : (int)$priceDigits;
    if ($priceValue <= 0) $errors[] = 'Giá sản phẩm phải lớn hơn 0.';

    if (empty($_FILES['main_image']['name'])) {
        $errors[] = 'Vui lòng chọn Ảnh chính.';
    }

    if (empty($errors)) {
        // --- Upload Ảnh Chính ---
        $uploadedMain = processUpload($_FILES['main_image'], UPLOAD_DIR);
        
        if ($uploadedMain === false) {
            $errors[] = 'Lỗi upload ảnh chính (Sai định dạng hoặc lỗi server).';
        } elseif ($uploadedMain === null) {
            $errors[] = 'Vui lòng chọn ảnh chính hợp lệ.';
        } else {
            $mainImgUrl = DB_IMG_PATH . $uploadedMain;

            // --- Upload Ảnh Phụ ---
            $extraImgUrls = [];
            if (isset($_FILES['extra_images']) && !empty($_FILES['extra_images']['name'][0])) {
                $totalFiles = count($_FILES['extra_images']['name']);
                for ($i = 0; $i < $totalFiles; $i++) {
                    $singleFile = [
                        'name'     => $_FILES['extra_images']['name'][$i],
                        'type'     => $_FILES['extra_images']['type'][$i],
                        'tmp_name' => $_FILES['extra_images']['tmp_name'][$i],
                        'error'    => $_FILES['extra_images']['error'][$i],
                        'size'     => $_FILES['extra_images']['size'][$i]
                    ];
                    $uploadedExtra = processUpload($singleFile, UPLOAD_DIR);
                    if ($uploadedExtra) {
                        $extraImgUrls[] = DB_IMG_PATH . $uploadedExtra;
                    }
                }
            }

            // --- GỌI MODEL ĐỂ LƯU DB ---
            // Hàm addProduct($conn, $name, $category, $price, $mainImg, $extraImgs)
            // Bạn đã thêm hàm này vào src/Models/Product.php ở bước trước
            $newId = addProduct($conn, $name, $category, $priceValue, $mainImgUrl, $extraImgUrls);

            if ($newId) {
                $successMessage = "Thêm thành công sản phẩm ID: $newId";
                // Reset form
                $name = $category = $priceRaw = ''; 
            } else {
                $errors[] = "Lỗi khi lưu vào Database.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm sản phẩm (Upload) - FigureWorld</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../layouts/header.css">
    <link rel="stylesheet" href="/DACS/public/assets/css/contact_styles.css">
    <style>
        .remove-img-btn {
            background: #ffecec; color: #ff4d4d; border: 1px solid #ff4d4d;
            padding: 5px 10px; cursor: pointer; border-radius: 4px; font-size: 0.8rem;
            margin-left: 5px; display: inline-flex; align-items: center; justify-content: center;
        }
        .remove-img-btn:hover { background: #ff4d4d; color: white; }
        .input-group-dynamic { display: flex; gap: 5px; margin-bottom: 8px; align-items: center; }
        .input-group-dynamic .input-wrapper { flex-grow: 1; margin-bottom: 0; }
        .add-more-btn {
            background: #e2e8f0; color: #2d3748; border: none; padding: 8px 12px;
            border-radius: 4px; cursor: pointer; font-size: 0.9rem; margin-top: 5px;
        }
        .add-more-btn:hover { background: #cbd5e0; }
        
        /* Style cho input file đẹp hơn chút */
        input[type="file"] {
            padding: 8px;
            background: #fff;
        }
    </style>
</head>
<body>
    <?php if (file_exists(__DIR__ . '/../layouts/header.php')) include __DIR__ . '/../layouts/header.php'; ?>

    <section class="contact-hero">
        <div class="contact-hero-inner">
            <h1>🛠 Thêm Sản Phẩm (Upload)</h1>
        </div>
    </section>

    <div class="container">
        <div class="contact-grid">
            <div class="contact-form-card">
                <h2 class="form-title">Thông tin sản phẩm</h2>

                <?php if (!empty($errors)): ?>
                    <div class="success-message show" style="background: #ef4444; color: white;">
                        <ul style="padding-left: 20px; margin: 0;">
                            <?php foreach ($errors as $err): ?><li><?php echo e($err); ?></li><?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($successMessage): ?>
                    <div class="success-message show"><span><?php echo e($successMessage); ?></span></div>
                <?php endif; ?>

                <form method="post" action="" enctype="multipart/form-data">
                    
                    <div class="form-group">
                        <label>Tên sản phẩm <span class="required">*</span></label>
                        <div class="input-wrapper"><input type="text" name="name" value="<?php echo e($name); ?>" required></div>
                    </div>

                    <div class="form-group">
                        <label>Danh mục <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <select name="category" required>
                                <option value="">-- Chọn --</option>
                                <option value="anime" <?php echo $category=='anime'?'selected':''; ?>>Anime</option>
                                <option value="gundam" <?php echo $category=='gundam'?'selected':''; ?>>Gundam</option>
                                <option value="marvel" <?php echo $category=='marvel'?'selected':''; ?>>Marvel</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Giá (VNĐ) <span class="required">*</span></label>
                        <div class="input-wrapper"><input type="text" name="price" value="<?php echo e($priceRaw); ?>" required></div>
                    </div>

                    <div class="form-group">
                        <label>Ảnh chính (Upload từ máy) <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <input type="file" name="main_image" accept="image/*" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Ảnh phụ (Chọn nhiều ảnh)</label>
                        <div id="extra-images-container">
                            <div class="input-group-dynamic">
                                <div class="input-wrapper">
                                    <input type="file" name="extra_images[]" accept="image/*">
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" class="add-more-btn" id="btnAddImage">
                            <i class="fas fa-plus"></i> Thêm file khác
                        </button>
                    </div>

                    <button type="submit" class="submit-btn"><i class="fas fa-cloud-upload-alt"></i> Thêm sản phẩm</button>
                </form>
            </div>
        </div>
    </div>

    <script src="/DACS/public/assets/js/scripts.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnAdd = document.getElementById('btnAddImage');
            const container = document.getElementById('extra-images-container');

            if (btnAdd && container) {
                btnAdd.addEventListener('click', function() {
                    const div = document.createElement('div');
                    div.className = 'input-group-dynamic';
                    div.innerHTML = `
                        <div class="input-wrapper">
                            <input type="file" name="extra_images[]" accept="image/*">
                        </div>
                        <button type="button" class="remove-img-btn" onclick="this.parentElement.remove()">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                    container.appendChild(div);
                });
            }
        });
    </script>
</body>
</html>

