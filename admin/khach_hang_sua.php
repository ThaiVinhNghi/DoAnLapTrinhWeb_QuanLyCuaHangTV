<?php
session_start();
require_once '../thu_vien/connect.php';

$id_kh = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$thongBao = '';

// Lấy thông tin cũ
$sql_get = "SELECT * FROM khachhang WHERE ID = ?";
$stmt_get = $conn->prepare($sql_get);
$stmt_get->bind_param("i", $id_kh);
$stmt_get->execute();
$kh = $stmt_get->get_result()->fetch_assoc();

if (!$kh) {
    echo "<script>alert('Không tìm thấy khách hàng!'); window.location.href='khach_hang.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hoVaTen = trim($_POST['HoVaTen']);
    $dienThoai = trim($_POST['DienThoai']);
    $diaChi = trim($_POST['DiaChi']);
    $matKhau = trim($_POST['MatKhau']); 

    if (empty($matKhau)) {
        // Không đổi mật khẩu
        $sql_update = "UPDATE khachhang SET HoVaTen=?, DienThoai=?, DiaChi=? WHERE ID=?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sssi", $hoVaTen, $dienThoai, $diaChi, $id_kh);
    } else {
        // Đổi luôn mật khẩu
        $sql_update = "UPDATE khachhang SET HoVaTen=?, DienThoai=?, DiaChi=?, MatKhau=? WHERE ID=?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ssssi", $hoVaTen, $dienThoai, $diaChi, $matKhau, $id_kh);
    }
    
    if ($stmt_update->execute()) {
        echo "<script>alert('Cập nhật thông tin thành công!'); window.location.href='khach_hang.php';</script>";
        exit();
    } else {
        $thongBao = "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa Khách Hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 600px;">
    <div class="card shadow border-0">
        <div class="card-header bg-warning text-dark">
            <h4 class="mb-0">Cập Nhật Khách Hàng #<?php echo $id_kh; ?></h4>
        </div>
        <div class="card-body">
            <?php echo $thongBao; ?>
            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Họ và Tên *</label>
                    <input type="text" name="HoVaTen" class="form-control" value="<?php echo htmlspecialchars($kh['HoVaTen']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Điện Thoại</label>
                    <input type="text" name="DienThoai" class="form-control" value="<?php echo htmlspecialchars($kh['DienThoai']); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Địa Chỉ</label>
                    <input type="text" name="DiaChi" class="form-control" value="<?php echo htmlspecialchars($kh['DiaChi']); ?>">
                </div>
                <hr>
                <div class="mb-3">
                    <label class="form-label fw-bold text-muted">Tên đăng nhập (Không thể thay đổi)</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($kh['TenDangNhap']); ?>" readonly disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Mật khẩu mới (Để trống nếu không muốn đổi)</label>
                    <input type="password" name="MatKhau" class="form-control" placeholder="Nhập mật khẩu mới...">
                </div>
                <button type="submit" class="btn btn-warning w-100 fw-bold">CẬP NHẬT LƯU</button>
                <a href="khach_hang.php" class="btn btn-outline-secondary w-100 mt-2">Hủy bỏ</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>