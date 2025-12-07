<?php
// File: src/Models/Product.php
require_once __DIR__ . '/../Config/db.php';
require_once __DIR__ . '/../Helpers/image_helper.php';

/**
 * 1. Lấy danh sách sản phẩm theo danh mục (Dùng cho trang Anime, Gundam...)
 */
function getProductsByCategory($conn, $category, $limit = 100) {
    // Lấy image_url gốc từ DB
    $sql = "SELECT id, name, price, image_url, category, overview AS series, details AS brand, note AS scale
            FROM products
            WHERE category = ?
            ORDER BY created_at DESC
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $category, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    if ($result && $result->num_rows > 0) {
        $products = $result->fetch_all(MYSQLI_ASSOC);
        foreach ($products as &$p) {
            if (isset($p['image_url'])) {
                $p['image_url'] = normalizeImageUrl($p['image_url']);
                // Tự động tạo thêm key img_url để tương thích với mọi view cũ/mới
                $p['img_url'] = $p['image_url']; 
            }
        }
    }
    $stmt->close();
    return $products;
}

/**
 * 2. Lấy sản phẩm nổi bật (Dùng cho trang chủ index.php)
 */
function getFeaturedProducts($conn, $limit = 10) {
    // Trang chủ đang dùng 'img_url', ta alias ngay trong SQL
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
        // Tạo thêm image_url để đồng bộ
        $row['image_url'] = $row['img_url'];
        $products[] = $row;
    }
    $stmt->close();
    return $products;
}

/**
 * 3. Lấy chi tiết 1 sản phẩm (Dùng cho trang product.php)
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
        // FIX QUAN TRỌNG: Tạo thêm 'img_url' để view product.php không bị lỗi
        $product['img_url'] = $product['image_url']; 
    }
    return $product;
}

/**
 * 4. Lấy danh sách ảnh phụ (Gallery) - MỚI THÊM
 */
function getProductImages($conn, $productId, $mainImage) {
    // Kiểm tra bảng product_images xem có tồn tại không
    // Nếu bạn chưa tạo bảng này, hàm sẽ trả về mảng chỉ chứa ảnh chính
    $sql = "SHOW TABLES LIKE 'product_images'";
    $result = $conn->query($sql);
    
    $images = [];
    
    if ($result && $result->num_rows > 0) {
        $sqlImg = "SELECT image_url FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC";
        $stmt = $conn->prepare($sqlImg);
        if ($stmt) {
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                if (!empty($row['image_url'])) {
                    $images[] = normalizeImageUrl($row['image_url']);
                }
            }
            $stmt->close();
        }
    }
    
    // Luôn đảm bảo có ít nhất 1 ảnh (ảnh chính) để hiển thị
    if (empty($images) && !empty($mainImage)) {
        $images[] = $mainImage;
    }
    
    return $images;
}

/**
 * 5. Lấy sản phẩm liên quan (Cùng danh mục) - MỚI THÊM
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
        $row['img_url'] = $row['image_url']; // Đồng bộ
        $products[] = $row;
    }
    $stmt->close();
    return $products;
}
/**
 * [ADMIN] Lấy toàn bộ sản phẩm để quản lý
 */
function getAllProducts($conn) {
    $sql = "SELECT * FROM products ORDER BY id DESC";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * [ADMIN] Xóa sản phẩm
 */
function deleteProduct($conn, $id) {
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}
?>