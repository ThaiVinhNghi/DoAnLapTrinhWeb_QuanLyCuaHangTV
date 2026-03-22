<?php
session_start();
// Lùi ra ngoài để tìm file login
if (!isset($_SESSION['nhanvien_id'])) {
    header("Location: ../login.php");
    exit();
}
// Lùi ra ngoài để gọi file connect
require_once '../connect.php';

$thongBao = '';

// Nếu người dùng bấm nút "Thêm Sản Phẩm" (Gửi form qua POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tenSanPham = $_POST['TenSanPham'];
    $loaiSanPhamID = $_POST['LoaiSanPhamID'];
    $hangSanXuatID = $_POST['HangSanXuatID'];
    $donGia = $_POST['DonGia'];
    $soLuong = $_POST['SoLuong'];
    
    // Xử lý upload ảnh
    $hinhAnh = '';
    // Kiểm tra xem người dùng có chọn file ảnh không và không bị lỗi
    if (isset($_FILES['HinhAnh']) && $_FILES['HinhAnh']['error'] == 0) {
        // Gắn thêm thời gian hiện tại vào tên ảnh để tránh trùng lặp tên file
        $hinhAnh = time() . '_' . basename($_FILES['HinhAnh']['name']); 
        $target_dir = "../uploads/"; // ĐÃ SỬA: Lùi ra ngoài thư mục gốc để lưu vào uploads
        $target_file = $target_dir . $hinhAnh;
        
        // Di chuyển file từ bộ nhớ tạm vào thư mục uploads
        move_uploaded_file($_FILES["HinhAnh"]["tmp_name"], $target_file);
    }

    // Viết câu lệnh SQL thêm vào bảng SanPham
    $sql = "INSERT INTO SanPham (TenSanPham, LoaiSanPhamID, HangSanXuatID, DonGia, SoLuong, HinhAnh) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siiiis", $tenSanPham, $loaiSanPhamID, $hangSanXuatID, $donGia, $soLuong, $hinhAnh);
    
    if ($stmt->execute()) {
        // Thêm xong thì chuyển về trang danh sách (nằm cùng thư mục admin nên không cần ../)
        header("Location: san_pham.php");
        exit();
    } else {
        $thongBao = "Lỗi thêm dữ liệu: " . $conn->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Tivi mới - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Thêm Tivi Mới</h5>
                </div>
                <div class="card-body">
                    
                    <?php if($thongBao != ''): ?>
                        <div class="alert alert-danger"><?php echo $thongBao; ?></div>
                    <?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Tên Sản Phẩm (Tivi)</label>
                            <input type="text" class="form-control" name="TenSanPham" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Loại Tivi</label>
                                <select class="form-select" name="LoaiSanPhamID" required>
                                    <option value="">-- Chọn Loại --</option>
                                    <?php
                                    // Lấy danh sách Loại từ CSDL
                                    $sql_loai = "SELECT ID, TenLoai FROM LoaiSanPham";
                                    $kq_loai = $conn->query($sql_loai);
                                    while($row_loai = $kq_loai->fetch_assoc()) {
                                        echo "<option value='".$row_loai['ID']."'>".$row_loai['TenLoai']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hãng Sản Xuất</label>
                                <select class="form-select" name="HangSanXuatID" required>
                                    <option value="">-- Chọn Hãng --</option>
                                    <?php
                                    // Lấy danh sách Hãng từ CSDL
                                    $sql_hang = "SELECT ID, TenHangSanXuat FROM HangSanXuat";
                                    $kq_hang = $conn->query($sql_hang);
                                    while($row_hang = $kq_hang->fetch_assoc()) {
                                        echo "<option value='".$row_hang['ID']."'>".$row_hang['TenHangSanXuat']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Giá Bán (VNĐ)</label>
                                <input type="number" class="form-control" name="DonGia" min="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số Lượng</label>
                                <input type="number" class="form-control" name="SoLuong" min="0" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Hình Ảnh</label>
                            <input type="file" class="form-control" name="HinhAnh" accept="image/*">
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="san_pham.php" class="btn btn-secondary">Quay lại</a>
                            <button type="submit" class="btn btn-success">Lưu Sản Phẩm</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>