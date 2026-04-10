<?php
session_start();
require_once 'thu_vien/connect.php';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>N&U Store | Trải Nghiệm Tivi Đỉnh Cao</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="tai_nguyen/css/style.css">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark navbar-premium sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4 text-white" href="trang_chu.php"><i class="bi bi-tv text-danger"></i> N&U</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link active" href="trang_chu.php">Khám Phá</a></li>
                    <li class="nav-item"><a class="nav-link" href="san_pham.php">Sản Phẩm</a></li>
                    <li class="nav-item"><a class="nav-link" href="trang_chu.php#tin-tuc">Tin Tức</a></li>
                    <li class="nav-item"><a class="nav-link" href="trang_chu.php#lien-he">Hỗ Trợ</a></li>
                </ul>
                <div class="d-flex align-items-center gap-3">
                    <a href="gio_hang.php" class="text-white text-decoration-none position-relative fs-5">
                        <i class="bi bi-bag"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                            <?php echo isset($_SESSION['gio_hang']) ? count($_SESSION['gio_hang']) : 0; ?>
                        </span>
                    </a>
                    <div class="vr bg-secondary mx-2" style="width: 2px; height: 24px;"></div>
                    <?php if (isset($_SESSION['khach_hang_id'])): ?>
                        <div class="dropdown">
                            <a class="text-white text-decoration-none dropdown-toggle fw-bold" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['khach_hang_ten']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3">
                                <li><a class="dropdown-item" href="san_pham.php#san-pham-da-mua">Đơn hàng của tôi</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger fw-bold" href="logout_khach.php">Đăng xuất</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login_khach.php" class="text-white text-decoration-none fw-bold" style="font-size: 0.9rem;">Đăng Nhập</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div id="heroCarousel" class="carousel slide carousel-fade hero-banner" data-bs-ride="carousel">
        <div class="carousel-indicators mb-4">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active rounded-circle" style="width: 10px; height: 10px;"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" class="rounded-circle" style="width: 10px; height: 10px;"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active hero-bg-1" data-bs-interval="4000">
                <div class="hero-content text-white">
                    <span class="badge bg-danger mb-3 px-3 py-2 fw-bold text-uppercase letter-spacing-1">Thế Hệ Mới 2026</span>
                    <h1 class="hero-title">Định Nghĩa Lại<br><span class="text-danger">Màu Sắc</span> Tuyệt Đối</h1>
                    <p class="lead text-white-50 mb-4 fs-5">Khám phá dòng Tivi QD-Mini LED với độ sáng và độ tương phản vượt mọi giới hạn. Trải nghiệm điện ảnh ngay tại phòng khách nhà bạn.</p>
                    <a href="san_pham.php" class="btn btn-light btn-pill text-dark">Khám phá ngay</a>
                </div>
            </div>
            <div class="carousel-item hero-bg-2" data-bs-interval="4000">
                <div class="hero-content text-white">
                    <span class="badge bg-primary mb-3 px-3 py-2 fw-bold text-uppercase letter-spacing-1">Giải Trí Đỉnh Cao</span>
                    <h1 class="hero-title">Âm Thanh Vòm<br>Sống Động 360°</h1>
                    <p class="lead text-white-50 mb-4 fs-5">Tích hợp công nghệ Dolby Atmos, mang đến không gian âm thanh chân thực như rạp hát chuyên nghiệp.</p>
                    <a href="san_pham.php#khuyen-mai" class="btn btn-outline-light btn-pill">Xem ưu đãi</a>
                </div>
            </div>
        </div>
        <button class="hero-control position-absolute" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" style="left: 16px; top: 50%; transform: translateY(-50%);">
            <i class="bi bi-chevron-left fs-4"></i>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="hero-control position-absolute" type="button" data-bs-target="#heroCarousel" data-bs-slide="next" style="right: 16px; top: 50%; transform: translateY(-50%);">
            <i class="bi bi-chevron-right fs-4"></i>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <div class="container mt-n5 position-relative" style="z-index: 10; margin-top: -50px;">
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <a href="san_pham.php" class="text-decoration-none">
                    <div class="card premium-card bg-dark text-white text-center py-4">
                        <i class="bi bi-display display-4 mb-2 text-danger"></i>
                        <h5 class="fw-bold">Tất Cả Tivi</h5>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="san_pham.php#khuyen-mai" class="text-decoration-none">
                    <div class="card premium-card bg-danger text-white text-center py-4">
                        <i class="bi bi-fire display-4 mb-2 text-white"></i>
                        <h5 class="fw-bold">Hot Sale Lễ Lớn</h5>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="trang_chu.php#lien-he" class="text-decoration-none">
                    <div class="card premium-card bg-white text-dark text-center py-4 border">
                        <i class="bi bi-headset display-4 mb-2 text-primary"></i>
                        <h5 class="fw-bold">Hỗ Trợ KH 24/7</h5>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <div class="container mb-5" style="margin-top: 100px;" id="tin-tuc">
        <h2 class="premium-section-title">Tin Tức & Công Nghệ</h2>

        <?php
        $sql_bai = "SELECT * FROM baiviet WHERE TrangThai = 1 ORDER BY NgayDang DESC LIMIT 15";
        $res_bai = $conn->query($sql_bai);
        $all_news = [];
        if ($res_bai && $res_bai->num_rows > 0) {
            while ($bai = $res_bai->fetch_assoc()) {
                $all_news[] = $bai;
            }
        }
        $news_chunks = array_chunk($all_news, 3);
        ?>

        <div id="newsCarousel" class="carousel slide" data-bs-ride="false">
            <div class="carousel-inner p-3">
                <?php if (!empty($news_chunks)): ?>
                    <?php foreach ($news_chunks as $index => $chunk): ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <div class="row row-cols-1 row-cols-md-3 g-4">
                                <?php foreach ($chunk as $bai):
                                    $img_bai = !empty($bai['HinhAnh']) ? "uploads/" . $bai['HinhAnh'] : "uploads/no-image.jpg";
                                    $moTaNgan = mb_substr(strip_tags($bai['NoiDung']), 0, 120, 'UTF-8') . '...';
                                    ?>
                                    <div class="col">
                                        <div class="card h-100 premium-card shadow-sm">
                                            <div class="overflow-hidden" style="height: 220px;">
                                                <img src="<?php echo $img_bai; ?>" class="w-100 h-100 object-fit-cover" alt="Tin tức">
                                            </div>
                                            <div class="card-body p-4 d-flex flex-column">
                                                <small class="text-danger fw-bold mb-2 text-uppercase" style="font-size: 0.75rem;">
                                                    <i class="bi bi-calendar-event"></i> <?php echo date('d M, Y', strtotime($bai['NgayDang'])); ?>
                                                </small>
                                                <h5 class="card-title fw-bold text-dark lh-base mb-3">
                                                    <a href="doc_tin.php?id=<?php echo $bai['ID']; ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($bai['TieuDe']); ?></a>
                                                </h5>
                                                <p class="card-text text-secondary small mb-4"><?php echo $moTaNgan; ?></p>
                                                <a href="doc_tin.php?id=<?php echo $bai['ID']; ?>" class="mt-auto fw-bold text-dark text-decoration-none border-bottom border-dark d-inline-block" style="width: max-content; padding-bottom: 2px;">Xem chi tiết <i class="bi bi-arrow-right"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center">Chưa có bài viết nào được cập nhật.</p>
                <?php endif; ?>
            </div>
            
            <?php if (count($news_chunks) > 1): ?>
                <div class="d-flex justify-content-center mt-4 gap-3">
                    <button class="btn btn-dark rounded-circle" type="button" data-bs-target="#newsCarousel" data-bs-slide="prev" style="width: 40px; height: 40px;">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="btn btn-dark rounded-circle" type="button" data-bs-target="#newsCarousel" data-bs-slide="next" style="width: 40px; height: 40px;">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-dark text-white py-5 mt-5" id="lien-he">
        <div class="container py-4">
            <div class="row align-items-center g-5">
                <div class="col-lg-5">
                    <h2 class="fw-bold mb-4 display-6">Ghé Thăm<br>Showroom N&U</h2>
                    <p class="text-white-50 mb-5 fs-5">Trải nghiệm thực tế các dòng Tivi cao cấp nhất. Đội ngũ chuyên gia của chúng tôi luôn sẵn sàng tư vấn giải pháp phù hợp nhất cho không gian của bạn.</p>
                    
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-light rounded-circle text-dark d-flex align-items-center justify-content-center me-4" style="width: 60px; height: 60px;">
                            <i class="bi bi-geo-alt-fill fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-white-50 text-uppercase fw-bold mb-1" style="font-size: 0.8rem;">Địa chỉ</h6>
                            <h5 class="mb-0 fw-bold">54/98 Trần Quang Khải, P. Mỹ Thới, Long Xuyên</h5>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-light rounded-circle text-dark d-flex align-items-center justify-content-center me-4" style="width: 60px; height: 60px;">
                            <i class="bi bi-telephone-fill fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-white-50 text-uppercase fw-bold mb-1" style="font-size: 0.8rem;">Hotline (8:00 - 21:00)</h6>
                            <h5 class="mb-0 fw-bold">0123.456.789</h5>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-7">
                    <div class="premium-card overflow-hidden shadow-lg p-1 bg-white">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d502345.29747846996!2d104.83141518906247!3d10.376014800000027!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x310a737b9f23c3ff%3A0xdb7be52fa915fc1f!2zSG_DoG5nIEjDoCBNb2JpbGU!5e0!3m2!1svi!2s!4v1775786373144!5m2!1svi!2s" 
                            width="100%" 
                            height="450" 
                            style="border:0; border-radius: 12px;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-black text-white-50 text-center py-4 border-top border-secondary">
        <div class="container">
            <p class="mb-1 text-white fw-bold">© 2026 - Cửa Hàng Tivi Nghi và Uy.</p>
            <p class="small mb-0">Chất lượng tạo nên thương hiệu. Mọi bản quyền được bảo lưu.</p>
        </div>
    </footer>

    <a href="https://zalo.me/0931082845" target="_blank" class="nút-chat-nổi position-fixed bottom-0 end-0 m-4 bg-primary rounded-circle shadow-lg d-flex align-items-center justify-content-center hover-zoom" style="width: 60px; height: 60px; z-index: 1000; transition: transform 0.3s;">
        <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="white" viewBox="0 0 16 16">
            <path d="M8 15c4.418 0 8-3.134 8-7s-3.582-7-8-7-8 3.134-8 7c0 1.76.743 3.37 1.97 4.6-.097 1.016-.417 2.13-.771 2.966-.079.186.074.394.273.362 2.256-.37 3.597-.938 4.18-1.234A9.06 9.06 0 0 0 8 15z" />
        </svg>
    </a>

    <div class="modal fade" id="adminLoginModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header bg-black text-white border-0 py-4">
                    <h5 class="modal-title fw-bold mx-auto"><i class="bi bi-cpu text-danger me-2"></i> SYSTEM ADMIN</h5>
                    <button type="button" class="btn-close btn-close-white position-absolute end-0 me-4" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-5">
                    <form action="login.php" method="POST">
                        <div class="form-floating mb-4">
                            <input type="text" class="form-control bg-light border-0" id="adminUsername" name="username" placeholder="Tài khoản" required style="border-radius: 8px;">
                            <label for="adminUsername" class="text-muted"><i class="bi bi-person-fill me-1"></i> Tài khoản</label>
                        </div>
                        <div class="form-floating mb-5">
                            <input type="password" class="form-control bg-light border-0" id="adminPassword" name="password" placeholder="Mật khẩu" required style="border-radius: 8px;">
                            <label for="adminPassword" class="text-muted"><i class="bi bi-key-fill me-1"></i> Mật khẩu</label>
                        </div>
                        <button type="submit" class="btn btn-dark btn-pill w-100 py-3 fs-6">Truy Cập Hệ Thống</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Lắng nghe sự kiện phím tắt đăng nhập Admin (Ctrl + Shift + A)
        document.addEventListener('keydown', function(event) {
            if (event.ctrlKey && event.shiftKey && (event.key === 'A' || event.key === 'a')) {
                event.preventDefault();
                var myModal = new bootstrap.Modal(document.getElementById('adminLoginModal'));
                myModal.show();
                document.getElementById('adminLoginModal').addEventListener('shown.bs.modal', function () {
                    document.getElementById('adminUsername').focus();
                });
            }
        });
    </script>
</body>
</html>