<?php
// File: src/Models/Product.php

// Nhúng file cấu hình DB và Helper xử lý ảnh
require_once __DIR__ . '/../Config/db.php';
require_once __DIR__ . '/../Helpers/image_helper.php';

/* ==========================================================================
   KHU VỰC 1: FRONTEND (DÀNH CHO KHÁCH HÀNG)
   Các hàm lấy dữ liệu để hiển thị ra trang web
   ========================================================================== */

/**
 * 1. Lấy danh sách sản phẩm theo danh mục (Có phân trang)
 * @param object $conn Kết nối DB
 * @param string $category Tên danh mục (anime, gundam...)
 * @param int $limit Số lượng lấy
 * @param int $offset Vị trí bắt đầu
 */
function getProductsByCategory($conn, $category, $limit = 10, $offset = 0) {
    $sql = "SELECT id, name, price, image_url, category, overview AS series, details AS brand, note AS scale
            FROM products
            WHERE category = ?
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $category, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    if ($result && $result->num_rows > 0) {
        $products = $result->fetch_all(MYSQLI_ASSOC);
        // Chuẩn hóa link ảnh cho đẹp
        foreach ($products as &$p) {
            if (isset($p['image_url'])) {
                $p['image_url'] = normalizeImageUrl($p['image_url']);
                $p['img_url'] = $p['image_url']; // Alias cho view nào dùng biến này
            }
        }
    }
    $stmt->close();
    return $products;
}

/**
 * 2. Đếm tổng số sản phẩm trong danh mục
 * (Dùng để tính toán số trang hiển thị)
 */
function countProductsByCategory($conn, $category) {
    $sql = "SELECT COUNT(*) as total FROM products WHERE category = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result['total'] ?? 0;
}

/**
 * 3. Lấy các sản phẩm nổi bật (Mới nhất)
 */
function getFeaturedProducts($conn, $limit = 10) {
    $sql = "SELECT id, name, price, image_url AS img_url, category 
            FROM products 
            ORDER BY id DESC 
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $row['img_url'] = normalizeImageUrl($row['img_url']);
        $row['image_url'] = $row['img_url'];
        $products[] = $row;
    }
    $stmt->close();
    return $products;
}

/**
 * 4. Lấy chi tiết 1 sản phẩm theo ID
 */
function getProductById($conn, $id) {
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($product) {
        $product['image_url'] = normalizeImageUrl($product['image_url']);
        $product['img_url'] = $product['image_url']; 
    }
    return $product;
}

/**
 * 5. Lấy sản phẩm liên quan (Cùng danh mục, trừ chính nó)
 */
function getRelatedProducts($conn, $category, $excludeId, $limit = 4) {
    $sql = "SELECT id, name, price, image_url, category 
            FROM products 
            WHERE category = ? AND id != ? 
            ORDER BY RAND() 
            LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $category, $excludeId, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $row['image_url'] = normalizeImageUrl($row['image_url']);
        $row['img_url'] = $row['image_url'];
        $products[] = $row;
    }
    $stmt->close();
    return $products;
}

/**
 * 6. Lấy danh sách ảnh phụ (Gallery)
 * (Hàm này dùng chung cho cả Frontend xem ảnh và Admin sửa ảnh)
 */
function getProductImages($conn, $productId) {
    // Kiểm tra bảng tồn tại để tránh lỗi nếu chưa tạo bảng
    $check = $conn->query("SHOW TABLES LIKE 'product_images'");
    if ($check->num_rows == 0) return [];

    $sql = "SELECT id, image_url FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $images = [];
    while ($row = $result->fetch_assoc()) {
        $row['image_url'] = normalizeImageUrl($row['image_url']);
        $images[] = $row;
    }
    $stmt->close();
    return $images;
}


/* ==========================================================================
   KHU VỰC 2: ADMIN (DÀNH CHO QUẢN TRỊ VIÊN)
   Các hàm Thêm, Sửa, Xóa (CRUD)
   ========================================================================== */

/**
 * A. Lấy toàn bộ sản phẩm (Cho trang Manage Products)
 */
function getAllProducts($conn) {
    $sql = "SELECT * FROM products ORDER BY id DESC";
    $result = $conn->query($sql);
    if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

/**
 * B. Thêm sản phẩm mới (Kèm ảnh phụ)
 */
function addProduct($conn, $name, $category, $price, $mainImg, $extraImgs = []) {
    // 1. Insert thông tin chính
    $sql = "INSERT INTO products (name, category, price, image_url) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssis', $name, $category, $price, $mainImg);
    
    if ($stmt->execute()) {
        $newId = $stmt->insert_id;
        $stmt->close();

        // 2. Insert ảnh phụ (nếu có)
        if (!empty($extraImgs)) {
            addProductExtraImages($conn, $newId, $extraImgs);
        }
        return $newId;
    }
    return false;
}

/**
 * C. Cập nhật sản phẩm
 * @param string|null $newImage Đường dẫn ảnh mới (nếu null thì giữ ảnh cũ)
 */
function updateProduct($conn, $id, $name, $category, $price, $newImage = null) {
    if ($newImage) {
        // Có thay đổi ảnh đại diện
        $sql = "UPDATE products SET name = ?, category = ?, price = ?, image_url = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssisi", $name, $category, $price, $newImage, $id);
    } else {
        // Giữ nguyên ảnh cũ
        $sql = "UPDATE products SET name = ?, category = ?, price = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $name, $category, $price, $id);
    }
    return $stmt->execute();
}

/**
 * D. Xóa sản phẩm hoàn toàn (Xóa cả ảnh phụ liên quan)
 */
function deleteProduct($conn, $id) {
    // Xóa ảnh phụ trước (để sạch DB)
    $conn->query("DELETE FROM product_images WHERE product_id = $id");

    // Xóa sản phẩm chính
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

/* ==========================================================================
   KHU VỰC 3: HÀM HỖ TRỢ XỬ LÝ ẢNH PHỤ (ADMIN)
   ========================================================================== */

/**
 * Thêm danh sách ảnh phụ cho 1 sản phẩm
 */
function addProductExtraImages($conn, $productId, $extraImgs) {
    if (empty($extraImgs)) return true;

    $sql = "INSERT INTO product_images (product_id, image_url, sort_order) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $order = 1;
    
    foreach ($extraImgs as $img) {
        $stmt->bind_param("isi", $productId, $img, $order);
        $stmt->execute();
        $order++;
    }
    $stmt->close();
    return true;
}

/**
 * Xóa 1 ảnh phụ cụ thể (Dùng trong trang Edit Product)
 */
function deleteProductImageById($conn, $imageId) {
    $sql = "DELETE FROM product_images WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $imageId);
    return $stmt->execute();
}
?>