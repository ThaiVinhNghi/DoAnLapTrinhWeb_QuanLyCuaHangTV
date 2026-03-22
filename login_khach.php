<?php
session_start();
require_once 'connect.php';

// Nếu khách đã đăng nhập rồi thì đẩy về thanh toán
if (isset($_SESSION['khach_hang_id'])) {
    header("Location: thanh_toan.php");
    exit();
}

$thongBao = '';
$loaiThongBao = 'danger';

// Nếu vừa đăng ký thành công từ dang_ky.php chuyển sang
if (isset($_GET['dangky']) && $_GET['dangky'] == 'thanhcong') {
    $thongBao = "Đăng ký thành công! Vui lòng đăng nhập.";
    $loaiThongBao = "success";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tenDangNhap = trim($_POST['TenDangNhap']);
    $matKhau = trim($_POST['MatKhau']);

    if (empty($tenDangNhap) || empty($matKhau)) {
        $thongBao = "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!";
        $loaiThongBao = "danger";
    } else {
        // Kiểm tra trong bảng khachhang
        $sql = "SELECT ID, HoVaTen, MatKhau FROM khachhang WHERE TenDangNhap = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $tenDangNhap);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if ($matKhau === $row['MatKhau']) {
                // Đăng nhập thành công, lưu thông tin vào Session
                $_SESSION['khach_hang_id'] = $row['ID'];
                $_SESSION['khach_hang_ten'] = $row['HoVaTen'];

                // Chuyển hướng về thanh toán
                header("Location: thanh_toan.php");
                exit();
            } else {
                $thongBao = "Mật khẩu không chính xác!";
                $loaiThongBao = "danger";
            }
        } else {
            $thongBao = "Tài khoản không tồn tại!";
            $loaiThongBao = "danger";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập Khách hàng - TIVI STORE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h4 class="mb-0"><i class="bi bi-person-circle"></i> Đăng Nhập Khách Hàng</h4>
                </div>
                <div class="card-body p-4">

                    <?php if ($thongBao != ''): ?>
                        <div class="alert alert-<?php echo $loaiThongBao; ?> text-center">
                            <?php echo $thongBao; ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label text-muted fw-bold">Tên đăng nhập</label>
                            <input type="text" name="TenDangNhap" class="form-control" required placeholder="Nhập tên đăng nhập...">
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted fw-bold">Mật khẩu</label>
                            <input type="password" name="MatKhau" class="form-control" required placeholder="Nhập mật khẩu...">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold fs-5">ĐĂNG NHẬP</button>
                    </form>

                    <div class="mt-4 text-center">
                        <p class="mb-1">Chưa có tài khoản?</p>
                        <a href="dang_ky.php" class="btn btn-outline-secondary w-100">Đăng ký ngay</a>
                    </div>

                    <div class="text-center mt-3">
                        <a href="trang_chu.php" class="text-decoration-none text-muted"><i class="bi bi-arrow-left"></i> Về trang chủ</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>