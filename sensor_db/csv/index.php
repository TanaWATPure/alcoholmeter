<?php
include_once "../db.php";

// ดึงข้อมูลจากตาราง alcohol
$sql = "SELECT a.id, a.rfid, a.alcoholvalue, u.name, u.identification, a.updated_at 
        FROM alcohol AS a
        INNER JOIN users AS u ON a.rfid = u.rfid";$result = $conn->query($sql);

if ($result === false) {
    // แสดงข้อความข้อผิดพลาด
    echo "Error: " . $conn->error;
} else {
    if ($result->num_rows > 0) {
        // เก็บข้อมูลแต่ละแถวในตัวแปร data
        $data = [];
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        // สร้างไฟล์ CSV
        $csvFileName = "alcohol_data.csv";
        $outputCsv = fopen($csvFileName, 'w');

        // เขียนข้อมูลคอลัมน์
        fputcsv($outputCsv, array('ID','RFID', 'Alcohol Value', 'Name', 'Identification', 'Timestamp'));

        // เขียนข้อมูลแถว
        foreach ($data as $row) {
            fputcsv($outputCsv, $row);
        }

        fclose($outputCsv);

        // กำหนด header และส่งออกไฟล์
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="'.$csvFileName.'"');
        header('Cache-Control: max-age=0');

        readfile($csvFileName);
        exit();
    } else {
        echo "0 results";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Display</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-5">
    <h1>Data Display</h1>
    <!-- แสดงตารางข้อมูลที่ดึงมาจากฐานข้อมูล -->
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
