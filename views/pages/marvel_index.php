<?php
// Controller đã truyền qua biến $products và $conn (nếu cần)
// Controller cũng đã session_start() rồi
$isLoggedIn = isset($_SESSION['user_id']); // Kiểm tra lại biến này cho chắc
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marvel Figure Store - FigureWorld</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/DACS/public/assets/css/page.css">
    <link rel="stylesheet" href="/DACS/views/layouts/header.css">
</head>
<body data-logged-in="<?= $isLoggedIn ? '1' : '0'; ?>">
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <!-- Loading overlay -->
    <div class="loading" id="loading">
        <div class="spinner"></div>
    </div>

    <main class="main-content">
        <!-- HERO -->
        <div class="hero-banner">
            <div class="hero-content">
                <h1 class="hero-title">Marvel Figure Collection</h1>
                <p class="hero-subtitle">
                    Khám phá mô hình Marvel chính hãng từ Hot Toys, Sideshow, Kotobukiya
                    và nhiều thương hiệu khác
                </p>

                <div class="hero-stats">
                    <div class="hero-stat">
                        <span class="hero-stat-number">200+</span>
                        <span class="hero-stat-label">Marvel Figures</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-number">30+</span>
                        <span class="hero-stat-label">Nhân vật</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-number">100%</span>
                        <span class="hero-stat-label">Chính hãng</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- DÒNG SẢN PHẨM NỔI BẬT -->
        <section class="popular-series">
            <div class="section-container">
                <div class="section-header">
                    <h2 class="section-title">Dòng sản phẩm nổi bật</h2>
                    <p class="section-subtitle">
                        Chọn dòng yêu thích để khám phá bộ sưu tập figure
                    </p>
                </div>

                <div class="series-grid">
                    <!-- Dùng button thay vì a href="#" -->
                    <button type="button" class="series-card" onclick="filterBySeries('avengers')">
                        <div class="series-image avengers">
                            <img src="/DACS/public/assets/img/avengers.webp" alt="Avengers">
                        </div>
                        <div class="series-info">
                            <div class="series-name">Avengers</div>
                        </div>
                    </button>

                    <button type="button" class="series-card" onclick="filterBySeries('iron-man')">
                        <div class="series-image iron-man">
                            <img src="/DACS/public/assets/img/iron-man.png" alt="Iron Man">
                        </div>
                        <div class="series-info">
                            <div class="series-name">Iron Man</div>
                        </div>
                    </button>

                    <button type="button" class="series-card" onclick="filterBySeries('spider-man')">
                        <div class="series-image spider-man">
                            <img src="/DACS/public/assets/img/spider-man.png" alt="Spider-Man">
                        </div>
                        <div class="series-info">
                            <div class="series-name">Spider-Man</div>
                        </div>
                    </button>

                    <button type="button" class="series-card" onclick="filterBySeries('guardians')">
                        <div class="series-image guardians">
                            <img src="/DACS/public/assets/img/guardians-of-the-galaxy.png" alt="Guardians of the Galaxy">
                        </div>
                        <div class="series-info">
                            <div class="series-name">Guardians of the Galaxy</div>
                        </div>
                    </button>

                    <button type="button" class="series-card" onclick="filterBySeries('black-panther')">
                        <div class="series-image black-panther">
                            <img src="/DACS/public/assets/img/black-panther.png" alt="Black Panther">
                        </div>
                        <div class="series-info">
                            <div class="series-name">Black Panther</div>
                        </div>
                    </button>
                </div>
            </div>
        </section>

        <!-- SHOP + BỘ LỌC -->
        <div class="shop-container">
            <!-- Nút mở sidebar -->
            <button class="filter-toggle" type="button" onclick="toggleSidebar()">
                <i class="fas fa-filter"></i>
                Bộ lọc &amp; Tìm kiếm
            </button>

            <!-- SIDEBAR -->
            <aside class="sidebar" id="sidebar">
                <!-- Dòng / Franchise -->
                <div class="filter-section">
                    <div class="filter-header">
                        <span>Dòng/Franchise</span>
                        <i class="fas fa-chevron-up"></i>
                    </div>
                    <div class="filter-content">
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="avengers" value="avengers">
                            <span>Avengers</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="iron-man" value="iron-man">
                            <span>Iron Man</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="spider-man" value="spider-man">
                            <span>Spider-Man</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="x-men" value="x-men">
                            <span>X-Men</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="guardians" value="guardians">
                            <span>Guardians of the Galaxy</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="black-panther" value="black-panther">
                            <span>Black Panther</span>
                        </div>
                    </div>
                </div>

                <!-- Loại Figure -->
                <div class="filter-section">
                    <div class="filter-header">
                        <span>Loại Figure</span>
                        <i class="fas fa-chevron-up"></i>
                    </div>
                    <div class="filter-content">
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="hot-toys" value="hot-toys">
                            <span>Hot Toys 1/6</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="sideshow" value="sideshow">
                            <span>Sideshow Collectibles</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="kotobukiya" value="kotobukiya">
                            <span>Kotobukiya ARTFX</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="marvel-legends" value="marvel-legends">
                            <span>Marvel Legends</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="statue" value="statue">
                            <span>Statue</span>
                        </div>
                    </div>
                </div>

                <!-- Tỷ lệ -->
                <div class="filter-section">
                    <div class="filter-header">
                        <span>Tỷ lệ</span>
                        <i class="fas fa-chevron-up"></i>
                    </div>
                    <div class="filter-content">
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="1-6" value="1-6">
                            <span>1/6 Scale</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="1-7" value="1-7">
                            <span>1/7 Scale</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="1-10" value="1-10">
                            <span>1/10 Scale</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="1-12" value="1-12">
                            <span>1/12 Scale</span>
                        </div>
                    </div>
                </div>

                <!-- Khoảng giá -->
                <div class="filter-section">
                    <div class="filter-header">
                        <span>Khoảng giá</span>
                        <i class="fas fa-chevron-up"></i>
                    </div>
                    <div class="filter-content">
                        <div class="price-range">
                            <div class="price-inputs">
                                <input type="number" class="price-input" placeholder="Từ" id="minPrice">
                                <input type="number" class="price-input" placeholder="Đến" id="maxPrice">
                            </div>
                        </div>

                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="under-1m" value="under-1m">
                            <span>Dưới 1,000,000₫</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="1m-3m" value="1m-3m">
                            <span>1,000,000₫ - 3,000,000₫</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="3m-5m" value="3m-5m">
                            <span>3,000,000₫ - 5,000,000₫</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="above-5m" value="above-5m">
                            <span>Trên 5,000,000₫</span>
                        </div>
                    </div>
                </div>

                <!-- Tình trạng -->
                <div class="filter-section">
                    <div class="filter-header">
                        <span>Tình trạng</span>
                        <i class="fas fa-chevron-up"></i>
                    </div>
                    <div class="filter-content">
                        <div class="filter-checkbox">
                            <input type="checkbox" id="in-stock" value="in-stock">
                            <span>Còn hàng</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" id="preorder" value="preorder">
                            <span>Pre-order</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" id="limited" value="limited">
                            <span>Limited Edition</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" id="exclusive" value="exclusive">
                            <span>Exclusive</span>
                        </div>
                    </div>
                </div>

                <button class="clear-filters" type="button" onclick="clearAllFilters()">
                    <i class="fas fa-times" aria-hidden="true"></i>
                    <span>Xóa tất cả bộ lọc</span>
                </button>
            </aside>

            <!-- KHU VỰC SẢN PHẨM -->
            <div class="content-area">
                <div class="shop-controls">
                    <div class="results-info">
                        Hiển thị <strong id="resultCount"><?= count($products ?? []) ?></strong> sản phẩm
                    </div>

                    <div class="controls-right">
                        <div class="view-toggle">
                            <button class="view-btn active" type="button" data-view="grid">
                                <i class="fas fa-th"></i>
                            </button>
                            <button class="view-btn" type="button" data-view="list">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>

                        <select class="sort-select" id="sortSelect">
                            <option value="featured">Nổi bật</option>
                            <option value="newest">Mới nhất</option>
                            <option value="price-low">Giá thấp đến cao</option>
                            <option value="price-high">Giá cao đến thấp</option>
                            <option value="rating">Đánh giá cao</option>
                            <option value="popular">Phổ biến</option>
                        </select>
                    </div>
                </div>

                <div class="products-grid" id="productsGrid">
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $p): ?>
                            
                            <?php include __DIR__ . '/../layouts/product_card.php'; ?>
                            
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Hiện chưa có sản phẩm nào.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>
    <script src="/DACS/public/assets/js/page.js"></script>
</body>
</html>