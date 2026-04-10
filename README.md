# N&U Store — Cấu trúc dự án (tóm tắt)

Dự án PHP (chạy trên XAMPP). Đã tái cấu trúc các tài nguyên tĩnh và helper để dễ bảo trì.

Thư mục chính sau tái cấu trúc:

- `admin/` — giao diện quản trị (giữ nguyên vị trí).
- `thu_vien/` — helper PHP và kết nối CSDL (ví dụ: `connect.php`, `nhatky_helper.php`).
- `tai_nguyen/`
	- `tai_nguyen/css/` — stylesheet chính (`style.css`).
	- `tai_nguyen/js/` — file JS (`script.js`).
	- `tai_nguyen/anh/` — nơi để lưu ảnh tĩnh nếu muốn di chuyển từ `uploads/`.
- `uploads/` — ảnh tải lên (hiện vẫn giữ nguyên để tránh hỏng đường dẫn sản phẩm).
- Các trang front-end (root): `trang_chu.php`, `san_pham.php`, `doc_tin.php`, `gio_hang.php`, v.v.

Ghi chú quan trọng:
- Tất cả các `require` và `href/src` trong mã đã được cập nhật để trỏ tới `thu_vien/` và `tai_nguyen/`.
- Các wrapper gốc (tệp tại root cho `connect.php`, `nhatky_helper.php`, `style.css`, `script.js`) đã được xóa — hệ thống bây giờ sử dụng trực tiếp tệp trong `thu_vien/` và `tai_nguyen/`.
- Nếu muốn di chuyển ảnh từ `uploads/` sang `tai_nguyen/anh/`, hãy cân nhắc cập nhật các đường dẫn hình ảnh trong cơ sở dữ liệu hoặc chạy script di chuyển.

Hành động đề xuất tiếp theo:
- Kiểm tra giao diện trên trình duyệt (QA trực quan).
- (Tùy chọn) Di chuyển/đồng bộ `uploads/` → `tai_nguyen/anh/` và cập nhật đường dẫn.

Liên hệ: chỉnh sửa README này nếu cần thay đổi cấu trúc.
