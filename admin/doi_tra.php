<?php
session_start();
require_once '../thu_vien/connect.php';
require_once '../thu_vien/nhatky_helper.php';

// Kiểm tra quyền admin (phần này bạn tự thêm code bảo mật giống các file admin khác)

// Lấy danh sách phiếu đổi trả "Chờ xử lý"
$sql = "SELECT dt.*, kh.HoVaTen
        FROM doitra dt
        JOIN khachhang kh ON dt.KhachHangID = kh.ID
        WHERE dt.TrangThai = 'Chờ xử lý'
        ORDER BY dt.ID DESC";
$result = $conn->query($sql);

require_once 'header.php';
require_once 'sidebar.php';
?>

<div class="col-md-9 col-lg-10 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary fw-bold"><i class="bi bi-arrow-left-right me-2"></i>Duyệt yêu cầu Đổi/Trả</h3>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th width="100">ID Phiếu</th>
                            <th width="120">Hóa đơn</th>
                            <th>Khách hàng</th>
                            <th width="150">Loại yêu cầu</th>
                            <th width="180">Ngày lập</th>
                            <th width="120">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold">#DT<?php echo $row['ID']; ?></td>
                                    <td class="text-primary fw-bold">#HD<?php echo $row['HoaDonID']; ?></td>
                                    <td class="text-start fw-bold"><?php echo $row['HoVaTen']; ?></td>
                                    <td>
                                        <?php echo ($row['LoaiYeuCau'] == 'Đổi hàng') ? '<span class="badge bg-warning text-dark">Đổi hàng</span>' : '<span class="badge bg-danger">Trả hàng</span>'; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i:s', strtotime($row['NgayLap'])); ?></td>
                                    <td>
                                        <a href="doi_tra_xuly.php?id=<?php echo $row['ID']; ?>" class="btn btn-sm btn-outline-primary">Xử lý / Duyệt</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Chưa có yêu cầu đổi trả nào đang chờ xử lý.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>