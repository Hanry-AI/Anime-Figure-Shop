<?php
namespace DACS\Controllers;

// 1. Nhúng file Config (để lấy kết nối DB nếu cần kiểm tra luồng)
// và file Model User (chứa logic xử lý dữ liệu người dùng)
require_once __DIR__ . '/../Config/db.php';
require_once __DIR__ . '/../Models/User.php';

// 2. Sử dụng Namespace để gọi Class ngắn gọn hơn
use DACS\Models\UserModel;

class AuthController {
    /**
     * Biến này sẽ chứa "Instance" (đối tượng) của UserModel.
     * Thay vì cầm kết nối $conn, Controller sẽ cầm cái Model quản lý User.
     */
    private $userModel;

    /**
     * HÀM KHỞI TẠO (Constructor)
     * ---------------------------
     * Đây là hàm chạy đầu tiên khi class AuthController được gọi.
     * * @param mysqli $db : Kết nối Database được truyền từ index.php vào.
     * Kỹ thuật này gọi là "Dependency Injection" (Tiêm phụ thuộc).
     * Giúp Controller không phụ thuộc cứng vào biến global, dễ kiểm thử và bảo trì.
     */
    public function __construct($db)
    {
        // Khởi tạo UserModel và đưa kết nối DB cho nó giữ
        $this->userModel = new UserModel($db);

        // Kiểm tra xem session đã được bật chưa.
        // Session rất quan trọng để lưu trạng thái "Đã đăng nhập" của người dùng.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * HÀM INDEX (Main Action)
     * -----------------------
     * Nhiệm vụ:
     * 1. Điều hướng các hành động (Login, Register, Logout).
     * 2. Chuẩn bị dữ liệu (thông báo lỗi, dữ liệu cũ).
     * 3. Gọi View để hiển thị giao diện cho người dùng.
     */
    public function index()
    {
        // Khởi tạo mảng chứa lỗi, mặc định là rỗng
        $errors = [
            'login'    => '',
            'register' => '',
        ];

        // Khởi tạo mảng lưu dữ liệu cũ (để khi nhập sai không bị mất chữ đã gõ)
        $oldInput = [];

        // --- BƯỚC 1: KIỂM TRA HÀNH ĐỘNG LOGOUT ---
        // Nếu trên thanh địa chỉ có ?action=logout
        if (isset($_GET['action']) && $_GET['action'] === 'logout') {
            $this->logout(); // Gọi hàm xử lý đăng xuất
        }

        // --- BƯỚC 2: XỬ LÝ KHI NGƯỜI DÙNG BẤM NÚT (SUBMIT FORM) ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // A. Nếu người dùng bấm nút ở Form Đăng Nhập
            if (isset($_POST['login_email'])) {
                // Lưu lại email đã nhập
                $oldInput['login_email'] = $_POST['login_email'] ?? '';

                // Gọi hàm xử lý logic đăng nhập (xem bên dưới)
                $loginError = $this->handleLogin(
                    $_POST['login_email'] ?? '',
                    $_POST['login_password'] ?? ''
                );

                // Nếu hàm trả về lỗi (chuỗi text), gán vào mảng errors
                if ($loginError) {
                    $errors['login'] = $loginError;
                }
            }

            // B. Nếu người dùng bấm nút ở Form Đăng Ký
            elseif (isset($_POST['register_email'])) {
                // Lưu lại dữ liệu cũ
                $oldInput['register_name']  = $_POST['register_name'] ?? '';
                $oldInput['register_email'] = $_POST['register_email'] ?? '';

                // Gọi hàm xử lý logic đăng ký (xem bên dưới)
                $registerError = $this->handleRegister($_POST);

                // Nếu có lỗi thì gán vào mảng errors
                if ($registerError) {
                    $errors['register'] = $registerError;
                }
            }
        }

        // --- BƯỚC 3: GỌI VIEW HIỂN THỊ ---
        // Truyền biến $errors và $oldInput sang file View để sử dụng
        require __DIR__ . '/../../views/pages/auth_index.php';
    }

    // =========================================================================
    // KHU VỰC CÁC HÀM XỬ LÝ LOGIC (PRIVATE METHODS)
    // Các hàm này để private vì chỉ dùng nội bộ trong class này, không gọi từ ngoài.
    // =========================================================================

    /**
     * Xử lý Logic Đăng nhập
     * @return string|null : Trả về chuỗi lỗi nếu thất bại, hoặc chuyển hướng nếu thành công.
     */
    private function handleLogin($email, $password)
    {
        // [QUAN TRỌNG] Gọi method login từ UserModel (OOP)
        // Thay vì gọi hàm lẻ loginUser($conn...) như cũ
        $user = $this->userModel->login($email, $password);

        if ($user) {
            // Đăng nhập thành công -> Lưu thông tin quan trọng vào Session
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role']; // Lưu quyền để phân quyền Admin/User

            // Điều hướng trình duyệt về trang chủ
            header('Location: /DACS/public/index.php');
            exit; // Dừng code ngay lập tức sau khi header location
        }

        // Trả về thông báo lỗi nếu login thất bại
        return 'Email hoặc mật khẩu không đúng.';
    }

    /**
     * Xử lý Logic Đăng ký
     * @param array $data : Mảng chứa dữ liệu $_POST
     */
    private function handleRegister($data)
    {
        // Lấy dữ liệu an toàn bằng toán tử null coalescing (??)
        // Nghĩa là: Nếu không tồn tại thì lấy chuỗi rỗng ''
        $name    = $data['register_name']     ?? '';
        $email   = $data['register_email']    ?? '';
        $pass    = $data['register_password'] ?? '';
        $confirm = $data['confirm_password']  ?? '';

        // Validation cơ bản: Kiểm tra mật khẩu nhập lại
        if ($pass !== $confirm) {
            return 'Mật khẩu xác nhận không khớp.';
        }

        // [QUAN TRỌNG] Gọi method register từ UserModel (OOP)
        $result = $this->userModel->register($name, $email, $pass);

        // Kiểm tra kết quả trả về từ Model
        // Nếu là số (numeric) -> Đó là ID của user mới -> Thành công
        if (is_numeric($result)) {
            // Tự động đăng nhập cho người dùng luôn
            $_SESSION['user_id']   = $result;
            $_SESSION['user_name'] = $name;

            header('Location: /DACS/public/index.php');
            exit;
        }

        // Nếu không phải số -> Đó là thông báo lỗi (VD: "Email đã tồn tại")
        return $result;
    }

    /**
     * Xử lý Đăng xuất
     */
    public function logout()
    {
        // Xoá sạch mảng Session
        $_SESSION = [];
        
        // Hủy session hoàn toàn trên server
        session_destroy();

        // Quay về trang chủ
        header('Location: /DACS/public/index.php');
        exit;
    }
}
?>