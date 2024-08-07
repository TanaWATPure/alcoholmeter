<?php
// เชื่อมต่อกับฐานข้อมูล
include_once "./db.php"; 

// ตั้งค่า timezone
date_default_timezone_set('Asia/Bangkok');

try {
    // เริ่มต้น transaction
    $conn->begin_transaction();

    // ตรวจสอบว่ามีการร้องขอตรวจสอบ RFID หรือไม่
    if (isset($_GET['check_exists']) && $_GET['check_exists'] == "true") {
        if (!isset($_GET["rfid"])) {
            echo json_encode(["error" => "RFID not provided for checking."]);
            exit;
        }

        $rfid = $_GET["rfid"];
        // ใช้ COUNT(*) แทน SELECT 1 เพื่อประสิทธิภาพที่ดีขึ้น
        $sql = "SELECT COUNT(*) FROM `users` WHERE rfid = ?"; 
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $rfid);
        $stmt->execute();
        $stmt->bind_result($count); // ผูกผลลัพธ์กับตัวแปร $count
        $stmt->fetch(); 

        if ($count > 0) {
            echo "true"; 
        } else {
            echo "false"; 
        }
        // ปิด statement
        $stmt->close();
        exit; 
    }

    // ตรวจสอบว่าค่า alcoholvalue และ rfid ถูกส่งมาหรือไม่
    if (!isset($_GET["alcoholvalue"]) || !isset($_GET["rfid"])) {
        echo json_encode(["error" => "Value not found."]);
        exit;
    }

    // รับค่า alcoholvalue และ rfid จาก HTTP GET request
    $alcoholvalue = $_GET["alcoholvalue"];
    $rfid = $_GET["rfid"];

    // เรียกฟังก์ชันเพื่อค้นหาข้อมูลผู้ใช้จากตาราง users โดยใช้ rfid เป็นเงื่อนไข
    $sql = "SELECT name, identification FROM `users` WHERE rfid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $rfid);
    $stmt->execute();
    $stmt->store_result(); // เก็บผลลัพธ์ไว้ใช้ซ้ำ

    // ตรวจสอบว่ามีข้อมูลผู้ใช้ที่ตรงกับ rfid หรือไม่
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($name, $identification); // ผูกผลลัพธ์กับตัวแปร
        $stmt->fetch();
        
        $data = [
            'name' => $name,
            'identification' => $identification
        ];
        
        // เพิ่มข้อมูลลงในตาราง alcohol 
        $insert_alcohol = "INSERT INTO alcohol (rfid, alcoholvalue) VALUES (?, ?)";
        $stmt_insert = $conn->prepare($insert_alcohol);
        $stmt_insert->bind_param("ss", $rfid, $alcoholvalue); 
        $stmt_insert->execute();
        // ปิด statement insert
        $stmt_insert->close();
    } else {
        // Handle the case where no user is found 
        error_log("No users found with RFID: $rfid"); 
        $data = [];
    }

    // ปิด statement select
    $stmt->close();

    // ยืนยันการเปลี่ยนแปลงใน transaction
    $conn->commit();

    // แปลงข้อมูลเป็น JSON และส่งกลับไปยัง client
    header('Content-Type: application/json');
    echo json_encode($data); 

} catch (Exception $e) {
    // Rollback transaction ถ้าเกิดข้อผิดพลาด
    $conn->rollback();

    header('Content-Type: application/json');
    http_response_code(500); // Internal Server Error
    echo json_encode(["error" => "Database error: " . $e->getMessage()]); 
    exit;
}
?>