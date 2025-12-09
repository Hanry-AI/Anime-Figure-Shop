# FigureWorld - Dự án E-commerce Figure

Dự án website bán hàng figure Anime, Gundam và Marvel chính hãng. Mã nguồn PHP thuần theo mô hình Controller-View-Model đơn giản, router tự viết bằng query string.

## 📋 Yêu cầu hệ thống

- PHP >= 7.4
- MySQL >= 5.7
- Apache (khuyến nghị dùng XAMPP trên Windows) hoặc Nginx
- Composer (để autoload PSR-4)

## 🚀 Cài đặt nhanh

1) Clone dự án

```bash
git clone <repository-url>
cd DACS
```

2) Cấu hình Database

- Tạo database: `dacs2`
- Cập nhật file `src/Config/db.php` theo môi trường của bạn:

```php
$servername = 'localhost';
$username   = 'root';
$password   = '';
$dbname     = 'dacs2';
```

3) Cấu hình Web Server trỏ vào thư mục public/

- Với XAMPP (Windows): đặt dự án tại `D:/xampp/htdocs/DACS`
- Sửa `httpd.conf` hoặc cấu hình VirtualHost để DocumentRoot là `D:/xampp/htdocs/DACS/public`

VirtualHost mẫu:

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

Thêm hosts:

```
127.0.0.1 dacs.local
```

4) Cài dependencies PHP và autoload

```bash
composer install
```

Composer autoload (đã cấu hình sẵn):

```json
"autoload": { "psr-4": { "DACS\\": "src/" } }
```

Chạy lệnh sau mỗi khi thêm class/namespace mới:

```bash
composer dump-autoload
```

5) Phân quyền thư mục ảnh

- Ảnh tĩnh và ảnh upload để trong `public/assets/img/`. Đảm bảo thư mục có quyền ghi.

## 🗺️ Điều hướng (Router)

Ứng dụng sử dụng `public/index.php` làm router. Các route dùng query string `?page=...&action=...`:

- Trang chủ: `/DACS/public/index.php` hoặc `/?page=home`
- Đăng nhập/Đăng ký/Đăng xuất: `?page=auth` (action=logout để thoát)
- Danh mục Anime: `?page=anime`
- Danh mục Gundam: `?page=gundam` (hỗ trợ phân trang: `&page_num=2` ...)
- Danh mục Marvel: `?page=marvel`
- Chi tiết sản phẩm: `?page=product&id={product_id}`
- Liên hệ: `?page=contact`
- Khuyến mãi: `?page=promo`
- Hồ sơ cá nhân: `?page=profile` (yêu cầu đăng nhập)
- Giỏ hàng: `?page=cart` (lấy dữ liệu cart qua POST JSON)

## ✨ Tính năng chính

- Danh mục sản phẩm theo Anime/Gundam/Marvel, có phân trang ở Gundam
- Trang chi tiết sản phẩm kèm gallery ảnh và sản phẩm liên quan
- Giỏ hàng phía client gửi dữ liệu sang server để tính tổng tiền an toàn theo DB
- Đăng nhập/Đăng ký/Đăng xuất, trang hồ sơ người dùng
- Khu vực quản trị cơ bản cho sản phẩm (views/admin/*)

## 🗃️ Lược đồ database tham khảo

Tạo tối thiểu 3 bảng như sau (có thể điều chỉnh theo nhu cầu):

```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(50) DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  category ENUM('anime','gundam','marvel') NOT NULL,
  price INT NOT NULL,
  image_url VARCHAR(255) NOT NULL,
  overview TEXT NULL,
  details TEXT NULL,
  note TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE product_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  image_url VARCHAR(255) NOT NULL,
  sort_order INT DEFAULT 0,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
```

Lưu ý: code đã xử lý normalize đường dẫn ảnh để phù hợp khi lưu đường dẫn tương đối trong DB.

## 📁 Cấu trúc dự án

```
├── public/
│   ├── index.php            # Router chính
│   └── assets/              # CSS/JS/Images
├── src/
│   ├── Config/              # db.php, constants.php
│   ├── Controllers/         # Auth, Home, Page, Product, Cart
│   ├── Models/              # Product.php, User.php
│   └── Helpers/             # image_helper.php, format_helper.php
└── views/
    ├── layouts/             # header.php, footer.php, product_card.php
    ├── pages/               # index.php, anime_index.php, ...
    └── admin/               # add/edit/delete/manage products
```

## 🔧 Cấu hình bổ sung

- File cấu hình DB: `src/Config/db.php`
- Hằng số: `src/Config/constants.php` (tuỳ chọn)

## 🧪 Kiểm tra nhanh

- Truy cập: `http://dacs.local/` (nếu cấu hình vhost) hoặc `http://localhost/DACS/public/`
- Đảm bảo hiển thị danh mục và có thể đăng ký/đăng nhập.

## 👥 Tác giả

DACS Project Team

## 📄 License

MIT License

