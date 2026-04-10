<?php
session_start();
require_once '../thu_vien/connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ten = trim($_POST['TenNhaCungCap']);
    $dt = trim($_POST['DienThoai']);
    $email = trim($_POST['Email']);
    $dc = trim($_POST['DiaChi']);

    $sql = "INSERT INTO nhacungcap (TenNhaCungCap, DienThoai, Email, DiaChi) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $ten, $dt, $email, $dc);
    $stmt->execute();
    echo "<script>alert('Thêm nhà cung cấp thành công!'); window.location.href='danh_muc.php';</script>";
    exit();
}

require_once 'header.php'; require_once 'sidebar.php';
?>
<div class="col-md-8 mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-warning text-dark"><h5>Thêm Nhà Cung Cấp</h5></div>
        <div class="card-body">
            <form action="" method="POST">
                <div class="mb-3"><label class="fw-bold">Tên Nhà Cung Cấp *</label><input type="text" name="TenNhaCungCap" class="form-control" required></div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="fw-bold">Điện Thoại</label><input type="text" name="DienThoai" class="form-control"></div>
                    <div class="col-md-6 mb-3"><label class="fw-bold">Email</label><input type="email" name="Email" class="form-control"></div>
                </div>
                <div class="mb-3"><label class="fw-bold">Địa Chỉ</label><input type="text" name="DiaChi" class="form-control"></div>
                <button type="submit" class="btn btn-warning fw-bold">Lưu lại</button>
                <a href="danh_muc.php" class="btn btn-secondary">Hủy</a>
            </form>
        </div>
    </div>
</div>
<?php require_once 'footer.php'; ?>