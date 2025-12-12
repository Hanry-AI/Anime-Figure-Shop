<?php
session_start();

// Định nghĩa đường dẫn gốc (để trỏ về vendor/autoload)
define('PROJECT_ROOT', dirname(dirname(__DIR__)));

// 1. [QUAN TRỌNG] Load Composer Autoload
require_once PROJECT_ROOT . '/vendor/autoload.php';

// 2. Sử dụng Namespace chuẩn
use DACS\Config\Database;
use DACS\Models\ProductModel;

// 3. [BẢO MẬT] AUTH GUARD - Chặn người lạ
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /DACS/public/index.php');
    exit;
}

try {
    // 4. Khởi tạo Database & Model (Chuẩn OOP)
    // Thay thế cho việc require db.php và dùng biến $conn trôi nổi
    $db = new Database();
    $conn = $db->getConnection();
    $productModel = new ProductModel($conn);

    // 5. Cấu hình upload
    // Đường dẫn vật lý để lưu file
    if (!defined('UPLOAD_DIR')) define('UPLOAD_DIR', PROJECT_ROOT . '/public/assets/img/');
    // Đường dẫn web để lưu vào DB (Dạng tương đối assets/img/ten-anh.jpg)
    // ImageHelper sẽ tự thêm /DACS/public/... vào trước khi hiển thị
    if (!defined('DB_IMG_PATH')) define('DB_IMG_PATH', 'assets/img/');

    // --- Helper Functions ---
    function e($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }

    function processUpload($fileInput) {
        if (!isset($fileInput['name']) || $fileInput['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($fileInput['type'], $allowedTypes)) {
            return false;
        }
        
        $ext = strtolower(pathinfo($fileInput['name'], PATHINFO_EXTENSION));
        // Tạo tên file ngẫu nhiên để tránh trùng
        $filename = time() . '_' . uniqid() . '.' . $ext;
        $targetFilePath = UPLOAD_DIR . $filename;
        
        if (move_uploaded_file($fileInput['tmp_name'], $targetFilePath)) {
            // Trả về tên file. ImageHelper sẽ xử lý phần path còn lại.
            return $filename;
        }
        return false;
    }

    // --- XỬ LÝ FORM SUBMIT ---
    $errors = [];
    $name = $category = $priceRaw = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name     = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? 'anime');
        $priceRaw = trim($_POST['price'] ?? '0');
        
        // Validate
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
            // 1. Upload Ảnh Chính
            $uploadedMainName = processUpload($_FILES['main_image']);
            
            if ($uploadedMainName === false) {
                $errors[] = 'Lỗi upload ảnh chính (File lỗi hoặc sai định dạng).';
            } elseif ($uploadedMainName === null) {
                $errors[] = 'Vui lòng chọn ảnh chính hợp lệ.';
            } else {
                // Lưu tên file vào DB (VD: 17345678_abc.jpg)
                $mainImgToSave = DB_IMG_PATH . $uploadedMainName;

                // 2. Upload Ảnh Phụ
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
                        $uploadedExtraName = processUpload($singleFile);
                        if ($uploadedExtraName) {
                            $extraImgUrls[] = DB_IMG_PATH . $uploadedExtraName;
                        }
                    }
                }

                // 3. GỌI MODEL ĐỂ LƯU
                $newId = $productModel->addProduct($name, $category, $priceValue, $mainImgToSave, $extraImgUrls);

                if ($newId) {
                    $_SESSION['flash_message'] = "Thêm thành công sản phẩm ID: $newId";
                    $_SESSION['flash_type'] = 'success';
                    
                    header('Location: manage_products.php');
                    exit;
                } else {
                    $errors[] = "Lỗi hệ thống: Không thể lưu vào Database.";
                }
            }
        }
    }

} catch (Exception $e) {
    die("Lỗi hệ thống: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm sản phẩm - Admin FigureWorld</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/DACS/public/assets/css/styles.css">
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
        input[type="file"] { padding: 8px; background: #fff; }
        .contact-form-card { max-width: 800px; margin: 0 auto; }
    </style>
</head>
<body>
    
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <section class="contact-hero" style="padding: 40px 0; background: #f1f5f9; margin-top: 80px;">
        <div class="contact-hero-inner" style="text-align: center;">
            <h1 style="color: #0f172a;">🛠 Thêm Sản Phẩm Mới</h1>
        </div>
    </section>

    <div class="container" style="margin-top: 30px; margin-bottom: 50px;">
        <div class="contact-form-card">
            
            <?php if (!empty($errors)): ?>
                <div class="success-message show" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;">
                    <ul style="padding-left: 20px; margin: 0;">
                        <?php foreach ($errors as $err): ?><li><i class="fas fa-exclamation-circle"></i> <?php echo e($err); ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label>Tên sản phẩm <span class="required">*</span></label>
                    <div class="input-wrapper"><input type="text" name="name" value="<?php echo e($name); ?>" required placeholder="Nhập tên sản phẩm..."></div>
                </div>

                <div class="form-group">
                    <label>Danh mục <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <select name="category" required>
                            <option value="">-- Chọn danh mục --</option>
                            <option value="anime" <?php echo $category=='anime'?'selected':''; ?>>Anime Figure</option>
                            <option value="gundam" <?php echo $category=='gundam'?'selected':''; ?>>Gundam Model</option>
                            <option value="marvel" <?php echo $category=='marvel'?'selected':''; ?>>Marvel</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Giá (VNĐ) <span class="required">*</span></label>
                    <div class="input-wrapper"><input type="number" name="price" value="<?php echo e($priceRaw); ?>" required placeholder="Nhập giá tiền..."></div>
                </div>

                <div class="form-group">
                    <label>Ảnh chính (Bắt buộc) <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <input type="file" name="main_image" accept="image/*" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Ảnh phụ (Thư viện ảnh)</label>
                    <div id="extra-images-container">
                        <div class="input-group-dynamic">
                            <div class="input-wrapper">
                                <input type="file" name="extra_images[]" accept="image/*">
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" class="add-more-btn" id="btnAddImage">
                        <i class="fas fa-plus"></i> Thêm ảnh khác
                    </button>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <a href="manage_products.php" class="submit-btn" style="background: #64748b; text-align: center; text-decoration: none; display:inline-block; padding: 12px 20px; color:white; border-radius:6px;">
                        Hủy bỏ
                    </a>
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-save"></i> Lưu Sản Phẩm
                    </button>
                </div>
                
            </form>
        </div>
    </div>

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
                        <button type="button" class="remove-img-btn" onclick="this.parentElement.remove()" title="Xóa dòng này">
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