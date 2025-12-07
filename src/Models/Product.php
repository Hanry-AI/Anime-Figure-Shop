<?php
// File: src/Models/Product.php
require_once __DIR__ . '/../Config/db.php';
require_once __DIR__ . '/../Helpers/image_helper.php';

/**
 * Lấy danh sách sản phẩm theo danh mục
 */
function getProductsByCategory($conn, $category, $limit = 100) {
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
        // Xử lý chuẩn hóa ảnh ngay tại đây, giao diện không cần lo nữa
        foreach ($products as &$p) {
            if (isset($p['image_url'])) {
                $p['image_url'] = normalizeImageUrl($p['image_url']);
            }
        }
    }
    $stmt->close();
    return $products;
}

/**
 * Lấy sản phẩm nổi bật (cho trang chủ)
 */
function getFeaturedProducts($conn, $limit = 10) {
    $sql = "SELECT id, name, price, image_url AS img_url 
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
        $products[] = $row;
    }
    $stmt->close();
    return $products;
}

/**
 * Lấy chi tiết 1 sản phẩm
 */
function getProductById($conn, $id) {
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($product) {
        $product['image_url'] = normalizeImageUrl($product['image_url']); // hoặc img_url tùy DB
    }
    return $product;
}
?>