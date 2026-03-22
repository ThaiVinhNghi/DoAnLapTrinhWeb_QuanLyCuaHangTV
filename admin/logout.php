<?php
session_start();

unset($_SESSION['nhanvien_id']);
unset($_SESSION['nhanvien_ten']);
unset($_SESSION['vai_tro']);
unset($_SESSION['ten_vai_tro']);

header("Location: ../login.php");
exit();
?>