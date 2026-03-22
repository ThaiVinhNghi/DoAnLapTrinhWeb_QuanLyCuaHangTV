<?php
session_start();
// Kiểm tra đăng nhập Admin ở đây (nếu có)
if (!isset($_SESSION['nhanvien_id'])) {
    header("Location: ../login.php");
    exit();
}

// require_once '../connect.php'; 

// 1. Nhúng phần Đầu & Navbar
require_once 'header.php'; 

// 2. Nhúng thanh Menu trái
require_once 'sidebar.php'; 

$hoTen = $_SESSION['ho_ten'] ?? 'Người dùng';
$quyenHan = $_SESSION['quyen_han'] ?? 0;
$tenQuyen = ($quyenHan == 1) ? 'Quản trị viên (Admin)' : 'Nhân viên';
?>

<div class="col-md-9 col-lg-10">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <h5 class="mb-0">Tổng quan hệ thống</h5>
        </div>
        <div class="card-body">
            <h4 class="text-success">Đăng nhập thành công!</h4>
            <p>Xin chào: <strong><?php echo htmlspecialchars($hoTen); ?></strong></p>
            <p>Bạn đang đăng nhập với tư cách là: <strong><?php echo htmlspecialchars($tenQuyen); ?></strong></p>
            <hr>
            <p>Sử dụng menu bên trái để quản lý sản phẩm, danh mục, hóa đơn, khách hàng và nhân viên.</p>
            <p>Chúc bạn một ngày làm việc hiệu quả!</p>
        </div>
    </div>
</div>

<?php
// 4. Nhúng phần Đuôi để đóng thẻ
require_once 'footer.php'; 
?>