<div align="center">
  <h1>📺 HỆ THỐNG QUẢN LÝ CỬA HÀNG TIVI</h1>
  <p><b>Đồ án Lập trình Web - Quản lý bán hàng, sản phẩm, hóa đơn và bảo hành</b></p>
</div>

---

## 📌 Giới thiệu đề tài
Dự án được thực hiện nhằm hỗ trợ quản lý bán hàng, sản phẩm, khách hàng, giỏ hàng, hóa đơn, bảo hành và các chức năng quản trị trong cửa hàng Tivi. 

Hệ thống được xây dựng theo hướng thực tế, dễ sử dụng, giao diện thân thiện, phù hợp với mô hình quản lý cửa hàng điện máy quy mô nhỏ đến vừa.

## 👨‍💻 Sinh viên thực hiện

| Họ và tên | MSSV | Lớp |
| :--- | :---: | :---: |
| **Thái Vĩnh Nghi** | `DTH235704` | DH24TH2 |
| **Nguyễn Hoàng Uy** | `DTH235812` | DH24TH3 |

## 🛠️ Công nghệ sử dụng

- **Ngôn ngữ & Xử lý:** PHP, JavaScript
- **Giao diện:** HTML5, CSS3, Bootstrap
- **Cơ sở dữ liệu:** MySQL
- **Môi trường & Công cụ:** XAMPP, Visual Studio Code, GitHub

---

## 📂 Mục đích sử dụng GitHub
Nhóm sử dụng **GitHub** để:
- Lưu trữ source code đồ án.
- Đồng bộ code giữa các thành viên.
- Theo dõi lịch sử chỉnh sửa.
- Hỗ trợ làm việc nhóm hiệu quả.
- Sao lưu project trong quá trình thực hiện.

---

## ⚙️ Cấu hình môi trường phát triển

Dự án được phát triển trên môi trường **XAMPP + MySQL**. Mỗi thành viên sử dụng một cổng kết nối khác nhau để tránh xung đột trong quá trình làm việc:

| Sinh viên | Cổng MySQL sử dụng |
| :--- | :---: |
| **Thái Vĩnh Nghi** | `3306` |
| **Nguyễn Hoàng Uy** | `3307` |

> ⚠️ **Lưu ý:** Khi chạy project trên máy, cần chỉnh lại file kết nối cơ sở dữ liệu (`connect.php`) cho đúng với cổng đang sử dụng.

---

## 🔐 Phân quyền hệ thống

Hệ thống được thiết kế với 3 vai trò chính, đảm bảo logic nghiệp vụ thực tế:

### 1. 🔴 Admin (Quản trị viên)
Là người quản trị cao nhất, có toàn quyền quản lý hệ thống:
- Quản lý toàn bộ danh mục: Sản phẩm, Hóa đơn, Bảo hành, Khách hàng.
- **Đặc quyền:** Xem danh sách nhân viên và tạo mới nhân viên.

### 2. 🟡 Nhân viên
Thực hiện các nghiệp vụ bán hàng và hỗ trợ khách hàng:
- Lập hóa đơn, hỗ trợ bán hàng và quản lý thông tin khách hàng.
- **Hạn chế:** ❌ Không được phép xem danh sách nhân viên và ❌ Không được tạo mới nhân viên.

### 3. 🟢 Khách hàng
Người trực tiếp sử dụng hệ thống để mua sắm:
- Đăng ký/Đăng nhập, xem và tìm kiếm sản phẩm.
- Thêm vào giỏ hàng và thanh toán.
- *Khách hàng có thể tự đăng ký hoặc được Admin/Nhân viên thêm vào hệ thống.*

---

## 🧩 Chức năng chính

