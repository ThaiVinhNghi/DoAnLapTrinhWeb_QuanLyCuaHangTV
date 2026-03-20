<?php
session_start();
require_once '../connect.php';

$id = isset($_GET['id']) ? $_GET['id'] : 0;
$stmt_get = $conn->prepare("SELECT * FROM nhacungcap WHERE ID = ?");
$stmt_get->bind_param("i", $id);
$stmt_get->execute();
$ncc = $stmt_get->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ten = trim($_POST['TenNhaCungCap']);
    $dt = trim($_POST['DienThoai']);
    $email = trim($_POST['Email']);
    $dc = trim($_POST['DiaChi']);

    $sql = "UPDATE nhacungcap SET TenNhaCungCap=?, DienThoai=?, Email=?, DiaChi=? WHERE ID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $ten, $dt, $email, $dc, $id);
    $stmt->execute();
    echo "<script>alert('Cập nhật thành công!'); window.location.href='danh_muc.php';</script>";
    exit();
}

require_once 'header.php'; require_once 'sidebar.php';
?>
<div class="col-md-8 mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white"><h5>Sửa Nhà Cung Cấp</h5></div>
        <div class="card-body">
            <form action="" method="POST">
                <div class="mb-3"><label class="fw-bold">Tên Nhà Cung Cấp *</label><input type="text" name="TenNhaCungCap" class="form-control" value="<?php echo htmlspecialchars($ncc['TenNhaCungCap']); ?>" required></div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="fw-bold">Điện Thoại</label><input type="text" name="DienThoai" class="form-control" value="<?php echo htmlspecialchars($ncc['DienThoai']); ?>"></div>
                    <div class="col-md-6 mb-3"><label class="fw-bold">Email</label><input type="email" name="Email" class="form-control" value="<?php echo htmlspecialchars($ncc['Email']); ?>"></div>
                </div>
                <div class="mb-3"><label class="fw-bold">Địa Chỉ</label><input type="text" name="DiaChi" class="form-control" value="<?php echo htmlspecialchars($ncc['DiaChi']); ?>"></div>
                <button type="submit" class="btn btn-primary">Cập nhật</button>
                <a href="danh_muc.php" class="btn btn-secondary">Hủy</a>
            </form>
        </div>
    </div>
</div>
<?php require_once 'footer.php'; ?>