<?php
session_start();
require_once '../thu_vien/connect.php';

$id_hoadon = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_hoadon <= 0) {
    die("Không tìm thấy mã hóa đơn.");
}

// Lấy thông tin hóa đơn + khách hàng
$sql_hd = "SELECT h.*, k.HoVaTen, k.DienThoai, k.DiaChi
           FROM hoadon h
           JOIN khachhang k ON h.KhachHangID = k.ID
           WHERE h.ID = ?";
$stmt_hd = $conn->prepare($sql_hd);
$stmt_hd->bind_param("i", $id_hoadon);
$stmt_hd->execute();
$hoaDon = $stmt_hd->get_result()->fetch_assoc();

if (!$hoaDon) {
    die("Hóa đơn không tồn tại.");
}

// Lấy tên nhân viên duyệt
$tenNhanVienDuyet = 'Chưa duyệt';
if (!empty($hoaDon['NhanVienID'])) {
    $sql_nv = "SELECT HoVaTen FROM nhanvien WHERE ID = ?";
    $stmt_nv = $conn->prepare($sql_nv);
    $stmt_nv->bind_param("i", $hoaDon['NhanVienID']);
    $stmt_nv->execute();
    $row_nv = $stmt_nv->get_result()->fetch_assoc();
    if ($row_nv) {
        $tenNhanVienDuyet = $row_nv['HoVaTen'];
    }
}

// Lấy chi tiết sản phẩm
$sql_ct = "SELECT ct.SoLuongBan, ct.DonGiaBan, sp.TenSanPham
           FROM hoadon_chitiet ct
           JOIN sanpham sp ON ct.SanPhamID = sp.ID
           WHERE ct.HoaDonID = ?";
$stmt_ct = $conn->prepare($sql_ct);
$stmt_ct->bind_param("i", $id_hoadon);
$stmt_ct->execute();
$result_ct = $stmt_ct->get_result();

$dsSanPham = [];
$tongTien = 0;
$stt = 1;

while ($row = $result_ct->fetch_assoc()) {
    $row['ThanhTien'] = $row['SoLuongBan'] * $row['DonGiaBan'];
    $tongTien += $row['ThanhTien'];
    $row['STT'] = $stt++;
    $dsSanPham[] = $row;
}

// Đường dẫn logo
$logoPath = "../uploads/logodoan.jpg"; 
if (!file_exists($logoPath)) {
    $logoPath = "../uploads/no-image.jpg";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phiếu Xuất Hóa Đơn #HD<?php echo $id_hoadon; ?></title>
    <link rel="stylesheet" href="../tai_nguyen/css/style.css">
</head>
<body>

<div class="screen-buttons">
    <a href="hoa_don_chi_tiet.php?id=<?php echo $id_hoadon; ?>" class="btn btn-back">Quay lại</a>
    <a href="javascript:window.print()" class="btn">In / Lưu PDF</a>
</div>

<div class="invoice-box">
    <div class="top">
        <div class="company">
            <img src="<?php echo $logoPath; ?>" alt="Logo">
            <div class="company-info">
                <h2>Công ty TNHH N&amp;U</h2>
                <p><strong>Địa chỉ:</strong> Long Xuyên, An Giang</p>
                <p><strong>Điện thoại:</strong> 0901 234 567</p>
                <p><strong>Email:</strong> hotro@tivinu.com</p>
            </div>
        </div>
    </div>

    <div class="title">Phiếu xuất hóa đơn</div>

    <div class="meta">
        Số HĐ: HD<?php echo $id_hoadon; ?> &nbsp;&nbsp;|&nbsp;&nbsp;
        Ngày: <?php echo date('d/m/Y', strtotime($hoaDon['NgayLap'])); ?>
    </div>

    <div class="section-title">1. Thông tin khách hàng</div>
    <div class="info">
        <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($hoaDon['HoVaTen']); ?></p>
        <p><strong>Điện thoại:</strong> <?php echo htmlspecialchars($hoaDon['DienThoai']); ?></p>
        <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($hoaDon['DiaChi']); ?></p>
    </div>

    <div class="section-title">2. Thông tin sản phẩm</div>
    <table>
        <thead>
            <tr>
                <th style="width:70px;">STT</th>
                <th>Tên sản phẩm</th>
                <th style="width:120px;">Số lượng</th>
                <th style="width:160px;">Đơn giá</th>
                <th style="width:180px;">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dsSanPham as $sp): ?>
                <tr>
                    <td class="text-center"><?php echo $sp['STT']; ?></td>
                    <td><?php echo htmlspecialchars($sp['TenSanPham']); ?></td>
                    <td class="text-center"><?php echo (int)$sp['SoLuongBan']; ?></td>
                    <td class="text-right"><?php echo number_format($sp['DonGiaBan'], 0, ',', '.'); ?> đ</td>
                    <td class="text-right text-red"><?php echo number_format($sp['ThanhTien'], 0, ',', '.'); ?> đ</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="tong-cong">
        Tổng cộng: <span><?php echo number_format($tongTien, 0, ',', '.'); ?> đ</span>
    </div>

    <div class="section-title">3. Ghi chú</div>
    <div class="ghi-chu">
        <p>- Ghi chú đơn hàng: <?php echo !empty($hoaDon['GhiChuHoaDon']) ? htmlspecialchars($hoaDon['GhiChuHoaDon']) : 'Không có'; ?></p>
        <p>- Trạng thái duyệt: <?php echo htmlspecialchars($tenNhanVienDuyet); ?></p>
    </div>

    <div class="sign">
        <div class="sign-box">
            <strong>Người mua hàng</strong>
            <div>(Ký và ghi rõ họ tên)</div>
        </div>

        <div class="sign-box">
            <strong>Người lập hóa đơn</strong>
            <div><?php echo htmlspecialchars($tenNhanVienDuyet); ?></div>
        </div>
    </div>
</div>

</body>
</html>