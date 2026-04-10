<?php
session_start();
require_once '../thu_vien/connect.php';

// Bảo mật: Chỉ admin mới được thêm
if (!isset($_SESSION['quyen_han']) || $_SESSION['quyen_han'] != 1) {
    echo "<script>alert('Bạn không có quyền!'); window.location.href='index.php';</script>";
    exit();
}

$thongBao = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hoVaTen = trim($_POST['HoVaTen']);
    $dienThoai = trim($_POST['DienThoai']);
    $diaChi = trim($_POST['DiaChi']);
    $tenDangNhap = trim($_POST['TenDangNhap']);
    $matKhau = trim($_POST['MatKhau']);
    $quyenHan = $_POST['QuyenHan']; 
    $ngayVaoLam = trim($_POST['NgayVaoLam']); 
    
    // Nếu để trống thì lấy ngày hôm nay
    if (empty($ngayVaoLam)) {
        $ngayVaoLam = date('Y-m-d');
    }

    if (empty($hoVaTen) || empty($tenDangNhap) || empty($matKhau)) {
        $thongBao = "<div class='alert alert-danger'>Vui lòng nhập đủ các thông tin bắt buộc!</div>";
    } else {
        $sql_check = "SELECT ID FROM nhanvien WHERE TenDangNhap = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $tenDangNhap);
        $stmt_check->execute();
        
        if ($stmt_check->get_result()->num_rows > 0) {
            $thongBao = "<div class='alert alert-warning'>Tên đăng nhập này đã có người sử dụng!</div>";
        } else {
            // CẬP NHẬT: Thêm NgayVaoLam vào SQL
            $sql = "INSERT INTO nhanvien (HoVaTen, DienThoai, DiaChi, TenDangNhap, MatKhau, QuyenHan, NgayVaoLam) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssis", $hoVaTen, $dienThoai, $diaChi, $tenDangNhap, $matKhau, $quyenHan, $ngayVaoLam);
            
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
<div class="container mt-5" style="max-width: 700px;">
    <div class="card shadow border-0">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0">Thêm Nhân Viên Mới</h4>
        </div>
        <div class="card-body p-4">
            <?php echo $thongBao; ?>
            <form action="" method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Họ và Tên *</label>
                        <input type="text" name="HoVaTen" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Điện Thoại</label>
                        <input type="text" name="DienThoai" class="form-control">
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

                <div class="row bg-light border rounded p-2 mb-4 mx-0 mt-2">
                    <div class="col-md-6 mb-2 mt-2">
                        <label class="form-label fw-bold text-primary">Ngày Vào Làm *</label>
                        <input type="date" name="NgayVaoLam" class="form-control border-primary" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-6 mb-2 mt-2">
                        <label class="form-label fw-bold text-danger">Quyền Hạn *</label>
                        <select name="QuyenHan" class="form-select border-danger">
                            <option value="0">Nhân viên thường</option>
                            <option value="1">Quản trị viên (Admin)</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100 fw-bold py-2">LƯU THÔNG TIN</button>
                <a href="nhan_vien.php" class="btn btn-outline-secondary w-100 mt-2">Hủy bỏ</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>