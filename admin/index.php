<?php 
session_start();
require_once '../thu_vien/connect.php';

// =======================================================
// XỬ LÝ TỰ ĐỘNG HÓA DỮ LIỆU TỪ DATABASE
// =======================================================

// 1. Tính Doanh Thu Tháng Này (Dựa vào bảng hoadon_chitiet và hoadon)
$sql_doanhthu = "SELECT SUM(hc.SoLuongBan * hc.DonGiaBan) as TongDoanhThu 
                 FROM hoadon_chitiet hc 
                 JOIN hoadon hd ON hc.HoaDonID = hd.ID 
                 WHERE MONTH(hd.NgayLap) = MONTH(CURRENT_DATE()) 
                 AND YEAR(hd.NgayLap) = YEAR(CURRENT_DATE())";
$res_dt = $conn->query($sql_doanhthu);
$doanhThuThang = ($res_dt && $row = $res_dt->fetch_assoc()) ? (float)$row['TongDoanhThu'] : 0;

// 2. Tính Số Đơn Hàng Mới Trong Tháng
$sql_donhang = "SELECT COUNT(ID) as SoDon FROM hoadon 
                WHERE MONTH(NgayLap) = MONTH(CURRENT_DATE()) 
                AND YEAR(NgayLap) = YEAR(CURRENT_DATE())";
$res_dh = $conn->query($sql_donhang);
$soDonHang = ($res_dh && $row = $res_dh->fetch_assoc()) ? (int)$row['SoDon'] : 0;

// 3. Tổng Số Khách Hàng (Tự động cập nhật khi có khách đăng ký mới)
$sql_khachhang = "SELECT COUNT(ID) as SoKhach FROM khachhang";
$res_kh = $conn->query($sql_khachhang);
$soKhachHang = ($res_kh && $row = $res_kh->fetch_assoc()) ? (int)$row['SoKhach'] : 0;

// 4. Số Yêu Cầu Hỗ Trợ (Giả sử lấy số lượng Đơn Bảo Hành đang 'Chờ xử lý')
$sql_hotro = "SELECT COUNT(ID) as SoYeuCau FROM baohanh WHERE TrangThai = 'Chờ xử lý'";
$res_ht = $conn->query($sql_hotro);
$soYeuCau = ($res_ht && $row = $res_ht->fetch_assoc()) ? (int)$row['SoYeuCau'] : 0;

// 5. Lấy Danh Sách Sản Phẩm Bán Chạy Nhất (Top 5)
$sql_banchay = "SELECT sp.ID, sp.TenSanPham, sp.HinhAnh, SUM(hc.SoLuongBan) as TongDaBan
                FROM hoadon_chitiet hc
                JOIN sanpham sp ON hc.SanPhamID = sp.ID
                GROUP BY sp.ID
                ORDER BY TongDaBan DESC
                LIMIT 5";
$result_banchay = $conn->query($sql_banchay);

// 6. Lấy Danh Sách Sản Phẩm Sắp Hết Hàng (Tồn kho <= 5, Top 5)
$sql_hethang = "SELECT ID, TenSanPham, HinhAnh, SoLuong 
                FROM sanpham 
                WHERE SoLuong <= 5 
                ORDER BY SoLuong ASC 
                LIMIT 5";
$result_hethang = $conn->query($sql_hethang);


// Gọi file Header & Sidebar giao diện
include 'header.php'; 
include 'sidebar.php'; 
?>

<style>
    .main-content { padding: 30px; }
    .product-list-item img { width: 50px; height: 50px; object-fit: contain; mix-blend-mode: multiply; }
</style>

