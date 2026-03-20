<?php
session_start();
require_once '../connect.php';

$pn_id = $_GET['id'];

// 1. Lấy thông tin chung của phiếu nhập
$sql_pn = "SELECT pn.*, nv.HoVaTen, ncc.TenNhaCungCap 
           FROM phieunhap pn
           JOIN nhanvien nv ON pn.NhanVienID = nv.ID
           JOIN nhacungcap ncc ON pn.NhaCungCapID = ncc.ID
           WHERE pn.ID = $pn_id";
$info = $conn->query($sql_pn)->fetch_assoc();

// 2. Lấy danh sách sản phẩm trong phiếu đó (Sửa JOIN thành LEFT JOIN)
$sql_ct = "SELECT ct.*, sp.TenSanPham 
           FROM phieunhap_chitiet ct
           LEFT JOIN sanpham sp ON ct.SanPhamID = sp.ID
           WHERE ct.PhieuNhapID = $pn_id";
$result_ct = $conn->query($sql_ct);

require_once 'header.php';
require_once 'sidebar.php';
?>

<div class="col-md-9 col-lg-10 mb-5">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Chi Tiết Phiếu Nhập #<?php echo $pn_id; ?></h5>
            <a href="nhap_hang.php" class="btn btn-sm btn-secondary">Quay lại</a>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <p><strong>Nhà cung cấp:</strong> <?php echo $info['TenNhaCungCap']; ?></p>
                    <p><strong>Ngày nhập:</strong> <?php echo date("d/m/Y H:i", strtotime($info['NgayNhap'])); ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p><strong>Nhân viên lập:</strong> <?php echo $info['HoVaTen']; ?></p>
                    <p><strong>Ghi chú:</strong> <?php echo $info['GhiChu']; ?></p>
                </div>
            </div>

            <table class="table table-bordered">
                <thead class="table-light text-center">
                    <tr>
                        <th>STT</th>
                        <th>Tên Sản Phẩm</th>
                        <th>Số Lượng</th>
                        <th>Đơn Giá Nhập</th>
                        <th>Thành Tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $stt = 1;
                    while($item = $result_ct->fetch_assoc()): ?>
                    <tr>
                        <td class="text-center"><?php echo $stt++; ?></td>
                        <td><?php echo $item['TenSanPham']; ?></td>
                        <td class="text-center"><?php echo $item['SoLuongNhap']; ?></td>
                        <td class="text-end"><?php echo number_format($item['DonGiaNhap'], 0, ',', '.'); ?> đ</td>
                        <td class="text-end fw-bold"><?php echo number_format($item['SoLuongNhap'] * $item['DonGiaNhap'], 0, ',', '.'); ?> đ</td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">TỔNG CỘNG:</th>
                        <th class="text-end text-danger fs-5"><?php echo number_format($info['TongTien'], 0, ',', '.'); ?> đ</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>