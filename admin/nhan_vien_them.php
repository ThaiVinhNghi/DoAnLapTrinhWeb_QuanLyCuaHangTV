<?php
session_start();
require_once '../connect.php';

$thongBao = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hoVaTen = trim($_POST['HoVaTen']);
    $dienThoai = trim($_POST['DienThoai']);
    $diaChi = trim($_POST['DiaChi']);
    $tenDangNhap = trim($_POST['TenDangNhap']);
    $matKhau = trim($_POST['MatKhau']);
    $quyenHan = $_POST['QuyenHan']; // 1 là Admin, 0 là Nhân viên

    if (empty($hoVaTen) || empty($tenDangNhap) || empty($matKhau)) {
        $thongBao = "<div class='alert alert-danger'>Vui lòng nhập đủ các thông tin bắt buộc!</div>";
    } else {
        // Kiểm tra xem tên đăng nhập đã tồn tại chưa
        $sql_check = "SELECT ID FROM nhanvien WHERE TenDangNhap = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $tenDangNhap);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            $thongBao = "<div class='alert alert-warning'>Tên đăng nhập này đã có người sử dụng!</div>";
        } else {
            // Thêm vào database
            $sql = "INSERT INTO nhanvien (HoVaTen, DienThoai, DiaChi, TenDangNhap, MatKhau, QuyenHan) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $hoVaTen, $dienThoai, $diaChi, $tenDangNhap, $matKhau, $quyenHan);
            
            if ($stmt->execute()) {
                echo "<script>alert('Thêm nhân viên thành công!'); window.location.href='nhan_vien.php';</script>";
                exit();
            } else {
                $thongBao = "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Nhân Viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 600px;">
    <div class="card shadow border-0">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0">Thêm Nhân Viên Mới</h4>
        </div>
        <div class="card-body">
            <?php echo $thongBao; ?>
            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Họ và Tên *</label>
                    <input type="text" name="HoVaTen" class="form-control" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Điện Thoại</label>
                        <input type="text" name="DienThoai" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Quyền Hạn *</label>
                        <select name="QuyenHan" class="form-select">
                            <option value="0">Nhân viên thường</option>
                            <option value="1">Quản trị viên (Admin)</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Địa Chỉ</label>
                    <input type="text" name="DiaChi" class="form-control">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tên đăng nhập *</label>
                        <input type="text" name="TenDangNhap" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Mật khẩu *</label>
                        <input type="password" name="MatKhau" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-success w-100 fw-bold">LƯU THÔNG TIN</button>
                <a href="nhan_vien.php" class="btn btn-outline-secondary w-100 mt-2">Hủy bỏ</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>