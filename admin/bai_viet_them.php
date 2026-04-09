<?php
session_start();
require_once '../connect.php';
require_once '../nhatky_helper.php';

// Kiểm tra nhân viên đăng nhập
if (!isset($_SESSION['nhanvien_id'])) {
    header("Location: ../login.php");
    exit();
}

$thongBao = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tieuDe = trim($_POST['TieuDe']);
    $noiDung = trim($_POST['NoiDung']);
    $trangThai = isset($_POST['TrangThai']) ? 1 : 0;
    $nhanVienID = $_SESSION['nhanvien_id'];
    $hinhAnh = null;

    if (empty($tieuDe) || empty($noiDung)) {
        $thongBao = "<div class='alert alert-danger'>Vui lòng nhập Tiêu đề và Nội dung!</div>";
    } else {
        // Xử lý upload ảnh bìa
        if (isset($_FILES['HinhAnh']) && $_FILES['HinhAnh']['error'] == 0) {
            $file_name = basename($_FILES['HinhAnh']['name']);
            $file_tmp = $_FILES['HinhAnh']['tmp_name'];
            
            // Đổi tên file để không bị trùng
            $hinhAnh = time() . "_" . $file_name;
            $upload_path = "../uploads/" . $hinhAnh;
            
            move_uploaded_file($file_tmp, $upload_path);
        }

        $sql = "INSERT INTO baiviet (TieuDe, NoiDung, HinhAnh, NhanVienID, TrangThai) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $tieuDe, $noiDung, $hinhAnh, $nhanVienID, $trangThai);
        
        if ($stmt->execute()) {
            $id_bai = $conn->insert_id;
            ghiNhatKyTuSession($conn, 'ThemBaiViet', 'baiviet', $id_bai, "Đăng bài viết: $tieuDe");
            echo "<script>alert('Đăng bài thành công!'); window.location.href='bai_viet.php';</script>";
            exit();
        } else {
            $thongBao = "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
        }
    }
}

require_once 'header.php';
require_once 'sidebar.php';
?>

<script src="https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>

<div class="col-md-9 col-lg-10 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary fw-bold"><i class="bi bi-pencil-square"></i> Viết bài mới</h3>
        <a href="bai_viet.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Quay lại</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <?php echo $thongBao; ?>
            <form action="" method="POST" enctype="multipart/form-data">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Tiêu đề bài viết *</label>
                    <input type="text" name="TieuDe" class="form-control form-control-lg" placeholder="Nhập tiêu đề thật giật gân..." required>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Ảnh bìa thu nhỏ</label>
                        <input type="file" name="HinhAnh" class="form-control" accept="image/*">
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check form-switch fs-5">
                            <input class="form-check-input" type="checkbox" name="TrangThai" id="TrangThai" checked>
                            <label class="form-check-label fw-bold" for="TrangThai">Hiển thị bài viết ngay lập tức</label>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Nội dung chi tiết *</label>
                    <textarea name="NoiDung" id="NoiDung" class="form-control" rows="10" required></textarea>
                    <script>
                        // Kích hoạt CKEditor cho khung nhập liệu
                        CKEDITOR.replace('NoiDung');
                    </script>
                </div>

                <button type="submit" class="btn btn-success btn-lg w-100 fw-bold">
                    <i class="bi bi-send-check"></i> XUẤT BẢN BÀI VIẾT
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>