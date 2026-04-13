# N&U STORE - HƯỚNG DẪN SỬ DỤNG TOÀN DIỆN

**Cửa hàng bán TV & Điện tử trực tuyến**  
*Hệ thống quản lý bán hàng, trả góp, bảo hành tích hợp*

---

## 📋 MỤC LỤC

1. [Tổng Quan](#tổng-quan)
2. [Cài Đặt & Setup](#cài-đặt--setup)
3. [Thông Tin Đăng Nhập](#thông-tin-đăng-nhập-mặc-định)
4. [Hướng Dẫn Sử Dụng Khách Hàng](#hướng-dẫn-sử-dụng-khách-hàng)
5. [Hướng Dẫn Sử Dụng Admin](#hướng-dẫn-sử-dụng-admin)
6. [Cấu Trúc Dự Án](#cấu-trúc-dự-án)
7. [Tính Năng Chính](#tính-năng-chính)
8. [Khắc Phục Sự Cố](#khắc-phục-sự-cố)

---

## 📱 TỔNG QUAN

### Chức Năng Chính

**Cho Khách Hàng:**
- 🛍️ Duyệt và tìm kiếm sản phẩm
- 🛒 Quản lý giỏ hàng
- 💳 Thanh toán 100% hoặc trả góp
- 📦 Theo dõi hóa đơn
- ✅ Kiểm tra bảo hành
- ⭐ Đánh giá sản phẩm

**Cho Quản Trị Viên (Admin):**
- 👥 Quản lý khách hàng & nhân viên
- 📊 Quản lý sản phẩm + kho hàng
- 📋 Quản lý hóa đơn & chi tiết bán
- 📥 Quản lý nhập hàng
- 🏢 Quản lý nhà cung cấp & hãng sản xuất
- ✔️ Duyệt đơn trả góp
- 📅 Theo dõi thanh toán trả góp
- 🔖 Quản lý mã khuyến mãi
- 📝 Xem nhật ký hệ thống
- 🛠️ Quản lý bảo hành

### Công Nghệ Sử Dụng

- **Backend:** PHP 7+ với MySQLi (Prepared Statements)
- **Database:** MySQL (MariaDB 10.4+)
- **Frontend:** Bootstrap 5.3 + Vanilla JavaScript
- **Security:** BCRYPT password hashing, SQL injection prevention
- **Session:** 30 phút timeout tự động
- **Rate Limiting:** Anti-brute-force (5 lần / 15 phút)

---

## 🚀 CÀI ĐẶT & SETUP

### Yêu Cầu Hệ Thống

- XAMPP 7.4+ (hoặc PHP 7.4+)
- MySQL 5.7+ / MariaDB 10.4+
- Modern Browser (Chrome, Firefox, Edge)

### Bước 1: Chuẩn Bị Database

```bash
# Trong phpMyAdmin hoặc MySQL CLI:
# 1. Tạo database
CREATE DATABASE quanlycuahangtivi CHARACTER SET utf8mb4;

# 2. Import file SQL
# File: quanlycuahangtivi.sql
```

**Hoặc sử dụng phpMyAdmin:**
1. Mở `http://localhost/phpmyadmin`
2. Tạo database mới: `quanlycuahangtivi`
3. Chọn database → Import → Chọn file `quanlycuahangtivi.sql`

### Bước 2: Hash Password Cũ (Lần Đầu)

```bash
# Chạy script migration:
# Truy cập: http://localhost/xampp/htdocs/MIGRATION_HASH_PASSWORD.php
```

**Hoặc chạy lệnh SQL tại phpMyAdmin:**
```sql
-- Thêm cột MatKhauHash nếu chưa có
ALTER TABLE khachhang ADD COLUMN MatKhauHash VARCHAR(255) NULL;
ALTER TABLE nhanvien ADD COLUMN MatKhauHash VARCHAR(255) NULL;

-- Script migration sẽ hash tất cả password cũ
```

### Bước 3: Cấu Hình Kết Nối Database

**File:** `thu_vien/connect.php`

```php
// Kiểm tra cấu hình:
$servername = "localhost";
$username = "root";
$password = "";  // XAMPP mặc định trống
$dbname = "quanlycuahangtivi";
```

### Bước 4: Khởi Động

```bash
# 1. Bật XAMPP Control Panel
# 2. Khởi động Apache + MySQL
# 3. Truy cập:
#    - Homepage: http://localhost/xampp/htdocs
#    - Admin: http://localhost/xampp/htdocs/admin/index.php
```

---

## 🔑 THÔNG TIN ĐĂNG NHẬP MẶC ĐỊNH

### ADMIN ACCOUNT

| Tài Khoản | Mật Khẩu | Vai Trò | Ghi Chú |
|-----------|---------|--------|--------|
| `Bo` | `123` | Admin | Quản trị viên cấp cao |
| `Vu` | `123` | Nhân viên | Quầy bán hàng |
| `Thuan` | `123` | Nhân viên | Quầy bán hàng |
| `phatle` | `123` | Nhân viên | Quầy bán hàng |
| `namdang` | `123` | Nhân viên | Quầy bán hàng |
| `kyly` | `123` | Admin | Quản trị viên |

### KHÁCH HÀNG DEMO

| Tài Khoản | Mật Khẩu | Họ Tên | Ghi Chú |
|-----------|---------|--------|--------|
| `Bo` | `123` | Thái Vĩnh Nghi | Tài khoản test |
| Hoặc đăng ký tài khoản mới | - | - | Chọn "Đăng Ký" |

### ⚠️ Khuyến Nghị Bảo Mật

1. **Đổi mật khẩu ngay lập tức** sau khi đăng nhập lần đầu
2. Sử dụng mật khẩu **tối thiểu 8 ký tự** (số + chữ + ký tự đặc biệt)
3. Không dùng mật khẩu giống username
4. Định kỳ cập nhật mật khẩu (30 ngày/lần)

---

## 👥 HƯỚNG DẪN SỬ DỤNG KHÁCH HÀNG

### 1. ĐĂNG KÝ TÀI KHOẢN

**URL:** `http://localhost/xampp/htdocs/dang_ky.php`

**Các bước:**
1. Nhập thông tin cá nhân:
   - **Họ và tên:** Đầy đủ (VD: Nguyễn Văn A)
   - **Điện thoại:** 10 số bắt đầu từ 0 (VD: 0987654321)
   - **Địa chỉ:** Chi tiết địa chỉ giao hàng
   - **Tên đăng nhập:** 6-50 ký tự, chỉ chữ/số/_
   - **Mật khẩu:** Tối thiểu 6 ký tự

2. Nhấn "Đăng Ký" → Chuyển hướng tới trang chủ
3. Đăng nhập lại bằng tài khoản vừa tạo

### 2. DUYỆT & TÌM KIẾM SẢN PHẨM

**URL:** `http://localhost/xampp/htdocs/san_pham.php`

**Tính năng:**
- 📋 Xem danh sách 20 loại TV
- 🔍 Tìm kiếm theo tên sản phẩm
- 💰 Lọc theo giá (từ → đến)
- 📊 Lọc theo hãng (Sony, Samsung, LG, v.v.)

**Thông tin sản phẩm:**
- Tên + hình ảnh
- Giá bán (VND)
- % giảm giá (nếu có)
- Số lượng còn lại
- Mô tả chi tiết

### 3. THÊM VÀO GIỎ HÀNG

**Từ trang chi tiết sản phẩm:**

1. Nhấn "Chi Tiết" trên sản phẩm
2. Chọn **số lượng** (không vượt quá tồn kho)
3. Nhấn **"Thêm Vào Giỏ"**
4. Xác nhận → Giỏ hàng cập nhật

**Hạn chế:**
- ❌ Không được đặt hơn số lượng có sẵn
- ❌ Nếu hết hàng, nút sẽ bị vô hiệu hóa

### 4. QUẢN LÝ GIỎ HÀNG

**URL:** `http://localhost/xampp/htdocs/gio_hang.php`

**Tính năng:**
- 👁️ Xem danh sách sản phẩm trong giỏ
- 🔢 Thay đổi số lượng
- ❌ Xóa sản phẩm khỏi giỏ
- 💵 Tính tổng tiền tự động
- 💳 Tiến hành thanh toán

### 5. THANH TOÁN

**URL:** `http://localhost/xampp/htdocs/thanh_toan.php`

#### Option A: THANH TOÁN 100%

1. Quy trình:
   - Xem lại chi tiết đơn hang
   - Điền địa chỉ giao hàng
   - Chọn "Thanh toán toàn bộ"
   - Xác nhận → Hóa đơn được tạo

2. Hóa đơn sẽ được gửi tới email + lưu lại trong tài khoản

#### Option B: TRẢ GÓP (Vay tiêu dùng)

**Bước 1: Điền Thông Tin**
- Tiền trả trước (≥ 20% giá trị)
- Số tháng trả góp (6/9/12 tháng)
- Lãi suất cho vay từ hệ thống

**Bước 2: Tính Toán Tự Động**
```
Số tiền còn lại = Tổng tiền - Trả trước
Tổng phải trả = Số tiền còn lại + (Số tiền còn lại × Lãi suất / 100 / 12 × Số tháng)
Tiền/tháng = Tổng phải trả / Số tháng
```

**Bước 3: Gửi Đơn**
- Nhấn "Gửi đơn trả góp"
- Trạng thái: "Chờ duyệt" (Admin kiểm tra)
- Thông báo qua email

**Bước 4: Chờ Duyệt**
- Admin sẽ duyệt trong 24h
- SMS/email thông báo kết quả

**Bước 5: Thanh Toán Kỳ Hạn**
- Sau khi duyệt → Thực hiện thanh toán kỳ 1
- Ký hạn tiếp theo được tạo tự động
- Sổ góp được lưu trong "Tài khoản" → "Lịch sử trả góp"

### 6. KIỂM TRA HÓA ĐƠN & LỊCH SỬ

**URL:** `http://localhost/xampp/htdocs` (nhấn tên user)

**Thông tin:**
- 📋 Danh sách hóa đơn đã lập
- 📅 Chi tiết từng hóa đơn
- 🗂️ Tải lại hóa đơn (PDF)
- 🔄 Lịch sử trả góp (nếu có)

### 7. KIỂM TRA BẢO HÀNH

**URL:** `http://localhost/xampp/htdocs/xuat_bao_hanh.php` (Admin)

**Khách hàng kiểm tra:**
1. Vào "Tài khoản" → "Hóa đơn"
2. Tìm hóa đơn có sản phẩm
3. Nhấn "Xuất Bảo Hành" → Xem thông tin

**Thông tin bảo hành:**
- 🆔 Mã serial
- 📅 Ngày hết hạn
- ✅ Trạng thái ("Đang bảo hành" / "Hết hạn")

### 8. ĐÁNH GIÁ SẢN PHẨM

**URL:** `http://localhost/xampp/htdocs/danh_gia.php`

**Cách thức:**
1. Chọn sản phẩm
2. Nhập bình luận (tùy chọn)
3. Chọn xếp hạng (1-5 ⭐)
4. Nhấn "Gửi Đánh Giá"

**Lưu ý:**
- ❌ Không được đánh giá hai lần cùng sản phẩm
- ✅ Đánh giá của bạn sẽ hiện trên trang sản phẩm

---

## 🛠️ HƯỚNG DẪN SỬ DỤNG ADMIN

### ĐĂNG NHẬP

**URL:** `http://localhost/xampp/htdocs/admin/index.php`

```
Tài khoản: Bo
Mật khẩu: 123
(hoặc kyly / 123)
```

### DASHBOARD

**Trang chính:** Tổng quan hệ thống
- 📊 Thống kê bán hàng
- 👥 Số khách hàng đang dùng
- 📦 Sản phẩm tồn kho
- 💰 Doanh thu tháng

### 1. QUẢN LÝ SẢN PHẨM

**Menu:** Admin → Sản Phẩm

#### 1.1 Xem Danh Sách

- 📋 Liệt kê tất cả 20 sản phẩm
- 🔍 Tìm kiếm theo tên
- 📊 Xem giá, số lượng, hãng
- ⚙️ Nút Sửa / Xóa

#### 1.2 Thêm Sản Phẩm Mới

**Các trường:**
- **Tên sản phẩm** (VD: Samsung 65 inch QLED)
- **Loại sản phẩm** (VD: TV QLED)
- **Hãng** (VD: Samsung)
- **Giá bán** (VND)
- **Số lượng tồn kho**
- **% giảm giá** (0-100)
- **Mô tả chi tiết**
- **Hình ảnh** (Upload JPG)

**Lưu ý:**
- Tên phải rõ ràng, đầy đủ specs
- Giá phải > 0
- Số lượng khởi tạo (được cập nhật khi nhập)

#### 1.3 Sửa Sản Phẩm

1. Nhấn "Sửa" trên sản phẩm
2. Thay đổi thông tin cần thiết
3. Nhấn "Lưu"

**Có thể sửa:**
- Giá bán
- Số lượng (nếu cần điều chỉnh)
- % giảm giá
- Mô tả
- Hình ảnh

#### 1.4 Xóa Sản Phẩm

❌ **Lưu ý:** Chỉ xóa nếu:
- Chưa từng bán (không có hóa đơn)
- Không còn tồn kho
- Không phục vụ khách hàng

### 2. QUẢN LÝ HÓA ĐƠN & CHI TIẾT BÁN

**Menu:** Admin → Hóa Đơn

#### 2.1 Xem Danh Sách Hóa Đơn

- 📋 Liệt kê hóa đơn (ngày mới nhất trước)
- 👤 Tên khách hàng
- 💰 Tổng tiền
- 📅 Ngày lập

#### 2.2 Xem Chi Tiết Hóa Đơn

1. Nhấn ID hóa đơn
2. Xem chi tiết:
   - Danh sách sản phẩm × số lượng × giá
   - Tổng tiền
   - Thông tin khách
   - Ghi chú đặc biệt

#### 2.3 In / Xuất Hóa Đơn

**Nút "Xuất Phiếu":**
- 📄 Tạo file PDF hóa đơn
- 🖨️ Có thể in trực tiếp
- 💾 Lưu phòng kiểm toán

### 3. QUẢN LÝ TRẢ GÓP

**Menu:** Admin → Trả Góp → Đơn Đăng Ký

#### 3.1 Danh Sách Đơn Chờ Duyệt

**Bộ lọc:**
- 🔔 "Chờ duyệt" (mặc định)
- ✅ "Đã duyệt"
- ❌ "Từ chối"

**Thông tin đơn:**
- 👤 Khách hàng
- 💵 Giá trị hóa đơn
- 💸 Tiền trả trước
- 📅 Kỳ hạn (tháng)
- % Lãi suất / tháng

#### 3.2 Duyệt Đơn Trả Góp

**Bước:**
1. Nhấn ID đơn
2. Xem chi tiết:
   - Thông tin khách hàng
   - Sản phẩm + giá
   - Tính toán góp tự động (✓)
3. **Nút "Duyệt"** → Trạng thái = "Đã duyệt"
4. **Nút "Từ Chối"** → Trạng thái = "Từ chối"

**Ghi chú duyệt:**
- Kiểm tra giấy tờ tùy thân khách
- Xác nhận điều kiện thanh toán
- Nếu từ chối → thêm lý do

#### 3.3 Quản Lý Thanh Toán

**Menu:** Trả Góp → Thanh Toán

**Thông tin:**
- 📋 Danh sách đơn góp "Đã duyệt"
- 💰 Số tiền / tháng
- 📅 Lịch thanh toán đến hạn
- ⏰ Nhắc nợ (nếu quá hạn)

**Tác động:**
1. Khách thanh toán → Nhân viên ghi nhận
2. Nhấn "Nhập Thanh Toán"
3. Chọn kỳ thanh toán
4. Nhập số tiền
5. Hệ thống cập nhật tự động

### 4. QUẢN LÝ NHẬP HÀNG

**Menu:** Admin → Nhập Hàng

#### 4.1 Tạo Phiếu Nhập

**Bước:**
1. Nhấn "Thêm Phiếu Nhập"
2. Chọn **nhà cung cấp** (VD: Samsung, Sony)
3. Chọn **ngày nhập**
4. **Thêm sản phẩm:**
   - Chọn sản phẩm
   - Nhập số lượng
   - Nhập giá nhập (khác với giá bán)
5. Nhấn "Lưu Phiếu Nhập"

**Kết quả:**
- ✅ Phiếu được tạo
- ✅ Tồn kho tự động cập nhật (+số lượng)

#### 4.2 Xem Lịch Sử Nhập

- 📋 Danh sách nhập từ các nhà cung cấp
- 🏢 Tên nhà cung cấp
- 💰 Tổng tiền nhập
- 📅 Ngày nhập

### 5. QUẢN LÝ BẢO HÀNH

**Menu:** Admin → Bảo Hành

#### 5.1 Danh Sách Bảo Hành

- 🆔 Mã sản phẩm / serial
- 📅 Ngày hết hạn
- 📊 Trạng thái ("Còn BH" / "Hết hạn")

#### 5.2 Xuất Phiếu Bảo Hành

1. Tìm theo: Hóa đơn ID / Serial / Sản phẩm
2. Nhấn "Xuất Phiếu"
3. PDF được tạo → In hoặc gửi khách

#### 5.3 Gia Hạn Bảo Hành

1. Chọn bản ghi bảo hành
2. Nhập số tháng gia hạn
3. Nhấn "Cập Nhật Ngày Hết Hạn"

### 6. QUẢN LÝ KHÁCH HÀNG & NHÂN VIÊN

**Menu:** Admin → Khách Hàng / Nhân Viên

#### 6.1 Xem Danh Sách

- 👤 Tên
- 📞 Điện thoại
- 📍 Địa chỉ
- 🔐 Tên đăng nhập
- ⚙️ Nút Sửa / Xóa

#### 6.2 Thêm Nhân Viên Mới

**Fields:**
- Họ tên
- Điện thoại
- Địa chỉ
- Tên đăng nhập
- Mật khẩu (khuyến nghị: 12 ký tự ngẫu nhiên)
- Quyền hạn (0=Nhân viên, 1=Admin)

#### 6.3 Sửa Thông Tin Nhân Viên

- Cập nhật thông tin cá nhân
- 🔄 Đặt lại mật khẩu (nếu quên)
- Thay đổi quyền hạn

#### 6.4 Khóa / Xóa Tài Khoản

⚠️ Chỉ xóa nếu:
- Nhân viên đã nghỉ việc
- Khách hàng yêu cầu
- Không có giao dịch liên quan

### 7. QUẢN LÝ LIÊN HỆ HỆ THỐNG

**Menu:** Admin → Nhật Ký Hệ Thống

**Thông tin:**
- 👤 Người dùng + tài khoản
- 🎯 Hành động (Đăng nhập, Đặt hàng, Duyệt góp...)
- 📅 Thời gian
- 🌐 IP address

**Dùng để:**
- 🔍 Kiểm tra hoạt động đáng ngờ
- 📊 Thống kê khách hàng tích cực
- 🛡️ Theo dõi bảo mật

---

## 📁 CẤU TRÚC DỰ ÁN

```
e:\xampp\htdocs\
├── admin/                       [Giao diện Quản trị]
│   ├── index.php               [Dashboard]
│   ├── san_pham.php            [QL Sản phẩm]
│   ├── hoa_don.php             [QL Hóa đơn]
│   ├── khach_hang.php          [QL Khách hàng]
│   ├── nhan_vien.php           [QL Nhân viên]
│   ├── tra_gop.php             [QL Trả góp]
│   ├── bao_hanh.php            [QL Bảo hành]
│   ├── nhap_hang.php           [QL Nhập hàng]
│   ├── nhatky_hethong.php      [Nhật ký]
│   └── header.php, footer.php, sidebar.php [Layout]
│
├── thu_vien/                    [Thư viện & Hỗ trợ]
│   ├── connect.php             [Kết nối DB]
│   ├── nhatky_helper.php       [Hàm ghi nhật ký]
│   ├── rate_limit_helper.php   [Chống brute-force]
│   └── [Các helper khác]
│
├── tai_nguyen/                  [Tài nguyên Tĩnh]
│   ├── css/
│   │   └── style.css           [Stylesheet chính]
│   └── js/
│       └── script.js           [JavaScript chung]
│
├── uploads/                     [Ảnh Sản Phẩm Upload]
│   └── [sanpham_*.jpg]
│
├── [Trang Front-End]
│   ├── trang_chu.php           [Trang chủ]
│   ├── san_pham.php            [Danh sách sản phẩm]
│   ├── chi_tiet_san_pham.php   [Chi tiết sản phẩm]
│   ├── gio_hang.php            [Giỏ hàng]
│   ├── thanh_toan.php          [Thanh toán / Trả góp]
│   ├── dang_ky.php             [Đăng ký]
│   ├── login_khach.php         [Đăng nhập khách]
│   ├── logout_khach.php        [Đăng xuất]
│   ├── danh_gia.php            [Đánh giá]
│   ├── doc_tin.php             [Bài viết]
│   ├── xuat_hoa_don.php        [Xuất hóa đơn]
│   └── ...
│
├── database_structure.txt       [Mô tả DB]
├── MIGRATION_HASH_PASSWORD.php  [Script hash password]
└── quanlycuahangtivi.sql       [File SQL import]
```

---

## ✨ TÍNH NĂNG CHÍNH

### 🔐 BẢNG BẢO MẬT

| Tính Năng | Chi Tiết | Mục Đích |
|-----------|---------|---------|
| **BCRYPT Hashing** | password_hash() / password_verify() | Mã hóa mật khẩu an toàn |
| **Prepared Statements** | MySQLi bind_param() | Ngăn SQL Injection |
| **Session Timeout** | 30 phút tự logout | Bảo vệ máy công cộng |
| **Rate Limiting** | Max 5 lần / 15 phút → Khóa 30 phút | Chống Brute-force |
| **Input Validation** | Phone: 0xxxxxxxxx, Password ≥ 6 ký tự | Validate dữ liệu |
| **Nhật Ký Hệ Thống** | Ghi log tất cả hành động | Audit & Phát hiện lạm dụng |

### 💰 TÍNH NĂNG THANH TOÁN

| Loại | Mô Tả |
|------|-------|
| **Thanh toán 100%** | Toàn bộ tiền ngay lập tức |
| **Trả góp** | Phân chia thành 6-12 kỳ |
| **Tính lãi suất** | Công thức: Lãi = Gốc × Lãi/100 / 12 × Kỳ |
| **Nhắc nợ** | Tự động nhắc nếu quá hạn |
| **Sổ góp** | Lịch sử thanh toán chi tiết |

### 📊 TÍNH NĂNG BÁO CÁO

- 📈 Doanh thu tháng / quý / năm
- 👥 Số khách hàng mới
- 📦 Sản phẩm bán chạy nhất
- 💸 Nợ góp thời hạn
- ⏰ Bảo hành sắp hết hạn
- 🛍️ Khách VIP (mua nhiều)

---

## 🐛 KHẮC PHỤC SỰ CỐ

### ❌ Không Thể Đăng Nhập

**Nguyên nhân:**
- Sai tên đăng nhập / mật khẩu
- Tài khoản bị khóa (quá 5 lần sai)

**Giải pháp:**
1. Kiểm tra tên tài khoản (hoàn toàn chính xác)
2. Nếu quên mật khẩu → Liên hệ Admin để reset
3. Chờ 30 phút nếu tài khoản bị khóa
4. Xóa cookie browser → F5 refresh

### ❌ Lỗi "Database Connection Failed"

**Nguyên nhân:**
- MySQL chưa khởi động
- Cấu hình connect.php sai

**Giải pháp:**
1. Kiểm tra XAMPP: Apache + MySQL bật ✓
2. Kiểm tra file `thu_vien/connect.php`
   ```php
   $servername = "localhost";
   $username = "root";
   $password = "";
   $dbname = "quanlycuahangtivi";
   ```
3. Kiểm tra Database có tồn tại: `quanlycuahangtivi` ✓

### ❌ Lỗi "Session Expired"

**Nguyên nhân:**
- Phiên làm việc vượt quá 30 phút

**Giải pháp:**
1. Đăng xuất → Đăng nhập lại
2. Lưu dữ liệu trước khi session hết

### ❌ Lỗi "Image Not Found"

**Nguyên nhân:**
- File ảnh không trong `/uploads/`
- Đường dẫn sai trong database

**Giải pháp:**
1. Kiểm tra file tại `/uploads/sanpham_*.jpg`
2. Upload ảnh bằng Admin → Thêm sản phẩm
3. Kiểm tra tên file hoàn toàn chính xác

### ❌ Không Thể Thêm Vào Giỏ Hàng

**Nguyên nhân:**
- Sản phẩm hết hàng (SoLuong = 0)
- Số lượng vượt quá tồn kho

**Giải pháp:**
1. Kiểm tra số lượng tồn kho
2. Chọn số lượng ≤ số tồn kho
3. Admin cần nhập hàng

### ❌ Lỗi Thanh Toán Trả Góp

**Nguyên nhân:**
- Tiền trả trước < 20% giá trị
- Kỳ hạn ngoài phạm vi 6-12 tháng

**Giải pháp:**
1. Tăng tiền trả trước
2. Chọn kỳ hạn 6, 9, hoặc 12 tháng
3. Kiểm tra lãi suất hợp lệ

### ❌ Không Tìm Thấy Hóa Đơn

**Nguyên nhân:**
- Hóa đơn chưa được tạo
- Lọc theo ngày sai

**Giải pháp:**
1. Đảm bảo đã thanh toán xong
2. Mở "Tài khoản" → "Hóa đơn"
3. Kiểm tra ngày lập hóa đơn
4. Liên hệ Admin để tìm

---

## 📞 HỖ TRỢ & LIÊN HỆ

### Thông Tin Kỹ Thuật
- **Dự án:** N&U Store (Quản lý bán TV)
- **Phiên bản:** 2.0 (2026)
- **Language:** PHP + MySQL + Bootstrap

### Liên Hệ
- 📧 Email: admin@nustore.local
- 📱 Hotline: 0931-082-845 (Thái Vĩnh Nghi)
- 🏢 Địa chỉ: Long Xuyên, An Giang

### Báo Cáo Lỗi
1. Ghi lại thời gian & các bước tái hiện
2. Chụp ảnh lỗi
3. Liên hệ Admin với thông tin chi tiết

---

## 📝 GHI CHÚ QUAN TRỌNG

✅ **Dự án đã được test và fix:**
- 15 lỗi bảo mật & logic đã khắc phục
- Rate limiting, password hashing, SQL injection protection
- Session timeout, real-time form validation

⚠️ **Trước khi production:**
1. Chạy MIGRATION_HASH_PASSWORD.php
2. Đổi admin password mặc định
3. Backup database định kỳ
4. Test toàn bộ quy trình bán hàng / trả góp

📅 **Cập nhật lần cuối:** 13/04/2026


