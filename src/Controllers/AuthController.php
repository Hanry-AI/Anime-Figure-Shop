<?php
namespace DACS\Controllers;

// Nhúng file config và Model User để xử lý đăng nhập/đăng ký
require_once __DIR__ . '/../Config/db.php';
require_once __DIR__ . '/../Models/User.php';

class AuthController {
    private $conn;

    /**
     * HÀM KHỞI TẠO (Constructor)
     * - Nhận kết nối $db từ index.php truyền vào (Dependency Injection)
     * - Giúp code sạch, không dùng global, dễ giải thích với giáo viên.
     */
    public function __construct($db)
    {
        $this->conn = $db; // Lưu kết nối vào biến nội bộ để dùng

        // Kiểm tra xem session đã bật chưa, nếu chưa thì bật lên
        // (Session dùng để lưu trạng thái đăng nhập của người dùng)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Điểm vào chính: Hiển thị form và Xử lý khi người dùng bấm nút
     */
    public function index()
    {
        // Mảng chứa thông báo lỗi (ban đầu để rỗng)
        $errors = [
            'login'    => '',
            'register' => '',
        ];

        // Mảng lưu lại những gì người dùng đã nhập (để nếu lỗi thì không phải nhập lại từ đầu)
        $oldInput = [];

        // 1. XỬ LÝ ĐĂNG XUẤT (Nếu URL có ?action=logout)
        if (isset($_GET['action']) && $_GET['action'] === 'logout') {
            $this->logout(); // Gọi hàm logout bên dưới
        }

        // 2. XỬ LÝ KHI NGƯỜI DÙNG BẤM NÚT (SUBMIT FORM)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Trường hợp: Bấm nút ĐĂNG NHẬP
            if (isset($_POST['login_email'])) {
                $oldInput['login_email'] = $_POST['login_email'] ?? '';

                // Gọi hàm xử lý đăng nhập
                $loginError = $this->handleLogin(
                    $_POST['login_email'] ?? '',
                    $_POST['login_password'] ?? ''
                );

                // Nếu có lỗi thì gán vào mảng lỗi để hiển thị ra View
                if ($loginError) {
                    $errors['login'] = $loginError;
                }
            }

            // Trường hợp: Bấm nút ĐĂNG KÝ
            elseif (isset($_POST['register_email'])) {
                $oldInput['register_name']  = $_POST['register_name'] ?? '';
                $oldInput['register_email'] = $_POST['register_email'] ?? '';

                // Gọi hàm xử lý đăng ký
                $registerError = $this->handleRegister($_POST);

                if ($registerError) {
                    $errors['register'] = $registerError;
                }
            }
        }

        // 3. GỌI VIEW HIỂN THỊ
        // Truyền $errors và $oldInput sang View để dùng
        require __DIR__ . '/../../views/pages/auth_index.php';
    }

    // --- CÁC HÀM XỬ LÝ RIÊNG TƯ (PRIVATE) ---

    private function handleLogin($email, $password)
    {
        // Gọi Model User để kiểm tra DB (truyền $this->conn vào)
        $user = loginUser($this->conn, $email, $password);

        if ($user) {
            // Đăng nhập thành công -> Lưu thông tin vào Session
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role']; // Lưu quyền (admin/user)

            // Chuyển hướng về trang chủ
            header('Location: /DACS/public/index.php');
            exit;
        }

        return 'Email hoặc mật khẩu không đúng.';
    }

    private function handleRegister($data)
    {
        $name    = $data['register_name']     ?? '';
        $email   = $data['register_email']    ?? '';
        $pass    = $data['register_password'] ?? '';
        $confirm = $data['confirm_password']  ?? '';

        // Kiểm tra mật khẩu nhập lại
        if ($pass !== $confirm) {
            return 'Mật khẩu xác nhận không khớp.';
        }

        // Gọi Model để thêm người dùng vào DB
        $result = registerUser($this->conn, $name, $email, $pass);

        // Nếu kết quả trả về là số (ID người dùng mới) -> Thành công
        if (is_numeric($result)) {
            $_SESSION['user_id']   = $result;
            $_SESSION['user_name'] = $name;

            header('Location: /DACS/public/index.php');
            exit;
        }

        // Nếu Model trả về chuỗi thông báo lỗi (VD: Email đã tồn tại)
        return $result;
    }

    public function logout()
    {
        // Xoá sạch Session
        $_SESSION = [];
        session_destroy();

        // Về trang chủ
        header('Location: /DACS/public/index.php');
        exit;
    }
}
?>