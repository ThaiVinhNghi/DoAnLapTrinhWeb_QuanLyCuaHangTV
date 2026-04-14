<?php
session_start();
require_once 'thu_vien/connect.php';

// Lấy ID bài viết từ URL
$id_bai = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_bai <= 0) {
    echo "<script>alert('Bài viết không tồn tại!'); window.location.href='trang_chu.php';</script>";
    exit();
}

// Truy vấn lấy dữ liệu bài viết
$sql = "SELECT bv.*, nv.HoVaTen 
        FROM baiviet bv 
        LEFT JOIN nhanvien nv ON bv.NhanVienID = nv.ID 
        WHERE bv.ID = ? AND bv.TrangThai = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_bai);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>alert('Bài viết không tồn tại hoặc đã bị ẩn!'); window.location.href='trang_chu.php';</script>";
    exit();
}

$bai = $result->fetch_assoc();
$img_cover = !empty($bai['HinhAnh']) ? "uploads/" . $bai['HinhAnh'] : "uploads/no-image.jpg";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($bai['TieuDe']); ?> - N&U Store</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        /* Navbar Premium */
        .navbar-premium {
            background-color: rgba(10, 10, 10, 0.95) !important;
            backdrop-filter: blur(10px);
            padding: 15px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .navbar-premium .nav-link {
            text-transform: uppercase;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.7) !important;
            transition: color 0.3s;
        }
        .navbar-premium .nav-link:hover, .navbar-premium .nav-link.active {
            color: #fff !important;
        }

        .btn-pill {
            border-radius: 50px;
            padding: 12px 28px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        /* Giao diện Đọc Báo */
        .article-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .article-title {
            font-size: 2.8rem;
            font-weight: 800;
            letter-spacing: -1px;
            line-height: 1.2;
            color: #111;
        }

        .article-meta {
            font-size: 0.95rem;
            letter-spacing: 0.5px;
        }

        .article-content {
            font-size: 1.15rem;
            line-height: 1.8;
            color: #333;
        }

        /* Xử lý ảnh do trình soạn thảo sinh ra để không bị vỡ giao diện */
        .article-content img {
            max-width: 100%;
            height: auto !important;
            border-radius: 12px;
            margin: 2rem 0;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }
        
        .article-content h2, .article-content h3 {
            font-weight: 800;
            margin-top: 2rem;
            margin-bottom: 1rem;
            letter-spacing: -0.5px;
        }
    </style>
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
                    <li class="nav-item"><a class="nav-link" href="trang_chu.php">Khám Phá</a></li>
                    <li class="nav-item"><a class="nav-link" href="san_pham.php">Sản Phẩm</a></li>
                    <li class="nav-item"><a class="nav-link active" href="trang_chu.php#tin-tuc">Tin Tức</a></li>
                    <li class="nav-item"><a class="nav-link" href="trang_chu.php#lien-he">Hỗ Trợ</a></li>
                </ul>
                <div class="d-flex align-items-center gap-3">
                    <a href="gio_hang.php" class="text-white text-decoration-none position-relative fs-5 me-2">
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

    <div class="bg-white py-3 border-bottom shadow-sm">
        <div class="container article-container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size: 0.9rem;">
                    <li class="breadcrumb-item"><a href="trang_chu.php" class="text-decoration-none text-secondary">Trang Chủ</a></li>
                    <li class="breadcrumb-item"><a href="trang_chu.php#tin-tuc" class="text-decoration-none text-secondary">Tin Tức</a></li>
                    <li class="breadcrumb-item active fw-bold text-dark" aria-current="page">Chi tiết bài viết</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container mt-5 mb-5 pb-5 article-container">
        
        <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border-0">
            
            <div class="text-center mb-5">
                <span class="badge bg-dark px-3 py-2 text-uppercase mb-4" style="letter-spacing: 1px;">Góc Công Nghệ</span>
                <h1 class="article-title mb-4"><?php echo htmlspecialchars($bai['TieuDe']); ?></h1>
                
                <div class="article-meta text-muted d-flex justify-content-center align-items-center gap-4 flex-wrap">
                    <span><i class="bi bi-calendar-event me-1"></i> <?php echo date('d M, Y', strtotime($bai['NgayDang'])); ?></span>
                    <span><i class="bi bi-pen me-1"></i> Đăng bởi <strong class="text-dark"><?php echo htmlspecialchars($bai['HoVaTen']); ?></strong></span>
                </div>
            </div>

            <div class="mb-5 rounded-4 overflow-hidden shadow-sm" style="height: 450px; background-color: #000;">
                <img src="<?php echo $img_cover; ?>" class="w-100 h-100 opacity-75" style="object-fit: cover;" alt="Cover Image">
            </div>

            <div class="article-content">
                <?php echo $bai['NoiDung']; ?>
            </div>
            
            <hr class="my-5 text-muted opacity-25">
            
            <div class="text-center">
                <a href="trang_chu.php#tin-tuc" class="btn btn-outline-dark btn-pill">
                    <i class="bi bi-arrow-left me-2"></i> Trở về tin tức khác
                </a>
            </div>
            
        </div>
    </div>

    <footer class="bg-black text-white-50 text-center py-4 border-top border-secondary">
        <div class="container">
            <p class="mb-1 text-white fw-bold">© 2026 - Cửa Hàng Tivi Nghi và Uy.</p>
            <p class="small mb-0">Chất lượng tạo nên thương hiệu. Mọi bản quyền được bảo lưu.</p>
        </div>
    </footer>

    <a href="https://zalo.me/0931082845" target="_blank" class="position-fixed bottom-0 end-0 m-4 bg-primary rounded-circle shadow-lg d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; z-index: 1000; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
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