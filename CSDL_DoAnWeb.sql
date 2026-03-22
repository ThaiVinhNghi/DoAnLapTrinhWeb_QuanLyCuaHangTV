CREATE DATABASE IF NOT EXISTS QuanLyCuaHangTivi;
USE QuanLyCuaHangTivi;

-- =========================================================================
-- PHẦN 0: TẮT KIỂM TRA KHÓA NGOẠI
-- =========================================================================
SET FOREIGN_KEY_CHECKS = 0;

-- =========================================================================
-- PHẦN 1: TẠO CẤU TRÚC CÁC BẢNG (BẮT BUỘC PHẢI CÓ)
-- =========================================================================

CREATE TABLE IF NOT EXISTS NhanVien (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    HoVaTen VARCHAR(255),
    DienThoai VARCHAR(20),
    DiaChi VARCHAR(255),
    TenDangNhap VARCHAR(50),
    MatKhau VARCHAR(50),
    QuyenHan INT
);

CREATE TABLE IF NOT EXISTS KhachHang (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    HoVaTen VARCHAR(255),
    DienThoai VARCHAR(20),
    DiaChi VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS NhaCungCap (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    TenNhaCungCap VARCHAR(255),
    DienThoai VARCHAR(20),
    Email VARCHAR(100),
    DiaChi VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS HangSanXuat (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    TenHangSanXuat VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS LoaiSanPham (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    TenLoai VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS SanPham (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    LoaiSanPhamID INT,
    HangSanXuatID INT,
    TenSanPham VARCHAR(255),
    DonGia DECIMAL(15,2),
    SoLuong INT,
    HinhAnh VARCHAR(255),
    MoTa TEXT,
    FOREIGN KEY (LoaiSanPhamID) REFERENCES LoaiSanPham(ID),
    FOREIGN KEY (HangSanXuatID) REFERENCES HangSanXuat(ID)
);

CREATE TABLE IF NOT EXISTS HoaDon (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    NhanVienID INT,
    KhachHangID INT,
    NgayLap DATETIME,
    GhiChuHoaDon TEXT,
    FOREIGN KEY (NhanVienID) REFERENCES NhanVien(ID),
    FOREIGN KEY (KhachHangID) REFERENCES KhachHang(ID)
);

CREATE TABLE IF NOT EXISTS HoaDon_ChiTiet (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    HoaDonID INT,
    SanPhamID INT,
    SoLuongBan INT,
    DonGiaBan DECIMAL(15,2),
    FOREIGN KEY (HoaDonID) REFERENCES HoaDon(ID),
    FOREIGN KEY (SanPhamID) REFERENCES SanPham(ID)
);

CREATE TABLE IF NOT EXISTS PhieuNhap (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    NhanVienID INT,
    NhaCungCapID INT,
    NgayNhap DATETIME,
    TongTien DECIMAL(15,2),
    GhiChu TEXT,
    FOREIGN KEY (NhanVienID) REFERENCES NhanVien(ID),
    FOREIGN KEY (NhaCungCapID) REFERENCES NhaCungCap(ID)
);


CREATE TABLE IF NOT EXISTS PhieuNhap_ChiTiet (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    PhieuNhapID INT,
    SanPhamID INT,
    SoLuongNhap INT,
    DonGiaNhap DECIMAL(15,2),
    FOREIGN KEY (PhieuNhapID) REFERENCES PhieuNhap(ID),
    FOREIGN KEY (SanPhamID) REFERENCES SanPham(ID)
);

CREATE TABLE IF NOT EXISTS BaoHanh (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    HoaDonID INT,
    SanPhamID INT,
    SoSerial VARCHAR(100),
    NgayKichHoat DATE,
    NgayHetHan DATE,
    TrangThai VARCHAR(50),
    FOREIGN KEY (HoaDonID) REFERENCES HoaDon(ID),
    FOREIGN KEY (SanPhamID) REFERENCES SanPham(ID)
);

-- =========================================================================
-- PHẦN 2: LÀM SẠCH DỮ LIỆU CŨ & RESET ID
-- =========================================================================

SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM BaoHanh;
DELETE FROM HoaDon_ChiTiet;
DELETE FROM HoaDon;
DELETE FROM PhieuNhap_ChiTiet;
DELETE FROM PhieuNhap;
DELETE FROM SanPham;
DELETE FROM LoaiSanPham;
DELETE FROM HangSanXuat;
DELETE FROM NhaCungCap;
DELETE FROM KhachHang;
DELETE FROM NhanVien;

ALTER TABLE BaoHanh AUTO_INCREMENT = 1;
ALTER TABLE HoaDon_ChiTiet AUTO_INCREMENT = 1;
ALTER TABLE HoaDon AUTO_INCREMENT = 1;
ALTER TABLE PhieuNhap_ChiTiet AUTO_INCREMENT = 1;
ALTER TABLE PhieuNhap AUTO_INCREMENT = 1;
ALTER TABLE SanPham AUTO_INCREMENT = 1;
ALTER TABLE LoaiSanPham AUTO_INCREMENT = 1;
ALTER TABLE HangSanXuat AUTO_INCREMENT = 1;
ALTER TABLE NhaCungCap AUTO_INCREMENT = 1;
ALTER TABLE KhachHang AUTO_INCREMENT = 1;
ALTER TABLE NhanVien AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================================
-- PHẦN 3: THÊM DỮ LIỆU (INSERT)
-- =========================================================================

INSERT INTO NhanVien (ID, HoVaTen, DienThoai, DiaChi, TenDangNhap, MatKhau, QuyenHan) VALUES 
(1, 'Thái Vĩnh Nghi', '0931082845', 'Long Xuyên, An Giang', 'Bo', '123', 1),
(2, 'Phó Bảo Phong', '0912345678', 'Long Xuyên, An Giang', 'Vu', '123', 0),
(3, 'Huỳnh Minh Thuận', '0923456789', 'Long Xuyên, An Giang', 'Thuan', '123', 0),
(4, 'Lê Tấn Phát', '0934567890', 'Ninh Kiều, TP. Cần Thơ', 'phatle', '123', 0),
(5, 'Đặng Phương Nam', '0945678901', 'Gò Vấp, TP. Hồ Chí Minh', 'namdang', '123', 0),
(6, 'Lý Nhã Kỳ', '0956789012', 'Bình Thạnh, TP. Hồ Chí Minh', 'kyly', '123', 1);

INSERT INTO KhachHang (ID, HoVaTen, DienThoai, DiaChi) VALUES 
(1, 'Lê Thị Kim Yến', '0987654321', 'TP. Thủ Đức, TP. Hồ Chí Minh'),
(2, 'Nguyễn Huỳnh Minh Trí', '0976543210', 'Dĩ An, Bình Dương'),
(3, 'Phạm Thị Mai', '0965432109', 'Biên Hòa, Đồng Nai'),
(4, 'Vương Nhật Nam', '0954321098', 'Quận 10, TP. Hồ Chí Minh'),
(5, 'Hoàng Trọng Nghĩa', '0945678123', 'KĐT Bán đảo Linh Đàm, Hoàng Mai, Hà Nội'),
(6, 'Đinh Tuấn Tài', '0978123456', '22 Lê Duẩn, Hải Châu, Đà Nẵng'),
(7, 'Vũ Bích Ngọc', '0909888777', 'Tòa nhà Landmark 81, Bình Thạnh, TP. HCM'),
(8, 'Ngô Thanh Hùng', '0988666555', '45 Lý Tự Trọng, Ninh Kiều, Cần Thơ'),
(9, 'Bùi Phương Linh', '0911222333', 'Chung cư Ehome, Bình Tân, TP. HCM'),
(10, 'Đỗ Thái Sơn', '0933444555', '112 Quang Trung, Gò Vấp, TP. HCM'),
(11, 'Trương Mỹ Lan', '0922333444', 'Biệt thự Chateau, Quận 7, TP. HCM'),
(12, 'Lý Hải Yến', '0966777888', 'KĐT Ecopark, Văn Giang, Hưng Yên'),
(13, 'Vương Đình Khang', '0955666777', 'Hẻm 304 Hoàng Diệu, Q4, TP. HCM'),
(14, 'Châu Tấn Phát', '0999888111', '89 Võ Văn Ngân, Thủ Đức, TP. HCM'),
(15, 'Hồ Bảo Trâm', '0908111222', 'Tòa nhà The Manor, Nam Từ Liêm, Hà Nội'),
(16, 'Trần Văn Hùng', '0981234567', 'Quận 1, TP. Hồ Chí Minh'),
(17, 'Lê Thị Bích Trâm', '0902345678', 'Cái Răng, TP. Cần Thơ'),
(18, 'Nguyễn Hoàng Nam', '0933456789', 'Đống Đa, Hà Nội'),
(19, 'Phạm Ngọc Thảo', '0944567890', 'Sơn Trà, TP. Đà Nẵng'),
(20, 'Vũ Hải Đăng', '0915678901', 'Phú Nhuận, TP. Hồ Chí Minh'),
(21, 'Đặng Thị Kim Ngọc', '0976789012', 'Thuận An, Bình Dương'),
(22, 'Bùi Minh Triết', '0987890123', 'TP. Mỹ Tho, Tiền Giang'),
(23, 'Ngô Quỳnh Hương', '0998901234', 'Tân Bình, TP. Hồ Chí Minh'),
(24, 'Trịnh Công Sơn', '0909012345', 'TP. Huế, Thừa Thiên Huế'),
(25, 'Lý Thảo My', '0930123456', 'Cầu Giấy, Hà Nội'),
(26, 'Hồ Tấn Đạt', '0941234567', 'Quận 8, TP. Hồ Chí Minh'),
(27, 'Đinh Trọng Quý', '0912345670', 'TP. Biên Hòa, Đồng Nai'),
(28, 'Tăng Thanh Hà', '0973456781', 'Quận 2, TP. Hồ Chí Minh'),
(29, 'Châu Khải Phong', '0984567892', 'Thanh Khê, TP. Đà Nẵng'),
(30, 'Mai Phương Thúy', '0995678903', 'Nam Từ Liêm, Hà Nội'),
(31, 'Lương Gia Huy', '0906789014', 'Tân Phú, TP. Hồ Chí Minh'),
(32, 'Vương Tú Anh', '0937890125', 'Gò Vấp, TP. Hồ Chí Minh'),
(33, 'Cao Thái Sơn', '0948901236', 'TP. Vũng Tàu, Bà Rịa - Vũng Tàu'),
(34, 'Đoàn Văn Hậu', '0919012347', 'Hưng Hà, Thái Bình'),
(35, 'Nguyễn Quang Hải', '0970123458', 'Đông Anh, Hà Nội'),
(36, 'Phan Văn Đức', '0981234569', 'TP. Vinh, Nghệ An'),
(37, 'Quách Ngọc Ngoan', '0992345670', 'TP. Cà Mau, Cà Mau'),
(38, 'Trương Đình Hoàng', '0903456781', 'TP. Buôn Ma Thuột, Đắk Lắk'),
(39, 'Khổng Tú Quỳnh', '0934567892', 'Quận 5, TP. Hồ Chí Minh'),
(40, 'Lâm Chấn Khang', '0945678903', 'Bình Thủy, TP. Cần Thơ');

INSERT INTO NhaCungCap (ID, TenNhaCungCap, DienThoai, Email, DiaChi) VALUES
(1, 'Công ty TNHH Điện tử Samsung Vina', '02831234567', 'contact@samsung.com.vn', 'Số 2 Hải Triều, Q1, TP. HCM'),
(2, 'Sony Electronics Việt Nam', '1800588885', 'info@sony.com.vn', 'Lầu 6, President Place, TP. HCM'),
(3, 'LG Electronics Việt Nam', '18001503', 'support.lg@lge.com', 'KCN Tràng Duệ, An Dương, Hải Phòng'),
(4, 'TCL Việt Nam', '1800588880', 'cskh@tcl.com', 'Phú Cường, Thủ Dầu Một, Bình Dương'),
(5, 'Panasonic Việt Nam', '18001593', 'customer@panasonic.com.vn', 'KCN Thăng Long, Đông Anh, Hà Nội'),
(6, 'Toshiba Việt Nam', '18001529', 'contact@toshiba.com.vn', 'Lầu 10, Tòa nhà Bitexco, Q1, TP. HCM'),
(7, 'Casper Việt Nam', '18006644', 'cskh@casper-electric.com', 'Tòa nhà Capital Place, Ba Đình, Hà Nội'),
(8, 'Xiaomi Việt Nam', '19005656', 'service.vn@xiaomi.com', 'KĐT Sala, Thủ Thiêm, TP. Thủ Đức'),
(9, 'Sharp Việt Nam', '18001599', 'info@sharp.vn', 'Tòa nhà Etown, Tân Bình, TP. HCM'),
(10, 'Hisense Việt Nam', '18008888', 'support@hisense.vn', 'Đường Nguyễn Thị Minh Khai, Q3, TP. HCM'),
(11, 'Tập đoàn Asanzo', '19006369', 'cskh@asanzo.vn', 'KCN Vĩnh Lộc, Bình Tân, TP. HCM'),
(12, 'Skyworth Việt Nam', '18001180', 'contact@skyworth.vn', 'KCN Sóng Thần, Dĩ An, Bình Dương'),
(13, 'Aqua Việt Nam', '1800585832', 'cskh@aquavietnam.vn', 'KCN Biên Hòa 2, Đồng Nai'),
(14, 'FFalcon Việt Nam', '19001234', 'info@ffalcon.com.vn', 'Tòa nhà Centre Point, Phú Nhuận, TP. HCM'),
(15, 'Coocaa Việt Nam', '18001190', 'support@coocaa.vn', 'Quận 7, TP. HCM');

INSERT INTO HangSanXuat (ID, TenHangSanXuat) VALUES 
(1, 'Sony'), (2, 'Samsung'), (3, 'LG'), (4, 'TCL'), (5, 'Casper'), 
(6, 'Xiaomi'), (7, 'Sharp'), (8, 'Toshiba'), (9, 'Coocaa');

INSERT INTO LoaiSanPham (ID, TenLoai) VALUES 
(1, 'Smart TV 4K'), (2, 'Android TV'), (3, 'Google TV'), (4, 'TV OLED'), (5, 'TV QLED'), (6, 'TV Mini LED'),
(7, 'Tivi Độ phân giải 8K'), (8, 'Tivi Màn Hình Cong'), (9, 'Tivi Khung Tranh (The Frame)'), (10, 'Tivi Cảm Ứng'),
(11, 'Tivi Ngoài Trời (Chống nước)'), (12, 'Loa Soundbar Tivi'), (13, 'Giá Treo Tivi Đa Năng'), 
(14, 'Android TV Box'), (15, 'Cáp HDMI/Phụ kiện khác');

INSERT INTO SanPham (ID, LoaiSanPhamID, HangSanXuatID, TenSanPham, DonGia, SoLuong, HinhAnh, MoTa) VALUES 
(1, 3, 1, 'Google Tivi Sony 4K 55 inch KD-55X80L', 16400000, 20, 'sony_55x80l.jpg', 'Bộ xử lý X1 4K HDR, Âm thanh vòm Dolby Atmos.'),
(2, 4, 1, 'Google Tivi OLED Sony 65 inch XR-65A80L', 42990000, 10, 'sony_oled_65.jpg', 'Màn hình OLED đen sâu thẳm, XR OLED Contrast Pro.'),
(3, 5, 2, 'Smart Tivi QLED Samsung 4K 55 inch QA55Q60B', 12900000, 35, 'samsung_qled_55.jpg', 'Công nghệ Quantum Dot, Đèn nền Dual LED.'),
(4, 6, 2, 'Smart Tivi Neo QLED 8K Samsung 75 inch', 85000000, 5, 'samsung_8k_75.jpg', 'Độ phân giải 8K siêu nét, Quantum Matrix Pro.'),
(5, 1, 2, 'Smart Tivi Samsung 4K Crystal UHD 43 inch', 8500000, 50, 'samsung_43_uhd.jpg', 'Bộ xử lý Crystal 4K, Motion Xcelerator.'),
(6, 4, 3, 'Smart Tivi OLED LG 4K 55 inch 55A3PSA', 23900000, 15, 'lg_oled_55.jpg', 'Điểm ảnh tự phát sáng, Bộ xử lý AI α7 Gen6.'),
(7, 1, 3, 'Smart Tivi LG 4K 50 inch 50UQ7550', 9200000, 40, 'lg_4k_50.jpg', 'Màn hình 4K UHD sắc nét, Filmmaker Mode.'),
(8, 3, 4, 'Google Tivi TCL 4K 55 inch 55P737', 7990000, 60, 'tcl_55p737.jpg', 'Điều khiển giọng nói rảnh tay, Viền siêu mỏng.'),
(9, 2, 6, 'Android Tivi Xiaomi A Pro 43 inch 4K', 6490000, 45, 'xiaomi_apro_43.jpg', 'Thiết kế kim loại, Dolby Audio, DTS-X.'),
(10, 2, 6, 'Android Tivi Xiaomi P1 55 inch', 7990000, 30, 'xiaomi_p1_55.jpg', 'Góc nhìn rộng 178 độ, Google Assistant.'),
(11, 1, 3, 'Smart Tivi LG NanoCell 4K 65 inch', 15900000, 20, 'lg_nanocell_65.jpg', 'Màu sắc thuần khiết, bộ xử lý α5 Gen5 4K.'),
(12, 9, 2, 'Smart Tivi Khung Tranh The Frame Samsung', 25500000, 10, 'samsung_theframe_55.jpg', 'Thiết kế khung tranh nghệ thuật, chống chói.'),
(13, 1, 1, 'Google Tivi Sony Bravia 4K 85 inch', 65900000, 5, 'sony_bravia_85.jpg', 'Màn hình khổng lồ, công nghệ Triluminos Pro.'),
(14, 6, 4, 'Smart Tivi TCL Mini LED 65 inch', 28900000, 15, 'tcl_miniled_65.jpg', 'Độ sáng cực cao, tần số quét 144Hz chơi game.'),
(15, 5, 5, 'Android Tivi Casper QLED 55 inch', 10500000, 25, 'casper_qled_55.jpg', 'Chấm lượng tử QLED, thiết kế tràn viền.'),
(16, 2, 7, 'Android Tivi Sharp 42 inch', 5200000, 40, 'sharp_42_fhd.jpg', 'Âm thanh Dolby Audio, công nghệ Nhật Bản.'),
(17, 1, 8, 'Smart Tivi Toshiba 4K 50 inch', 8500000, 30, 'toshiba_4k_50.jpg', 'Bộ xử lý Regza Engine 4K, công nghệ bù trừ chuyển động.'),
(18, 1, 9, 'Smart Tivi Coocaa 4K 55 inch', 7200000, 35, 'coocaa_4k_55.jpg', 'Màn hình bảo vệ mắt, viền siêu mỏng vô cực.'),
(19, 4, 3, 'Smart Tivi OLED Evo LG 77 inch', 85000000, 3, 'lg_oledevo_77.jpg', 'Độ sáng tăng cường 20%, thiết kế mỏng như giấy.'),
(20, 1, 2, 'Smart Tivi Samsung Crystal 4K 65 inch', 13500000, 40, 'samsung_crystal_65.jpg', 'Màu sắc sống động, thiết kế AirSlim tinh tế.');

INSERT INTO HoaDon (ID, NhanVienID, KhachHangID, NgayLap, GhiChuHoaDon) VALUES
(1, 1, 1, '2023-11-01 10:30:00', 'Khách mua làm quà tặng, xuất hóa đơn VAT'),
(2, 2, 2, '2023-11-02 14:15:00', NULL),
(3, 3, 3, '2023-11-05 09:00:00', 'Giao hàng sau 18h vì khách đi làm'),
(4, 4, 4, '2023-11-06 16:45:00', 'Thanh toán qua thẻ tín dụng Visa'),
(5, 1, 5, '2023-11-10 11:20:00', 'Khách VIP, tặng kèm giá treo đa năng'),
(6, 2, 6, '2023-11-11 13:10:00', NULL),
(7, 3, 7, '2023-11-15 15:30:00', 'Gọi điện trước 30 phút khi giao hàng'),
(8, 4, 8, '2023-11-18 10:05:00', 'Lắp đặt tại chung cư, cần thẻ thang máy'),
(9, 1, 9, '2023-11-20 08:30:00', NULL),
(10, 2, 10, '2023-11-22 17:00:00', 'Chuyển khoản trước 50% tiền cọc'),
(11, 3, 11, '2023-11-25 14:40:00', 'Nhờ kỹ thuật viên cài đặt sẵn các app xem phim'),
(12, 4, 12, '2023-11-28 09:15:00', NULL),
(13, 1, 13, '2023-12-01 11:50:00', 'Khách mua trả góp qua HD Saison'),
(14, 2, 14, '2023-12-05 16:20:00', 'Giao nhầm mã, đã thu hồi và đổi lại mã mới'),
(15, 3, 15, '2023-12-10 10:10:00', 'Đã thanh toán 100% bằng tiền mặt');

INSERT INTO HoaDon_ChiTiet (ID, HoaDonID, SanPhamID, SoLuongBan, DonGiaBan) VALUES 
(1, 1, 1, 1, 16400000), (2, 2, 3, 1, 12900000), (3, 2, 9, 1, 6490000), (4, 3, 5, 2, 8500000),
(5, 4, 7, 1, 9200000), (6, 5, 4, 1, 85000000), (7, 6, 8, 1, 7990000), (8, 7, 2, 1, 42990000),
(9, 7, 10, 1, 7990000), (10, 8, 6, 1, 23900000), (11, 9, 5, 1, 8500000), (12, 10, 1, 1, 16400000),
(13, 11, 3, 1, 12900000), (14, 12, 9, 2, 6490000), (15, 13, 2, 1, 42990000), (16, 14, 7, 1, 9200000),
(17, 15, 10, 2, 7990000);

INSERT INTO PhieuNhap (ID, NhanVienID, NhaCungCapID, NgayNhap, TongTien, GhiChu) VALUES
(1, 1, 2, '2023-10-15 08:30:00', 164000000, 'Nhập hàng lô Tivi Samsung đầu quý 4'),
(2, 2, 1, '2023-10-20 09:15:00', 470000000, 'Nhập bổ sung Tivi Sony cao cấp'),
(3, 1, 3, '2023-10-25 10:00:00', 250000000, 'Nhập lô Tivi LG bán Tết'),
(4, 3, 4, '2023-11-01 08:00:00', 125000000, 'Nhập lô hàng TCL tháng 11'),
(5, 4, 5, '2023-11-05 09:30:00', 85000000, 'Nhập bổ sung Tivi Casper'),
(6, 1, 8, '2023-11-10 14:00:00', 140000000, 'Nhập Xiaomi phục vụ Black Friday'),
(7, 2, 9, '2023-11-12 10:15:00', 95000000, 'Nhập hàng Sharp về kho Thủ Đức'),
(8, 3, 6, '2023-11-15 11:00:00', 110000000, 'Nhập lô Toshiba'),
(9, 4, 15, '2023-11-20 15:45:00', 65000000, 'Nhập Coocaa giá rẻ cho sinh viên'),
(10, 1, 1, '2023-11-25 08:30:00', 210000000, 'Nhập Samsung đợt 2 tháng 11'),
(11, 2, 2, '2023-12-01 09:00:00', 320000000, 'Nhập Sony chuẩn bị Noel'),
(12, 3, 3, '2023-12-05 13:20:00', 180000000, 'Nhập LG OLED đợt cuối năm'),
(13, 4, 4, '2023-12-10 16:10:00', 155000000, 'Nhập bổ sung TCL Mini LED'),
(14, 1, 5, '2023-12-15 10:30:00', 75000000, 'Nhập Casper đợt 2'),
(15, 2, 8, '2023-12-20 14:45:00', 195000000, 'Nhập Xiaomi đợt 2'),
(16, 3, 1, '2023-12-25 08:00:00', 450000000, 'Nhập số lượng lớn Samsung bán Tết Dương lịch'),
(17, 4, 2, '2024-01-05 09:15:00', 500000000, 'Nhập Sony bán Tết Nguyên Đán'),
(18, 1, 3, '2024-01-10 11:30:00', 380000000, 'Nhập LG bán Tết Nguyên Đán');

INSERT INTO BaoHanh (ID, HoaDonID, SanPhamID, SoSerial, NgayKichHoat, NgayHetHan, TrangThai) VALUES
(1, 1, 1, 'SN-SONY-10001', '2023-11-01', '2025-11-01', 'Đang bảo hành'),
(2, 2, 3, 'SN-SAMSUNG-20001', '2023-11-02', '2025-11-02', 'Đang bảo hành'),
(3, 3, 5, 'SN-SAMSUNG-20002', '2023-11-05', '2025-11-05', 'Đang bảo hành'),
(4, 4, 7, 'SN-LG-30001', '2023-11-06', '2025-11-06', 'Đang bảo hành'),
(5, 5, 4, 'SN-SAMSUNG-20003', '2023-11-10', '2025-11-10', 'Đang bảo hành'),
(6, 6, 8, 'SN-TCL-40001', '2023-11-11', '2026-11-11', 'Đang bảo hành'),
(7, 7, 2, 'SN-SONY-10002', '2023-11-15', '2025-11-15', 'Đang bảo hành'),
(8, 8, 6, 'SN-LG-30002', '2023-11-18', '2025-11-18', 'Đang bảo hành'),
(9, 9, 5, 'SN-SAMSUNG-20004', '2023-11-20', '2025-11-20', 'Đang bảo hành'),
(10, 10, 1, 'SN-SONY-10003', '2023-11-22', '2025-11-22', 'Đang bảo hành'),
(11, 11, 3, 'SN-SAMSUNG-20005', '2023-11-25', '2025-11-25', 'Đang bảo hành'),
(12, 12, 9, 'SN-XIAOMI-50001', '2023-11-28', '2025-11-28', 'Đang bảo hành'),
(13, 13, 2, 'SN-SONY-10004', '2023-12-01', '2025-12-01', 'Đang bảo hành'),
(14, 14, 7, 'SN-LG-30003', '2023-12-05', '2025-12-05', 'Đang bảo hành'),
(15, 15, 10, 'SN-XIAOMI-50002', '2023-12-10', '2025-12-10', 'Đã hủy');