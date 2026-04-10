<?php
session_start();
require_once '../thu_vien/connect.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phiếu bảo hành  #HD<?php echo $hoaDonID; ?></title>
    <link rel="stylesheet" href="../tai_nguyen/css/style.css">
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