<?php
session_start();
require_once 'connect.php';

// Lấy ID bài viết từ URL
$id_bai = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_bai <= 0) {
    echo "<script>alert('Bài viết không tồn tại!'); window.location.href='trang_chu.php';</script>";
    exit();
}

// Truy vấn lấy dữ liệu bài viết
$sql = "SELECT bv.*, nv.HoVaTen 
        FROM baiviet bv 
        LEFT JOIN nhanvien nv ON bv.NhanVienID = nv.ID 
        WHERE bv.ID = ? AND bv.TrangThai = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_bai);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>alert('Bài viết không tồn tại hoặc đã bị ẩn!'); window.location.href='trang_chu.php';</script>";
    exit();
}

$bai = $result->fetch_assoc();
$img_cover = !empty($bai['HinhAnh']) ? "uploads/" . $bai['HinhAnh'] : "uploads/no-image.jpg";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($bai['TieuDe']); ?> - Siêu Thị Tivi N&U</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Làm đẹp phần nội dung bài viết */
        .article-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #333;
        }
        .article-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 15px 0;
        }
        .article-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #dee2e6;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="trang_chu.php"><i class="bi bi-tv"></i> N&U</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="trang_chu.php">Trang Chủ</a></li>
                <li class="nav-item"><a class="nav-link" href="trang_chu.php#khuyen-mai">Khuyến Mãi</a></li>
                <li class="nav-item"><a class="nav-link" href="trang_chu.php#noi-bat">Nổi Bật</a></li>
                <li class="nav-item"><a class="nav-link active" href="trang_chu.php#tin-tuc">Tin Tức</a></li>
            </ul>
            <div class="d-flex align-items-center flex-wrap gap-2">
                <a href="gio_hang.php" class="btn btn-warning position-relative fw-bold">
                    <i class="bi bi-cart-fill"></i> Giỏ hàng
                </a>
                <?php if (isset($_SESSION['khach_hang_id'])): ?>
                    <span class="text-white fw-bold mx-2"><i class="bi bi-person-check-fill"></i> <?php echo htmlspecialchars($_SESSION['khach_hang_ten']); ?></span>
                    <a href="logout_khach.php" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Thoát</a>
                <?php else: ?>
                    <a href="login_khach.php" class="btn btn-light"><i class="bi bi-box-arrow-in-right"></i> Đăng nhập</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<div class="container mt-5 mb-5" style="max-width: 900px;">
    
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="trang_chu.php" class="text-decoration-none">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="trang_chu.php#tin-tuc" class="text-decoration-none">Tin tức</a></li>
            <li class="breadcrumb-item active" aria-current="page">Bài viết</li>
        </ol>
    </nav>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div style="height: 350px; width: 100%; background-image: url('<?php echo $img_cover; ?>'); background-size: cover; background-position: center;">
        </div>

        <div class="card-body p-4 p-md-5">
            <div class="article-header text-center">
                <h1 class="fw-bold text-dark mb-3"><?php echo htmlspecialchars($bai['TieuDe']); ?></h1>
                <div class="text-muted d-flex justify-content-center gap-4">
                    <span><i class="bi bi-calendar3"></i> <?php echo date('d/m/Y H:i', strtotime($bai['NgayDang'])); ?></span>
                    <span><i class="bi bi-person-circle"></i> Đăng bởi: <strong class="text-primary"><?php echo htmlspecialchars($bai['HoVaTen']); ?></strong></span>
                </div>
            </div>

            <div class="article-content">
                <?php echo $bai['NoiDung']; ?>
            </div>
            
            <div class="mt-5 text-center">
                <a href="trang_chu.php#tin-tuc" class="btn btn-outline-primary px-4 rounded-pill">
                    <i class="bi bi-arrow-left"></i> Xem các tin tức khác
                </a>
            </div>
        </div>
    </div>
</div>

<footer class="bg-dark text-white text-center py-4 mt-5">
    <div class="container">
        <p class="mb-0">© 2026 - Cửa Hàng Tivi N&U.</p>
        <p class="text-muted small">Cập nhật tin tức công nghệ mới nhất mỗi ngày.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>