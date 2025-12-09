<?php
// File: src/Models/Product.php
require_once __DIR__ . '/../Config/db.php';
require_once __DIR__ . '/../Helpers/image_helper.php';

/**
 * 1. Lấy danh sách sản phẩm theo danh mục
 */
function getProductsByCategory($conn, $category, $limit = 10, $offset = 0) {
    $sql = "SELECT id, name, price, image_url, category, overview AS series, details AS brand, note AS scale
            FROM products
            WHERE category = ?
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?"; // <-- Thêm OFFSET
    
    $stmt = $conn->prepare($sql);
    // Sửa bind_param thành "sii" (string, int, int)
    $stmt->bind_param("sii", $category, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    if ($result && $result->num_rows > 0) {
        $products = $result->fetch_all(MYSQLI_ASSOC);
        foreach ($products as &$p) {
            if (isset($p['image_url'])) {
                $p['image_url'] = normalizeImageUrl($p['image_url']);
                $p['img_url'] = $p['image_url']; 
            }
        }
    }
    $stmt->close();
    return $products;
}
// 2. Thêm hàm mới để đếm tổng số sản phẩm (Dùng tính số trang)
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
 * 2. Lấy sản phẩm nổi bật
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
 * 3. Lấy chi tiết 1 sản phẩm
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
 * [QUAN TRỌNG] Đã XÓA hàm getProductImages cũ ở đây để tránh lỗi redeclare
 */

/**
 * 5. Lấy sản phẩm liên quan
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

/**
 * [ADMIN] Thêm sản phẩm mới
 */
function addProduct($conn, $name, $category, $price, $mainImg, $extraImgs = []) {
    // 1. Insert Product
    $sql = "INSERT INTO products (name, category, price, image_url) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssis', $name, $category, $price, $mainImg);
    
    if ($stmt->execute()) {
        $newId = $stmt->insert_id;
        $stmt->close();

        // 2. Insert Extra Images (nếu có)
        if (!empty($extraImgs)) {
            $sqlImg = "INSERT INTO product_images (product_id, image_url, sort_order) VALUES (?, ?, ?)";
            $stmtImg = $conn->prepare($sqlImg);
            $order = 1;
            foreach ($extraImgs as $img) {
                $stmtImg->bind_param('isi', $newId, $img, $order);
                $stmtImg->execute();
                $order++;
            }
            $stmtImg->close();
        }
        return $newId;
    }
    return false;
}

/**
 * [ADMIN] Cập nhật thông tin sản phẩm (đã thêm hàm updateProduct từ bước trước)
 */
function updateProduct($conn, $id, $name, $category, $price, $newImage = null) {
    if ($newImage) {
        $sql = "UPDATE products SET name = ?, category = ?, price = ?, image_url = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssisi", $name, $category, $price, $newImage, $id);
    } else {
        $sql = "UPDATE products SET name = ?, category = ?, price = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $name, $category, $price, $id);
    }
    return $stmt->execute();
}

/**
 * [CORE] Lấy danh sách ảnh phụ (Hàm này dùng chung cho cả Admin và Frontend)
 * - Trả về mảng chứa cả ID và URL
 */
function getProductImages($conn, $productId) {
    // Kiểm tra bảng tồn tại
    $sql = "SHOW TABLES LIKE 'product_images'";
    $result = $conn->query($sql);
    $images = [];

    if ($result && $result->num_rows > 0) {
        // Lấy cả ID để phục vụ việc xóa ảnh
        $sqlImg = "SELECT id, image_url FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC";
        $stmt = $conn->prepare($sqlImg);
        if ($stmt) {
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $row['image_url'] = normalizeImageUrl($row['image_url']); 
                $images[] = $row; // Lưu cả id và url
            }
            $stmt->close();
        }
    }
    return $images;
}

/**
 * [ADMIN] Xóa 1 ảnh phụ
 */
function deleteProductImageById($conn, $imageId) {
    $sql = "DELETE FROM product_images WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $imageId);
    return $stmt->execute();
}

/**
 * [ADMIN] Thêm loạt ảnh phụ mới cho sản phẩm cũ
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
    return true;
}
?>