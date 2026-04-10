<?php
// includes/sidebar.php
?>

<div class="col-md-3 col-lg-2 p-0 admin-sidebar-wrapper offcanvas-md offcanvas-start bg-dark text-white" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
    <div class="offcanvas-header d-md-none border-bottom border-secondary">
        <h5 class="offcanvas-title fw-bold" id="sidebarOffcanvasLabel"><i class="bi bi-cpu-fill text-danger me-2"></i> MENU QUẢN TRỊ</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#sidebarOffcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column p-0">
        
        <div class="sidebar-heading mt-3">Menu Chính</div>
        <a href="index.php" class="sidebar-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
            <i class="bi bi-speedometer2 text-info"></i> Bảng điều khiển
        </a>
        <a href="san_pham.php" class="sidebar-link <?php echo ($current_page == 'san_pham.php') ? 'active' : ''; ?>">
            <i class="bi bi-tv text-primary"></i> Sản phẩm
        </a>
        <a href="danh_muc.php" class="sidebar-link <?php echo ($current_page == 'danh_muc.php') ? 'active' : ''; ?>">
            <i class="bi bi-tags text-warning"></i> Danh mục
        </a>
        <a href="hoa_don.php" class="sidebar-link <?php echo ($current_page == 'hoa_don.php') ? 'active' : ''; ?>">
            <i class="bi bi-receipt text-success"></i> Hóa đơn
        </a>
        <a href="khach_hang.php" class="sidebar-link <?php echo ($current_page == 'khach_hang.php') ? 'active' : ''; ?>">
            <i class="bi bi-people"></i> Khách hàng
        </a>
        
        <div class="sidebar-heading">Nội Dung & Dịch Vụ</div>
        <a href="bai_viet.php" class="sidebar-link <?php echo ($current_page == 'bai_viet.php') ? 'active' : ''; ?>">
            <i class="bi bi-newspaper text-info"></i> Tin tức
        </a>        
        <a href="bao_hanh.php" class="sidebar-link <?php echo ($current_page == 'bao_hanh.php') ? 'active' : ''; ?>">
            <i class="bi bi-shield-check text-primary"></i> Bảo hành
        </a>

        <?php if (isset($duocPhepXuLyPhucTap) && $duocPhepXuLyPhucTap): ?>
            <a href="tra_gop.php" class="sidebar-link <?php echo ($current_page == 'tra_gop.php') ? 'active' : ''; ?>">
                <i class="bi bi-credit-card-2-front text-warning"></i> Trả góp
            </a>
            <a href="doi_tra.php" class="sidebar-link <?php echo ($current_page == 'doi_tra.php') ? 'active' : ''; ?>">
                <i class="bi bi-arrow-repeat text-danger"></i> Đổi trả
            </a>
        <?php else: ?>
            <a href="#" class="sidebar-link locked" onclick="alert('Tính năng bị khóa!\nYêu cầu thâm niên làm việc từ 1 năm trở lên.'); return false;">
                <i class="bi bi-lock-fill text-danger"></i> Trả góp
                <span class="badge bg-danger ms-auto" style="font-size: 0.65rem;">KHÓA</span>
            </a>
            <a href="#" class="sidebar-link locked" onclick="alert('Tính năng bị khóa!\nYêu cầu thâm niên làm việc từ 1 năm trở lên.'); return false;">
                <i class="bi bi-lock-fill text-danger"></i> Đổi trả
                <span class="badge bg-danger ms-auto" style="font-size: 0.65rem;">KHÓA</span>
            </a>
        <?php endif; ?>

        <?php if (isset($isAdmin) && $isAdmin): ?>
            <div class="sidebar-heading">Hệ Thống</div>
            <a href="nhap_hang.php" class="sidebar-link <?php echo ($current_page == 'nhap_hang.php') ? 'active' : ''; ?>">
                <i class="bi bi-box-arrow-in-down text-warning"></i> Nhập hàng
            </a>
            <a href="nhan_vien.php" class="sidebar-link <?php echo ($current_page == 'nhan_vien.php') ? 'active' : ''; ?>">
                <i class="bi bi-person-badge text-success"></i> Nhân viên
            </a>
            <a href="nhatky_hethong.php" class="sidebar-link <?php echo ($current_page == 'nhatky_hethong.php') ? 'active' : ''; ?>">
                <i class="bi bi-clock-history text-secondary"></i> Nhật ký hệ thống
            </a>
        <?php endif; ?>
        
        <div class="d-md-none mt-auto p-3 border-top border-secondary">
            <a href="../trang_chu.php" class="btn btn-outline-light w-100" target="_blank">
                <i class="bi bi-box-arrow-up-right me-1"></i> Xem Website
            </a>
        </div>
        <div class="pb-5 d-none d-md-block"></div>
    </div>
</div>