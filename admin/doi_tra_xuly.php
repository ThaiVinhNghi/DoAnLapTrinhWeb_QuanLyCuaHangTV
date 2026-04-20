<?php
session_start();
require_once '../thu_vien/connect.php';
require_once '../thu_vien/nhatky_helper.php';

// Kiểm tra quyền (Chỉ Admin hoặc Quản lý mới được duyệt)
if (!isset($_SESSION['admin_id'])) {
    echo "<script>alert('Bạn không có quyền truy cập!'); window.location.href='index.php';</script>";
    exit();
}

$id_doitra = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_doitra <= 0) {
    echo "<script>alert('Không tìm thấy phiếu yêu cầu!'); window.location.href='doi_tra.php';</script>";
    exit();
}

$thongBao = '';
$admin_id = $_SESSION['admin_id'];

// ==========================================
// 1. XỬ LÝ KHI BẤM "TỪ CHỐI"
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tu_choi'])) {
    $lyDoTuChoi = trim($_POST['ly_do_tu_choi'] ?? 'Không hợp lệ');
    $ngayXuLy = date('Y-m-d H:i:s');
    
    $sql_tu_choi = "UPDATE doitra SET TrangThai = 'Từ chối', NhanVienID = ?, NgayXuLy = ?, LyDo = CONCAT(LyDo, '\n[Admin Từ chối: ', ?, ']') WHERE ID = ?";
    $stmt = $conn->prepare($sql_tu_choi);
    $stmt->bind_param("issi", $admin_id, $ngayXuLy, $lyDoTuChoi, $id_doitra);
    
    if ($stmt->execute()) {
        ghiNhatKyTuSession($conn, 'TuChoiDoiTra', 'doitra', $id_doitra, "Từ chối yêu cầu đổi/trả #DT{$id_doitra}. Lý do: {$lyDoTuChoi}");
        $thongBao = "<div class='alert alert-danger'>Đã từ chối phiếu yêu cầu!</div>";
    }
}

// ==========================================
// 2. LẤY THÔNG TIN PHIẾU HIỆN TẠI ĐỂ KIỂM TRA TRƯỚC KHI DUYỆT
// ==========================================
$sql_phieu = "SELECT dt.*, kh.HoVaTen, kh.DienThoai FROM doitra dt JOIN khachhang kh ON dt.KhachHangID = kh.ID WHERE dt.ID = ?";
$stmt_phieu = $conn->prepare($sql_phieu);
$stmt_phieu->bind_param("i", $id_doitra);
$stmt_phieu->execute();
$phieu = $stmt_phieu->get_result()->fetch_assoc();

if (!$phieu) {
    echo "<script>alert('Phiếu không tồn tại!'); window.location.href='doi_tra.php';</script>";
    exit();
}

// Lấy chi tiết các sản phẩm trong phiếu này
$sql_ct = "SELECT dtc.*, sp.TenSanPham, sp.HinhAnh FROM doitra_chitiet dtc JOIN sanpham sp ON dtc.SanPhamID = sp.ID WHERE dtc.DoiTraID = ?";
$stmt_ct = $conn->prepare($sql_ct);
$stmt_ct->bind_param("i", $id_doitra);
$stmt_ct->execute();
$chiTietResult = $stmt_ct->get_result();

$danhSachChiTiet = [];
while ($row = $chiTietResult->fetch_assoc()) {
    $danhSachChiTiet[] = $row;
}

