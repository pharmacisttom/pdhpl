<?php
// ไฟล์: /api/get_today_opd.php
session_start();
header("Content-Type: application/json; charset=UTF-8");

// ตรวจสอบสิทธิ์ (ป้องกันคนพิมพ์ URL เข้ามาดึงข้อมูลโดยตรง)
if (!isset($_SESSION['kpi_user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Query นับจำนวน Visit ของผู้ป่วยนอก (OPD) เฉพาะวันที่ปัจจุบัน (CURDATE)
    $query = "SELECT COUNT(DISTINCT hn, frequency) as total_visit 
              FROM opd.opd 
              WHERE regdate = CURDATE()";
              
    $stmt = $db->prepare($query);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // ส่งค่ากลับเป็น JSON และใส่ คอมม่า (,) ให้ตัวเลขด้วย number_format
    echo json_encode([
        "status" => "success",
        "total_visit" => number_format($row['total_visit'])
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error", 
        "message" => "Database Error: " . $e->getMessage()
    ]);
}
?>