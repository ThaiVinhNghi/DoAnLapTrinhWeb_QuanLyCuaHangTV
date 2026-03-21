<?php
session_start();
require_once '../connect.php';
require_once 'header.php';
require_once 'sidebar.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['nhanvien_id'])) {
    header("Location: ../login.php");
    exit();
}

// Kiểm tra id phiếu nhập
if (!isset($_GET['id']) || (int)$_GET['id'] <= 0) {
    echo "<script>alert('Thiếu mã phiếu nhập!'); window.location.href='nhap_hang.php';</script>";
    exit();
}

$phieu_nhap_id = (int)$_GET['id'];
$thongBao = '';
$loaiThongBao = 'danger';

// Lấy thông tin phiếu nhập
$sql_pn = "SELECT * FROM phieunhap WHERE ID = ?";
$stmt_pn = $conn->prepare($sql_pn);
$stmt_pn->bind_param("i", $phieu_nhap_id);
$stmt_pn->execute();
$result_pn = $stmt_pn->get_result();

if ($result_pn->num_rows == 0) {
    echo "<script>alert('Phiếu nhập không tồn tại!'); window.location.href='nhap_hang.php';</script>";
    exit();
}

$phieuNhap = $result_pn->fetch_assoc();

// Lấy chi tiết phiếu nhập
$sql_ct = "SELECT ct.*, sp.TenSanPham, sp.SoLuong
           FROM phieunhap_chitiet ct
           LEFT JOIN sanpham sp ON ct.SanPhamID = sp.ID
           WHERE ct.PhieuNhapID = ?";
$stmt_ct = $conn->prepare($sql_ct);
$stmt_ct->bind_param("i", $phieu_nhap_id);
$stmt_ct->execute();
$result_ct = $stmt_ct->get_result();

$danhSachChiTiet = [];
while ($row = $result_ct->fetch_assoc()) {
    $danhSachChiTiet[] = $row;
}

// Lấy danh sách nhà cung cấp
$sql_ncc = "SELECT ID, TenNhaCungCap FROM nhacungcap ORDER BY TenNhaCungCap ASC";
$result_ncc = $conn->query($sql_ncc);

// Lấy danh sách sản phẩm
$sql_sp = "SELECT ID, TenSanPham, DonGia, SoLuong FROM sanpham ORDER BY TenSanPham ASC";
$result_sp = $conn->query($sql_sp);

