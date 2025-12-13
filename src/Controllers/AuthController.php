<?php
namespace DACS\Controllers;

// Sử dụng Namespace để gọi Class ngắn gọn hơn
use DACS\Models\UserModel;

class AuthController {
    private $userModel;

    public function __construct($db)
    {
        // Khởi tạo UserModel
        $this->userModel = new UserModel($db);
    }

    public function index()
    {
        $errors = [
            'login'    => '',
            'register' => '',
        ];
        $oldInput = [];

        // 1. Xử lý Logout
        if (isset($_GET['action']) && $_GET['action'] === 'logout') {
            $this->logout();
        }

        // 2. Xử lý Submit Form
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // A. Đăng nhập
            if (isset($_POST['login_email'])) {
                $oldInput['login_email'] = $_POST['login_email'] ?? '';
                
                $loginError = $this->handleLogin(
                    $_POST['login_email'] ?? '',
                    $_POST['login_password'] ?? ''
                );

                if ($loginError) {
                    $errors['login'] = $loginError;
                }
            }

            // B. Đăng ký
            elseif (isset($_POST['register_email'])) {
                $oldInput['register_name']  = $_POST['register_name'] ?? '';
                $oldInput['register_email'] = $_POST['register_email'] ?? '';

                $registerError = $this->handleRegister($_POST);

                if ($registerError) {
                    $errors['register'] = $registerError;
                }
            }
        }

        // 3. Hiển thị View
        require __DIR__ . '/../../views/pages/auth_index.php';
    }

    // =========================================================================
    // PRIVATE METHODS
    // =========================================================================

    /**
     * Xử lý Logic Đăng nhập (ĐÃ SỬA LẠI CHUẨN)
     */
    private function handleLogin($email, $password)
    {
        // 1. Lấy thông tin user từ DB qua Model
        // Lưu ý: Hàm login trong Model chỉ nên trả về dữ liệu user dựa trên email
        $user = $this->userModel->login($email, $password); 

        // 2. Kiểm tra: User tồn tại VÀ Mật khẩu trùng khớp
        if ($user && password_verify($password, $user['password'])) {
            
            // --- A. Lưu Session cơ bản ---
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['full_name']; // Hoặc $user['name'] tùy DB của bạn
            $_SESSION['user_role'] = $user['role'];
            
            // --- B. Logic Khôi phục & Gộp Giỏ hàng (QUAN TRỌNG) ---
            // Lấy giỏ hàng cũ đã lưu trong DB
            $savedCart = $this->userModel->getCartData($user['id']);
            
            // Nếu có giỏ hàng cũ, gộp nó vào session hiện tại
            if (!empty($savedCart)) {
                if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
                    $_SESSION['cart'] = [];
                }

                foreach ($savedCart as $productId => $qty) {
                    if (isset($_SESSION['cart'][$productId])) {
                        $_SESSION['cart'][$productId] += $qty; // Cộng dồn số lượng
                    } else {
                        $_SESSION['cart'][$productId] = $qty; // Thêm mới
                    }
                }
                
                // Cập nhật ngược lại DB giỏ hàng mới nhất (đã gộp)
                $this->userModel->updateCart($user['id'], $_SESSION['cart']);
            } 
            // Nếu DB chưa có giỏ hàng, nhưng session đang có (khách vãng lai mua trước khi login)
            elseif (!empty($_SESSION['cart'])) {
                $this->userModel->updateCart($user['id'], $_SESSION['cart']);
            }
            // -----------------------------------------------------

            // --- C. Hoàn tất ---
            session_write_close(); // Lưu session ngay lập tức

            // Điều hướng về trang chủ
            header('Location: /'); 
            exit;
        }

        // Nếu sai
        return 'Email hoặc mật khẩu không chính xác.';
    }

    /**
     * Xử lý Logic Đăng ký
     */
    private function handleRegister($data)
    {
        $name    = $data['register_name']     ?? '';
        $email   = $data['register_email']    ?? '';
        $pass    = $data['register_password'] ?? '';
        $confirm = $data['confirm_password']  ?? '';

        if ($pass !== $confirm) {
            return 'Mật khẩu xác nhận không khớp.';
        }

        // Gọi Model tạo user
        $result = $this->userModel->register($name, $email, $pass);

        // Nếu trả về ID (số) -> Thành công
        if (is_numeric($result)) {
            $_SESSION['user_id']   = $result;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role'] = 'user'; // Mặc định là user
            $_SESSION['cart']      = [];     // User mới thì giỏ hàng trống

            header('Location: /');
            exit;
        }

        // Trả về lỗi (VD: Email đã tồn tại)
        return $result;
    }

    /**
     * Xử lý Đăng xuất
     */
    public function logout()
    {
        // Xóa giỏ hàng trong session (nhưng trong DB vẫn còn giữ cho lần sau)
        unset($_SESSION['user_id']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_role']);
        unset($_SESSION['cart']); 

        // Hủy toàn bộ session
        session_destroy();

        // Quay về trang chủ
        header('Location: /');
        exit;
    }
}
?>