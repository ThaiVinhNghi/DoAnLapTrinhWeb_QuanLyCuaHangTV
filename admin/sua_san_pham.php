<?php
session_start();
// Kiểm tra đăng nhập (Lùi ra ngoài tìm file login)
if (!isset($_SESSION['nhanvien_id'])) {
    header("Location: ../login.php");
    exit();
}
// Lùi ra ngoài để gọi file connect
require_once '../connect.php';

$thongBao = '';
$id = 0;

// Lấy ID sản phẩm từ thanh URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];
} else {
    header("Location: san_pham.php");
    exit();
}

// 1. XỬ LÝ KHI NGƯỜI DÙNG BẤM NÚT "CẬP NHẬT" (GỬI FORM POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tenSanPham = $_POST['TenSanPham'];
    $loaiSanPhamID = $_POST['LoaiSanPhamID'];
    $hangSanXuatID = $_POST['HangSanXuatID'];
    $donGia = $_POST['DonGia'];
    $soLuong = $_POST['SoLuong'];
    
    // Lấy tên hình ảnh cũ để dự phòng
    $hinhAnh = $_POST['HinhAnhCu']; 
    
    // Nếu người dùng có chọn upload ảnh mới
    if (isset($_FILES['HinhAnh']) && $_FILES['HinhAnh']['error'] == 0) {
        $anhMoi = time() . '_' . basename($_FILES['HinhAnh']['name']); 
        $target_dir = "../uploads/"; // ĐÃ SỬA: Lùi ra ngoài lưu vào thư mục uploads
        $target_file = $target_dir . $anhMoi;
        
        // Chép file mới vào thư mục
        if (move_uploaded_file($_FILES["HinhAnh"]["tmp_name"], $target_file)) {
            // Xóa file ảnh cũ (nếu có) để khỏi nặng ổ cứng
            if (!empty($hinhAnh) && file_exists("../uploads/" . $hinhAnh)) {
                unlink("../uploads/" . $hinhAnh);
            }
            // Gán tên ảnh mới để lưu vào CSDL
            $hinhAnh = $anhMoi; 
        }
    }

    // Viết câu lệnh SQL Cập nhật dữ liệu
    $sql_update = "UPDATE SanPham SET TenSanPham = ?, LoaiSanPhamID = ?, HangSanXuatID = ?, DonGia = ?, SoLuong = ?, HinhAnh = ? WHERE ID = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("siiiisi", $tenSanPham, $loaiSanPhamID, $hangSanXuatID, $donGia, $soLuong, $hinhAnh, $id);
    
    if ($stmt_update->execute()) {
        header("Location: san_pham.php");
        exit();
    } else {
        $thongBao = "Lỗi cập nhật dữ liệu: " . $conn->error;
    }
    $stmt_update->close();
}

// 2. LẤY DỮ LIỆU CŨ HIỂN THỊ LÊN FORM
$sql_get = "SELECT * FROM SanPham WHERE ID = ?";
$stmt_get = $conn->prepare($sql_get);
$stmt_get->bind_param("i", $id);
$stmt_get->execute();
$result_get = $stmt_get->get_result();

if ($result_get->num_rows > 0) {
    $sp = $result_get->fetch_assoc();
} else {
    // Nếu không tìm thấy sản phẩm, quay về trang danh sách
    header("Location: san_pham.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa Tivi - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Cập Nhật Thông Tin Tivi (ID: <?php echo $id; ?>)</h5>
                </div>
                <div class="card-body">
                    
                    <?php if($thongBao != ''): ?>
                        <div class="alert alert-danger"><?php echo $thongBao; ?></div>
                    <?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="HinhAnhCu" value="<?php echo $sp['HinhAnh']; ?>">

                        <div class="mb-3">
                            <label class="form-label">Tên Sản Phẩm (Tivi)</label>
                            <input type="text" class="form-control" name="TenSanPham" value="<?php echo $sp['TenSanPham']; ?>" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Loại Tivi</label>
                                <select class="form-select" name="LoaiSanPhamID" required>
                                    <option value="">-- Chọn Loại --</option>
                                    <?php
                                    $sql_loai = "SELECT ID, TenLoai FROM LoaiSanPham";
                                    $kq_loai = $conn->query($sql_loai);
                                    while($row_loai = $kq_loai->fetch_assoc()) {
                                        $selected = ($row_loai['ID'] == $sp['LoaiSanPhamID']) ? 'selected' : '';
                                        echo "<option value='".$row_loai['ID']."' $selected>".$row_loai['TenLoai']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hãng Sản Xuất</label>
                                <select class="form-select" name="HangSanXuatID" required>
                                    <option value="">-- Chọn Hãng --</option>
                                    <?php
                                    $sql_hang = "SELECT ID, TenHangSanXuat FROM HangSanXuat";
                                    $kq_hang = $conn->query($sql_hang);
                                    while($row_hang = $kq_hang->fetch_assoc()) {
                                        $selected = ($row_hang['ID'] == $sp['HangSanXuatID']) ? 'selected' : '';
                                        echo "<option value='".$row_hang['ID']."' $selected>".$row_hang['TenHangSanXuat']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Giá Bán (VNĐ)</label>
                                <input type="number" class="form-control" name="DonGia" value="<?php echo $sp['DonGia']; ?>" min="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số Lượng</label>
                                <input type="number" class="form-control" name="SoLuong" value="<?php echo $sp['SoLuong']; ?>" min="0" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Hình Ảnh (Chỉ chọn nếu muốn đổi ảnh khác)</label>
                            <input type="file" class="form-control" name="HinhAnh" accept="image/*">
                            <div class="mt-2">
                                <p class="text-muted small mb-1">Ảnh hiện tại:</p>
                                <?php 
                                // ĐÃ SỬA: Lùi ra ngoài thư mục (../) để tải ảnh cũ
                                $hinhHienTai = !empty($sp['HinhAnh']) ? "../uploads/".$sp['HinhAnh'] : "../uploads/no-image.jpg";
                                ?>
                                <img src="<?php echo $hinhHienTai; ?>" alt="Ảnh Tivi" class="img-thumbnail" style="max-height: 120px;">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="san_pham.php" class="btn btn-secondary">Hủy bỏ / Quay lại</a>
                            <button type="submit" class="btn btn-warning fw-bold">Lưu Cập Nhật</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>