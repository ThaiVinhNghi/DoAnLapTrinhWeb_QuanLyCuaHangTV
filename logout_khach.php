<?php
session_start();

// Xóa session đăng nhập khách hàng
unset($_SESSION['khach_hang_id']);
unset($_SESSION['khach_hang_ten']);

// Chuyển về trang chủ
header("Location: trang_chu.php");
exit();
?>