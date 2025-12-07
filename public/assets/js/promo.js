// Lấy sẵn các phần tử cần dùng
var promoCards = document.querySelectorAll('.promo-card');
var filterTabs = document.querySelectorAll('.filter-tab');

var hoursEl   = document.getElementById('hours');
var minutesEl = document.getElementById('minutes');
var secondsEl = document.getElementById('seconds');

// Lấy giá trị đếm ngược ban đầu từ HTML
var hours   = parseInt(hoursEl.textContent, 10)   || 0;
var minutes = parseInt(minutesEl.textContent, 10) || 0;
var seconds = parseInt(secondsEl.textContent, 10) || 0;

// Lọc khuyến mãi theo tab
function filterPromos(event, category) {
    // Bỏ active trên tất cả tab
    filterTabs.forEach(function (tab) {
        tab.classList.remove('active');
    });

    // Thêm active cho tab đang bấm
    event.target.classList.add('active');

    // Ẩn/hiện các card
    promoCards.forEach(function (card) {
        var cardCategory = card.getAttribute('data-category');

        if (category === 'all' || cardCategory === category) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Copy mã khuyến mãi
function copyCode(code) {
    // Cách cơ bản: tạo input tạm, copy rồi xoá
    var tempInput = document.createElement('input');
    tempInput.value = code;
    document.body.appendChild(tempInput);

    tempInput.select();
    document.execCommand('copy');

    document.body.removeChild(tempInput);
    alert('Đã copy mã: ' + code);
}

// Cập nhật đồng hồ đếm ngược
function updateCountdown() {
    // Giảm 1 giây
    seconds--;

    if (seconds < 0) {
        seconds = 59;
        minutes--;
    }

    if (minutes < 0) {
        minutes = 59;
        hours--;
    }

    if (hours < 0) {
        // Giống code cũ: quay lại 23h nếu âm (loop 24h)
        hours = 23;
    }

    // Cập nhật lên giao diện, luôn 2 chữ số
    hoursEl.textContent   = hours.toString().padStart(2, '0');
    minutesEl.textContent = minutes.toString().padStart(2, '0');
    secondsEl.textContent = seconds.toString().padStart(2, '0');
}

// Gọi update mỗi 1 giây
setInterval(updateCountdown, 1000);
