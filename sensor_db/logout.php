<?php
session_start();

// ลบ session ทั้งหมด
session_destroy();

// Redirect ไปยังหน้า login.php
header("Location: login");
exit();
?>
