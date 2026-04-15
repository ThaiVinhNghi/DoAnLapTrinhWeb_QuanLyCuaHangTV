<?php
session_start();
require_once '../thu_vien/connect.php';
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

// LẤY DANH SÁCH SẢN PHẨM (Dùng HangSanXuatID đóng vai trò là NhaCungCapID để khớp với CSDL của bạn)
$sql_sp = "SELECT ID, TenSanPham, DonGia, SoLuong, HangSanXuatID AS NhaCungCapID FROM sanpham ORDER BY TenSanPham ASC";
$result_sp = $conn->query($sql_sp);

$danhSachSanPham = [];
if ($result_sp && $result_sp->num_rows > 0) {
    while ($row = $result_sp->fetch_assoc()) {
        $danhSachSanPham[] = $row;
    }
}

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

<div class="col-md-9 col-lg-10 mb-5 main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary fw-bold">
            <i class="bi bi-box-arrow-in-down"></i> Tạo Phiếu Nhập Hàng
        </h3>
        <a href="nhap_hang.php" class="btn btn-secondary fw-bold rounded-pill px-4">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <?php if (!empty($thongBao)): ?>
        <div class="alert alert-<?php echo $loaiThongBao; ?> shadow-sm">
            <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $thongBao; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="card shadow-sm border-0 mb-4 rounded-4">
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-secondary text-uppercase" style="letter-spacing: 0.5px;"><i class="bi bi-building"></i> Nhà cung cấp</label>
                        <select name="NhaCungCapID" id="selectNhaCungCap" class="form-select form-select-lg border-primary shadow-none" required onchange="locSanPhamTheoNCC()">
                            <option value="">-- Chọn Nhà cung cấp / Hãng sản xuất --</option>
                            <?php if ($result_ncc && $result_ncc->num_rows > 0): ?>
                                <?php while ($ncc = $result_ncc->fetch_assoc()): ?>
                                    <option value="<?php echo $ncc['ID']; ?>">
                                        <?php echo $ncc['TenNhaCungCap']; ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="text-muted small mt-4">
                            <i class="bi bi-info-circle text-primary"></i> Vui lòng chọn Nhà cung cấp để hiển thị đúng danh sách sản phẩm tương ứng.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-header bg-primary text-white fw-bold d-flex justify-content-between align-items-center p-3 border-0">
                <span class="fs-5"><i class="bi bi-list-ul me-2"></i> Danh sách sản phẩm nhập</span>
                <button type="button" class="btn btn-light btn-sm fw-bold text-primary rounded-pill px-3 shadow-sm" onclick="themDong()">
                    <i class="bi bi-plus-lg"></i> Thêm dòng
                </button>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center mb-0" id="bangSanPham">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40%;" class="py-3">Tên Sản phẩm</th>
                                <th style="width: 15%;" class="py-3">SL nhập</th>
                                <th style="width: 20%;" class="py-3">Giá nhập (đ)</th>
                                <th style="width: 20%;" class="py-3">Thành tiền</th>
                                <th style="width: 5%;" class="py-3">Xóa</th>
                            </tr>
                        </thead>
                        <tbody id="dsSanPham">
                            <tr>
                                <td class="p-2">
                                    <select name="san_pham[0][id]" class="form-select cbo-san-pham" required>
                                        <option value="">-- Vui lòng chọn NCC trước --</option>
                                    </select>
                                </td>
                                <td class="p-2">
                                    <input type="number" name="san_pham[0][soluong]" class="form-control text-center so-luong" min="1" value="1" required oninput="tinhTien()">
                                </td>
                                <td class="p-2">
                                    <input type="number" name="san_pham[0][gia]" class="form-control text-end don-gia" min="0" value="0" required oninput="tinhTien()">
                                </td>
                                <td class="p-2 bg-light">
                                    <input type="text" class="form-control text-end thanh-tien fw-bold text-danger border-0 bg-transparent" value="0 đ" readonly>
                                </td>
                                <td class="p-2">
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="xoaDong(this)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-body bg-light border-top p-4">
                <div class="row align-items-end g-4">
                    <div class="col-md-7">
                        <label class="form-label fw-bold text-secondary">Ghi chú phiếu nhập (Tùy chọn)</label>
                        <textarea name="GhiChu" class="form-control rounded-3" rows="3" placeholder="Ví dụ: Nhập bổ sung đợt 1 tháng 4..."></textarea>
                    </div>
                    <div class="col-md-5 text-end">
                        <div class="d-flex justify-content-end align-items-center">
                            <span class="fw-bold text-secondary me-3 text-uppercase" style="letter-spacing: 1px;">Tổng thanh toán:</span>
                            <input type="text" id="TongTienHienThi" class="form-control d-inline-block w-auto text-end fw-bold text-danger bg-transparent border-0 p-0 m-0" style="font-size: 2.2rem;" value="0 đ" readonly>
                            <input type="hidden" name="TongTien" id="TongTien" value="0">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white text-end p-4 border-top-0">
                <button type="submit" class="btn btn-success btn-lg fw-bold px-5 rounded-pill shadow-sm py-3">
                    <i class="bi bi-check2-circle me-2 fs-5"></i> XÁC NHẬN LƯU PHIẾU NHẬP
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    // Mảng chứa toàn bộ dữ liệu SP do PHP đẩy ra
    const tatCaSanPham = <?php echo json_encode($danhSachSanPham); ?>;
    let index = 1;

    // Lọc SP khi thay đổi Select Box NCC
    function locSanPhamTheoNCC() {
        const idNCC = document.getElementById('selectNhaCungCap').value;
        const cacOChonSP = document.querySelectorAll('.cbo-san-pham');

        const spLocDuoc = tatCaSanPham.filter(sp => sp.NhaCungCapID == idNCC);

        cacOChonSP.forEach(cbo => {
            const idCu = cbo.value; 
            cbo.innerHTML = ''; 
            
            if (idNCC === "") {
                cbo.innerHTML = '<option value="">-- Vui lòng chọn NCC trước --</option>';
                return;
            }

            if (spLocDuoc.length === 0) {
                cbo.innerHTML = '<option value="">-- NCC này chưa có sản phẩm --</option>';
                return;
            }

            cbo.innerHTML = '<option value="">-- Chọn sản phẩm --</option>';
            spLocDuoc.forEach(sp => {
                const giaBanHienTai = Number(sp.DonGia).toLocaleString('vi-VN') + 'đ';
                const option = document.createElement('option');
                option.value = sp.ID;
                option.textContent = `${sp.TenSanPham} (Tồn kho: ${sp.SoLuong} | Đang bán: ${giaBanHienTai})`;
                
                if (sp.ID == idCu) option.selected = true; 
                cbo.appendChild(option);
            });
        });
    }

    // Thêm dòng mới
    function themDong() {
        const tbody = document.getElementById('dsSanPham');
        const idNCC = document.getElementById('selectNhaCungCap').value;
        let optionsHTML = '';

        if (idNCC === "") {
            optionsHTML = '<option value="">-- Vui lòng chọn NCC trước --</option>';
        } else {
            const spLocDuoc = tatCaSanPham.filter(sp => sp.NhaCungCapID == idNCC);
            if (spLocDuoc.length === 0) {
                optionsHTML = '<option value="">-- NCC này chưa có sản phẩm --</option>';
            } else {
                optionsHTML = '<option value="">-- Chọn sản phẩm --</option>';
                spLocDuoc.forEach(sp => {
                    const giaBanHienTai = Number(sp.DonGia).toLocaleString('vi-VN') + 'đ';
                    optionsHTML += `<option value="${sp.ID}">${sp.TenSanPham} (Tồn kho: ${sp.SoLuong} | Đang bán: ${giaBanHienTai})</option>`;
                });
            }
        }

        const html = `
            <tr>
                <td class="p-2">
                    <select name="san_pham[${index}][id]" class="form-select cbo-san-pham" required>
                        ${optionsHTML}
                    </select>
                </td>
                <td class="p-2">
                    <input type="number" name="san_pham[${index}][soluong]" class="form-control text-center so-luong" min="1" value="1" required oninput="tinhTien()">
                </td>
                <td class="p-2">
                    <input type="number" name="san_pham[${index}][gia]" class="form-control text-end don-gia" min="0" value="0" required oninput="tinhTien()">
                </td>
                <td class="p-2 bg-light">
                    <input type="text" class="form-control text-end thanh-tien fw-bold text-danger border-0 bg-transparent" value="0 đ" readonly>
                </td>
                <td class="p-2">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="xoaDong(this)">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;

        tbody.insertAdjacentHTML('beforeend', html);
        index++;
        tinhTien();
    }

    // Xóa dòng
    function xoaDong(button) {
        const tbody = document.getElementById('dsSanPham');
        if (tbody.rows.length > 1) {
            button.closest('tr').remove();
            tinhTien();
        } else {
            alert('Phiếu nhập phải có ít nhất 1 sản phẩm.');
        }
    }

    // Tính toán tổng tiền realtime
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

    // Khởi tạo
    document.addEventListener('DOMContentLoaded', function() {
        tinhTien();
        locSanPhamTheoNCC();
    });
</script>

<?php require_once 'footer.php'; ?>