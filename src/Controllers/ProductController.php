<?php
namespace DACS\Controllers;

use DACS\Models\ProductModel;
use DACS\Core\Request;  // [MỚI] Sử dụng Class Request
use DACS\Core\View;     // [MỚI] Sử dụng Class View

class ProductController {
    private $conn;
    private $productModel;

    public function __construct($db) {
        $this->conn = $db;
        $this->productModel = new ProductModel($db);
    }

    /**
     * TRANG ANIME
     * @param Request $request Biến request được Router truyền vào
     */
    public function indexAnime(Request $request) {
        $products = $this->productModel->getProductsByCategory('anime');
        
        // [MỚI] Gọi View chuẩn OOP, truyền dữ liệu qua mảng
        View::render('pages/anime_index', [
            'products' => $products
        ]);
    }

    /**
     * TRANG GUNDAM (Có phân trang)
     */
    public function indexGundam(Request $request) {
        $limit = 10;
        
        // [MỚI] Thay thế $_GET['page_num'] bằng $request->get()
        // Hàm get() của bạn đã viết sẵn logic: nếu không có thì lấy giá trị mặc định (1)
        $page = (int)$request->get('page_num', 1);
        if ($page < 1) $page = 1;
        
        $offset = ($page - 1) * $limit;
    
        $products = $this->productModel->getProductsByCategory('gundam', $limit, $offset);
        $totalProducts = $this->productModel->countProductsByCategory('gundam');
        $totalPages = ceil($totalProducts / $limit);
    
        // [MỚI] Render View
        View::render('pages/gundam_index', [
            'products'      => $products,
            'totalPages'    => $totalPages,
            'page'          => $page
        ]);
    }

    /**
     * TRANG MARVEL
     */
    public function indexMarvel(Request $request) {
        $products = $this->productModel->getProductsByCategory('marvel');
        View::render('pages/marvel_index', ['products' => $products]);
    }

    /**
     * TRANG CHI TIẾT SẢN PHẨM
     */
    public function detail(Request $request) {
        // [MỚI] Lấy ID từ request object
        $id = (int)$request->get('id', 0);

        $product = $this->productModel->getProductById($id);

        if (!$product) {
            header('Location: /DACS/public/index.php');
            exit;
        }

        $images = $this->productModel->getProductImages($id);
        $relatedProducts = $this->productModel->getRelatedProducts($product['category'], $id);
        $firstImg = $product['image_url']; 

        // [MỚI] Render View
        View::render('pages/product', [
            'product'         => $product,
            'images'          => $images,
            'relatedProducts' => $relatedProducts,
            'firstImg'        => $firstImg
        ]);
    }
}
?>