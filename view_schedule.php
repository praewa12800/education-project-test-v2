<?php
include 'config.php';
session_start();
$role = $_SESSION['role'];

// เลียนแบบ Logic ใน Pseudo Code: IF role == "student" ...
$sql = "SELECT s.*, subj.subject_name FROM schedules s 
        JOIN subjects subj ON s.subject_id = subj.id 
        WHERE s.type = 'class' ORDER BY s.day_of_week, s.start_time";

$result = mysqli_query($conn, $sql);
echo "<h2>ตารางเรียนของคุณ ($role)</h2>";
while($row = mysqli_fetch_assoc($result)) {
    echo $row['day_of_week'] . ": " . $row['subject_name'] . " (" . $row['start_time'] . "-" . $row['end_time'] . ") ห้อง " . $row['room'] . "<br>";
}
?>