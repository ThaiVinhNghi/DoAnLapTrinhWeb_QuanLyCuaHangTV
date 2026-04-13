<?php
/**
 * MIGRATION SCRIPT - Hash Password Khách Hàng & Nhân Viên
 * Chạy script này 1 lần trên console/browser để hash tất cả password existing
 * 
 * Sau khi chạy xong, cần update logic login để dùng password_verify()
 */

require_once 'thu_vien/connect.php';

echo "=== MIGRATION: Hash Password ===\n\n";

// === STEP 1: Add password_hash column nếu chưa có ===
$check_col = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='khachhang' AND COLUMN_NAME='MatKhauHash'";
$res = $conn->query($check_col);

if (!$res || $res->num_rows == 0) {
    echo "1. Thêm cột MatKhauHash vào bảng khachhang...\n";
    $sql_add = "ALTER TABLE khachhang ADD COLUMN MatKhauHash VARCHAR(255) NULL";
    if ($conn->query($sql_add)) {
        echo "   ✅ Thêm cột thành công\n\n";
    } else {
        echo "   ❌ Lỗi: " . $conn->error . "\n";
        exit;
    }
}

// === STEP 2: Hash password khách hàng ===
echo "2. Hash password khách hàng...\n";
$sql_kh = "SELECT ID, MatKhau FROM khachhang WHERE MatKhauHash IS NULL OR MatKhauHash = ''";
$res_kh = $conn->query($sql_kh);
$count_kh = 0;

if ($res_kh && $res_kh->num_rows > 0) {
    while ($row = $res_kh->fetch_assoc()) {
        $id = $row['ID'];
        $password = $row['MatKhau'];
        $hash = password_hash($password, PASSWORD_BCRYPT);
        
        $sql_update = "UPDATE khachhang SET MatKhauHash = ? WHERE ID = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("si", $hash, $id);
        if ($stmt->execute()) {
            $count_kh++;
        }
        $stmt->close();
    }
}
echo "   ✅ Hash $count_kh khách hàng\n\n";

// === STEP 3: Hash password nhân viên ===
echo "3. Hash password nhân viên...\n";

$check_col_nv = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='nhanvien' AND COLUMN_NAME='MatKhauHash'";
$res_nv_col = $conn->query($check_col_nv);

if (!$res_nv_col || $res_nv_col->num_rows == 0) {
    echo "   Thêm cột MatKhauHash vào bảng nhanvien...\n";
    $sql_add_nv = "ALTER TABLE nhanvien ADD COLUMN MatKhauHash VARCHAR(255) NULL";
    if ($conn->query($sql_add_nv)) {
        echo "   ✅ Thêm cột thành công\n";
    } else {
        echo "   ⚠️ Cột có thể đã tồn tại hoặc lỗi: " . $conn->error . "\n";
    }
}

$sql_nv = "SELECT ID, MatKhau FROM nhanvien WHERE MatKhauHash IS NULL OR MatKhauHash = ''";
$res_nv = $conn->query($sql_nv);
$count_nv = 0;

if ($res_nv && $res_nv->num_rows > 0) {
    while ($row = $res_nv->fetch_assoc()) {
        $id = $row['ID'];
        $password = $row['MatKhau'];
        $hash = password_hash($password, PASSWORD_BCRYPT);
        
        $sql_update = "UPDATE nhanvien SET MatKhauHash = ? WHERE ID = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("si", $hash, $id);
        if ($stmt->execute()) {
            $count_nv++;
        }
        $stmt->close();
    }
}
echo "   ✅ Hash $count_nv nhân viên\n\n";

echo "=== MIGRATION HOÀN THÀNH ===\n";
echo "✅ Tổng: " . ($count_kh + $count_nv) . " password đã được hash\n\n";

echo "🔔 LƯU Ý:\n";
echo "1. Xóa script này sau khi chạy\n";
echo "2. Update login_khach.php để dùng password_verify()\n";
echo "3. Update login.php để dùng password_verify()\n";
echo "4. Sau khi verify logic login, có thể xóa cột MatKhau cũ\n";

$conn->close();
?>
