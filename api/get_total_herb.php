<?php
// ไฟล์: /api/get_total_herb.php
session_start();
header("Content-Type: application/json; charset=UTF-8");

if (!isset($_SESSION['kpi_user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // กำหนดช่วงเวลาปีงบประมาณ 2569 (1 ต.ค. 2568 - 30 ก.ย. 2569)
    $start_date = '2025-10-01';
    $end_date = '2026-09-30';

    // Query คำนวณมูลค่ายา (จำนวน x ราคา) โดย Join ตารางสั่งยา กับ ตารางรายการยา
    $query = "SELECT SUM(d.amount * i.unitprice) as total_value 
              FROM opd.drug_order_opd d
              LEFT JOIN hos.itemlist i ON d.codedrug = i.itemcode
              WHERE d.regdate BETWEEN :start_date AND :end_date
              AND i.groupitem = 'HERB'"; // ⚠️ หมายเหตุ: เปลี่ยน 'HERB' เป็นรหัสกลุ่มยาสมุนไพรของ รพ.ปลวกแดง จริงๆ นะครับ
              
    $stmt = $db->prepare($query);
    $stmt->bindParam(":start_date", $start_date);
    $stmt->bindParam(":end_date", $end_date);
    $stmt->execute();
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // ตรวจสอบค่าว่าง (กรณีที่ยังไม่มียอดสั่งยาเลยให้เป็น 0)
    $total = $row['total_value'] ? $row['total_value'] : 0;

    echo json_encode([
        "status" => "success",
        "total_value" => number_format($total, 2) // ทศนิยม 2 ตำแหน่ง
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error", 
        "message" => "Database Error: " . $e->getMessage()
    ]);
}
?>