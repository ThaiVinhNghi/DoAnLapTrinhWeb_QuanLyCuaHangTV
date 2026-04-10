<?php 
// 1. Gọi file Header (chứa Navbar và mở thẻ Container/Row)
include 'header.php'; 

// 2. Gọi file Sidebar (Cột menu bên trái)
include 'sidebar.php'; 
?>
 
<div class="col-md-9 col-lg-10 main-content">
    
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-secondary">Admin</a></li>
            <li class="breadcrumb-item active fw-bold text-dark" aria-current="page">Bảng điều khiển</li>
        </ol>
    </nav>

    <h3 class="fw-bold mb-4">Tổng Quan Hệ Thống</h3>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase mb-2" style="font-size: 0.8rem;">Doanh Thu Tháng</h6>
                        <h3 class="fw-bold text-dark m-0">245.000.000 đ</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                        <i class="bi bi-graph-up-arrow fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase mb-2" style="font-size: 0.8rem;">Đơn Hàng Mới</h6>
                        <h3 class="fw-bold text-dark m-0">48</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                        <i class="bi bi-cart-check fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted fw-bold text-uppercase mb-2" style="font-size: 0.8rem;">Yêu Cầu Hỗ Trợ</h6>
                        <h3 class="fw-bold text-danger m-0">5</h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle">
                        <i class="bi bi-headset fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// 4. Gọi file Footer (Đóng các thẻ div layout và load Javascript)
include 'footer.php'; 
?>