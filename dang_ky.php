<?php
session_start();
require_once 'connect.php';

$thongBao = '';
$loaiThongBao = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ form
    $hoVaTen = $_POST['HoVaTen']; 
    $dienThoai = $_POST['DienThoai'];
    $diaChi = $_POST['DiaChi'];
    $tenDangNhap = $_POST['TenDangNhap'];
    $matKhau = $_POST['MatKhau'];
    $nhapLaiMatKhau = $_POST['NhapLaiMatKhau'];

    if ($matKhau !== $nhapLaiMatKhau) {
        $thongBao = "Mật khẩu nhập lại không khớp!";
        $loaiThongBao = "danger";
    } else {
        // Kiểm tra trùng tên đăng nhập
        $sql_check = "SELECT * FROM khachhang WHERE TenDangNhap = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $tenDangNhap);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();

        if ($res_check->num_rows > 0) {
            $thongBao = "Tên đăng nhập này đã có người sử dụng. Vui lòng chọn tên khác!";
            $loaiThongBao = "danger";
        } else {
            // Đã sửa lại câu SQL cho đúng với cột HoVaTen
            $sql_insert = "INSERT INTO khachhang (HoVaTen, DienThoai, DiaChi, TenDangNhap, MatKhau) VALUES (?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("sssss", $hoVaTen, $dienThoai, $diaChi, $tenDangNhap, $matKhau);
            
            if ($stmt_insert->execute()) {
                $thongBao = "Đăng ký thành công! Bạn có thể đăng nhập ngay.";
                $loaiThongBao = "success";
            } else {
                $thongBao = "Có lỗi xảy ra: " . $conn->error;
                $loaiThongBao = "danger";
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng Ký - TIVI STORE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width: 600px;">
    <div class="card shadow">
        <div class="card-header bg-primary text-white text-center">
            <h4>Đăng Ký Khách Hàng</h4>
        </div>
        <div class="card-body p-4">
            <?php if($thongBao != ''): ?>
                <div class="alert alert-<?php echo $loaiThongBao; ?> text-center">
                    <?php echo $thongBao; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label">Họ và Tên</label>
                    <input type="text" name="HoVaTen" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Điện Thoại</label>
                    <input type="text" name="DienThoai" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Địa Chỉ</label>
                    <input type="text" name="DiaChi" class="form-control" required>
                </div>
                <hr>
                <div class="mb-3">
                    <label class="form-label">Tên Đăng Nhập</label>
                    <input type="text" name="TenDangNhap" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mật Khẩu</label>
                    <input type="password" name="MatKhau" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nhập Lại Mật Khẩu</label>
                    <input type="password" name="NhapLaiMatKhau" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">ĐĂNG KÝ</button>
            </form>
            <div class="text-center mt-3">
                <a href="trang_chu.php" class="text-decoration-none">Về trang chủ</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>