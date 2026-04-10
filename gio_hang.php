<?php
session_start();
require_once 'thu_vien/connect.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng của bạn - N&U Store</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="tai_nguyen/css/style.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark navbar-premium sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4 text-white" href="trang_chu.php"><i class="bi bi-tv text-danger"></i> N&U</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="trang_chu.php">Khám Phá</a></li>
                    <li class="nav-item"><a class="nav-link" href="san_pham.php">Sản Phẩm</a></li>
                    <li class="nav-item"><a class="nav-link" href="trang_chu.php#tin-tuc">Tin Tức</a></li>
                </ul>
                <div class="d-flex align-items-center gap-3">
                    <a href="trang_chu.php" class="btn btn-outline-light btn-pill">Tiếp tục mua sắm</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5 mb-5">
        <h2 class="premium-section-title">Giỏ Hàng Của Bạn</h2>

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
                                // Bổ sung lấy cột PhanTramGiam
                                $sql = "SELECT TenSanPham, DonGia, HinhAnh, PhanTramGiam FROM SanPham WHERE ID = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $id_sp);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                
                                if ($row = $result->fetch_assoc()) {
                                    $hinhAnh = !empty($row['HinhAnh']) ? "uploads/".$row['HinhAnh'] : "uploads/no-image.jpg";
                                    
                                    // XỬ LÝ TÍNH TOÁN GIÁ KHUYẾN MÃI
                                    $giaGoc = $row['DonGia'];
                                    $phanTram = isset($row['PhanTramGiam']) ? $row['PhanTramGiam'] : 0;
                                    $giaBanThucTe = $giaGoc - ($giaGoc * $phanTram / 100);
                                    
                                    $thanhTien = $giaBanThucTe * $so_luong;
                                    $tongTien += $thanhTien;
                                    
                                    // Xử lý hiển thị giao diện Giá
                                    if ($phanTram > 0) {
                                        $hienThiGia = '<span class="text-muted text-decoration-line-through small">'.number_format($giaGoc, 0, ',', '.').' đ</span><br>
                                                       <span class="text-danger fw-bold">'.number_format($giaBanThucTe, 0, ',', '.').' đ</span>';
                                        $hienThiTen = $row['TenSanPham'] . ' <span class="badge bg-danger ms-2">-'.$phanTram.'%</span>';
                                    } else {
                                        $hienThiGia = '<span class="text-danger fw-bold">'.number_format($giaGoc, 0, ',', '.').' đ</span>';
                                        $hienThiTen = $row['TenSanPham'];
                                    }
                                    
                                    echo '<tr>
                                            <td><img src="'.$hinhAnh.'" width="60" class="rounded border"></td>
                                            <td class="fw-bold">'.$hienThiTen.'</td>
                                            <td>'.$hienThiGia.'</td>
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
                <div>
                    <a href="trang_chu.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Tiếp tục mua hàng</a>
                    <a href="xoa_gio_hang.php?clear=1" onclick="return confirm('Bạn có chắc muốn xóa toàn bộ giỏ hàng?');" class="btn btn-outline-danger ms-2"><i class="bi bi-trash"></i> Xóa giỏ hàng của tôi</a>
                </div>
                <div class="text-end">
                    <span class="text-muted">Tổng thanh toán:</span>
                    <h2 class="text-danger fw-bold mb-0"><?php echo number_format($tongTien, 0, ',', '.'); ?> VNĐ</h2>
                    <a href="thanh_toan.php" class="btn btn-success btn-lg mt-3 px-5"><i class="bi bi-check-circle-fill"></i> ĐẶT HÀNG NGAY</a>
                </div>
            </div>
        </div>
    <?php } ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>