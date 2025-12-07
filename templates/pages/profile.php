<?php
session_start();
require_once __DIR__ . '/../../src/Config/db.php'; // đảm bảo $conn (mysqli) tồn tại

// Bắt buộc đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: /DACS/templates/pages/auth_index.php?action=login');
    exit;
}

$userId     = (int)$_SESSION['user_id'];
$successMsg = '';
$errorMsg   = '';

// Lấy thông tin hiện tại
$sql  = "SELECT id, name, email, created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$currentUser = $stmt->get_result()->fetch_assoc();

if (!$currentUser) {
    $errorMsg = 'Không tìm thấy tài khoản của bạn.';
}

// Xử lý cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $currentUser) {
    $name        = trim($_POST['name']  ?? '');
    $email       = trim($_POST['email'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmNew  = $_POST['confirm_new_password'] ?? '';

    if ($name === '' || $email === '') {
        $errorMsg = 'Tên và email không được để trống.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = 'Email không hợp lệ.';
    } elseif ($newPassword !== '' && $newPassword !== $confirmNew) {
        $errorMsg = 'Mật khẩu mới xác nhận không khớp.';
    } else {
        // Nếu đổi email -> kiểm tra trùng
        if ($email !== $currentUser['email']) {
            $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
            $check->bind_param('si', $email, $userId);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $errorMsg = 'Email này đã được sử dụng bởi tài khoản khác.';
            }
        }

        if ($errorMsg === '') {
            if ($newPassword !== '') {
                $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                $update = $conn->prepare(
                    "UPDATE users SET name = ?, email = ?, password_hash = ? WHERE id = ?"
                );
                $update->bind_param('sssi', $name, $email, $hash, $userId);
            } else {
                $update = $conn->prepare(
                    "UPDATE users SET name = ?, email = ? WHERE id = ?"
                );
                $update->bind_param('ssi', $name, $email, $userId);
            }

            if ($update->execute()) {
                $successMsg = 'Cập nhật thông tin tài khoản thành công.';
                $_SESSION['user_name'] = $name;

                // reload lại data mới
                $stmt->execute();
                $currentUser = $stmt->get_result()->fetch_assoc();
            } else {
                $errorMsg = 'Có lỗi xảy ra khi lưu, vui lòng thử lại.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông tin tài khoản</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/DACS/public/assets/css/profile_styles.css">
    <link rel="stylesheet" href="../layouts/header.css">
</head>
<body class="profile-page">
<?php include __DIR__ . '/../layouts/header.php'; ?>

<main class="profile-main">
    <h2 class="profile-title">Thông tin tài khoản</h2>

    <?php if ($errorMsg): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($errorMsg); ?>
        </div>
    <?php endif; ?>

    <?php if ($successMsg): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($successMsg); ?>
        </div>
    <?php endif; ?>

    <?php if ($currentUser): ?>
    <form method="post" id="profileForm">
        <div class="form-group">
            <label for="nameInput">Họ và tên</label>
            <div class="input-wrapper">
                <input type="text" id="nameInput" name="name"
                       value="<?php echo htmlspecialchars($currentUser['name']); ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="emailInput">Email</label>
            <div class="input-wrapper">
                <input type="email" id="emailInput" name="email"
                       value="<?php echo htmlspecialchars($currentUser['email']); ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="newPassword">Mật khẩu mới (để trống nếu không đổi)</label>
            <div class="input-wrapper">
                <input type="password" id="newPassword" name="new_password" placeholder="••••••••">
                <button type="button"
                        class="toggle-password"
                        data-target="newPassword">
                    <i class="fa-regular fa-eye"></i>
                </button>
            </div>
        </div>

        <div class="form-group">
            <label for="confirmNewPassword">Xác nhận mật khẩu mới</label>
            <div class="input-wrapper">
                <input type="password" id="confirmNewPassword" name="confirm_new_password" placeholder="••••••••">
                <button type="button"
                        class="toggle-password"
                        data-target="confirmNewPassword">
                    <i class="fa-regular fa-eye"></i>
                </button>
            </div>
        </div>

        <p class="profile-meta">
            Tài khoản được tạo lúc:
            <strong><?php echo htmlspecialchars($currentUser['created_at']); ?></strong>
        </p>

        <button type="submit" class="submit-btn">Lưu thay đổi</button>
    </form>
    <?php endif; ?>
</main>

<script src="/DACS/public/assets/js/profile.js"></script>
</body>
</html>