// Xử lý cập nhật phiếu nhập
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nhanvien_id   = $_SESSION['nhanvien_id'];
    $nhacungcap_id = isset($_POST['NhaCungCapID']) ? (int)$_POST['NhaCungCapID'] : 0;
    $ghi_chu       = isset($_POST['GhiChu']) ? trim($_POST['GhiChu']) : '';
    $tong_tien     = isset($_POST['TongTien']) ? (float)$_POST['TongTien'] : 0;
    $danh_sach_sp  = isset($_POST['san_pham']) ? $_POST['san_pham'] : [];

    if ($nhacungcap_id <= 0) {
        $thongBao = "Vui lòng chọn nhà cung cấp.";
    } elseif (empty($danh_sach_sp)) {
        $thongBao = "Vui lòng thêm ít nhất 1 sản phẩm.";
    } else {
        $hopLe = true;
        $tong_tien_tinh_lai = 0;

        foreach ($danh_sach_sp as $sp) {
            $sp_id = isset($sp['id']) ? (int)$sp['id'] : 0;
            $sl    = isset($sp['soluong']) ? (int)$sp['soluong'] : 0;
            $gia   = isset($sp['gia']) ? (float)$sp['gia'] : 0;

            if ($sp_id <= 0 || $sl <= 0 || $gia < 0) {
                $hopLe = false;
                break;
            }

            $tong_tien_tinh_lai += $sl * $gia;
        }

        if (!$hopLe) {
            $thongBao = "Dữ liệu sản phẩm không hợp lệ.";
        } else {
            $tong_tien = $tong_tien_tinh_lai;

            $conn->begin_transaction();

            try {
                // BƯỚC 1: Hoàn kho theo chi tiết cũ (trừ lại số lượng đã cộng trước đó)
                $sql_old_ct = "SELECT SanPhamID, SoLuongNhap FROM phieunhap_chitiet WHERE PhieuNhapID = ?";
                $stmt_old_ct = $conn->prepare($sql_old_ct);
                $stmt_old_ct->bind_param("i", $phieu_nhap_id);
                $stmt_old_ct->execute();
                $result_old_ct = $stmt_old_ct->get_result();

                $sql_tru_kho = "UPDATE sanpham SET SoLuong = SoLuong - ? WHERE ID = ?";
                $stmt_tru_kho = $conn->prepare($sql_tru_kho);

                while ($old = $result_old_ct->fetch_assoc()) {
                    $old_sl = (int)$old['SoLuongNhap'];
                    $old_sp_id = (int)$old['SanPhamID'];

                    $stmt_tru_kho->bind_param("ii", $old_sl, $old_sp_id);
                    if (!$stmt_tru_kho->execute()) {
                        throw new Exception("Không thể cập nhật lại tồn kho cũ.");
                    }
                }

                // BƯỚC 2: Xóa chi tiết cũ
                $sql_delete_ct = "DELETE FROM phieunhap_chitiet WHERE PhieuNhapID = ?";
                $stmt_delete_ct = $conn->prepare($sql_delete_ct);
                $stmt_delete_ct->bind_param("i", $phieu_nhap_id);

                if (!$stmt_delete_ct->execute()) {
                    throw new Exception("Không thể xóa chi tiết phiếu nhập cũ.");
                }

                // BƯỚC 3: Cập nhật phiếu nhập
                $sql_update_pn = "UPDATE phieunhap
                                  SET NhanVienID = ?, NhaCungCapID = ?, TongTien = ?, GhiChu = ?
                                  WHERE ID = ?";
                $stmt_update_pn = $conn->prepare($sql_update_pn);
                $stmt_update_pn->bind_param("iidsi", $nhanvien_id, $nhacungcap_id, $tong_tien, $ghi_chu, $phieu_nhap_id);

                if (!$stmt_update_pn->execute()) {
                    throw new Exception("Không thể cập nhật phiếu nhập.");
                }

                // BƯỚC 4: Thêm chi tiết mới + cộng kho lại
                $sql_insert_ct = "INSERT INTO phieunhap_chitiet (PhieuNhapID, SanPhamID, SoLuongNhap, DonGiaNhap)
                                  VALUES (?, ?, ?, ?)";
                $stmt_insert_ct = $conn->prepare($sql_insert_ct);

                $sql_cong_kho = "UPDATE sanpham SET SoLuong = SoLuong + ? WHERE ID = ?";
                $stmt_cong_kho = $conn->prepare($sql_cong_kho);

                foreach ($danh_sach_sp as $sp) {
                    $sp_id = (int)$sp['id'];
                    $sl    = (int)$sp['soluong'];
                    $gia   = (float)$sp['gia'];

                    $stmt_insert_ct->bind_param("iiid", $phieu_nhap_id, $sp_id, $sl, $gia);
                    if (!$stmt_insert_ct->execute()) {
                        throw new Exception("Không thể lưu chi tiết phiếu nhập mới.");
                    }

                    $stmt_cong_kho->bind_param("ii", $sl, $sp_id);
                    if (!$stmt_cong_kho->execute()) {
                        throw new Exception("Không thể cập nhật tồn kho mới.");
                    }
                }

                $conn->commit();
                echo "<script>alert('Cập nhật phiếu nhập thành công!'); window.location.href='nhap_hang.php';</script>";
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                $thongBao = "Lỗi: " . $e->getMessage();
            }
        }
    }
}
?>

