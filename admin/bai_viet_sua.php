<?php
session_start();
require_once '../thu_vien/connect.php';
require_once '../thu_vien/nhatky_helper.php';

// Kiểm tra quyền nhân viên
if (!isset($_SESSION['nhanvien_id'])) {
    header("Location: ../login.php");
    exit();
}

$id_bai = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$thongBao = '';

// 1. Lấy thông tin bài viết cũ
$sql_get = "SELECT * FROM baiviet WHERE ID = ?";
$stmt_get = $conn->prepare($sql_get);
$stmt_get->bind_param("i", $id_bai);
$stmt_get->execute();
$bai = $stmt_get->get_result()->fetch_assoc();

if (!$bai) {
    echo "<script>alert('Bài viết không tồn tại!'); window.location.href='bai_viet.php';</script>";
    exit();
}

// 2. Xử lý cập nhật khi bấm Lưu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cap_nhat'])) {
    $tieuDe = trim($_POST['TieuDe']);
    $noiDung = trim($_POST['NoiDung']);
    $trangThai = isset($_POST['TrangThai']) ? 1 : 0;
    $hinhAnhMoi = $bai['HinhAnh']; // Mặc định giữ ảnh cũ

    if (empty($tieuDe) || empty($noiDung)) {
        $thongBao = "<div class='alert alert-danger'>Vui lòng nhập đầy đủ tiêu đề và nội dung!</div>";
    } else {
        // Xử lý nếu có upload ảnh mới
        if (isset($_FILES['HinhAnh']) && $_FILES['HinhAnh']['error'] == 0) {
            $file_name = basename($_FILES['HinhAnh']['name']);
            $hinhAnhMoi = time() . "_" . $file_name;
            $upload_path = "../uploads/" . $hinhAnhMoi;
            
            if (move_uploaded_file($_FILES['HinhAnh']['tmp_name'], $upload_path)) {
                // Xóa ảnh cũ nếu có (để nhẹ server)
                if (!empty($bai['HinhAnh']) && file_exists("../uploads/" . $bai['HinhAnh'])) {
                    unlink("../uploads/" . $bai['HinhAnh']);
                }
            }
        }

        $sql_update = "UPDATE baiviet SET TieuDe = ?, NoiDung = ?, HinhAnh = ?, TrangThai = ? WHERE ID = ?";
        $stmt_up = $conn->prepare($sql_update);
        $stmt_up->bind_param("sssii", $tieuDe, $noiDung, $hinhAnhMoi, $trangThai, $id_bai);
        
        if ($stmt_up->execute()) {
            ghiNhatKyTuSession($conn, 'SuaBaiViet', 'baiviet', $id_bai, "Cập nhật bài viết: $tieuDe");
            echo "<script>alert('Cập nhật bài viết thành công!'); window.location.href='bai_viet.php';</script>";
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
        <h3 class="text-primary fw-bold"><i class="bi bi-pencil-square"></i> Chỉnh sửa bài viết</h3>
        <a href="bai_viet.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Quay lại danh sách</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <?php echo $thongBao; ?>
            <form action="" method="POST" enctype="multipart/form-data">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Tiêu đề bài viết</label>
                    <input type="text" name="TieuDe" class="form-control form-control-lg" value="<?php echo htmlspecialchars($bai['TieuDe']); ?>" required>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Ảnh bìa hiện tại</label>
                        <div class="mb-2">
                            <img src="../uploads/<?php echo !empty($bai['HinhAnh']) ? $bai['HinhAnh'] : 'no-image.jpg'; ?>" width="150" class="img-thumbnail">
                        </div>
                        <label class="form-label small text-muted">Chọn ảnh mới nếu muốn thay đổi:</label>
                        <input type="file" name="HinhAnh" class="form-control" accept="image/*">
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check form-switch fs-5 mb-3">
                            <input class="form-check-input" type="checkbox" name="TrangThai" id="TrangThai" <?php echo ($bai['TrangThai'] == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label fw-bold" for="TrangThai">Hiển thị bài viết</label>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Nội dung chi tiết</label>
                    <textarea name="NoiDung" id="NoiDung" class="form-control" rows="15"><?php echo $bai['NoiDung']; ?></textarea>
                    <script>
                        CKEDITOR.replace('NoiDung', {
                            height: 400
                        });
                    </script>
                </div>

                <button type="submit" name="cap_nhat" class="btn btn-primary btn-lg w-100 fw-bold">
                    <i class="bi bi-save"></i> LƯU THAY ĐỔI
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>