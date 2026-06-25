<?php
// /api/get_kpi_herb.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection(); // เรียกใช้ Connection เดียว

// คำสั่ง SQL ที่มีการ Join ข้ามฐานข้อมูล (opd และ hos) บน Server 192.168.111.251
$query = "
    SELECT 
        DATE_FORMAT(d.regdate, '%Y-%m') as month_year,
        SUM(d.amount * i.unitprice) as total_value
    FROM opd.drug_order_opd d
    LEFT JOIN hos.itemlist i ON d.codedrug = i.itemcode
    WHERE d.regdate BETWEEN '2025-10-01' AND '2026-09-30'
    AND i.groupitem = 'HERB' 
    GROUP BY DATE_FORMAT(d.regdate, '%Y-%m')
    ORDER BY DATE_FORMAT(d.regdate, '%Y-%m') ASC
";

try {
    $stmt = $db->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(["status" => "success", "data" => $results]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>