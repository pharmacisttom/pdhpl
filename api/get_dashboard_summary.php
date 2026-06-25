<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../config/database.php';

$database = new Database();
$app_conn = $database->getAppConnection();

if (!$app_conn) {
    echo json_encode(["status" => "error", "message" => "ไม่สามารถเชื่อมต่อฐานข้อมูลได้"]);
    exit();
}

$b_year = $_GET['year'] ?? '2569';

try {
    // ดึงข้อมูลภาพรวมแต่ละหมวดหมู่
    $stmt = $app_conn->prepare("
        SELECT 
            m.excellence_category,
            COUNT(m.id) as total,
            SUM(CASE WHEN c.calculated_result IS NOT NULL THEN 1 ELSE 0 END) as passed
        FROM kpi_master m
        LEFT JOIN kpi_data_cache c ON m.id = c.kpi_id AND c.b_year = :b_year
        WHERE m.status = 'active'
        GROUP BY m.excellence_category
    ");
    
    $stmt->execute([':b_year' => $b_year]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // จัดรูปแบบให้เรียกใช้ง่ายใน JS
    $summary = [
        'PP&P Excellence' => ['total' => 0, 'passed' => 0],
        'Service Excellence' => ['total' => 0, 'passed' => 0],
        'People Excellence' => ['total' => 0, 'passed' => 0],
        'Governance Excellence' => ['total' => 0, 'passed' => 0],
        'Health Economy Excellence' => ['total' => 0, 'passed' => 0]
    ];

    foreach ($results as $row) {
        $cat = $row['excellence_category'];
        if (isset($summary[$cat])) {
            $summary[$cat]['total'] = (int)$row['total'];
            $summary[$cat]['passed'] = (int)$row['passed'];
        }
    }

    echo json_encode([
        "status" => "success",
        "data" => $summary
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