<div class="col-md-9 col-lg-10 main-content">
    
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-secondary">Admin</a></li>
            <li class="breadcrumb-item active fw-bold text-dark" aria-current="page">Bảng điều khiển</li>
        </ol>
    </nav>

    <h3 class="fw-bold mb-4">Tổng Quan Hệ Thống</h3>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-bottom border-success border-4">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase mb-2" style="font-size: 0.75rem;">Doanh Thu Tháng</h6>
                        <h4 class="fw-bold text-dark m-0"><?php echo number_format($doanhThuThang, 0, ',', '.'); ?> đ</h4>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                        <i class="bi bi-graph-up-arrow fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-bottom border-primary border-4">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase mb-2" style="font-size: 0.75rem;">Đơn Hàng Tháng</h6>
                        <h4 class="fw-bold text-dark m-0"><?php echo $soDonHang; ?></h4>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                        <i class="bi bi-cart-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-bottom border-info border-4">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase mb-2" style="font-size: 0.75rem;">Khách Hàng</h6>
                        <h4 class="fw-bold text-dark m-0"><?php echo $soKhachHang; ?></h4>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                        <i class="bi bi-people-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-bottom border-danger border-4">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase mb-2" style="font-size: 0.75rem;">Chờ Xử Lý</h6>
                        <h4 class="fw-bold text-danger m-0"><?php echo $soYeuCau; ?></h4>
                    </div>
                    <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle">
                        <i class="bi bi-headset fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold m-0"><i class="bi bi-star-fill text-warning me-2"></i> Top 5 Sản Phẩm Bán Chạy</h6>
                    <a href="san_pham.php" class="text-decoration-none small text-primary">Xem tất cả</a>
                </div>
                <div class="card-body p-4">
                    <div class="list-group list-group-flush">
                        <?php if ($result_banchay && $result_banchay->num_rows > 0): ?>
                            <?php while($sp = $result_banchay->fetch_assoc()): ?>
                                <?php $img = !empty($sp['HinhAnh']) ? "../uploads/" . $sp['HinhAnh'] : "../uploads/no-image.jpg"; ?>
                                <div class="list-group-item px-0 py-3 d-flex align-items-center product-list-item border-bottom-0">
                                    <div class="bg-light rounded p-2 me-3">
                                        <img src="<?php echo $img; ?>" alt="Ảnh">
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1 text-dark" style="font-size: 0.9rem;"><?php echo htmlspecialchars($sp['TenSanPham']); ?></h6>
                                        <small class="text-muted">Mã SP: #<?php echo $sp['ID']; ?></small>
                                    </div>
                                    <div>
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">
                                            Đã bán: <?php echo $sp['TongDaBan']; ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted text-center my-4">Chưa có dữ liệu bán hàng.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold m-0"><i class="bi bi-exclamation-circle-fill text-danger me-2"></i> Sản Phẩm Sắp Hết Hàng</h6>
                    <a href="nhap_hang.php" class="text-decoration-none small text-danger">Tạo phiếu nhập</a>
                </div>
                <div class="card-body p-4">
                    <div class="list-group list-group-flush">
                        <?php if ($result_hethang && $result_hethang->num_rows > 0): ?>
                            <?php while($sp = $result_hethang->fetch_assoc()): ?>
                                <?php $img = !empty($sp['HinhAnh']) ? "../uploads/" . $sp['HinhAnh'] : "../uploads/no-image.jpg"; ?>
                                <div class="list-group-item px-0 py-3 d-flex align-items-center product-list-item border-bottom-0">
                                    <div class="bg-light rounded p-2 me-3">
                                        <img src="<?php echo $img; ?>" alt="Ảnh">
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1 text-dark" style="font-size: 0.9rem;"><?php echo htmlspecialchars($sp['TenSanPham']); ?></h6>
                                        <small class="text-muted">Mã SP: #<?php echo $sp['ID']; ?></small>
                                    </div>
                                    <div>
                                        <?php if ($sp['SoLuong'] == 0): ?>
                                            <span class="badge bg-danger rounded-pill px-3 py-2">Hết hàng</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                                                Còn lại: <?php echo $sp['SoLuong']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted text-center my-4">Kho hàng đang ổn định. Không có sản phẩm nào sắp hết.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div> </div>

<?php 
// Gọi file Footer
include 'footer.php'; 
?>