<div class="col-md-9 col-lg-10 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-warning fw-bold">
            <i class="bi bi-pencil-square"></i> Sửa Phiếu Nhập Hàng
        </h3>
        <a href="nhap_hang.php" class="btn btn-secondary fw-bold">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <?php if (!empty($thongBao)): ?>
        <div class="alert alert-<?php echo $loaiThongBao; ?> shadow-sm">
            <?php echo $thongBao; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nhà cung cấp</label>
                        <select name="NhaCungCapID" class="form-select" required>
                            <option value="">-- Chọn nhà cung cấp --</option>
                            <?php if ($result_ncc && $result_ncc->num_rows > 0): ?>
                                <?php while ($ncc = $result_ncc->fetch_assoc()): ?>
                                    <option value="<?php echo $ncc['ID']; ?>"
                                        <?php echo ($phieuNhap['NhaCungCapID'] == $ncc['ID']) ? 'selected' : ''; ?>>
                                        <?php echo $ncc['TenNhaCungCap']; ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tổng tiền</label>
                        <input type="text" id="TongTienHienThi" class="form-control fw-bold text-danger"
                               value="<?php echo number_format($phieuNhap['TongTien'], 0, ',', '.'); ?> đ" readonly>
                        <input type="hidden" name="TongTien" id="TongTien" value="<?php echo $phieuNhap['TongTien']; ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">Ghi chú</label>
                        <textarea name="GhiChu" class="form-control" rows="3" placeholder="Nhập ghi chú nếu có..."><?php echo htmlspecialchars($phieuNhap['GhiChu']); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-warning text-dark fw-bold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list-ul"></i> Danh sách sản phẩm nhập</span>
                <button type="button" class="btn btn-light btn-sm fw-bold" onclick="themDong()">
                    <i class="bi bi-plus-lg"></i> Thêm sản phẩm
                </button>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center mb-0" id="bangSanPham">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 35%;">Sản phẩm</th>
                                <th style="width: 15%;">Số lượng</th>
                                <th style="width: 20%;">Đơn giá nhập</th>
                                <th style="width: 20%;">Thành tiền</th>
                                <th style="width: 10%;">Xóa</th>
                            </tr>
                        </thead>
                        <tbody id="dsSanPham">
                            <?php if (!empty($danhSachChiTiet)): ?>
                                <?php foreach ($danhSachChiTiet as $index => $ct): ?>
                                    <tr>
                                        <td>
                                            <select name="san_pham[<?php echo $index; ?>][id]" class="form-select" required>
                                                <option value="">-- Chọn sản phẩm --</option>
                                                <?php
                                                if ($result_sp && $result_sp->num_rows > 0):
                                                    $result_sp->data_seek(0);
                                                    while ($sp = $result_sp->fetch_assoc()):
                                                ?>
                                                    <option value="<?php echo $sp['ID']; ?>"
                                                        <?php echo ($ct['SanPhamID'] == $sp['ID']) ? 'selected' : ''; ?>>
                                                        <?php echo $sp['TenSanPham']; ?> (Tồn: <?php echo $sp['SoLuong']; ?>)
                                                    </option>
                                                <?php
                                                    endwhile;
                                                endif;
                                                ?>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="san_pham[<?php echo $index; ?>][soluong]"
                                                   class="form-control so-luong" min="1"
                                                   value="<?php echo $ct['SoLuongNhap']; ?>" required oninput="tinhTien()">
                                        </td>
                                        <td>
                                            <input type="number" name="san_pham[<?php echo $index; ?>][gia]"
                                                   class="form-control don-gia" min="0"
                                                   value="<?php echo $ct['DonGiaNhap']; ?>" required oninput="tinhTien()">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control thanh-tien" value="0 đ" readonly>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="xoaDong(this)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td>
                                        <select name="san_pham[0][id]" class="form-select" required>
                                            <option value="">-- Chọn sản phẩm --</option>
                                            <?php
                                            if ($result_sp && $result_sp->num_rows > 0):
                                                $result_sp->data_seek(0);
                                                while ($sp = $result_sp->fetch_assoc()):
                                            ?>
                                                <option value="<?php echo $sp['ID']; ?>">
                                                    <?php echo $sp['TenSanPham']; ?> (Tồn: <?php echo $sp['SoLuong']; ?>)
                                                </option>
                                            <?php
                                                endwhile;
                                            endif;
                                            ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="san_pham[0][soluong]" class="form-control so-luong" min="1" value="1" required oninput="tinhTien()">
                                    </td>
                                    <td>
                                        <input type="number" name="san_pham[0][gia]" class="form-control don-gia" min="0" value="0" required oninput="tinhTien()">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control thanh-tien" value="0 đ" readonly>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="xoaDong(this)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer text-end">
                <button type="submit" class="btn btn-success fw-bold px-4">
                    <i class="bi bi-save"></i> Cập Nhật Phiếu Nhập
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    let index = <?php echo count($danhSachChiTiet) > 0 ? count($danhSachChiTiet) : 1; ?>;

    function themDong() {
        const tbody = document.getElementById('dsSanPham');

        const html = `
            <tr>
                <td>
                    <select name="san_pham[${index}][id]" class="form-select" required>
                        <option value="">-- Chọn sản phẩm --</option>
                        <?php
                        if ($result_sp && $result_sp->num_rows > 0):
                            $result_sp->data_seek(0);
                            while ($sp = $result_sp->fetch_assoc()):
                        ?>
                            <option value="<?php echo $sp['ID']; ?>">
                                <?php echo addslashes($sp['TenSanPham']); ?> (Tồn: <?php echo $sp['SoLuong']; ?>)
                            </option>
                        <?php
                            endwhile;
                        endif;
                        ?>
                    </select>
                </td>
                <td>
                    <input type="number" name="san_pham[${index}][soluong]" class="form-control so-luong" min="1" value="1" required oninput="tinhTien()">
                </td>
                <td>
                    <input type="number" name="san_pham[${index}][gia]" class="form-control don-gia" min="0" value="0" required oninput="tinhTien()">
                </td>
                <td>
                    <input type="text" class="form-control thanh-tien" value="0 đ" readonly>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm" onclick="xoaDong(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;

        tbody.insertAdjacentHTML('beforeend', html);
        index++;
        tinhTien();
    }

    function xoaDong(button) {
        const tbody = document.getElementById('dsSanPham');
        if (tbody.rows.length > 1) {
            button.closest('tr').remove();
            tinhTien();
        } else {
            alert('Phải có ít nhất 1 sản phẩm.');
        }
    }

    function tinhTien() {
        const rows = document.querySelectorAll('#dsSanPham tr');
        let tongTien = 0;

        rows.forEach(row => {
            const soLuong = parseFloat(row.querySelector('.so-luong').value) || 0;
            const donGia = parseFloat(row.querySelector('.don-gia').value) || 0;
            const thanhTien = soLuong * donGia;

            row.querySelector('.thanh-tien').value = thanhTien.toLocaleString('vi-VN') + ' đ';
            tongTien += thanhTien;
        });

        document.getElementById('TongTien').value = tongTien;
        document.getElementById('TongTienHienThi').value = tongTien.toLocaleString('vi-VN') + ' đ';
    }

    document.addEventListener('DOMContentLoaded', function() {
        tinhTien();
    });
</script>

<?php require_once 'footer.php'; ?>