<?php
session_start();
require_once '../thu_vien/connect.php'; // Đảm bảo đường dẫn file connect đúng

// Xử lý khi Admin bấm nút Cập nhật khuyến mãi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cap_nhat_km'])) {
    $id = (int)$_POST['id_sp'];
    $phan_tram = (int)$_POST['phan_tram'];
    
    // Giới hạn phần trăm từ 0 đến 100
    if ($phan_tram >= 0 && $phan_tram <= 100) {
        $sql_update = "UPDATE sanpham SET PhanTramGiam = $phan_tram WHERE ID = $id";
        $conn->query($sql_update);
        $thong_bao = "Cập nhật khuyến mãi thành công!";
    }
}
?>
<div class="container mt-4">
    <h2 class="text-danger fw-bold"><i class="bi bi-tags-fill"></i> Quản lý Khuyến Mãi Sản Phẩm</h2>
    
    <?php if(isset($thong_bao)) echo "<div class='alert alert-success'>$thong_bao</div>"; ?>

    <table class="table table-bordered table-hover mt-3 bg-white shadow-sm">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Tên Sản Phẩm</th>
                <th>Giá Gốc</th>
                <th>Giá Sau Giảm</th>
                <th>% Giảm Giá</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT ID, TenSanPham, DonGia, PhanTramGiam FROM sanpham ORDER BY ID DESC";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                $giaGoc = $row['DonGia'];
                $phanTram = $row['PhanTramGiam'];
                $giaSauGiam = $giaGoc - ($giaGoc * $phanTram / 100);
            ?>
            <tr>
                <td><?= $row['ID'] ?></td>
                <td><?= htmlspecialchars($row['TenSanPham']) ?></td>
                <td><?= number_format($giaGoc, 0, ',', '.') ?> đ</td>
                <td class="text-danger fw-bold"><?= number_format($giaSauGiam, 0, ',', '.') ?> đ</td>
                <form method="POST" action="">
                    <td style="width: 150px;">
                        <input type="hidden" name="id_sp" value="<?= $row['ID'] ?>">
                        <div class="input-group">
                            <input type="number" name="phan_tram" class="form-control text-center" value="<?= $phanTram ?>" min="0" max="100">
                            <span class="input-group-text">%</span>
                        </div>
                    </td>
                    <td>
                        <button type="submit" name="cap_nhat_km" class="btn btn-primary btn-sm">Lưu</button>
                    </td>
                </form>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>