| STT | Phân hệ | Chức năng chi tiết |
| :---: | :--- | :--- |
| **1** | **Trải nghiệm mua sắm** | Xem trang chủ (sản phẩm nổi bật/mới), xem chi tiết, tìm kiếm sản phẩm. |
| **2** | **Giao dịch** | Quản lý giỏ hàng (thêm, sửa, xóa), xử lý đặt hàng và lập hóa đơn thanh toán. |
| **3** | **Tài khoản** | Đăng ký, đăng nhập, đăng xuất và xác thực người dùng. |
| **4** | **Quản trị chung** | Quản lý sản phẩm, khách hàng, hóa đơn. |
| **5** | **Hậu mãi & Nhân sự** | Quản lý thông tin/thời hạn bảo hành, quản lý nhân viên (Chỉ Admin). |

---

## 🗂️ Cấu trúc mã nguồn chính
📁 DoAnLapTrinhWeb_QuanLyCuaHangTV
 ├── 📁 admin/                   # Khu vực dành riêng cho quản trị hệ thống
 ├── 📁 uploads/                 # Lưu trữ hình ảnh và dữ liệu tải lên
 ├── 📁 filexuathoadonchitiet/   # Lưu file hóa đơn chi tiết
 ├── 📁 filexuatphieubaohanh/    # Lưu file phiếu bảo hành
 ├── 📄 connect.php              # Cấu hình kết nối CSDL MySQL
 ├── 📄 trang_chu.php            # Giao diện Trang chủ
 ├── 📄 gio_hang.php             # Xử lý Giỏ hàng
 ├── 📄 thanh_toan.php           # Xử lý Thanh toán
 ├── 📄 dang_ky.php              # Form đăng ký tài khoản
🚀 Hướng dẫn cài đặt và chạy dự án
Bước 1: Clone source code
Mở Terminal hoặc Command Prompt và chạy lệnh sau:

Bash
git clone [https://github.com/ThaiVinhNghi/DoAnLapTrinhWeb_QuanLyCuaHangTV.git](https://github.com/ThaiVinhNghi/DoAnLapTrinhWeb_QuanLyCuaHangTV.git)
Bước 2: Cài đặt vào XAMPP
Di chuyển thư mục project vừa clone về vào thư mục htdocs của XAMPP.

Ví dụ đường dẫn: C:\xampp\htdocs\DoAnLapTrinhWeb_QuanLyCuaHangTV
Bước 3: Khởi động Server
Mở XAMPP Control Panel, sau đó khởi động 2 module:

Apache

MySQL (Lưu ý kiểm tra xem cổng đang là 3306 hay 3307)
Bước 4: Thiết lập Cơ sở dữ liệu
Truy cập http://localhost/phpmyadmin trên trình duyệt.

Tạo một Database mới.

Chuyển sang tab Import (Nhập).

Chọn và import file quanlycuahangtivi.sql hoặc CSDL_DoAnWeb.sql có sẵn trong thư mục project.
Bước 5: Cấu hình kết nối CSDL
Mở file connect.php bằng Visual Studio Code và cập nhật thông tin cho khớp với máy của bạn:
$host = "localhost";    // hoặc 127.0.0.1
$user = "root";
$password = "";         // Mật khẩu MySQL (thường để trống)
$database = "ten_database_vua_tao";
$port = 3306;           // Đổi thành 3307 nếu máy bạn khác
Kết luận
Dự án là cơ hội để nhóm rèn luyện và áp dụng thực tế các kiến thức:

Phân tích và thiết kế hệ thống web thực tiễn.

Lập trình PHP kết hợp thao tác cơ sở dữ liệu MySQL.

Quản lý phiên bản và làm việc nhóm hiệu quả thông qua GitHub.

Xây dựng luồng nghiệp vụ hoàn chỉnh từ mua hàng đến quản lý nhân sự.

📎 Repository GitHub: DoAnLapTrinhWeb_QuanLyCuaHangTV
Nếu các bạn có ý tưởng mới, muốn đóng góp thêm tính năng hoặc gặp khó khăn trong quá trình cài đặt, đừng ngần ngại nhắn tin hoặc tạo Issue / Pull Request nhé. Tụi mình luôn sẵn sàng giúp đỡ, giải đáp và cùng nhau học hỏi phát triển! 😊
 ├── 📄 login.php                # Đăng nhập hệ thống
 └── 📄 quanlycuahangtivi.sql    # File dump Cơ sở dữ liệu
