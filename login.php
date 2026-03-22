<?php
// Bắt buộc phải có hàm này ở dòng ĐẦU TIÊN để khởi tạo Session
session_start();

// Gọi file kết nối cơ sở dữ liệu
require_once 'connect.php';

$error = ''; // Biến lưu thông báo lỗi

// Kiểm tra xem người dùng có bấm nút Đăng nhập (Gửi form qua phương thức POST) chưa
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Sử dụng Prepared Statement để chống lỗi bảo mật SQL Injection
    $sql = "SELECT ID, HoVaTen, QuyenHan FROM NhanVien WHERE TenDangNhap = ? AND MatKhau = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password); // "ss" nghĩa là 2 tham số đều là chuỗi (string)
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Đăng nhập thành công
        $row = $result->fetch_assoc();
        
        // Lưu thông tin vào Session
        $_SESSION['nhanvien_id'] = $row['ID'];
        $_SESSION['ho_ten'] = $row['HoVaTen'];
        $_SESSION['quyen_han'] = $row['QuyenHan']; // 1 là Admin, 0 là Nhân viên

        // Chuyển hướng về trang chủ quản lý (ví dụ: index.php)
        header("Location: admin/index.php");
        exit();
    } else {
        // Đăng nhập thất bại
        $error = "Tên đăng nhập hoặc mật khẩu không đúng!";
    }
    $stmt->close();
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
    
    <?php if($error != ''): ?>
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