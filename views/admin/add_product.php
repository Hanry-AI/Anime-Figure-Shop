<?php
session_start();

// 1. Load các file cấu hình
// Dùng __DIR__ để đường dẫn chính xác tuyệt đối
require_once __DIR__ . '/../../src/Config/db.php';
require_once __DIR__ . '/../../src/Models/Product.php';

// 2. [BẢO MẬT] AUTH GUARD - Chặn người lạ
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /DACS/public/index.php');
    exit;
}

// 3. [QUAN TRỌNG] Lấy kết nối Database
$conn = getDatabaseConnection();

// 4. Định nghĩa các hằng số đường dẫn (Nếu chưa có)
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', dirname(dirname(__DIR__)));
}
if (!defined('UPLOAD_DIR')) define('UPLOAD_DIR', PROJECT_ROOT . '/public/assets/img/');
if (!defined('DB_IMG_PATH')) define('DB_IMG_PATH', '/DACS/public/assets/img/');

// --- Helper Functions ---
function e($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

function processUpload($fileInput, $targetDir) {
    if (!isset($fileInput['name']) || $fileInput['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    // Chỉ cho phép ảnh
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($fileInput['type'], $allowedTypes)) {
        return false;
    }
    
    $filename = basename($fileInput['name']);
    // Thêm timestamp để tên file không bị trùng
    $targetName = time() . '_' . $filename; 
    $targetFilePath = $targetDir . $targetName;
    
    if (move_uploaded_file($fileInput['tmp_name'], $targetFilePath)) {
        return $targetName;
    }
    return false;
}

// --- XỬ LÝ FORM SUBMIT ---
$errors = [];
$successMessage = '';
$name = $category = $priceRaw = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? 'anime');
    $priceRaw = trim($_POST['price'] ?? '0');
    
    // Validate dữ liệu
    if ($name === '')     $errors[] = 'Tên sản phẩm là bắt buộc.';
    if ($category === '') $errors[] = 'Danh mục là bắt buộc.';
    if ($priceRaw === '') $errors[] = 'Giá là bắt buộc.';

    $priceDigits = preg_replace('/[^\d]/', '', $priceRaw);
    $priceValue = ($priceDigits === '') ? 0 : (int)$priceDigits;
    if ($priceValue <= 0) $errors[] = 'Giá sản phẩm phải lớn hơn 0.';

    if (empty($_FILES['main_image']['name'])) {
        $errors[] = 'Vui lòng chọn Ảnh chính.';
    }

    // Nếu không có lỗi thì xử lý upload
    if (empty($errors)) {
        // 1. Upload Ảnh Chính
        $uploadedMain = processUpload($_FILES['main_image'], UPLOAD_DIR);
        
        if ($uploadedMain === false) {
            $errors[] = 'Lỗi upload ảnh chính (File lỗi hoặc sai định dạng).';
        } elseif ($uploadedMain === null) {
            $errors[] = 'Vui lòng chọn ảnh chính hợp lệ.';
        } else {
            $mainImgUrl = DB_IMG_PATH . $uploadedMain;

            // 2. Upload Ảnh Phụ (Nếu có)
            $extraImgUrls = [];
            if (isset($_FILES['extra_images']) && !empty($_FILES['extra_images']['name'][0])) {
                $totalFiles = count($_FILES['extra_images']['name']);
                for ($i = 0; $i < $totalFiles; $i++) {
                    // Gom thông tin file lẻ từ mảng $_FILES
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

            // 3. Gọi Model để lưu vào Database
            // Biến $conn đã được khởi tạo ở đầu file -> Truyền vào hàm
            $newId = addProduct($conn, $name, $category, $priceValue, $mainImgUrl, $extraImgUrls);

            if ($newId) {
                // Set flash message cho đẹp (optional)
                $_SESSION['flash_message'] = "Thêm thành công sản phẩm ID: $newId";
                $_SESSION['flash_type'] = 'success';
                
                // Chuyển hướng về trang quản lý để tránh resubmit form khi F5
                header('Location: manage_products.php');
                exit;
            } else {
                $errors[] = "Lỗi hệ thống: Không thể lưu vào Database.";
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
    <title>Thêm sản phẩm - Admin FigureWorld</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../layouts/header.css">
    <link rel="stylesheet" href="/DACS/public/assets/css/contact_styles.css">
    <style>
        /* CSS cho phần thêm ảnh */
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
        
        /* Chỉnh lại form container cho gọn */
        .contact-form-card { max-width: 800px; margin: 0 auto; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <section class="contact-hero" style="padding: 40px 0; background: #f1f5f9;">
        <div class="contact-hero-inner">
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
                    <a href="manage_products.php" class="submit-btn" style="background: #64748b; text-align: center; text-decoration: none;">
                        Hủy bỏ
                    </a>
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-save"></i> Lưu Sản Phẩm
                    </button>
                </div>
                
            </form>
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