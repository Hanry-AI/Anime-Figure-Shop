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
    <title>Anime Figure Shop - FigureWorld</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/DACS/public/assets/css/page.css">
    <link rel="stylesheet" href="../layouts/header.css">
</head>
<body data-logged-in="<?= $isLoggedIn ? '1' : '0'; ?>">
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <!-- Loading overlay -->
    <div class="loading" id="loading">
        <div class="spinner"></div>
    </div>

    <main class="main-content">
        <!-- Hero banner -->
        <div class="hero-banner">
            <div class="hero-content">
                <h1 class="hero-title">Anime Figure Collection</h1>
                <p class="hero-subtitle">
                    Khám phá thế giới figure anime với hàng ngàn mô hình từ các series anime nổi tiếng nhất
                </p>

                <div class="hero-stats">
                    <div class="hero-stat">
                        <span class="hero-stat-number">500+</span>
                        <span class="hero-stat-label">Figure Anime</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-number">50+</span>
                        <span class="hero-stat-label">Anime Series</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-number">100%</span>
                        <span class="hero-stat-label">Chính hãng</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Series nổi tiếng -->
        <section class="popular-series">
            <div class="section-container">
                <div class="section-header">
                    <h2 class="section-title">Series Anime Nổi Tiếng</h2>
                    <p class="section-subtitle">Chọn series yêu thích để khám phá bộ sưu tập figure</p>
                </div>

                <div class="series-grid">
                    <!-- Dùng button gọi JS, không dùng javascript:void(0) -->
                    <button type="button" class="series-card" onclick="filterBySeries('naruto')">
                        <div class="series-image naruto">
                            <img src="/DACS/public/assets/img/naruto.png" alt="Naruto">
                        </div>
                        <div class="series-info">
                            <div class="series-name">Naruto</div>
                        </div>
                    </button>

                    <button type="button" class="series-card" onclick="filterBySeries('one-piece')">
                        <div class="series-image one-piece">
                            <img src="/DACS/public/assets/img/luffy-logo.png" alt="One Piece">
                        </div>
                        <div class="series-info">
                            <div class="series-name">One Piece</div>
                        </div>
                    </button>

                    <button type="button" class="series-card" onclick="filterBySeries('dragon-ball')">
                        <div class="series-image dragon-ball">
                            <img src="/DACS/public/assets/img/dragon-ball.png" alt="Dragon Ball">
                        </div>
                        <div class="series-info">
                            <div class="series-name">Dragon Ball</div>
                        </div>
                    </button>

                    <button type="button" class="series-card" onclick="filterBySeries('attack-titan')">
                        <div class="series-image attack-titan">
                            <img src="/DACS/public/assets/img/attack-on-titan.png" alt="Attack on Titan">
                        </div>
                        <div class="series-info">
                            <div class="series-name">Attack on Titan</div>
                        </div>
                    </button>

                    <button type="button" class="series-card" onclick="filterBySeries('demon-slayer')">
                        <div class="series-image demon-slayer">
                            <img src="/DACS/public/assets/img/demon-slayer.png" alt="Demon Slayer">
                        </div>
                        <div class="series-info">
                            <div class="series-name">Demon Slayer</div>
                        </div>
                    </button>

                    <button type="button" class="series-card" onclick="filterBySeries('my-hero')">
                        <div class="series-image my-hero">
                            <img src="/DACS/public/assets/img/my-hero-academia.png" alt="My Hero Academia">
                        </div>
                        <div class="series-info">
                            <div class="series-name">My Hero Academia</div>
                        </div>
                    </button>
                </div>
            </div>
        </section>

        <!-- Khu vực shop + bộ lọc -->
        <div class="shop-container">
            <!-- Nút mở sidebar (mobile) -->
            <button class="filter-toggle" type="button" onclick="toggleSidebar()">
                <i class="fas fa-filter"></i>
                Bộ lọc &amp; Tìm kiếm
            </button>

            <!-- Sidebar bộ lọc -->
            <aside class="sidebar" id="sidebar">
                <!-- Anime Series -->
                <div class="filter-section">
                    <div class="filter-header">
                        <span>Anime Series</span>
                        <i class="fas fa-chevron-up"></i>
                    </div>
                    <div class="filter-content">
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="naruto" value="naruto">
                            <span>Naruto / Boruto</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="one-piece" value="one-piece">
                            <span>One Piece</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="dragon-ball" value="dragon-ball">
                            <span>Dragon Ball</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="attack-titan" value="attack-titan">
                            <span>Attack on Titan</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="demon-slayer" value="demon-slayer">
                            <span>Demon Slayer</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="my-hero" value="my-hero">
                            <span>My Hero Academia</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="jujutsu" value="jujutsu">
                            <span>Jujutsu Kaisen</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="tokyo-ghoul" value="tokyo-ghoul">
                            <span>Tokyo Ghoul</span>
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
                            <input type="checkbox" data-filter="series" id="nendoroid" value="nendoroid">
                            <span>Nendoroid</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="figma" value="figma">
                            <span>Figma</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="scale-figure" value="scale-figure">
                            <span>Scale Figure</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="prize-figure" value="prize-figure">
                            <span>Prize Figure</span>
                        </div>
                        <div class="filter-checkbox">
                            <input type="checkbox" data-filter="series" id="statue" value="statue">
                            <span>Statue</span>
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
                <button class="clear-filters" type="button">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    <span>Xóa tất cả bộ lọc</span>
                </button>
            </aside>

            <!-- Khu vực sản phẩm -->
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

    <script src="/DACS/public/assets/js/product.js"></script>
    <script src="/DACS/public/assets/js/page.js"></script>
</body>
</html>