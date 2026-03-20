<?php
session_start();
require_once '../connect.php';

// Truy vấn dữ liệu cho 3 bảng
$sql_hsx = "SELECT * FROM hangsanxuat ORDER BY ID DESC";
$res_hsx = $conn->query($sql_hsx);

$sql_lsp = "SELECT * FROM loaisanpham ORDER BY ID DESC";
$res_lsp = $conn->query($sql_lsp);

$sql_ncc = "SELECT * FROM nhacungcap ORDER BY ID DESC";
$res_ncc = $conn->query($sql_ncc);

require_once 'header.php';
require_once 'sidebar.php';
?>

<div class="col-md-9 col-lg-10 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="text-primary fw-bold"><i class="bi bi-tags-fill"></i> Quản lý Danh Mục Hệ Thống</h3>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white pt-3 pb-0">
            <ul class="nav nav-tabs fw-bold" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="loai-tab" data-bs-toggle="tab" data-bs-target="#loai" type="button" role="tab"><i class="bi bi-box"></i> Loại Sản Phẩm</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="hang-tab" data-bs-toggle="tab" data-bs-target="#hang" type="button" role="tab"><i class="bi bi-award"></i> Hãng Sản Xuất</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="ncc-tab" data-bs-toggle="tab" data-bs-target="#ncc" type="button" role="tab"><i class="bi bi-truck"></i> Nhà Cung Cấp</button>
                </li>
            </ul>
        </div>
        
        <div class="card-body">
            <div class="tab-content" id="myTabContent">
                
                <div class="tab-pane fade show active" id="loai" role="tabpanel">
                    <div class="mb-3 text-end">
                        <a href="loai_them.php" class="btn btn-sm btn-primary"><i class="bi bi-plus-circle"></i> Thêm Loại Sản Phẩm</a>
                    </div>
                    <table class="table table-hover table-bordered align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Tên Loại Sản Phẩm</th>
                                <th width="150">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $res_lsp->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-bold"><?php echo $row['ID']; ?></td>
                                <td><?php echo htmlspecialchars($row['TenLoai']); ?></td>
                                <td>
                                    <a href="loai_sua.php?id=<?php echo $row['ID']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <a href="loai_xoa.php?id=<?php echo $row['ID']; ?>" onclick="return confirm('Bạn có chắc muốn xóa loại sản phẩm này?')" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if($res_lsp->num_rows == 0) echo "<tr><td colspan='3'>Chưa có dữ liệu</td></tr>"; ?>
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane fade" id="hang" role="tabpanel">
                    <div class="mb-3 text-end">
                        <a href="hang_them.php" class="btn btn-sm btn-success"><i class="bi bi-plus-circle"></i> Thêm Hãng Sản Xuất</a>
                    </div>
                    <table class="table table-hover table-bordered align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Tên Hãng Sản Xuất</th>
                                <th width="150">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $res_hsx->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-bold"><?php echo $row['ID']; ?></td>
                                <td><?php echo htmlspecialchars($row['TenHangSanXuat']); ?></td>
                                <td>
                                    <a href="hang_sua.php?id=<?php echo $row['ID']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <a href="hang_xoa.php?id=<?php echo $row['ID']; ?>" onclick="return confirm('Bạn có chắc muốn xóa hãng sản xuất này?')" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if($res_hsx->num_rows == 0) echo "<tr><td colspan='3'>Chưa có dữ liệu</td></tr>"; ?>
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane fade" id="ncc" role="tabpanel">
                    <div class="mb-3 text-end">
                        <a href="ncc_them.php" class="btn btn-sm btn-warning text-dark fw-bold"><i class="bi bi-plus-circle"></i> Thêm Nhà Cung Cấp</a>
                    </div>
                    <table class="table table-hover table-bordered align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Tên Nhà Cung Cấp</th>
                                <th>Điện Thoại</th>
                                <th>Email</th>
                                <th>Địa Chỉ</th>
                                <th width="150">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $res_ncc->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-bold"><?php echo $row['ID']; ?></td>
                                <td class="text-start"><?php echo htmlspecialchars($row['TenNhaCungCap']); ?></td>
                                <td><?php echo htmlspecialchars($row['DienThoai']); ?></td>
                                <td><?php echo htmlspecialchars($row['Email']); ?></td>
                                <td class="text-start"><?php echo htmlspecialchars($row['DiaChi']); ?></td>
                                <td>
                                    <a href="ncc_sua.php?id=<?php echo $row['ID']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <a href="ncc_xoa.php?id=<?php echo $row['ID']; ?>" onclick="return confirm('Bạn có chắc muốn xóa nhà cung cấp này?')" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if($res_ncc->num_rows == 0) echo "<tr><td colspan='6'>Chưa có dữ liệu</td></tr>"; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>