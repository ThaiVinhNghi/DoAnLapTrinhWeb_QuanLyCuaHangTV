<?php
session_start();
require_once '../connect.php';

$id_nv = isset($_GET['id']) ? $_GET['id'] : 0;
$thongBao = '';

// Lấy thông tin cũ
$sql_get = "SELECT * FROM nhanvien WHERE ID = ?";
$stmt_get = $conn->prepare($sql_get);
$stmt_get->bind_param("i", $id_nv);
$stmt_get->execute();
$nv = $stmt_get->get_result()->fetch_assoc();

if (!$nv) {
    echo "<script>alert('Không tìm thấy nhân viên!'); window.location.href='nhan_vien.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hoVaTen = trim($_POST['HoVaTen']);
    $dienThoai = trim($_POST['DienThoai']);
    $diaChi = trim($_POST['DiaChi']);
    $quyenHan = $_POST['QuyenHan'];
    $matKhau = trim($_POST['MatKhau']); // Nếu nhập mk mới thì đổi, không thì giữ nguyên

    if (empty($matKhau)) {
        // Không đổi mật khẩu
        $sql_update = "UPDATE nhanvien SET HoVaTen=?, DienThoai=?, DiaChi=?, QuyenHan=? WHERE ID=?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sssii", $hoVaTen, $dienThoai, $diaChi, $quyenHan, $id_nv);
    } else {
        // Đổi luôn mật khẩu
        $sql_update = "UPDATE nhanvien SET HoVaTen=?, DienThoai=?, DiaChi=?, QuyenHan=?, MatKhau=? WHERE ID=?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sssiis", $hoVaTen, $dienThoai, $diaChi, $quyenHan, $matKhau, $id_nv);
    }
    
    if ($stmt_update->execute()) {
        echo "<script>alert('Cập nhật thành công!'); window.location.href='nhan_vien.php';</script>";
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
    <title>Sửa Nhân Viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 600px;">
    <div class="card shadow border-0">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Cập Nhật Nhân Viên #<?php echo $id_nv; ?></h4>
        </div>
        <div class="card-body">
            <?php echo $thongBao; ?>
            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Họ và Tên *</label>
                    <input type="text" name="HoVaTen" class="form-control" value="<?php echo htmlspecialchars($nv['HoVaTen']); ?>" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Điện Thoại</label>
                        <input type="text" name="DienThoai" class="form-control" value="<?php echo htmlspecialchars($nv['DienThoai']); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Quyền Hạn *</label>
                        <select name="QuyenHan" class="form-select">
                            <option value="0" <?php echo ($nv['QuyenHan'] == 0) ? 'selected' : ''; ?>>Nhân viên thường</option>
                            <option value="1" <?php echo ($nv['QuyenHan'] == 1) ? 'selected' : ''; ?>>Quản trị viên (Admin)</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Địa Chỉ</label>
                    <input type="text" name="DiaChi" class="form-control" value="<?php echo htmlspecialchars($nv['DiaChi']); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Mật khẩu mới (Để trống nếu không muốn đổi)</label>
                    <input type="password" name="MatKhau" class="form-control" placeholder="Nhập mật khẩu mới...">
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-bold">CẬP NHẬT LƯU</button>
                <a href="nhan_vien.php" class="btn btn-outline-secondary w-100 mt-2">Hủy bỏ</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>