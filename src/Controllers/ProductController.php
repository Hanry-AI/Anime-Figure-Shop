<?php
namespace DACS\Controllers;

// [QUAN TRỌNG] Chỉ use Namespace, không require file thủ công
use DACS\Models\ProductModel;

class ProductController {
    private $conn;
    private $productModel;

    public function __construct($db) {
        $this->conn = $db;
        // Composer tự tìm file ProductModel.php
        $this->productModel = new ProductModel($db);
    }

    public function indexAnime() {
        $products = $this->productModel->getProductsByCategory('anime');
        require_once __DIR__ . '/../../views/pages/anime_index.php';
    }

    public function indexGundam() {
        $limit = 10;
        $page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1; 
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;
    
        $products = $this->productModel->getProductsByCategory('gundam', $limit, $offset);
        $totalProducts = $this->productModel->countProductsByCategory('gundam');
        $totalPages = ceil($totalProducts / $limit);
    
        require_once __DIR__ . '/../../views/pages/gundam_index.php';
    }

    public function indexMarvel() {
        $products = $this->productModel->getProductsByCategory('marvel');
        require_once __DIR__ . '/../../views/pages/marvel_index.php';
    }

    public function detail() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $product = $this->productModel->getProductById($id);

        if (!$product) {
            header('Location: /DACS/public/index.php');
            exit;
        }

        $images = $this->productModel->getProductImages($id);
        $relatedProducts = $this->productModel->getRelatedProducts($product['category'], $id);
        
        $firstImg = $product['image_url']; 

        require_once __DIR__ . '/../../views/pages/product.php';
    }
}
?>