// ==========================================
// 3. XỬ LÝ KHI BẤM "DUYỆT YÊU CẦU"
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['duyet_yeucau'])) {
    if ($phieu['TrangThai'] != 'Chờ xử lý') {
        $thongBao = "<div class='alert alert-warning'>Phiếu này đã được xử lý trước đó!</div>";
    } else {
        $ngayXuLy = date('Y-m-d H:i:s');
        $trangThaiMoi = 'Đã hoàn tất';
        $ghiChuNhatKy = '';

        $conn->begin_transaction();
        try {
            // Bước 3.1: Hoàn lại số lượng sản phẩm cũ vào kho
            foreach ($danhSachChiTiet as $ct) {
                $sp_cu_id = $ct['SanPhamID'];
                $soLuongTraLai = $ct['SoLuong'];
                
                // Cộng số lượng lại vào bảng sanpham
                $sql_hoan_kho = "UPDATE sanpham SET SoLuong = SoLuong + ? WHERE ID = ?";
                $stmt_hoan = $conn->prepare($sql_hoan_kho);
                $stmt_hoan->bind_param("ii", $soLuongTraLai, $sp_cu_id);
                $stmt_hoan->execute();
            }

            // Bước 3.2: Xử lý theo Loại Yêu Cầu (TRẢ hoặc ĐỔI)
            if ($phieu['LoaiYeuCau'] == 'Trả hàng') {
                // TRẢ HÀNG: Chỉ cần ghi nhận hoàn tiền
                $tienHoanFormat = number_format($phieu['TongTienHoan'], 0, ',', '.');
                $ghiChuNhatKy = "Duyệt TRẢ HÀNG #DT{$id_doitra}. Đã nhập lại kho và hoàn {$tienHoanFormat}đ cho khách.";
            } 
            elseif ($phieu['LoaiYeuCau'] == 'Đổi hàng') {
                // ĐỔI HÀNG: Phải lấy ID sản phẩm mới mà Admin chọn, trừ đi kho sản phẩm đó
                $sp_moi_id = isset($_POST['san_pham_moi']) ? (int)$_POST['san_pham_moi'] : 0;
                
                if ($sp_moi_id <= 0) {
                    throw new Exception("Vui lòng chọn sản phẩm mới để đổi cho khách!");
                }

                // Trừ đi số lượng của sản phẩm mới (Mặc định đổi số lượng bằng với số lượng mang tới)
                // Lấy tổng số lượng khách muốn đổi từ $danhSachChiTiet
                $tongSoLuongDoi = 0;
                foreach ($danhSachChiTiet as $ct) {
                    $tongSoLuongDoi += $ct['SoLuong'];
                }

                $sql_xuat_kho_moi = "UPDATE sanpham SET SoLuong = SoLuong - ? WHERE ID = ?";
                $stmt_xuat = $conn->prepare($sql_xuat_kho_moi);
                $stmt_xuat->bind_param("ii", $tongSoLuongDoi, $sp_moi_id);
                $stmt_xuat->execute();

                // Lưu sản phẩm mới vào bảng doitra để hiển thị cho khách
                $sql_save_sp_moi = "UPDATE doitra SET SanPhamMoiID = ? WHERE ID = ?";
                $stmt_save = $conn->prepare($sql_save_sp_moi);
                if ($stmt_save) {
                    $stmt_save->bind_param("ii", $sp_moi_id, $id_doitra);
                    $stmt_save->execute();
                    $stmt_save->close();
                }

                $ghiChuNhatKy = "Duyệt ĐỔI HÀNG #DT{$id_doitra}. Đã thu hồi SP cũ và xuất SP mới (ID: {$sp_moi_id}) giao cho khách.";
            }

            // Bước 3.3: Cập nhật trạng thái phiếu đổi trả
            $sql_update_phieu = "UPDATE doitra SET NhanVienID = ?, NgayXuLy = ?, TrangThai = ? WHERE ID = ?";
            $stmt_update = $conn->prepare($sql_update_phieu);
            $stmt_update->bind_param("issi", $admin_id, $ngayXuLy, $trangThaiMoi, $id_doitra);
            $stmt_update->execute();

            // Bước 3.4: Ghi nhật ký
            ghiNhatKyTuSession($conn, 'DuyetDoiTra', 'doitra', $id_doitra, $ghiChuNhatKy);

            $conn->commit();
            
            echo "<script>alert('Đã xử lý và cập nhật kho hàng thành công!'); window.location.href='doi_tra.php';</script>";
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $thongBao = "<div class='alert alert-danger'>Lỗi xử lý: " . $e->getMessage() . "</div>";
        }
    }
}

