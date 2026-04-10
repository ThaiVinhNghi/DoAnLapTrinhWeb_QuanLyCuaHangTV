// =========================================
// CHỨC NĂNG JAVASCRIPT - SCRIPT.JS
// =========================================

function updateFlashSaleTimer() {
    const now = new Date();
    // Flash sale kết thúc vào 23:59:59 ngày hôm nay
    const endOfDay = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59);
    const diff = endOfDay - now;

    if (diff <= 0) return;

    const hours = Math.floor((diff / (1000 * 60 * 60)) % 24).toString().padStart(2, '0');
    const minutes = Math.floor((diff / 1000 / 60) % 60).toString().padStart(2, '0');
    const seconds = Math.floor((diff / 1000) % 60).toString().padStart(2, '0');

    const timerSpan = document.getElementById('time-left');
    if(timerSpan) {
        timerSpan.innerText = `${hours}:${minutes}:${seconds}`;
    }
}

// Cập nhật đồng hồ mỗi 1 giây
setInterval(updateFlashSaleTimer, 1000);
// Gọi ngay lần đầu để không bị delay 1 giây
updateFlashSaleTimer();
