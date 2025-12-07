<?php
session_start();

// 1. Cấu hình & Kết nối CSDL
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', __DIR__ . '/../..');
}
require_once PROJECT_ROOT . '/src/Config/db.php';

// --- CẤU HÌNH QUAN TRỌNG (ĐÃ FIX LỖI) ---

// 1. Đường dẫn lưu file trên ổ cứng (File System)
// PROJECT_ROOT đã là thư mục dự án (D:/xampp/htdocs/DACS)
// Nên chỉ cần nối thêm /public/assets/img/ là đủ.
define('UPLOAD_DIR', PROJECT_ROOT . '/public/assets/img/');

// 2. Đường dẫn hiển thị trên web (URL)
// Trình duyệt cần biết tên dự án (/DACS) để tìm đúng ảnh
define('DB_IMG_PATH', '/DACS/public/assets/img/');

// 2. Hàm tiện ích
function e($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Hàm xử lý upload 1 file
function processUpload($fileInput, $targetDir) {
    // 1. Kiểm tra lỗi upload cơ bản
    if (!isset($fileInput['name']) || $fileInput['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    // 2. Vẫn giữ kiểm tra định dạng ảnh (để tránh upload nhầm file virus/php)
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($fileInput['type'], $allowedTypes)) {
        return false;
    }
    // 3. LẤY TÊN GỐC TUYỆT ĐỐI
    // basename() chỉ để đảm bảo không bị hack đường dẫn, còn lại giữ nguyên tên bạn đặt.
    $filename = basename($fileInput['name']);
    $targetFilePath = $targetDir . $filename;
    
    if (move_uploaded_file($fileInput['tmp_name'], $targetFilePath)) {
        return $filename;
    }
    return false;
}

// 3. Khởi tạo biến
$errors = [];
$successMessage = '';
$name = $category = $priceRaw = '';

// 4. Xử lý Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $priceRaw = trim($_POST['price'] ?? '');
    
    if ($name === '')     $errors[] = 'Tên sản phẩm là bắt buộc.';
    if ($category === '') $errors[] = 'Danh mục là bắt buộc.';
    if ($priceRaw === '') $errors[] = 'Giá là bắt buộc.';

    if (empty($_FILES['main_image']['name'])) {
        $errors[] = 'Vui lòng chọn Ảnh chính từ máy tính.';
    }

    $priceDigits = preg_replace('/[^\d]/', '', $priceRaw);
    $priceValue = ($priceDigits === '') ? 0 : (int)$priceDigits;
    if ($priceValue === 0) $errors[] = 'Giá sản phẩm không hợp lệ.';

    if (empty($errors)) {
        // --- XỬ LÝ UPLOAD ẢNH CHÍNH ---
        $uploadedMainFile = processUpload($_FILES['main_image'], UPLOAD_DIR);
        
        if ($uploadedMainFile === false) {
            $errors[] = 'Lỗi upload ảnh (Sai định dạng hoặc không thể lưu).';
        } elseif ($uploadedMainFile === null) {
            $errors[] = 'Lỗi hệ thống upload.';
        } else {
            // Đường dẫn lưu vào DB
            $mainImageUrl = DB_IMG_PATH . $uploadedMainFile;

            // BƯỚC 1: Insert Products (Cần update các cột này cho khớp với bảng của bạn)
            // Lưu ý: Nếu bảng của bạn không có cột overview, details... thì xóa bớt đi nhé
            $sql = "INSERT INTO products (name, category, price, image_url) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                // Chỉ bind 4 tham số cơ bản (siss)
                $stmt->bind_param('ssis', $name, $category, $priceValue, $mainImageUrl);

                if ($stmt->execute()) {
                    $newProductId = $stmt->insert_id;
                    $stmt->close();

                    // --- XỬ LÝ ẢNH PHỤ ---
                    if (isset($_FILES['extra_images']) && !empty($_FILES['extra_images']['name'][0])) {
                        $sqlImg = "INSERT INTO product_images (product_id, image_url, sort_order) VALUES (?, ?, ?)";
                        $stmtImg = $conn->prepare($sqlImg);
                        $order = 1;
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
                                $extraUrl = DB_IMG_PATH . $uploadedExtra;
                                $stmtImg->bind_param('isi', $newProductId, $extraUrl, $order);
                                $stmtImg->execute();
                                $order++;
                            }
                        }
                        if($stmtImg) $stmtImg->close();
                    }

                    $successMessage = "Thêm thành công! (ID: {$newProductId}). Hãy ra trang chủ kiểm tra sản phẩm MỚI này.";
                    $name = $category = $priceRaw = '';
                } else {
                    $errors[] = 'Lỗi DB: ' . $stmt->error;
                }
            } else {
                $errors[] = 'Lỗi Prepare: ' . $conn->error;
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
        document.getElementById('btnAddImage').addEventListener('click', function() {
            const container = document.getElementById('extra-images-container');
            const div = document.createElement('div');
            div.className = 'input-group-dynamic';
            
            // Input file thay vì text
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
    </script>
</body>
</html>

