<?php
session_start();
// Gọi Model
require_once __DIR__ . '/../src/Models/Product.php';

$isLoggedIn = isset($_SESSION['user_id']);

// Lấy sản phẩm nổi bật
$featuredProducts = getFeaturedProducts($conn, 10);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FigureWorld - Thế giới Figure chính hãng</title>

    <!-- CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="../views/layouts/header.css">
</head>
<body data-logged-in="<?= $isLoggedIn ? '1' : '0'; ?>">
    <!-- Loading overlay -->
    <div class="loading" id="loading">
        <div class="spinner"></div>
    </div>

    <?php include __DIR__ . '/../views/layouts/header.php'; ?>

    <!-- HERO -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1>Thế giới Figure chính hãng</h1>
                <p>
                    Khám phá bộ sưu tập figure anime, gundam và marvel đa dạng với chất lượng cao.
                    Giao hàng toàn quốc, đảm bảo hàng chính hãng 100%.
                </p>
                <div class="cta-buttons">
                    <a href="#products" class="cta-btn cta-primary">
                        <i class="fas fa-shopping-bag"></i>
                        Mua ngay
                    </a>
                    <a href="#categories" class="cta-btn cta-secondary">
                        <i class="fas fa-list"></i>
                        Xem danh mục
                    </a>
                </div>
            </div>

            <div class="hero-visual">
                <div class="floating-figures">
                    <div class="figure-card">
                        <img src="assets/img/luffy-logo.png" alt="Anime logo">
                        <h3>Anime</h3>
                    </div>
                    <div class="figure-card">
                        <img src="assets/img/gundam-logo.png" alt="Gundam logo">
                        <h3>Gundam</h3>
                    </div>
                    <div class="figure-card">
                        <img src="assets/img/marvel-logo.png" alt="Marvel logo">
                        <h3>Marvel</h3>
                    </div>
                    <div class="figure-card">
                        <i class="fas fa-star"></i>
                        <h3>Limited</h3>
                    </div>
                    <div class="figure-card">
                        <i class="fas fa-fire"></i>
                        <h3>Hot</h3>
                        <p>Bán chạy</p>
                    </div>
                    <div class="figure-card">
                        <i class="fas fa-gift"></i>
                        <h3>Sale</h3>
                        <p>Giảm 50%</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- DANH MỤC -->
    <section class="categories" id="categories">
        <div class="section-container">
            <div class="section-header">
                <h2 class="section-title">Danh mục sản phẩm</h2>
                <p class="section-subtitle">
                    Khám phá các dòng figure chính hãng từ những thương hiệu uy tín nhất thế giới
                </p>
            </div>

            <div class="category-grid">
                <a class="category-card" href="../views/pages/anime_index.php">
                    <div class="category-image">
                        <img src="assets/img/anime(2).webp" alt="Anime">
                    </div>
                    <div class="category-content">
                        <h3 class="category-title">Figure Anime</h3>
                        <p class="category-description">
                            Bộ sưu tập figure từ các anime nổi tiếng như Naruto, One Piece, Dragon Ball...
                        </p>
                        <div class="category-stats">
                            <span class="product-count"></span>
                            <i class="fas fa-arrow-right category-arrow"></i>
                        </div>
                    </div>
                </a>

                <a class="category-card" href="../views/pages/gundam_index.php">
                    <div class="category-image">
                        <img src="assets/img/gundam.jpg" alt="Gundam">
                    </div>
                    <div class="category-content">
                        <h3 class="category-title">Gundam Model Kit</h3>
                        <p class="category-description">
                            Mô hình Gundam chính hãng Bandai với đầy đủ các series từ UC đến AU
                        </p>
                        <div class="category-stats">
                            <span class="product-count"></span>
                            <i class="fas fa-arrow-right category-arrow"></i>
                        </div>
                    </div>
                </a>

                <a class="category-card" href="../views/pages/marvel_index.php">
                    <div class="category-image">
                        <img src="assets/img/marvel.jpg" alt="Marvel">
                    </div>
                    <div class="category-content">
                        <h3 class="category-title">Marvel Figures</h3>
                        <p class="category-description">
                            Siêu anh hùng Marvel với chất lượng cao từ Hot Toys, Sideshow...
                        </p>
                        <div class="category-stats">
                            <span class="product-count"></span>
                            <i class="fas fa-arrow-right category-arrow"></i>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- SẢN PHẨM NỔI BẬT -->
    <section class="featured-products" id="products">
        <div class="section-container">
            <div class="section-header">
                <h2 class="section-title">Sản phẩm nổi bật</h2>
                <p class="section-subtitle">
                    Những mô hình figure được yêu thích nhất từ cộng đồng collector
                </p>
            </div>

            <div class="products-grid" id="productsGrid">
                <?php $featuredProducts = $featuredProducts ?? []; ?>
                <?php if (!empty($featuredProducts)): ?>
                    <?php foreach ($featuredProducts as $row): ?>
                        <div class="product-card"
                             data-id="<?= (int)$row['id']; ?>"
                             data-name="<?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?>"
                             data-price="<?= (float)$row['price']; ?>">

                            <a href="../views/pages/product.php?id=<?= (int)$row['id']; ?>" class="product-image-link">
                                <div class="product-image">
                                    <img src="<?= htmlspecialchars(normalizeImageUrl($row['img_url']), ENT_QUOTES, 'UTF-8'); ?>"
                                         alt="<?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                            </a>

                            <div class="product-info">
                                <h3 class="product-title">
                                    <?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?>
                                </h3>
                                <div class="product-price">
                                    <?= number_format((float)$row['price'], 0, ',', '.'); ?>₫
                                </div>
                                <div class="product-actions">
                                    <button type="button" class="btn add-to-cart">
                                        <i class="fas fa-cart-plus"></i>
                                        Thêm vào giỏ
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Hiện chưa có sản phẩm nổi bật nào.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/../views/layouts/footer.php'; ?>

</body>
</html>