<?php
session_start();
require_once '../connect.php';

$hoaDonID = isset($_GET['hoadon_id']) ? (int)$_GET['hoadon_id'] : 0;
$sanPhamID = isset($_GET['sanpham_id']) ? (int)$_GET['sanpham_id'] : 0;

if ($hoaDonID <= 0 || $sanPhamID <= 0) {
    die("Không tìm thấy nhóm bảo hành.");
}

// Lấy thông tin chung hóa đơn + khách hàng + sản phẩm
$sql_info = "SELECT 
                h.ID AS MaHoaDon,
                h.NgayLap,
                h.GhiChuHoaDon,
                k.HoVaTen,
                k.DienThoai,
                k.DiaChi,
                sp.ID AS MaSanPham,
                sp.TenSanPham
             FROM hoadon h
             JOIN khachhang k ON h.KhachHangID = k.ID
             JOIN sanpham sp ON sp.ID = ?
             WHERE h.ID = ?";

$stmt_info = $conn->prepare($sql_info);
$stmt_info->bind_param("ii", $sanPhamID, $hoaDonID);
$stmt_info->execute();
$thongTin = $stmt_info->get_result()->fetch_assoc();

if (!$thongTin) {
    die("Không tìm thấy dữ liệu bảo hành.");
}

// Lấy danh sách các phiếu bảo hành thuộc cùng hóa đơn + sản phẩm
$sql_ds = "SELECT *
           FROM baohanh
           WHERE HoaDonID = ? AND SanPhamID = ?
           ORDER BY ID ASC";

$stmt_ds = $conn->prepare($sql_ds);
$stmt_ds->bind_param("ii", $hoaDonID, $sanPhamID);
$stmt_ds->execute();
$result_ds = $stmt_ds->get_result();

$danhSachBaoHanh = [];
while ($row = $result_ds->fetch_assoc()) {
    $danhSachBaoHanh[] = $row;
}

if (count($danhSachBaoHanh) == 0) {
    die("Không có phiếu bảo hành nào.");
}

$logoPath = "../uploads/logodoan.jpg";
if (!file_exists($logoPath)) {
    $logoPath = "../uploads/no-image.jpg";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phiếu bảo hành  #HD<?php echo $hoaDonID; ?></title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f2f2f2;
            margin: 0;
            padding: 20px;
            color: #222;
        }

        .screen-buttons {
            width: 1000px;
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

        .card-box {
            width: 1000px;
            max-width: 100%;
            margin: auto;
            background: #fff;
            padding: 30px 35px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.08);
        }

        .top {
            display: flex;
            align-items: center;
            gap: 20px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .top img {
            width: 100px;
            height: 100px;
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
            color: red;
            font-size: 38px;
            font-weight: bold;
            margin: 10px 0 30px;
            text-transform: uppercase;
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
            margin: 10px 0;
            font-size: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        table th, table td {
            border: 1px solid #d9d9d9;
            padding: 12px 10px;
            font-size: 16px;
        }

        table th {
            background: #f8f9fa;
            text-align: center;
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

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .screen-buttons {
                display: none;
            }

            .card-box {
                box-shadow: none;
                border-radius: 0;
                width: 100%;
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<div class="screen-buttons">
    <a href="bao_hanh_chi_tiet.php?hoadon_id=<?php echo $hoaDonID; ?>&sanpham_id=<?php echo $sanPhamID; ?>" class="btn btn-back">Quay lại</a>
    <a href="javascript:window.print()" class="btn">In / Lưu PDF</a>
</div>

<div class="card-box">
    <div class="top">
        <img src="<?php echo $logoPath; ?>" alt="Logo">
        <div class="company-info">
            <h2>Công ty TNHH N&amp;U</h2>
            <p><strong>Địa chỉ:</strong> Long Xuyên, An Giang</p>
            <p><strong>Điện thoại:</strong> 0901 234 567</p>
            <p><strong>Email:</strong> hotro@tivinu.com</p>
        </div>
    </div>

    <div class="title">Phiếu bảo hành</div>

    <div class="section-title">1. Thông tin khách hàng</div>
    <div class="info">
        <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($thongTin['HoVaTen']); ?></p>
        <p><strong>Điện thoại:</strong> <?php echo htmlspecialchars($thongTin['DienThoai']); ?></p>
        <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($thongTin['DiaChi']); ?></p>
    </div>

    <div class="section-title">2. Thông tin sản phẩm</div>
    <div class="info">
        <p><strong>Tên sản phẩm:</strong> <?php echo htmlspecialchars($thongTin['TenSanPham']); ?></p>
        <p><strong>Mã hóa đơn:</strong> #HD<?php echo $thongTin['MaHoaDon']; ?></p>
        <p><strong>Ngày mua:</strong> <?php echo date('d/m/Y', strtotime($thongTin['NgayLap'])); ?></p>
        <p><strong>Số lượng bảo hành:</strong> <?php echo count($danhSachBaoHanh); ?></p>
    </div>

    <div class="section-title">3. Danh sách serial bảo hành</div>
    <table>
        <thead>
            <tr>
                <th style="width: 70px;">STT</th>
                <th style="width: 120px;">Mã BH</th>
                <th>Số serial</th>
                <th style="width: 140px;">Ngày kích hoạt</th>
                <th style="width: 140px;">Ngày hết hạn</th>
                <th style="width: 160px;">Trạng thái</th>
            </tr>
        </thead>
        <tbody>
            <?php $stt = 1; ?>
            <?php foreach ($danhSachBaoHanh as $bh): ?>
                <tr>
                    <td style="text-align:center;"><?php echo $stt++; ?></td>
                    <td>#BH<?php echo $bh['ID']; ?></td>
                    <td><?php echo htmlspecialchars($bh['SoSerial']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($bh['NgayKichHoat'])); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($bh['NgayHetHan'])); ?></td>
                    <td><?php echo htmlspecialchars($bh['TrangThai']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="section-title">4. Quy định bảo hành</div>
    <div class="info">
        <p>- Vui lòng xuất trình phiếu bảo hành khi cần hỗ trợ.</p>
        <p>- Phiếu chỉ có hiệu lực với đúng sản phẩm và số serial ghi trong danh sách trên.</p>
        <p>- Không bảo hành các trường hợp rơi vỡ, vào nước, cháy nổ do người dùng.</p>
    </div>

    <div class="sign">
        <div class="sign-box">
            <strong>Khách hàng</strong>
            <div>(Ký và ghi rõ họ tên)</div>
        </div>

        <div class="sign-box">
            <strong>Nhân viên xác nhận</strong>
            <div>Công ty N&amp;U</div>
        </div>
    </div>
</div>

</body>
</html>