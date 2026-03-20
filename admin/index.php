<?php
// 1. Khởi tạo session để lấy thông tin đăng nhập
session_start();

// 2. Kiểm tra nếu chưa đăng nhập thì chuyển hướng ra ngoài tìm trang login
if (!isset($_SESSION['nhanvien_id'])) {
    header("Location: ../login.php"); // <--- ĐÃ SỬA THÊM ../
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ Quản trị - Cửa Hàng Tivi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">📺 Admin Tivi Store</a>
        <div class="d-flex text-white align-items-center">
            <span class="me-3">Xin chào, <strong><?php echo $_SESSION['ho_ten']; ?></strong>!</span>
            <a href="../logout.php" class="btn btn-sm btn-danger">Đăng xuất</a>
        </div>
    </div>
</nav>

<div class="container-fluid mt-4">
    <div class="row">
        
        <div class="col-md-3 col-lg-2">
            <div class="list-group">
                <a href="index.php" class="list-group-item list-group-item-action active">Bảng điều khiển</a>
                <a href="san_pham.php" class="list-group-item list-group-item-action">Quản lý Sản phẩm</a>
                <a href="danh_muc.php" class="list-group-item list-group-item-action">Quản lý Danh mục</a>
                <a href="hoa_don.php" class="list-group-item list-group-item-action">Quản lý Hóa đơn</a>
                <a href="khach_hang.php" class="list-group-item list-group-item-action">Quản lý Khách hàng</a>
                
                <?php if ($_SESSION['quyen_han'] == 1): ?>
                    <a href="nhan_vien.php" class="list-group-item list-group-item-action text-primary">Quản lý Nhân viên (Admin)</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-9 col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Tổng quan hệ thống</h5>
                </div>
                <div class="card-body">
                    <h4 class="card-title text-success">Đăng nhập thành công!</h4>
                    <p class="card-text">
                        Bạn đang đăng nhập với tư cách là: 
                        <strong>
                            <?php echo ($_SESSION['quyen_han'] == 1) ? 'Quản trị viên (Admin)' : 'Nhân viên bán hàng'; ?>
                        </strong>
                    </p>
                    <hr>
                    <p>Hãy chọn các chức năng bên menu trái để bắt đầu quản lý hệ thống. (Lưu ý: Các đường link chức năng bên trái hiện tại đang là file mẫu, chúng ta sẽ code dần nhé!).</p>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>