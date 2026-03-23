
Còn đây là **bản full README hoàn chỉnh** đã gộp luôn phần đó vào:

```markdown
# 🖥️ ĐỒ ÁN LẬP TRÌNH WEB  
# HỆ THỐNG QUẢN LÝ CỬA HÀNG TIVI

## 📌 Giới thiệu đề tài
Đây là đồ án **Lập trình Web** với đề tài xây dựng **Hệ thống quản lý cửa hàng Tivi**.  
Dự án được thực hiện nhằm hỗ trợ quản lý bán hàng, sản phẩm, khách hàng, giỏ hàng, hóa đơn, bảo hành và các chức năng quản trị trong cửa hàng.

Hệ thống được xây dựng theo hướng thực tế, dễ sử dụng, giao diện thân thiện, phù hợp với mô hình quản lý cửa hàng điện máy quy mô nhỏ đến vừa.

---

## 👨‍💻 Sinh viên thực hiện

| Họ và tên | MSSV | Lớp |
|----------|------|------|
| **Thái Vĩnh Nghi** | **DTH235704** | **DH24TH2** |
| **Nguyễn Hoàng Uy** | **DTH235812** | **DH24TH3** |

---

## 🛠️ Công nghệ sử dụng

| Thành phần | Công nghệ |
|-----------|-----------|
| Ngôn ngữ lập trình | **PHP** |
| Môi trường lập trình | **Visual Studio Code** |
| Web Server | **XAMPP** |
| Cơ sở dữ liệu | **MySQL** |
| Quản lý mã nguồn | **GitHub** |
| Giao diện | **HTML, CSS, Bootstrap** |
| Xử lý phía client | **JavaScript** |

---

## 📂 Mục đích sử dụng GitHub
Nhóm sử dụng **GitHub** để:

- Lưu trữ source code đồ án
- Đồng bộ code giữa các thành viên
- Theo dõi lịch sử chỉnh sửa
- Hỗ trợ làm việc nhóm hiệu quả
- Sao lưu project trong quá trình thực hiện

---

## ⚙️ Cấu hình môi trường phát triển

Dự án được phát triển trên môi trường **XAMPP + MySQL**.  
Mỗi thành viên sử dụng một cổng kết nối khác nhau để tránh xung đột trong quá trình làm việc:

| Sinh viên | Cổng MySQL sử dụng |
|----------|---------------------|
| **Thái Vĩnh Nghi** | **3306** |
| **Nguyễn Hoàng Uy** | **3307** |

> **Lưu ý:** Khi chạy project trên máy của từng thành viên, cần chỉnh lại file kết nối cơ sở dữ liệu (`connect.php`) cho đúng cổng đang sử dụng.

---

## 🔐 Phân quyền hệ thống

### 1. Admin
Admin có toàn quyền quản lý hệ thống, bao gồm:

- Xem toàn bộ dữ liệu trong hệ thống
- Quản lý sản phẩm
- Quản lý hóa đơn
- Quản lý bảo hành
- Quản lý khách hàng
- **Xem danh sách nhân viên**
- **Tạo mới nhân viên**
- Quản lý các chức năng quản trị khác

✅ **Chỉ Admin mới được xem và tạo nhân viên**

---

### 2. Nhân viên
Nhân viên được phép sử dụng các chức năng nghiệp vụ phục vụ bán hàng, nhưng bị giới hạn ở phần quản trị nhân sự.

Nhân viên có thể:

- Hỗ trợ bán hàng
- Lập hóa đơn
- Hỗ trợ quản lý khách hàng
- Thực hiện các thao tác nghiệp vụ được phân công

Nhân viên **không được phép**:

- **Xem danh sách nhân viên**
- **Tạo mới nhân viên**

❌ **Nhân viên không được xem và không được tạo nhân viên**

---

### 3. Khách hàng
Khách hàng là đối tượng sử dụng hệ thống để xem và mua sản phẩm.

Khách hàng có thể:

- Đăng ký tài khoản
- Đăng nhập hệ thống
- Xem sản phẩm
- Xem chi tiết sản phẩm
- Tìm kiếm sản phẩm
- Thêm sản phẩm vào giỏ hàng
- Thanh toán đơn hàng

✅ **Khách hàng có thể được thêm bởi nhiều đối tượng trong hệ thống**, bao gồm:

- Admin thêm khách hàng
- Nhân viên thêm khách hàng
- Khách hàng tự đăng ký tài khoản

---

## 🧩 Chức năng chính của hệ thống

| STT | Chức năng | Mô tả |
|-----|-----------|------|
| 1 | Trang chủ | Hiển thị danh sách sản phẩm nổi bật, sản phẩm mới |
| 2 | Chi tiết sản phẩm | Xem thông tin chi tiết của từng sản phẩm |
| 3 | Tìm kiếm sản phẩm | Tìm kiếm theo tên hoặc từ khóa |
| 4 | Giỏ hàng | Thêm, sửa, xóa sản phẩm trong giỏ hàng |
| 5 | Thanh toán | Xử lý đặt hàng và lập hóa đơn |
| 6 | Đăng ký khách hàng | Tạo tài khoản khách hàng mới |
| 7 | Đăng nhập / đăng xuất | Xác thực người dùng |
| 8 | Quản lý sản phẩm | Quản lý danh mục sản phẩm |
| 9 | Quản lý khách hàng | Lưu trữ và chỉnh sửa thông tin khách hàng |
| 10 | Quản lý hóa đơn | Theo dõi thông tin thanh toán, đơn hàng |
| 11 | Quản lý bảo hành | Quản lý thông tin kích hoạt và thời hạn bảo hành |
| 12 | Quản lý nhân viên | **Chỉ Admin được quyền xem và tạo** |

---

## 🗂️ Cấu trúc mã nguồn chính

- `admin/` : Khu vực quản trị hệ thống
- `uploads/` : Lưu hình ảnh và dữ liệu tải lên
- `filexuathoadonchitiet/` : Lưu file hóa đơn chi tiết
- `filexuatphieubaohanh/` : Lưu file phiếu bảo hành
- `connect.php` : File kết nối cơ sở dữ liệu
- `trang_chu.php` : Trang chủ hệ thống
- `gio_hang.php` : Quản lý giỏ hàng
- `thanh_toan.php` : Xử lý thanh toán
- `dang_ky.php` : Đăng ký tài khoản
- `login.php`, `login_khach.php` : Đăng nhập hệ thống
- `quanlycuahangtivi.sql`, `CSDL_DoAnWeb.sql` : File cơ sở dữ liệu

---

## 🚀 Hướng dẫn cài đặt và chạy dự án

### **Bước 1: Clone source code từ GitHub**
Mở Terminal hoặc Command Prompt và chạy lệnh:
Bước 2: Copy project vào thư mục htdocs của XAMPP

Di chuyển thư mục project vừa clone vào thư mục htdocs trong XAMPP.

Ví dụ:

C:\xampp\htdocs\DoAnLapTrinhWeb_QuanLyCuaHangTV
Bước 3: Khởi động XAMPP

Mở XAMPP Control Panel, sau đó bật:

Apache
MySQL

Nếu MySQL bị trùng cổng thì cần kiểm tra lại cổng kết nối đang sử dụng là:

3306 đối với sinh viên Thái Vĩnh Nghi
3307 đối với sinh viên Nguyễn Hoàng Uy
Bước 4: Tạo và import cơ sở dữ liệu

Thực hiện các bước sau:

Mở trình duyệt và vào phpMyAdmin
Tạo một database mới
Chọn tab Import
Import file cơ sở dữ liệu có trong project

Ví dụ file import:

quanlycuahangtivi.sql
CSDL_DoAnWeb.sql

Sau khi import xong, hệ thống sẽ có đầy đủ bảng dữ liệu để chạy.
Bước 5: Cấu hình kết nối và chạy project

Mở file connect.php và chỉnh lại thông tin cho phù hợp với máy đang sử dụng:

Host: localhost hoặc 127.0.0.1
User: root
Password: để trống hoặc mật khẩu MySQL đang dùng
Tên database: tên database vừa import
Port:
3306 với sinh viên Thái Vĩnh Nghi
3307 với sinh viên Nguyễn Hoàng Uy
🧪 Tài khoản và quyền sử dụng
Vai trò	Quyền chính
Admin	Toàn quyền hệ thống, xem và tạo nhân viên
Nhân viên	Thực hiện nghiệp vụ bán hàng, không được xem và tạo nhân viên
Khách hàng	Đăng ký, đăng nhập, mua hàng, thanh toán
📘 Mô hình hoạt động nghiệp vụ

Hệ thống được xây dựng theo logic:

Admin là người quản trị cao nhất
Nhân viên thực hiện nghiệp vụ bán hàng và hỗ trợ khách hàng
Khách hàng là người trực tiếp mua sản phẩm trên hệ thống
Thông tin nhân viên là dữ liệu quản trị nội bộ nên chỉ Admin được xem và tạo
Thông tin khách hàng linh hoạt hơn nên có thể được thêm bởi nhiều đối tượng trong hệ thống

Cách phân quyền này giúp hệ thống hợp lý hơn về mặt nghiệp vụ và phù hợp với yêu cầu đồ án.

📌 Ghi chú quan trọng
Dự án sử dụng PHP thuần kết hợp MySQL
Source code được quản lý bằng GitHub
Môi trường chạy thử là XAMPP
Khi đổi máy hoặc đổi thành viên sử dụng, cần kiểm tra lại cổng MySQL
Cần import đúng file cơ sở dữ liệu trước khi chạy hệ thống
🎯 Kết luận

Đồ án giúp nhóm vận dụng các kiến thức đã học như:

Phân tích và thiết kế hệ thống web
Lập trình PHP
Kết nối và xử lý dữ liệu với MySQL
Xây dựng chức năng đăng nhập, giỏ hàng, thanh toán, quản trị
Quản lý source code bằng GitHub
Làm việc nhóm trong quá trình phát triển phần mềm

Dự án là sản phẩm phục vụ học tập cho môn Lập trình Web, đồng thời là cơ hội để nhóm rèn luyện kỹ năng xây dựng một hệ thống quản lý tương đối hoàn chỉnh.
📎 Thông tin repository

Repository GitHub dùng để lưu trữ toàn bộ mã nguồn, dữ liệu và quá trình phát triển của đồ án.

Tên repository: DoAnLapTrinhWeb_QuanLyCuaHangTV

```bash
git clone <link-repository>
