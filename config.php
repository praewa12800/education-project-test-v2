<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "ed_db";

$conn = mysqli_connect($host, $user, $pass, $dbname);
mysqli_set_charset($conn, "utf8"); // บรรทัดนี้จะทำให้ภาษาไทยไม่เพี้ยน

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>