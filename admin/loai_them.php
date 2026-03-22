<?php
session_start();
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tenLoai = trim($_POST['TenLoai']);
    $sql = "INSERT INTO loaisanpham (TenLoai) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $tenLoai);
    $stmt->execute();
    echo "<script>alert('Thêm thành công!'); window.location.href='danh_muc.php';</script>";
    exit();
}

require_once 'header.php';
require_once 'sidebar.php';
?>
<div class="col-md-6 mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white"><h5>Thêm Loại Sản Phẩm</h5></div>
        <div class="card-body">
            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tên Loại Sản Phẩm</label>
                    <input type="text" name="TenLoai" class="form-control" required placeholder="Ví dụ: Tivi OLED...">
                </div>
                <button type="submit" class="btn btn-primary">Lưu lại</button>
                <a href="danh_muc.php" class="btn btn-secondary">Hủy</a>
            </form>
        </div>
    </div>
</div>
<?php require_once 'footer.php'; ?>