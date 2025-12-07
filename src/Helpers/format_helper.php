<?php
if (!function_exists('format_price')) {
    function format_price($price) {
        // Định dạng số: 0 chữ số thập phân, dấu phẩy ngăn cách hàng nghìn, dấu chấm thập phân
        return number_format($price, 0, ',', '.') . '₫';
    }
}
?>