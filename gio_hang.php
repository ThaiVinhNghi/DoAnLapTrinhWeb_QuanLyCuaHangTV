<?php
session_start();
require_once 'connect.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Giỏ hàng của bạn - TIVI STORE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="trang_chu.php"><i class="bi bi-tv"></i> TIVI STORE</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="trang_chu.php">Trang Chủ</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5 mb-5">
    <h3 class="mb-4 text-primary fw-bold"><i class="bi bi-cart3"></i> Giỏ Hàng Của Bạn</h3>

    <?php
    // Kiểm tra xem giỏ hàng có tồn tại và có sản phẩm nào không
    if (!isset($_SESSION['gio_hang']) || empty($_SESSION['gio_hang'])) {
        echo '<div class="alert alert-warning text-center p-5">
                <h4>Giỏ hàng của bạn đang trống!</h4>
                <p>Hãy quay lại trang chủ để chọn mua những chiếc Tivi tuyệt vời nhé.</p>
                <a href="trang_chu.php" class="btn btn-primary mt-3">Tiếp tục mua sắm</a>
              </div>';
    } else {
        // Có sản phẩm trong giỏ hàng
    ?>
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Hình ảnh</th>
                                <th>Tên Sản Phẩm</th>
                                <th>Đơn Giá</th>
                                <th width="120">Số Lượng</th>
                                <th>Thành Tiền</th>
                                <th>Xóa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $tongTien = 0;
                            // Vòng lặp lấy từng ID sản phẩm và Số lượng trong Session
                            foreach ($_SESSION['gio_hang'] as $id_sp => $so_luong) {
                                // Truy vấn CSDL để lấy tên và giá của ID này
                                $sql = "SELECT TenSanPham, DonGia, HinhAnh FROM SanPham WHERE ID = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $id_sp);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                
                                if ($row = $result->fetch_assoc()) {
                                    $hinhAnh = !empty($row['HinhAnh']) ? "uploads/".$row['HinhAnh'] : "uploads/no-image.jpg";
                                    $thanhTien = $row['DonGia'] * $so_luong;
                                    $tongTien += $thanhTien;
                                    
                                    echo '<tr>
                                            <td><img src="'.$hinhAnh.'" width="60" class="rounded border"></td>
                                            <td class="fw-bold">'.$row['TenSanPham'].'</td>
                                            <td class="text-danger">'.number_format($row['DonGia'], 0, ',', '.').' đ</td>
                                            <td>
                                                <input type="number" class="form-control text-center" value="'.$so_luong.'" readonly>
                                            </td>
                                            <td class="text-danger fw-bold">'.number_format($thanhTien, 0, ',', '.').' đ</td>
                                            <td>
                                                <a href="xoa_gio_hang.php?id='.$id_sp.'" class="btn btn-sm btn-outline-danger" title="Xóa khỏi giỏ"><i class="bi bi-trash"></i></a>
                                            </td>
                                          </tr>';
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white p-4 d-flex justify-content-between align-items-center">
                <a href="trang_chu.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Tiếp tục mua hàng</a>
                <div class="text-end">
                    <span class="text-muted">Tổng thanh toán:</span>
                    <h2 class="text-danger fw-bold mb-0"><?php echo number_format($tongTien, 0, ',', '.'); ?> VNĐ</h2>
                    <a href="thanh_toan.php" class="btn btn-success btn-lg mt-3 px-5"><i class="bi bi-check-circle-fill"></i> ĐẶT HÀNG NGAY</a>
                </div>
            </div>
        </div>
    <?php } ?>
</div>

</body>
</html>