<?php
session_start();
require_once 'connect.php';

// Lấy từ khóa người dùng nhập vào
$tuKhoa = isset($_GET['tukhoa']) ? trim($_GET['tukhoa']) : '';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tìm kiếm: <?php echo htmlspecialchars($tuKhoa); ?> - TIVI STORE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .product-card { transition: transform 0.3s, box-shadow 0.3s; border-radius: 15px; overflow: hidden; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.15); }
        .product-img { height: 200px; object-fit: contain; width: 100%; background-color: #fff; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="trang_chu.php"><i class="bi bi-tv"></i> TIVI STORE</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="trang_chu.php">Trang Chủ</a></li>
            </ul>
            
            <form class="d-flex me-3" action="tim_kiem.php" method="GET">
                <input class="form-control me-2" type="search" name="tukhoa" value="<?php echo htmlspecialchars($tuKhoa); ?>" placeholder="Tìm tên Tivi..." required>
                <button class="btn btn-outline-light" type="submit"><i class="bi bi-search"></i></button>
            </form>

            <div class="d-flex align-items-center">
                <a href="gio_hang.php" class="btn btn-warning me-2 fw-bold position-relative">
                    <i class="bi bi-cart-fill"></i> Giỏ hàng
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?php echo isset($_SESSION['gio_hang']) ? count($_SESSION['gio_hang']) : 0; ?>
                    </span>
                </a>
                <a href="login.php" class="btn btn-outline-light"><i class="bi bi-person-circle"></i> Admin</a>
            </div>
        </div>
    </div>
</nav>

<div class="container mt-5 mb-5">
    <h3 class="border-bottom pb-2 mb-4 text-primary fw-bold">
        Kết quả tìm kiếm cho: "<span class="text-danger"><?php echo htmlspecialchars($tuKhoa); ?></span>"
    </h3>
    
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php
        if ($tuKhoa != '') {
            // Dùng LIKE để tìm kiếm gần đúng tên sản phẩm
            $sql = "SELECT sp.*, hsx.TenHangSanXuat 
                    FROM SanPham sp 
                    LEFT JOIN HangSanXuat hsx ON sp.HangSanXuatID = hsx.ID
                    WHERE sp.TenSanPham LIKE ? 
                    ORDER BY sp.ID DESC"; 
            
            $stmt = $conn->prepare($sql);
            $searchParam = "%" . $tuKhoa . "%";
            $stmt->bind_param("s", $searchParam);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $hinhAnh = !empty($row['HinhAnh']) ? "uploads/".$row['HinhAnh'] : "uploads/no-image.jpg";
                    $tenSP = $row['TenSanPham'];
                    $giaBan = number_format($row['DonGia'], 0, ',', '.');
                    $tenHang = $row['TenHangSanXuat'];
                    $idSP = $row['ID'];
                    
                    echo '
                    <div class="col">
                        <div class="card h-100 product-card border-0 shadow-sm">
                            <span class="badge bg-danger position-absolute" style="top: 10px; right: 10px; z-index: 10;">'.$tenHang.'</span>
                            <a href="chi_tiet_san_pham.php?id='.$idSP.'">
                                <img src="'.$hinhAnh.'" class="card-img-top product-img p-3" alt="'.$tenSP.'">
                            </a>
                            <div class="card-body d-flex flex-column text-center">
                                <h6 class="card-title" style="height: 40px; overflow: hidden;">
                                    <a href="chi_tiet_san_pham.php?id='.$idSP.'" class="text-decoration-none text-dark fw-bold">'.$tenSP.'</a>
                                </h6>
                                <h5 class="card-text text-danger fw-bold mt-auto mb-3">'.$giaBan.' đ</h5>
                                <form action="them_gio_hang.php" method="POST">
                                    <input type="hidden" name="id_sp" value="'.$idSP.'">
                                    <button type="submit" class="btn btn-primary w-100 rounded-pill">
                                        <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>';
                }
            } else {
                echo '<div class="col-12"><div class="alert alert-warning text-center">Không tìm thấy Tivi nào phù hợp với từ khóa của bạn.</div></div>';
            }
            $stmt->close();
        } else {
            echo '<div class="col-12"><div class="alert alert-info text-center">Vui lòng nhập từ khóa để tìm kiếm.</div></div>';
        }
        ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>