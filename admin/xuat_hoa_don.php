<?php
session_start();
require_once '../connect.php';

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
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f2f2f2;
            margin: 0;
            padding: 20px;
            color: #222;
        }

        .invoice-box {
            width: 900px;
            max-width: 100%;
            margin: auto;
            background: #fff;
            padding: 30px 35px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.08);
        }

        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #ddd;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .company {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .company img {
            width: 110px;
            height: 110px;
            object-fit: contain;
        }

        .company-info h2 {
            margin: 0 0 8px;
            color: #0d3b8e;
            font-size: 28px;
        }

        .company-info p {
            margin: 4px 0;
            font-size: 16px;
        }

        .title {
            text-align: center;
            color: #d61f2c;
            font-size: 40px;
            font-weight: bold;
            margin: 10px 0 25px;
            text-transform: uppercase;
        }

        .meta {
            text-align: right;
            font-size: 18px;
            margin-bottom: 30px;
            font-weight: bold;
            color: #16325c;
        }

        .section-title {
            color: #d61f2c;
            font-size: 28px;
            font-weight: bold;
            margin: 25px 0 12px;
            border-bottom: 2px solid #eee;
            padding-bottom: 8px;
        }

        .info p {
            margin: 8px 0;
            font-size: 18px;
            line-height: 1.6;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        table th, table td {
            border: 1px solid #d9d9d9;
            padding: 12px 10px;
            font-size: 17px;
        }

        table th {
            background: #f5f7fb;
            text-align: center;
            color: #10284f;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-red { color: #d61f2c; font-weight: bold; }

        .tong-cong {
            margin-top: 18px;
            text-align: right;
            font-size: 22px;
            font-weight: bold;
        }

        .tong-cong span {
            color: #d61f2c;
            font-size: 26px;
        }

        .ghi-chu {
            margin-top: 25px;
            font-size: 18px;
            line-height: 1.6;
        }

        .sign {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
            text-align: center;
        }

        .sign-box {
            width: 42%;
            font-size: 18px;
        }

        .sign-box strong {
            display: block;
            margin-bottom: 50px;
            font-size: 20px;
        }

        .screen-buttons {
            width: 900px;
            max-width: 100%;
            margin: 0 auto 15px;
            text-align: right;
        }

        .btn {
            display: inline-block;
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            color: white;
            background: #0d6efd;
            margin-left: 8px;
            font-size: 15px;
        }

        .btn-back {
            background: #6c757d;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .screen-buttons {
                display: none;
            }

            .invoice-box {
                box-shadow: none;
                border-radius: 0;
                width: 100%;
                margin: 0;
                padding: 20px;
            }
        }
    </style>
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