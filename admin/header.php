<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị - Tivi Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark shadow-sm">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-tv"></i> Admin N&U
        </a>
        <div class="d-flex align-items-center">
            <a href="../trang_chu.php" class="btn btn-outline-info btn-sm me-3" target="_blank">
                <i class="bi bi-house-door-fill"></i> Về trang chủ
            </a>
            
            <span class="text-white me-3">
                Xin chào, <strong><?php echo isset($_SESSION['HoVaTen']) ? $_SESSION['HoVaTen'] : 'Admin'; ?></strong>!
            </span>
            <a href="logout.php" class="btn btn-danger btn-sm">Đăng xuất</a>
        </div>
    </div>
</nav>

<div class="container-fluid mt-3">
    <div class="row px-3">