let usingDomProducts = false;
let allDomProductCards = [];

document.addEventListener('DOMContentLoaded', function() {
    allDomProductCards = Array.from(document.querySelectorAll('#productsGrid .product-card'));
    usingDomProducts = allDomProductCards.length > 0;
    initializeFilters();
    initializeFilterCollapse();
    updateResultCountDom();
});

function initializeFilters() {
    document.querySelectorAll('.filter-checkbox input').forEach(checkbox => {
        checkbox.addEventListener('change', applyFilters);
    });
    const minEl = document.getElementById('minPrice');
    const maxEl = document.getElementById('maxPrice');
    if (minEl) minEl.addEventListener('input', debounce(applyFilters, 150));
    if (maxEl) maxEl.addEventListener('input', debounce(applyFilters, 150));

    const clearBtn = document.querySelector('.clear-filters');
    if (clearBtn) {
        clearBtn.addEventListener('click', function(e) {
            e.preventDefault();
            clearAllFilters();
        });
    }
    try { window.clearAllFilters = clearAllFilters; } catch (_) {}
}

function applyFilters() {
    // 1. Thu thập các filter đang được chọn
    const checkedFilters = { 
        series: [], type: [], brand: [], scale: [], condition: [], 
        grade: [], priceRanges: [] // Thêm grade và priceRanges
    };

    document.querySelectorAll('.filter-checkbox input:checked').forEach(input => {
        const value = (input.value || '').toString().toLowerCase();
        const section = input.closest('.filter-section');
        const headerText = (section?.querySelector('.filter-header span')?.textContent || '').toLowerCase();

        if (headerText.includes('series') || headerText.includes('gundam series')) {
            checkedFilters.series.push(value);
        } else if (headerText.includes('loại') || headerText.includes('type')) {
            checkedFilters.type.push(value);
        } else if (headerText.includes('sản xuất') || headerText.includes('brand')) {
            checkedFilters.brand.push(value);
        } else if (headerText.includes('tỷ lệ') || headerText.includes('scale')) {
            checkedFilters.scale.push(value);
        } else if (headerText.includes('grade')) { 
            // 👉 MỚI: Nhận diện cột Grade
            checkedFilters.grade.push(value);
        } else if (headerText.includes('khoảng giá') || headerText.includes('price')) {
            // 👉 MỚI: Nhận diện checkbox giá (checkbox ID)
            checkedFilters.priceRanges.push(input.id); 
        } else if (headerText.includes('tình trạng') || headerText.includes('condition')) {
            checkedFilters.condition.push(value);
        }
    });

    // Lấy giá min/max từ ô nhập liệu
    const minInput = Number(document.getElementById('minPrice')?.value || '') || null;
    const maxInput = Number(document.getElementById('maxPrice')?.value || '') || null;

    // 2. Lọc danh sách sản phẩm
    allDomProductCards = Array.from(document.querySelectorAll('#productsGrid .product-card'));
    usingDomProducts = allDomProductCards.length > 0;
    if (!usingDomProducts) return;

    allDomProductCards.forEach(card => {
        // Lấy dữ liệu
        const name      = (card.getAttribute('data-name')       || '').toLowerCase(); // Lấy tên để tìm Grade
        const series    = (card.getAttribute('data-series')     || '').toLowerCase();
        const type      = (card.getAttribute('data-type')       || '').toLowerCase();
        const brand     = (card.getAttribute('data-brand')      || '').toLowerCase();
        const scale     = (card.getAttribute('data-scale')      || '').toLowerCase();
        const condition = (card.getAttribute('data-condition')  || '').toLowerCase();
        const price     = Number(card.getAttribute('data-price')) || 0;

        let match = true;

        // --- Logic Lọc ---

        // 1. Series
        if (checkedFilters.series.length > 0) {
            const isMatch = checkedFilters.series.some(f => series.includes(f));
            if (!isMatch) match = false;
        }

        // 2. Grade (Tìm trong Tên sản phẩm vì DB không có cột Grade)
        if (checkedFilters.grade.length > 0) {
            // Ví dụ: Checkbox là 'hg' -> Kiểm tra tên có chứa 'hg' hoặc 'high grade' không
            const isMatch = checkedFilters.grade.some(f => name.includes(f));
            if (!isMatch) match = false;
        }

        // 3. Scale
        if (checkedFilters.scale.length > 0) {
            const isMatch = checkedFilters.scale.some(f => scale.includes(f));
            if (!isMatch) match = false;
        }

        // 4. Giá (Input nhập tay)
        if (minInput !== null && price < minInput) match = false;
        if (maxInput !== null && price > maxInput) match = false;

        // 5. Giá (Checkbox ranges) - Xử lý thủ công các ID bạn đã đặt
        if (checkedFilters.priceRanges.length > 0) {
            // Logic: Nếu chọn nhiều checkbox giá, chỉ cần thỏa mãn 1 trong số đó (OR logic)
            let priceMatch = false;
            checkedFilters.priceRanges.forEach(rangeId => {
                if (rangeId === 'under-500k' && price < 500000) priceMatch = true;
                if (rangeId === '500k-1m' && price >= 500000 && price <= 1000000) priceMatch = true;
                if (rangeId === '1m-2m' && price >= 1000000 && price <= 2000000) priceMatch = true;
                if (rangeId === 'above-2m' && price > 2000000) priceMatch = true;
                
                // Logic cho trang Anime/Marvel (nếu id khác)
                if (rangeId === 'under-1m' && price < 1000000) priceMatch = true;
                if (rangeId === '1m-3m' && price >= 1000000 && price <= 3000000) priceMatch = true;
                if (rangeId === '3m-5m' && price >= 3000000 && price <= 5000000) priceMatch = true;
                if (rangeId === 'above-5m' && price > 5000000) priceMatch = true;
            });
            if (!priceMatch) match = false;
        }

        // Ẩn / hiện
        card.style.display = match ? '' : 'none';
    });

    updateResultCountDom();
}

function clearAllFilters() {
    document.querySelectorAll('.filter-checkbox input[type="checkbox"]').forEach(cb => { cb.checked = false; });
    const minEl = document.getElementById('minPrice'); if (minEl) minEl.value = '';
    const maxEl = document.getElementById('maxPrice'); if (maxEl) maxEl.value = '';

    allDomProductCards = Array.from(document.querySelectorAll('#productsGrid .product-card'));
    usingDomProducts = allDomProductCards.length > 0;
    if (usingDomProducts) {
        allDomProductCards.forEach(card => { card.style.display = ''; });
        updateResultCountDom();
    }
}

function initializeFilterCollapse() {
    document.querySelectorAll('.filter-header').forEach(header => {
        header.addEventListener('click', function() {
            const section = this.closest('.filter-section');
            const content = this.nextElementSibling;
            const icon = this.querySelector('i');
            const collapsed = section?.classList.toggle('collapsed');
            if (collapsed) {
                if (content) content.style.display = 'none';
                if (icon) icon.className = 'fas fa-chevron-down';
            } else {
                if (content) content.style.display = 'block';
                if (icon) icon.className = 'fas fa-chevron-up';
            }
        });
    });
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => { clearTimeout(timeout); func(...args); };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function updateResultCountDom() {
    const cards = Array.from(document.querySelectorAll('#productsGrid .product-card'));
    const visible = cards.filter(c => c.style.display !== 'none').length || cards.length;
    const el = document.getElementById('resultCount');
    if (el) el.textContent = visible;
}

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) sidebar.classList.toggle('active');
}

window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        document.getElementById('sidebar')?.classList.remove('active');
    }
});
