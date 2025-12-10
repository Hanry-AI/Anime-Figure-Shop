<?php
// Controller cần truyền qua các biến: $products, $totalPages, $page
// Nếu Controller chưa truyền $page và $totalPages thì đoạn code phân trang bên dưới sẽ tự ẩn đi để không gây lỗi.
$isLoggedIn = isset($_SESSION['user_id']); 
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gundam Model Store - FigureWorld</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/DACS/public/assets/css/page.css">
    <link rel="stylesheet" href="/DACS/views/layouts/header.css">

    <style>
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 40px;
            padding-bottom: 20px;
        }
        .pagination {
            display: flex;
            list-style: none;
            gap: 8px;
            padding: 0;
        }
        .page-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            color: #374151;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            background: white;
        }
        .page-link:hover {
            background-color: #f3f4f6;
            color: #2563eb;
            border-color: #2563eb;
        }
        .page-link.active {
            background-color: #2563eb;
            color: white;
            border-color: #2563eb;
        }
    </style>
</head>
<body data-logged-in="<?= $isLoggedIn ? '1' : '0'; ?>">
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <div class="loading" id="loading">
        <div class="spinner"></div>
    </div>

    <main class="main-content">
        <div class="hero-banner">
        <div class="hero-banner gundam-bg">
        </div>

        <section class="popular-series">
            <div class="section-container">
                <div class="section-header">
                    <h2 class="section-title">Series Gundam Nổi Tiếng</h2>
                    <p class="section-subtitle">
                        Chọn series yêu thích để khám phá bộ sưu tập mô hình Gundam
                    </p>
                </div>

                <div class="series-grid">
                    <button type="button" class="series-card" onclick="filterBySeries('mobile-suit-gundam')">
                        <div class="series-image mobile-suit-gundam">
                            <img src="/DACS/public/assets/img/mobile-suit-gundam.jpg" alt="Mobile Suit Gundam">
                        </div>
                        <div class="series-info">
                            <div class="series-name">Mobile Suit Gundam</div>
                        </div>
                    </button>

                    <button type="button" class="series-card" onclick="filterBySeries('gundam-wing')">
                        <div class="series-image gundam-wing">
                            <img src="/DACS/public/assets/img/gundam-wing.png" alt="Gundam Wing">
                        </div>
                        <div class="series-info">
                            <div class="series-name">Gundam Wing</div>
                        </div>
                    </button>

                    <button type="button" class="series-card" onclick="filterBySeries('gundam-seed')">
                        <div class="series-image gundam-seed">
                            <img src="/DACS/public/assets/img/gundam-seed.png" alt="Gundam SEED">
                        </div>
                        <div class="series-info">
                            <div class="series-name">Gundam SEED</div>
                        </div>
                    </button>

                    <button type="button" class="series-card" onclick="filterBySeries('gundam-00')">
                        <div class="series-image gundam-00">
                            <img src="/DACS/public/assets/img/gundam00.png" alt="Gundam 00">
                        </div>
                        <div class="series-info">
                            <div class="series-name">Gundam 00</div>
                        </div>
                    </button>

                    <button type="button" class="series-card" onclick="filterBySeries('gundam-unicorn')">
                        <div class="series-image gundam-unicorn">
                            <img src="/DACS/public/assets/img/gundam-unicorn.png" alt="Gundam Unicorn">
                        </div>
                        <div class="series-info">
                            <div class="series-name">Gundam Unicorn</div>
                        </div>
                    </button>
                </div>
            </div>
        </section>

        <div class="shop-container">
            <button class="filter-toggle" type="button" onclick="toggleSidebar()">
                <i class="fas fa-filter"></i>
                Bộ lọc &amp; Tìm kiếm
            </button>

            <aside class="sidebar" id="sidebar">
                <div class="filter-section">
                    <div class="filter-header">
                        <span>Grade</span>
                        <i class="fas fa-chevron-up"></i>
                    </div>
                    <div class="filter-content">
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="hg" value="hg">
                            <span>HG (High Grade)</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="rg" value="rg">
                            <span>RG (Real Grade)</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="mg" value="mg">
                            <span>MG (Master Grade)</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="pg" value="pg">
                            <span>PG (Perfect Grade)</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="sd" value="sd">
                            <span>SD (Super Deformed)</span>
                        </div>
                    </div>
                </div>
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
                            <input type="checkbox" data-filter="series" id="under-500k" value="under-500k">
                            <span>Dưới 500,000₫</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="500k-1m" value="500k-1m">
                            <span>500,000₫ - 1,000,000₫</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="1m-2m" value="1m-2m">
                            <span>1,000,000₫ - 2,000,000₫</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="above-2m" value="above-2m">
                            <span>Trên 2,000,000₫</span>
                        </div>
                    </div>
                </div>

                <button class="clear-filters" type="button" onclick="clearAllFilters()">
                    <i class="fas fa-times" aria-hidden="true"></i>
                    <span>Xóa tất cả bộ lọc</span>
                </button>
            </aside>

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
                        <p style="padding: 20px; width: 100%;">Hiện chưa có sản phẩm nào.</p>
                    <?php endif; ?>
                </div>

                <?php if (isset($totalPages) && $totalPages > 1): ?>
                <div class="pagination-container">
                    <ul class="pagination">
                        <?php if (isset($page) && $page > 1): ?>
                            <li>
                                <a href="?page=gundam&page_num=<?= $page - 1 ?>" class="page-link">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li>
                                <a href="?page=gundam&page_num=<?= $i ?>" class="page-link <?= (isset($page) && $i == $page) ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if (isset($page) && $page < $totalPages): ?>
                            <li>
                                <a href="?page=gundam&page_num=<?= $page + 1 ?>" class="page-link">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
    </main>

    <section class="newsletter">
        <div class="section-container">
            <div class="newsletter-content">
                <h2>Đăng ký nhận tin tức</h2>
                <p>Nhận thông báo về sản phẩm mới, khuyến mãi và các sự kiện đặc biệt</p>

                <form class="newsletter-form">
                    <input type="email"
                           class="newsletter-input"
                           placeholder="Nhập email của bạn..."
                           required>
                    <button type="submit" class="newsletter-btn">
                        <i class="fas fa-paper-plane"></i>
                        Đăng ký
                    </button>
                </form>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/../layouts/footer.php'; ?>

    <script src="/DACS/public/assets/js/page.js"></script>
</body>
</html>