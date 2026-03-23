<?php
session_start();
require_once '../connect.php';

$sql = "SELECT tg.*, kh.HoVaTen, kh.DienThoai
        FROM tragop tg
        JOIN khachhang kh ON tg.KhachHangID = kh.ID
        ORDER BY tg.ID DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Trả góp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container mt-4 mb-5" style="max-width: 1450px;">
    <h2 class="text-primary fw-bold mb-4">
        <i class="bi bi-credit-card-2-front"></i> Quản lý Trả góp
    </h2>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th class="ps-3">Mã TG</th>
                            <th>Khách hàng</th>
                            <th>Ngày đăng ký</th>
                            <th class="text-end">Tổng tiền</th>
                            <th class="text-end">Trả trước</th>
                            <th class="text-end">Còn nợ</th>
                            <th class="text-center">Số tháng</th>
                            <th class="text-end">Góp / tháng</th>
                            <th>Hồ sơ</th>
                            <th>Tình trạng trả</th>
                            <th class="text-center">Nhắc</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <?php $conNo = max(0, $row['TongPhaiTra'] - $row['SoTienDaTra']); ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-danger">#TG<?php echo $row['ID']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['HoVaTen']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($row['DienThoai']); ?></small>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['NgayDangKy'])); ?></td>
                                    <td class="text-end text-danger fw-bold"><?php echo number_format($row['TongTien'], 0, ',', '.'); ?> đ</td>
                                    <td class="text-end"><?php echo number_format($row['SoTienTraTruoc'], 0, ',', '.'); ?> đ</td>
                                    <td class="text-end fw-bold"><?php echo number_format($conNo, 0, ',', '.'); ?> đ</td>
                                    <td class="text-center"><?php echo (int)$row['SoThangTraGop']; ?></td>
                                    <td class="text-end"><?php echo number_format($row['TienGopMoiThang'], 0, ',', '.'); ?> đ</td>
                                    <td>
                                        <?php if ($row['TrangThai'] == 'Chờ duyệt'): ?>
                                            <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                        <?php elseif ($row['TrangThai'] == 'Đã chuyển hóa đơn'): ?>
                                            <span class="badge bg-primary">Đã chuyển hóa đơn</span>
                                        <?php elseif ($row['TrangThai'] == 'Đã duyệt'): ?>
                                            <span class="badge bg-success">Đã duyệt</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($row['TrangThai']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['TinhTrangTra'] == 'Chờ duyệt'): ?>
                                            <span class="badge bg-secondary">Chờ duyệt</span>
                                        <?php elseif ($row['TinhTrangTra'] == 'Đang góp'): ?>
                                            <span class="badge bg-primary">Đang góp</span>
                                        <?php elseif ($row['TinhTrangTra'] == 'Đã tất toán'): ?>
                                            <span class="badge bg-success">Đã tất toán</span>
                                        <?php elseif ($row['TinhTrangTra'] == 'Nợ xấu'): ?>
                                            <span class="badge bg-danger">Nợ xấu</span>
                                        <?php else: ?>
                                            <span class="badge bg-dark"><?php echo htmlspecialchars($row['TinhTrangTra']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark"><?php echo (int)$row['SoLanNhacNho']; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <a href="tra_gop_chi_tiet.php?id=<?php echo $row['ID']; ?>" class="btn btn-info btn-sm text-white">
                                            <i class="bi bi-eye"></i> Xem chi tiết
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="12" class="text-center py-4 text-muted">Chưa có hồ sơ trả góp nào.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>