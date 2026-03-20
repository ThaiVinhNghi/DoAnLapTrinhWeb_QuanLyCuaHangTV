<?php
// Báo cho trình duyệt biết trang này dùng tiếng Việt (UTF-8)
header('Content-Type: text/html; charset=utf-8');
// Cấu hình các thông số kết nối database
$servername = "localhost"; // Thường là localhost nếu bạn dùng XAMPP, WAMP, Laragon...
$username   = "root";      // Tên đăng nhập MySQL mặc định của XAMPP là 'root'
$password   = "vertrigo";          // Mật khẩu MySQL mặc định của XAMPP là rỗng (để trống)
$dbname     = "QuanLyCuaHangTivi"; // Tên cơ sở dữ liệu của bạn

// Tạo kết nối bằng MySQLi (Hướng đối tượng)
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    // Nếu lỗi, dừng chương trình và in ra thông báo
    die("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
}

// Thiết lập charset utf8mb4 để đọc/ghi Tiếng Việt có dấu không bị lỗi font
$conn->set_charset("utf8mb4");

// Nếu muốn test xem kết nối thành công chưa, bạn có thể bỏ dấu // ở dòng dưới:
 //echo "Kết nối CSDL QuanLyCuaHangTivi thành công!";
?>