<?php
// เชื่อมต่อกับฐานข้อมูล
include_once "../db.php";

// ดึงข้อมูลจากตาราง alcohol
$sql = "SELECT id, rfid, alcoholvalue, updated_at FROM alcohol";
$result = $conn->query($sql);

$data = [];

if ($result !== false && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($data);
?>
