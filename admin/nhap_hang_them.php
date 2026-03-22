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

$thongBao = '';
$loaiThongBao = 'danger';

// Lấy danh sách nhà cung cấp
$sql_ncc = "SELECT ID, TenNhaCungCap FROM nhacungcap ORDER BY TenNhaCungCap ASC";
$result_ncc = $conn->query($sql_ncc);

// Lấy danh sách sản phẩm
$sql_sp = "SELECT ID, TenSanPham, DonGia, SoLuong FROM sanpham ORDER BY TenSanPham ASC";
$result_sp = $conn->query($sql_sp);

// Xử lý lưu phiếu nhập
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
                // BƯỚC 1: Lưu phiếu nhập
                $sql_pn = "INSERT INTO phieunhap (NhanVienID, NhaCungCapID, NgayNhap, TongTien, GhiChu)
                           VALUES (?, ?, NOW(), ?, ?)";
                $stmt_pn = $conn->prepare($sql_pn);
                $stmt_pn->bind_param("iids", $nhanvien_id, $nhacungcap_id, $tong_tien, $ghi_chu);

                if (!$stmt_pn->execute()) {
                    throw new Exception("Không thể tạo phiếu nhập.");
                }

                $phieu_nhap_id = $conn->insert_id;

                // BƯỚC 2: Lưu chi tiết + cập nhật kho
                $sql_ct = "INSERT INTO phieunhap_chitiet (PhieuNhapID, SanPhamID, SoLuongNhap, DonGiaNhap)
                           VALUES (?, ?, ?, ?)";
                $stmt_ct = $conn->prepare($sql_ct);

                $sql_update_kho = "UPDATE sanpham SET SoLuong = SoLuong + ? WHERE ID = ?";
                $stmt_update = $conn->prepare($sql_update_kho);

                foreach ($danh_sach_sp as $sp) {
                    $sp_id = (int)$sp['id'];
                    $sl    = (int)$sp['soluong'];
                    $gia   = (float)$sp['gia'];

                    $stmt_ct->bind_param("iiid", $phieu_nhap_id, $sp_id, $sl, $gia);
                    if (!$stmt_ct->execute()) {
                        throw new Exception("Không thể lưu chi tiết phiếu nhập.");
                    }

                    $stmt_update->bind_param("ii", $sl, $sp_id);
                    if (!$stmt_update->execute()) {
                        throw new Exception("Không thể cập nhật tồn kho.");
                    }
                }

                $conn->commit();
                echo "<script>alert('Nhập hàng thành công!'); window.location.href='nhap_hang.php';</script>";
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
        <h3 class="text-primary fw-bold">
            <i class="bi bi-plus-square"></i> Tạo Phiếu Nhập Hàng
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
                                    <option value="<?php echo $ncc['ID']; ?>">
                                        <?php echo $ncc['TenNhaCungCap']; ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tổng tiền</label>
                        <input type="text" id="TongTienHienThi" class="form-control fw-bold text-danger" value="0 đ" readonly>
                        <input type="hidden" name="TongTien" id="TongTien" value="0">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">Ghi chú</label>
                        <textarea name="GhiChu" class="form-control" rows="3" placeholder="Nhập ghi chú nếu có..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white fw-bold d-flex justify-content-between align-items-center">
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
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer text-end">
                <button type="submit" class="btn btn-success fw-bold px-4">
                    <i class="bi bi-save"></i> Lưu Phiếu Nhập
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    let index = 1;

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