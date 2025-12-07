# FigureWorld - Dự án E-commerce Figure

Dự án website bán hàng figure anime, Gundam và Marvel chính hãng.

## 📋 Yêu cầu hệ thống

- PHP >= 7.4
- MySQL >= 5.7
- Apache/Nginx với mod_rewrite
- Composer (tùy chọn, cho các thư viện nâng cao)

## 🚀 Cài đặt

### 1. Clone dự án

```bash
git clone <repository-url>
cd DACS
```

### 2. Cấu hình Database

- Tạo database MySQL với tên `dacs2`
- Chỉnh sửa file `src/Config/db.php` với thông tin kết nối của bạn:

```php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dacs2";
```

### 3. Cấu hình Web Server

#### XAMPP (Windows)

1. Đặt thư mục dự án vào `htdocs/DACS`
2. Cấu hình Document Root trỏ đến thư mục `public/`:
   - Mở `httpd.conf` trong XAMPP
   - Tìm `DocumentRoot` và đổi thành: `DocumentRoot "D:/xampp/htdocs/DACS/public"`
   - Tìm `<Directory>` tương ứng và đổi thành: `<Directory "D:/xampp/htdocs/DACS/public">`
   - Restart Apache

#### Hoặc sử dụng Virtual Host (Khuyến nghị)

Thêm vào `httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    ServerName dacs.local
    DocumentRoot "D:/xampp/htdocs/DACS/public"
    <Directory "D:/xampp/htdocs/DACS/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Thêm vào `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1    dacs.local
```

### 4. Cài đặt dependencies (Nếu có)

```bash
composer install
```

### 5. Cấu hình thư mục ảnh

Tất cả ảnh sẽ được lưu trực tiếp vào thư mục `public/assets/img/`. Đảm bảo thư mục này có quyền ghi.

## 📁 Cấu trúc dự án

```
├── .gitignore              # Loại bỏ file rác
├── README.md               # Tài liệu hướng dẫn
├── composer.json           # Quản lý thư viện PHP
├── public/                 # Document Root - Thư mục web công khai
│   ├── index.php           # Router chính
│   └── assets/             # CSS, JS, Images (ảnh upload cũng lưu tại đây)
├── src/                    # Logic PHP (Core)
│   ├── Config/             # Cấu hình (db.php, constants.php)
│   ├── Controllers/        # Xử lý logic
│   ├── Models/             # Tương tác Database
│   └── Helpers/            # Hàm tiện ích
└── views/              # Views (HTML/PHP)
    ├── layouts/            # header.php, footer.php
    ├── pages/              # Các trang
    └── admin/              # Trang quản trị
```

## 🔧 Cấu hình

### Database

File: `src/Config/db.php`

### Constants

File: `src/Config/constants.php` (tạo nếu cần)

## 📝 Ghi chú

- Đảm bảo thư mục `public/assets/img/` có quyền ghi để upload ảnh
- Kiểm tra kết nối database trước khi chạy
- Sử dụng `.env` cho production (khuyến nghị)

## 👥 Tác giả

DACS Project Team

## 📄 License

MIT License

