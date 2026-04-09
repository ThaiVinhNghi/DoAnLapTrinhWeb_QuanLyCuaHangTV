<?php
session_start();
require_once 'connect.php';

// Không cần load dữ liệu sản phẩm ở trang này nữa, giúp trang tải nhanh hơn
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siêu Thị Tivi N&U - Trang Chủ</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="trang_chu.php"><i class="bi bi-tv"></i> N&U</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="trang_chu.php">Trang Chủ</a></li>
                    <li class="nav-item"><a class="nav-link" href="san_pham.php">Sản Phẩm</a></li>
                    <li class="nav-item"><a class="nav-link" href="trang_chu.php#tin-tuc">Tin Tức</a></li>
                    <li class="nav-item"><a class="nav-link" href="trang_chu.php#lien-he">Liên Hệ</a></li>
                </ul>
                <form class="d-flex me-3" action="tim_kiem.php" method="GET">
                    <input class="form-control me-2" type="search" name="tukhoa" placeholder="Tìm tên Tivi..." required>
                    <button class="btn btn-outline-light" type="submit"><i class="bi bi-search"></i></button>
                </form>
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <a href="gio_hang.php" class="btn btn-warning position-relative fw-bold">
                        <i class="bi bi-cart-fill"></i> Giỏ hàng
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?php echo isset($_SESSION['gio_hang']) ? count($_SESSION['gio_hang']) : 0; ?>
                        </span>
                    </a>
                    <?php if (isset($_SESSION['khach_hang_id'])): ?>
                        <span class="text-white fw-bold mx-2"><i class="bi bi-person-check-fill"></i> Xin chào,
                            <?php echo htmlspecialchars($_SESSION['khach_hang_ten']); ?></span>
                        <a href="logout_khach.php" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Đăng
                            xuất</a>
                    <?php else: ?>
                        <a href="dang_ky.php" class="btn btn-outline-light"><i class="bi bi-person-plus"></i> Đăng ký</a>
                        <a href="login_khach.php" class="btn btn-light"><i class="bi bi-box-arrow-in-right"></i> Đăng
                            nhập</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div id="bannerCarousel" class="carousel slide carousel-fade shadow rounded overflow-hidden" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="0" class="active"></button>
                <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="1"></button>
            </div>
            <div class="carousel-inner">
                <div class="carousel-item active" data-bs-interval="3000">
                    <div class="p-4 p-md-5 text-bg-dark text-center d-flex flex-column justify-content-center align-items-center"
                        style="height: 320px; background: linear-gradient(to right, #0052D4, #4364F7, #6FB1FC);">
                        <h1 class="display-5 fw-bold">Đón Lễ Lớn - Sale Tivi Lên Đến 50%</h1>
                        <p class="lead my-3">Sở hữu ngay những chiếc Smart Tivi 4K, OLED, QLED với giá tốt nhất thị trường.</p>
                        <a href="san_pham.php#khuyen-mai" class="btn btn-warning btn-lg fw-bold rounded-pill shadow-sm">
                            <i class="bi bi-fire"></i> Săn Sale Ngay
                        </a>
                    </div>
                </div>
                <div class="carousel-item" data-bs-interval="3000">
                    <div class="p-4 p-md-5 text-bg-dark text-center d-flex flex-column justify-content-center align-items-center"
                        style="height: 320px; background: linear-gradient(to right, #11998e, #38ef7d);">
                        <h1 class="display-5 fw-bold">Bảo Hành Chính Hãng 2 Năm</h1>
                        <p class="lead my-3">Miễn phí lắp đặt tận nhà - Hỗ trợ trả góp 0% lãi suất.</p>
                        <a href="#lien-he" class="btn btn-dark btn-lg fw-bold rounded-pill shadow-sm">
                            <i class="bi bi-telephone-fill"></i> Liên Hệ Tư Vấn
                        </a>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
            </button>
        </div>
    </div>

    <div class="container mb-5 mt-5" id="tin-tuc">
        <div class="section-header mb-4 border-bottom border-info pb-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h3 class="text-info text-uppercase fw-bold mb-0">
                <i class="bi bi-newspaper"></i> Tin tức & Bài viết mới nhất
            </h3>

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

            <?php if (count($news_chunks) > 1): ?>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-info rounded-circle d-flex justify-content-center align-items-center shadow-sm" type="button" data-bs-target="#newsCarousel" data-bs-slide="prev" style="width: 36px; height: 36px;">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="btn btn-outline-info rounded-circle d-flex justify-content-center align-items-center shadow-sm" type="button" data-bs-target="#newsCarousel" data-bs-slide="next" style="width: 36px; height: 36px;">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <div id="newsCarousel" class="carousel slide" data-bs-ride="false" data-bs-interval="false">
            <div class="carousel-inner pb-3 pt-1 px-1">
                <?php if (!empty($news_chunks)): ?>
                    <?php foreach ($news_chunks as $index => $chunk): ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <div class="row row-cols-1 row-cols-md-3 g-4">
                                <?php foreach ($chunk as $bai):
                                    $img_bai = !empty($bai['HinhAnh']) ? "uploads/" . $bai['HinhAnh'] : "uploads/no-image.jpg";
                                    $moTaNgan = mb_substr(strip_tags($bai['NoiDung']), 0, 150, 'UTF-8') . '...';
                                    ?>
                                    <div class="col">
                                        <div class="card h-100 border-0 shadow-sm product-card">
                                            <img src="<?php echo $img_bai; ?>" class="card-img-top" alt="Tin tức" style="height: 200px; object-fit: cover;">
                                            <div class="card-body d-flex flex-column">
                                                <small class="text-muted mb-2"><i class="bi bi-calendar-event"></i> <?php echo date('d/m/Y', strtotime($bai['NgayDang'])); ?></small>
                                                <h5 class="card-title fw-bold text-dark">
                                                    <a href="doc_tin.php?id=<?php echo $bai['ID']; ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($bai['TieuDe']); ?></a>
                                                </h5>
                                                <p class="card-text text-muted small mt-2"><?php echo $moTaNgan; ?></p>
                                                <a href="doc_tin.php?id=<?php echo $bai['ID']; ?>" class="mt-auto text-info fw-bold text-decoration-none">Đọc tiếp <i class="bi bi-arrow-right"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <p class="text-muted text-center">Chưa có bài viết nào được đăng.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container mb-5 mt-5" id="lien-he">
        <h3 class="border-bottom pb-2 mb-4 text-primary text-uppercase fw-bold">Liên Hệ Với Chúng Tôi</h3>
        <div class="row g-4">
            <div class="col-md-5">
                <div class="card border-0 shadow-sm h-100 bg-primary text-white">
                    <div class="card-body d-flex flex-column justify-content-center p-4">
                        <h4 class="fw-bold mb-4"><i class="bi bi-headset"></i> Hỗ trợ 24/7</h4>
                        <div class="mb-3">
                            <h6 class="text-white-50 text-uppercase mb-1">Địa chỉ cửa hàng</h6>
                            <p class="fs-5"><i class="bi bi-geo-alt-fill text-warning me-2"></i> 54/98 Trần Quang Khải, P. Mỹ Thới, TP. Long Xuyên</p>
                        </div>
                        <div class="mb-3">
                            <h6 class="text-white-50 text-uppercase mb-1">Đường dây nóng</h6>
                            <p class="fs-5"><i class="bi bi-telephone-fill text-warning me-2"></i> <a href="tel:0123456789" class="text-white text-decoration-none">0123.456.789</a></p>
                        </div>
                        <div class="mb-4">
                            <h6 class="text-white-50 text-uppercase mb-1">Email hỗ trợ</h6>
                            <p class="fs-5"><i class="bi bi-envelope-fill text-warning me-2"></i> <a href="mailto:hotro@tivinu.com" class="text-white text-decoration-none">hotro@tivinu.com</a></p>
                        </div>
                        <a href="tel:0123456789" class="btn btn-warning btn-lg fw-bold rounded-pill shadow-sm">
                            <i class="bi bi-telephone-outbound"></i> Gọi Trực Tiếp Ngay
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-0 overflow-hidden rounded">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3924.364402685163!2d105.44196131461957!3d10.392634392582845!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x310a72c1c6bc1b09%3A0xc3dfcd7743d52ea2!2sTr%E1%BA%A7n%20Quang%20Kh%E1%BA%A3i%2C%20M%E1%BB%B9%20Th%E1%BB%9Bi%2C%20Th%C3%A0nh%20ph%E1%BB%91%20Long%20Xuy%C3%AAn%2C%20An%20Giang%2C%20Vietnam!5e0!3m2!1sen!2s!4v1680000000000!5m2!1sen!2s" 
                            width="100%" 
                            height="100%" 
                            style="border:0; min-height: 350px;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">© 2026 - Cửa Hàng Tivi Nghi và Uy.</p>
            <p class="text-muted small">Chất lượng tạo nên thương hiệu</p>
        </div>
    </footer>

    <a href="https://zalo.me/0931082845" target="_blank" class="nút-chat-nổi">
        <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="white" viewBox="0 0 16 16">
            <path d="M8 15c4.418 0 8-3.134 8-7s-3.582-7-8-7-8 3.134-8 7c0 1.76.743 3.37 1.97 4.6-.097 1.016-.417 2.13-.771 2.966-.079.186.074.394.273.362 2.256-.37 3.597-.938 4.18-1.234A9.06 9.06 0 0 0 8 15z" />
        </svg>
    </a>

    <div class="modal fade" id="adminLoginModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-shield-lock-fill text-warning"></i> Khu vực Quản trị viên</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form action="login.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tài khoản</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-person-fill"></i></span>
                                <input type="text" class="form-control" id="adminUsername" name="username" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Mật khẩu</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-key-fill"></i></span>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-dark w-100 fw-bold py-2">Đăng Nhập Hệ Thống</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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