<?php
session_start();
// Kiểm tra đăng nhập
if (!isset($_SESSION['nhanvien_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../thu_vien/connect.php';

// Kiểm tra xem có nhận được ID từ trên thanh URL (phương thức GET) không
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // BƯỚC 1: Tìm tên hình ảnh của Tivi này để xóa file ảnh trong thư mục (Giúp web không bị rác)
    $sql_get_img = "SELECT HinhAnh FROM SanPham WHERE ID = ?";
    $stmt_get_img = $conn->prepare($sql_get_img);
    $stmt_get_img->bind_param("i", $id);
    $stmt_get_img->execute();
    $result = $stmt_get_img->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $hinhAnh = $row['HinhAnh'];
        // ĐÃ SỬA: Tìm file trong thư mục uploads ở bên ngoài
        if (!empty($hinhAnh) && file_exists("../uploads/" . $hinhAnh)) {
            unlink("../uploads/" . $hinhAnh); // Lệnh unlink dùng để xóa file vật lý
        }
    }
    $stmt_get_img->close();

    // BƯỚC 2: Thực hiện câu lệnh xóa dữ liệu trong cơ sở dữ liệu
    $sql_delete = "DELETE FROM SanPham WHERE ID = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $id);
    
    if ($stmt_delete->execute()) {
        // Nếu xóa thành công, tự động chuyển hướng về lại trang danh sách sản phẩm
        header("Location: san_pham.php");
        exit();
    } else {
        echo "Lỗi khi xóa dữ liệu: " . $conn->error;
    }
    $stmt_delete->close();
} else {
    // Nếu không có ID thì cũng quay về trang danh sách
    header("Location: san_pham.php");
    exit();
}

$conn->close();
?>