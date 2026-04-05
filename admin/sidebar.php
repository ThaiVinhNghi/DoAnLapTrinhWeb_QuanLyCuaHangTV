<?php
// Kiểm tra quyền Admin (QuyenHan = 1)
$isAdmin = isset($_SESSION['admin_id']);

// Kiểm tra quyền xử lý Trả góp / Đổi trả (Là Admin HOẶC Nhân viên làm >= 1 năm)
$isNhanVienCu = isset($_SESSION['tham_nien_1_nam']) && $_SESSION['tham_nien_1_nam'] === true;
$duocPhepXuLyPhucTap = ($isAdmin || $isNhanVienCu);
?>

<div class="col-md-3 col-lg-2 mb-4">
    <div class="list-group shadow-sm">
        
        <a href="index.php" class="list-group-item list-group-item-action text-primary fw-bold">
            <i class="bi bi-speedometer2"></i> Bảng điều khiển
        </a>
        <a href="san_pham.php" class="list-group-item list-group-item-action">
            <i class="bi bi-box-seam"></i> Quản lý Sản phẩm
        </a>
        <a href="danh_muc.php" class="list-group-item list-group-item-action">
            <i class="bi bi-tags"></i> Quản lý Danh mục
        </a>
        <a href="hoa_don.php" class="list-group-item list-group-item-action">
            <i class="bi bi-receipt"></i> Quản lý Hóa đơn
        </a>
        <a href="khach_hang.php" class="list-group-item list-group-item-action">
            <i class="bi bi-people"></i> Quản lý Khách hàng
        </a>
        <a href="bao_hanh.php" class="list-group-item list-group-item-action">
            <i class="bi bi-shield-check me-2"></i> Quản lý Bảo hành
        </a>

        <?php if ($duocPhepXuLyPhucTap): ?>
            <a href="tra_gop.php" class="list-group-item list-group-item-action">
                <i class="bi bi-credit-card-2-front me-2"></i> Quản lý Trả góp
            </a>
            <a href="doi_tra.php" class="list-group-item list-group-item-action">
                <i class="bi bi-arrow-repeat me-2"></i> Quản lý Đổi trả
            </a>
        <?php else: ?>
            <a href="#" class="list-group-item list-group-item-action text-muted bg-light" onclick="alert('Tính năng bị khóa!\nYêu cầu thâm niên làm việc từ 1 năm trở lên.'); return false;">
                <i class="bi bi-lock-fill text-danger me-2"></i> Quản lý Trả góp <span class="badge bg-secondary ms-1">Khóa</span>
            </a>
            <a href="#" class="list-group-item list-group-item-action text-muted bg-light" onclick="alert('Tính năng bị khóa!\nYêu cầu thâm niên làm việc từ 1 năm trở lên.'); return false;">
                <i class="bi bi-lock-fill text-danger me-2"></i> Quản lý Đổi trả <span class="badge bg-secondary ms-1">Khóa</span>
            </a>
        <?php endif; ?>

        <?php if ($isAdmin): ?>
            <a href="nhap_hang.php" class="list-group-item list-group-item-action bg-light fw-semibold">
                <i class="bi bi-box-arrow-in-down me-2 text-warning"></i> Quản lý Nhập hàng
            </a>
            <a href="nhan_vien.php" class="list-group-item list-group-item-action text-success fw-semibold">
                <i class="bi bi-person-badge"></i> Quản lý Nhân viên
            </a>
            <a href="nhatky_hethong.php" class="list-group-item list-group-item-action bg-light fw-semibold">
                <i class="bi bi-clock-history me-2 text-dark"></i> Nhật ký hệ thống
            </a>
        <?php endif; ?>

    </div>
</div>