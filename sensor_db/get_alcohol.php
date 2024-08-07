<?php
session_start();
include_once "./db.php"; 

header('Content-Type: application/json');

$data = [];

try {
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    $alcohol_sql = "SELECT * FROM alcohol";
    $alcohol_result = $conn->query($alcohol_sql);

    if ($alcohol_result) {
        while ($row = $alcohol_result->fetch_assoc()) { 
            $data[] = $row; 
        }
        $alcohol_result->free_result(); // ปล่อยทรัพยากรหลังจากใช้งานเสร็จ
    } else {
        throw new Exception("Query failed: " . $conn->error);
    }

    echo json_encode($data);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
} 

?>