// Nếu là Đổi Hàng, lấy danh sách Tivi đang có sẵn trong kho để Admin chọn
$dsSanPhamMoi = [];
if ($phieu['LoaiYeuCau'] == 'Đổi hàng' && $phieu['TrangThai'] == 'Chờ xử lý') {
    $sql_sp = "SELECT ID, TenSanPham, DonGia, SoLuong FROM sanpham WHERE SoLuong > 0 ORDER BY TenSanPham ASC";
    $result_sp = $conn->query($sql_sp);
    while($s = $result_sp->fetch_assoc()) {
        $dsSanPhamMoi[] = $s;
    }
}

require_once 'header.php';
require_once 'sidebar.php';
?>

<div class="col-md-9 col-lg-10 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary fw-bold"><i class="bi bi-file-earmark-text"></i> Chi tiết phiếu #DT<?php echo $id_doitra; ?></h3>
        <a href="doi_tra.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Quay lại</a>
    </div>

    <?php echo $thongBao; ?>

    <div class="row">
        <div class="col-md-7">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white pb-0 border-0 pt-3">
                    <h5 class="fw-bold"><i class="bi bi-person-badge text-primary"></i> Thông tin yêu cầu</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="150" class="text-muted">Khách hàng:</td>
                            <td class="fw-bold"><?php echo htmlspecialchars($phieu['HoVaTen']); ?> - <?php echo htmlspecialchars($phieu['DienThoai']); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Hóa đơn gốc:</td>
                            <td class="fw-bold text-primary">#HD<?php echo $phieu['HoaDonID']; ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Loại yêu cầu:</td>
                            <td>
                                <?php echo ($phieu['LoaiYeuCau'] == 'Đổi hàng') ? '<span class="badge bg-warning text-dark px-3 py-2 fs-6 border border-warning">ĐỔI HÀNG</span>' : '<span class="badge bg-danger px-3 py-2 fs-6">TRẢ HÀNG</span>'; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Ngày gửi:</td>
                            <td><?php echo date('d/m/Y H:i', strtotime($phieu['NgayLap'])); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Lý do của khách:</td>
                            <td><div class="p-2 bg-light rounded border text-danger fw-semibold"><?php echo nl2br(htmlspecialchars($phieu['LyDo'])); ?></div></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Trạng thái:</td>
                            <td>
                                <?php if($phieu['TrangThai'] == 'Chờ xử lý') echo '<span class="badge bg-secondary">Chờ xử lý</span>'; ?>
                                <?php if($phieu['TrangThai'] == 'Đã hoàn tất') echo '<span class="badge bg-success">Đã hoàn tất</span>'; ?>
                                <?php if($phieu['TrangThai'] == 'Từ chối') echo '<span class="badge bg-dark">Từ chối</span>'; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-bold mb-3"><i class="bi bi-tv"></i> Sản phẩm khách mang tới</h5>
                    <table class="table table-bordered align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Hình ảnh</th>
                                <th>Tên sản phẩm</th>
                                <th>Số lượng</th>
                                <th>Tình trạng (Khách báo)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($danhSachChiTiet as $ct): ?>
                            <tr>
                                <td><img src="../uploads/<?php echo !empty($ct['HinhAnh']) ? $ct['HinhAnh'] : 'no-image.jpg'; ?>" width="60" class="rounded"></td>
                                <td class="text-start fw-bold"><?php echo htmlspecialchars($ct['TenSanPham']); ?></td>
                                <td><span class="badge bg-primary fs-6"><?php echo $ct['SoLuong']; ?></span></td>
                                <td class="text-danger small"><?php echo htmlspecialchars($ct['TinhTrangSanPham']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card shadow border-<?php echo ($phieu['LoaiYeuCau'] == 'Đổi hàng') ? 'warning' : 'danger'; ?>">
                <div class="card-header bg-white pt-3 pb-2">
                    <h5 class="fw-bold text-center"><i class="bi bi-gear-fill"></i> BẢNG XỬ LÝ QUYẾT ĐỊNH</h5>
                </div>
                <div class="card-body bg-light">
                    
                    <?php if ($phieu['TrangThai'] == 'Chờ xử lý'): ?>
                        <form method="POST" action="" onsubmit="return confirm('Bạn có chắc chắn muốn DUYỆT yêu cầu này? Kho hàng sẽ bị thay đổi.');">
                            
                            <?php if ($phieu['LoaiYeuCau'] == 'Trả hàng'): ?>
                                <div class="alert alert-danger text-center">
                                    <h6 class="mb-1">Số tiền cần hoàn cho khách</h6>
                                    <h3 class="fw-bold mb-0"><?php echo number_format($phieu['TongTienHoan'], 0, ',', '.'); ?> VNĐ</h3>
                                </div>
                                <p class="small text-muted"><i class="bi bi-info-circle"></i> Khi duyệt: Tivi khách trả sẽ được <strong>Cộng lại vào kho</strong>.</p>
                                
                                <button type="submit" name="duyet_yeucau" class="btn btn-danger btn-lg w-100 fw-bold mb-3 shadow">
                                    <i class="bi bi-check-circle"></i> DUYỆT NHẬN LẠI HÀNG
                                </button>
                            <?php endif; ?>

                            <?php if ($phieu['LoaiYeuCau'] == 'Đổi hàng'): ?>
                                <div class="alert alert-warning text-dark">
                                    <label class="form-label fw-bold"><i class="bi bi-search"></i> Chọn sản phẩm MỚI giao cho khách:</label>
                                    <select name="san_pham_moi" class="form-select border-dark" required>
                                        <option value="">-- Click để chọn Tivi trong kho --</option>
                                        <?php foreach($dsSanPhamMoi as $sp_moi): ?>
                                            <option value="<?php echo $sp_moi['ID']; ?>">
                                                <?php echo $sp_moi['TenSanPham']; ?> (Kho: <?php echo $sp_moi['SoLuong']; ?>) - <?php echo number_format($sp_moi['DonGia'], 0, ',', '.'); ?>đ
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <p class="small text-muted"><i class="bi bi-info-circle"></i> Khi duyệt: Tivi cũ được <strong>cộng lại kho</strong>, Tivi mới chọn sẽ bị <strong>trừ kho</strong>.</p>

                                <button type="submit" name="duyet_yeucau" class="btn btn-warning btn-lg w-100 fw-bold text-dark mb-3 shadow">
                                    <i class="bi bi-arrow-repeat"></i> DUYỆT ĐỔI HÀNG
                                </button>
                            <?php endif; ?>

                        </form>

                        <hr>
                        
                        <form method="POST" action="" onsubmit="return confirm('Xác nhận TỪ CHỐI yêu cầu này?');">
                            <div class="mb-2">
                                <label class="form-label fw-bold text-muted small">Lý do từ chối (Nội bộ):</label>
                                <input type="text" name="ly_do_tu_choi" class="form-control form-control-sm" placeholder="Ví dụ: Tivi rơi vỡ, hết hạn trả..." required>
                            </div>
                            <button type="submit" name="tu_choi" class="btn btn-outline-dark w-100">
                                <i class="bi bi-x-circle"></i> TỪ CHỐI YÊU CẦU
                            </button>
                        </form>

                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-check2-all text-success display-1"></i>
                            <h4 class="mt-3">Yêu cầu đã được đóng</h4>
                            <p class="text-muted">Xử lý lúc: <?php echo date('d/m/Y H:i', strtotime($phieu['NgayXuLy'])); ?></p>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>