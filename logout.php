<?php
session_start();
session_unset(); // ลบตัวแปรเซสชันทั้งหมด
session_destroy(); // ทำลายเซสชัน

// ส่งกลับไปหน้า login.php ที่อยู่ในโฟลเดอร์ Page
header("Location:login.php"); 
exit();
?>