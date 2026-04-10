<?php
session_start();
require_once '../thu_vien/connect.php';

// Bảo mật: Chỉ admin mới được sửa
if (!isset($_SESSION['quyen_han']) || $_SESSION['quyen_han'] != 1) {
    echo "<script>alert('Bạn không có quyền!'); window.location.href='index.php';</script>";
    exit();
}

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
    $ngayVaoLam = trim($_POST['NgayVaoLam']);
    if (empty($ngayVaoLam)) $ngayVaoLam = null;

    $matKhau = trim($_POST['MatKhau']); 

    if (empty($matKhau)) {
        // CẬP NHẬT: Thêm NgayVaoLam vào SQL (Không đổi mật khẩu)
        $sql_update = "UPDATE nhanvien SET HoVaTen=?, DienThoai=?, DiaChi=?, QuyenHan=?, NgayVaoLam=? WHERE ID=?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sssisi", $hoVaTen, $dienThoai, $diaChi, $quyenHan, $ngayVaoLam, $id_nv);
    } else {
        // CẬP NHẬT: Thêm NgayVaoLam vào SQL (Có đổi mật khẩu)
        $sql_update = "UPDATE nhanvien SET HoVaTen=?, DienThoai=?, DiaChi=?, QuyenHan=?, NgayVaoLam=?, MatKhau=? WHERE ID=?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sssissi", $hoVaTen, $dienThoai, $diaChi, $quyenHan, $ngayVaoLam, $matKhau, $id_nv);
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
<div class="container mt-5" style="max-width: 700px;">
    <div class="card shadow border-0">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Cập Nhật Nhân Viên #<?php echo $id_nv; ?></h4>
        </div>
        <div class="card-body p-4">
            <?php echo $thongBao; ?>
            <form action="" method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Họ và Tên *</label>
                        <input type="text" name="HoVaTen" class="form-control" value="<?php echo htmlspecialchars($nv['HoVaTen']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Điện Thoại</label>
                        <input type="text" name="DienThoai" class="form-control" value="<?php echo htmlspecialchars($nv['DienThoai']); ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Địa Chỉ</label>
                    <input type="text" name="DiaChi" class="form-control" value="<?php echo htmlspecialchars($nv['DiaChi']); ?>">
                </div>

                <div class="row bg-light border rounded p-2 mb-4 mx-0 mt-2">
                    <div class="col-md-6 mb-2 mt-2">
                        <label class="form-label fw-bold text-primary">Ngày Vào Làm *</label>
                        <?php 
                            // Xử lý hiển thị ngày đúng format cho thẻ input date
                            $valNgay = !empty($nv['NgayVaoLam']) ? date('Y-m-d', strtotime($nv['NgayVaoLam'])) : '';
                        ?>
                        <input type="date" name="NgayVaoLam" class="form-control border-primary" value="<?php echo $valNgay; ?>" required>
                    </div>
                    <div class="col-md-6 mb-2 mt-2">
                        <label class="form-label fw-bold text-danger">Quyền Hạn *</label>
                        <select name="QuyenHan" class="form-select border-danger">
                            <option value="0" <?php echo ($nv['QuyenHan'] == 0) ? 'selected' : ''; ?>>Nhân viên thường</option>
                            <option value="1" <?php echo ($nv['QuyenHan'] == 1) ? 'selected' : ''; ?>>Quản trị viên (Admin)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold text-muted">Mật khẩu mới (Để trống nếu không muốn đổi)</label>
                    <input type="password" name="MatKhau" class="form-control border-secondary" placeholder="Nhập mật khẩu mới...">
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-bold py-2">CẬP NHẬT LƯU</button>
                <a href="nhan_vien.php" class="btn btn-outline-secondary w-100 mt-2">Hủy bỏ</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>