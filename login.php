<?php
session_start();
require_once 'connect.php';
require_once 'nhatky_helper.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!";
    } else {
        $sql = "SELECT ID, HoVaTen, TenDangNhap, QuyenHan
                FROM nhanvien
                WHERE TenDangNhap = ? AND MatKhau = ?
                LIMIT 1";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ss", $username, $password);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();

                // Lưu các session chung cho mọi nhân viên
                $_SESSION['nhanvien_id'] = (int)$row['ID'];
                $_SESSION['nhan_vien_id'] = (int)$row['ID']; // alias để khỏi vỡ file cũ
                $_SESSION['nhanvien_hoten'] = $row['HoVaTen'];
                $_SESSION['ho_ten'] = $row['HoVaTen'];
                $_SESSION['nhanvien_tendangnhap'] = $row['TenDangNhap'];
                $_SESSION['ten_dang_nhap'] = $row['TenDangNhap'];
                $_SESSION['quyen_han'] = (int)$row['QuyenHan'];

                // NẾU LÀ ADMIN, PHẢI LƯU THÊM SESSION DÀNH RIÊNG CHO ADMIN
                if ((int)$row['QuyenHan'] === 1) {
                    $_SESSION['admin_id'] = (int)$row['ID'];
                    
                    // --- ĐÂY LÀ 2 DÒNG QUAN TRỌNG ĐƯỢC THÊM VÀO ---
                    $_SESSION['admin_hoten'] = $row['HoVaTen'];
                    $_SESSION['admin_tendangnhap'] = $row['TenDangNhap'];
                    // ----------------------------------------------
                } else {
                    unset($_SESSION['admin_id']);
                    unset($_SESSION['admin_hoten']);
                    unset($_SESSION['admin_tendangnhap']);
                }

                ghiNhatKy(
                    $conn,
                    ((int)$row['QuyenHan'] === 1) ? 'Admin' : 'NhanVien',
                    $row['ID'],
                    $row['TenDangNhap'],
                    $row['HoVaTen'],
                    'DangNhap',
                    'nhanvien',
                    $row['ID'],
                    'Đăng nhập hệ thống quản trị',
                    'ThanhCong'
                );

                header("Location: admin/index.php");
                exit();
            } else {
                $error = "Tên đăng nhập hoặc mật khẩu không đúng!";

                ghiNhatKy(
                    $conn,
                    'NhanVien',
                    null,
                    $username,
                    null,
                    'DangNhap',
                    'nhanvien',
                    null,
                    'Đăng nhập thất bại',
                    'ThatBai'
                );
            }

            $stmt->close();
        } else {
            $error = "Không thể chuẩn bị câu lệnh đăng nhập!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập hệ thống - Quản Lý Cửa Hàng Tivi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .login-box {
            width: 100%;
            max-width: 400px;
            margin: 100px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="login-box">
    <h3 class="text-center mb-4 text-primary">HỆ THỐNG QUẢN LÝ</h3>

    <?php if ($error != ''): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label for="username" class="form-label">Tên đăng nhập</label>
            <input type="text" class="form-control" id="username" name="username" required placeholder="Nhập tên đăng nhập...">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Mật khẩu</label>
            <input type="password" class="form-control" id="password" name="password" required placeholder="Nhập mật khẩu...">
        </div>
        <button type="submit" class="btn btn-primary w-100">Đăng Nhập</button>
    </form>
</div>

</body>
</html>