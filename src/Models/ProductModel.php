<?php
namespace DACS\Models;

// [QUAN TRỌNG] Composer tự nạp Class ImageHelper
use DACS\Helpers\ImageHelper;

class ProductModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db; 
    }

    /* ================= KHU VỰC CLIENT ================= */

    public function getProductsByCategory($category, $limit = 10, $offset = 0) {
        $sql = "SELECT id, name, price, image_url, category, overview AS series, details AS brand, note AS scale
                FROM products
                WHERE category = ?
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $category, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        if ($result && $result->num_rows > 0) {
            $products = $result->fetch_all(MYSQLI_ASSOC);
            foreach ($products as &$p) {
                if (isset($p['image_url'])) {
                    // Dòng này rất quan trọng: Gọi Helper để tạo đường dẫn đúng
                    $p['image_url'] = ImageHelper::normalizeUrl($p['image_url']);
                    $p['img_url'] = $p['image_url']; 
                }
            }
        }
        $stmt->close();
        return $products;
    }

    public function countProductsByCategory($category) {
        $sql = "SELECT COUNT(*) as total FROM products WHERE category = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result['total'] ?? 0;
    }

    public function getFeaturedProducts($limit = 10) {
        $sql = "SELECT id, name, price, image_url AS img_url, category 
                FROM products ORDER BY id DESC LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $row['img_url'] = ImageHelper::normalizeUrl($row['img_url']);
            $row['image_url'] = $row['img_url'];
            $products[] = $row;
        }
        $stmt->close();
        return $products;
    }

    public function getProductById($id) {
        $sql = "SELECT * FROM products WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($product) {
            $product['image_url'] = ImageHelper::normalizeUrl($product['image_url']);
            $product['img_url'] = $product['image_url']; 
        }
        return $product;
    }

    public function getRelatedProducts($category, $excludeId, $limit = 4) {
        $sql = "SELECT id, name, price, image_url, category 
                FROM products WHERE category = ? AND id != ? 
                ORDER BY RAND() LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $category, $excludeId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $row['image_url'] = ImageHelper::normalizeUrl($row['image_url']);
            $row['img_url'] = $row['image_url'];
            $products[] = $row;
        }
        $stmt->close();
        return $products;
    }

    public function getProductImages($productId) {
        $check = $this->conn->query("SHOW TABLES LIKE 'product_images'");
        if ($check->num_rows == 0) return [];

        $sql = "SELECT id, image_url FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $images = [];
        while ($row = $result->fetch_assoc()) {
            $row['image_url'] = ImageHelper::normalizeUrl($row['image_url']);
            $images[] = $row;
        }
        $stmt->close();
        return $images;
    }

    /* ================= KHU VỰC ADMIN ================= */

    public function getAllProducts() {
        $sql = "SELECT * FROM products ORDER BY id DESC";
        $result = $this->conn->query($sql);
        if ($result) return $result->fetch_all(MYSQLI_ASSOC);
        return [];
    }

    public function addProduct($name, $category, $price, $mainImg, $extraImgs = []) {
        $sql = "INSERT INTO products (name, category, price, image_url) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssis', $name, $category, $price, $mainImg);
        
        if ($stmt->execute()) {
            $newId = $stmt->insert_id;
            $stmt->close();
            if (!empty($extraImgs)) $this->addProductExtraImages($newId, $extraImgs);
            return $newId;
        }
        return false;
    }

    public function updateProduct($id, $name, $category, $price, $newImage = null) {
        if ($newImage) {
            $sql = "UPDATE products SET name = ?, category = ?, price = ?, image_url = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssisi", $name, $category, $price, $newImage, $id);
        } else {
            $sql = "UPDATE products SET name = ?, category = ?, price = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssii", $name, $category, $price, $id);
        }
        return $stmt->execute();
    }

    public function deleteProduct($id) {
        $this->conn->query("DELETE FROM product_images WHERE product_id = $id");
        $sql = "DELETE FROM products WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function addProductExtraImages($productId, $extraImgs) {
        if (empty($extraImgs)) return true;
        $sql = "INSERT INTO product_images (product_id, image_url, sort_order) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $order = 1;
        foreach ($extraImgs as $img) {
            $stmt->bind_param("isi", $productId, $img, $order);
            $stmt->execute();
            $order++;
        }
        $stmt->close();
        return true;
    }

    public function deleteProductImageById($imageId) {
        $sql = "DELETE FROM product_images WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $imageId);
        return $stmt->execute();
    }
}
?>