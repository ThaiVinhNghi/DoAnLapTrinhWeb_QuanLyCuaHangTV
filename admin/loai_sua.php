<?php
session_start();
require_once '../connect.php';

$id = isset($_GET['id']) ? $_GET['id'] : 0;
// Lấy thông tin cũ
$stmt_get = $conn->prepare("SELECT * FROM loaisanpham WHERE ID = ?");
$stmt_get->bind_param("i", $id);
$stmt_get->execute();
$loai = $stmt_get->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tenLoai = trim($_POST['TenLoai']);
    $sql = "UPDATE loaisanpham SET TenLoai = ? WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $tenLoai, $id);
    $stmt->execute();
    echo "<script>alert('Cập nhật thành công!'); window.location.href='danh_muc.php';</script>";
    exit();
}

require_once 'header.php';
require_once 'sidebar.php';
?>
<div class="col-md-6 mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-warning text-dark"><h5>Sửa Loại Sản Phẩm</h5></div>
        <div class="card-body">
            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tên Loại Sản Phẩm</label>
                    <input type="text" name="TenLoai" class="form-control" value="<?php echo htmlspecialchars($loai['TenLoai']); ?>" required>
                </div>
                <button type="submit" class="btn btn-warning fw-bold">Cập nhật</button>
                <a href="danh_muc.php" class="btn btn-secondary">Hủy</a>
            </form>
        </div>
    </div>
</div>
<?php require_once 'footer.php'; ?>