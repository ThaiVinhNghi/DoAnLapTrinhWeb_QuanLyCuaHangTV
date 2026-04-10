<?php
session_start();
// Hỗ trợ xóa một phần tử (id=...) hoặc xóa toàn bộ giỏ hàng (?clear=1)
if (isset($_GET['clear']) && $_GET['clear'] == '1') {
    if (isset($_SESSION['gio_hang'])) {
        unset($_SESSION['gio_hang']);
    }
    header("Location: gio_hang.php");
    exit();
}

if (isset($_GET['id'])) {
    // Lấy ID sản phẩm cần xóa từ URL (cast để an toàn)
    $id_sp = (int)$_GET['id'];

    // Kiểm tra giỏ hàng có tồn tại và sản phẩm có trong giỏ hay không
    if (isset($_SESSION['gio_hang']) && isset($_SESSION['gio_hang'][$id_sp])) {
        unset($_SESSION['gio_hang'][$id_sp]);
    }

    // Sau khi xóa xong thì quay lại trang giỏ hàng
    header("Location: gio_hang.php");
    exit();
}

// Nếu không có tham số hợp lệ, quay về trang chủ
header("Location: trang_chu.php");
exit();

?>