<?php
namespace DACS\Controllers;

// Nhúng config & model
require_once __DIR__ . '/../Config/db.php';
require_once __DIR__ . '/../Models/User.php';

class AuthController {
    private $conn;

    public function __construct()
    {
        // Lấy kết nối DB từ file db.php
        // (giữ kiểu global cho đơn giản đồ án)
        global $conn;
        $this->conn = $conn;

        // Đảm bảo session đã được khởi tạo
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Điểm vào chính: hiển thị form login/register + xử lý submit
     */
    public function index()
    {
        // Mảng lỗi mặc định
        $errors = [
            'login'    => '',
            'register' => '',
        ];

        // (Tuỳ chọn) mảng lưu input cũ để fill lại form
        $oldInput = [];

        // Nếu có yêu cầu logout qua GET
        if (isset($_GET['action']) && $_GET['action'] === 'logout') {
            $this->logout(); // sẽ exit ở trong hàm
        }

        // Nếu người dùng submit form
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Người dùng ấn Đăng nhập
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

            // Người dùng ấn Đăng ký
            elseif (isset($_POST['register_email'])) {
                // Lưu lại input để fill form nếu lỗi
                $oldInput['register_name']  = $_POST['register_name'] ?? '';
                $oldInput['register_email'] = $_POST['register_email'] ?? '';

                $registerError = $this->handleRegister($_POST);

                if ($registerError) {
                    $errors['register'] = $registerError;
                }
            }
        }

        // Sau khi xử lý xong: gọi View, truyền $errors và $oldInput
        // View chỉ việc hiển thị, không xử lý login/register nữa
        require __DIR__ . '/../../views/pages/auth_index.php';
    }

    private function handleLogin($email, $password)
    {
        $user = loginUser($this->conn, $email, $password);

        if ($user) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

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

        if ($pass !== $confirm) {
            return 'Mật khẩu xác nhận không khớp.';
        }

        $result = registerUser($this->conn, $name, $email, $pass);

        if (is_numeric($result)) {
            $_SESSION['user_id']   = $result;
            $_SESSION['user_name'] = $name;

            header('Location: /DACS/public/index.php');
            exit;
        }

        // Nếu model trả về chuỗi → lỗi (email tồn tại, ...)
        return $result;
    }

    public function logout()
    {
        // Xoá toàn bộ session
        $_SESSION = [];
        session_destroy();

        header('Location: /DACS/public/index.php');
        exit;
    }
}