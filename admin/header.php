<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// require_once '../../connect.php'; // Đảm bảo đường dẫn đúng tới file connect.php

// Kểm tra quyền Admin
$isAdmin = isset($_SESSION['admin_id']);
$isNhanVienCu = isset($_SESSION['tham_nien_1_nam']) && $_SESSION['tham_nien_1_nam'] === true;
$duocPhepXuLyPhucTap = ($isAdmin || $isNhanVienCu);

// Lấy tên file hiện tại để in đậm menu đang chọn (Active)
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ Thống Quản Trị - N&U Store</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="../tai_nguyen/css/style.css">
</head>
<body>

    <nav class="navbar navbar-dark admin-navbar sticky-top">
        <div class="container-fluid px-4">
            <button class="navbar-toggler d-md-none me-2 border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                <span class="navbar-toggler-icon"></span>
            </button>

            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="index.php">
                <i class="bi bi-cpu-fill text-danger fs-3"></i> 
                <span class="letter-spacing-1 d-none d-sm-inline">ADMIN N&U</span>
            </a>
            
            <div class="d-flex align-items-center gap-3 ms-auto">
                <a href="../trang_chu.php" class="btn btn-outline-light btn-pill d-none d-md-inline-block" target="_blank">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Xem Website
                </a>
                <div class="vr bg-secondary d-none d-md-block" style="width: 1px; height: 24px;"></div>
                <div class="text-white d-flex align-items-center gap-2">
                    <div class="bg-secondary rounded-circle d-flex justify-content-center align-items-center" style="width: 32px; height: 32px;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <span class="fw-semibold d-none d-sm-inline">
                        <?php echo isset($_SESSION['ho_ten']) ? htmlspecialchars($_SESSION['ho_ten']) : 'Quản trị viên'; ?>
                    </span>
                </div>
                <a href="logout.php" class="btn btn-danger btn-pill ms-2">
                    <i class="bi bi-power"></i> <span class="d-none d-sm-inline">Thoát</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">