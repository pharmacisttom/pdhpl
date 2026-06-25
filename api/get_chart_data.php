<?php
// ไฟล์: /api/get_chart_data.php
session_start();
header("Content-Type: application/json; charset=UTF-8");

if (!isset($_SESSION['kpi_user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getHisConnection();

try {
    // 1. สร้างโครงสร้างข้อมูล 12 เดือน สำหรับปีงบประมาณ 2569 (ต.ค. 68 - ก.ย. 69)
    $chart_data = [
        '2025-10' => ['label' => 'ต.ค. 68', 'opd' => 0, 'herb' => 0],
        '2025-11' => ['label' => 'พ.ย. 68', 'opd' => 0, 'herb' => 0],
        '2025-12' => ['label' => 'ธ.ค. 68', 'opd' => 0, 'herb' => 0],
        '2026-01' => ['label' => 'ม.ค. 69', 'opd' => 0, 'herb' => 0],
        '2026-02' => ['label' => 'ก.พ. 69', 'opd' => 0, 'herb' => 0],
        '2026-03' => ['label' => 'มี.ค. 69', 'opd' => 0, 'herb' => 0],
        '2026-04' => ['label' => 'เม.ย. 69', 'opd' => 0, 'herb' => 0],
        '2026-05' => ['label' => 'พ.ค. 69', 'opd' => 0, 'herb' => 0],
        '2026-06' => ['label' => 'มิ.ย. 69', 'opd' => 0, 'herb' => 0],
        '2026-07' => ['label' => 'ก.ค. 69', 'opd' => 0, 'herb' => 0],
        '2026-08' => ['label' => 'ส.ค. 69', 'opd' => 0, 'herb' => 0],
        '2026-09' => ['label' => 'ก.ย. 69', 'opd' => 0, 'herb' => 0]
    ];

    // 2. Query ดึงจำนวนผู้ป่วย OPD รายเดือน
    $q_opd = "SELECT DATE_FORMAT(regdate, '%Y-%m') as ym, COUNT(DISTINCT hn, frequency) as total
              FROM opd.opd
              WHERE regdate BETWEEN '2025-10-01' AND '2026-09-30'
              GROUP BY ym";
    $stmt1 = $db->query($q_opd);
    while ($row = $stmt1->fetch(PDO::FETCH_ASSOC)) {
        if (isset($chart_data[$row['ym']])) {
            $chart_data[$row['ym']]['opd'] = (int)$row['total'];
        }
    }

    // 3. Query ดึงมูลค่ายาสมุนไพรรายเดือน (Join ตาราง)
    $q_herb = "SELECT DATE_FORMAT(d.regdate, '%Y-%m') as ym, SUM(d.amount * i.unitprice) as total
               FROM opd.drug_order_opd d
               LEFT JOIN hos.itemlist i ON d.codedrug = i.itemcode
               WHERE d.regdate BETWEEN '2025-10-01' AND '2026-09-30'
               AND i.groupitem = 'HERB' 
               GROUP BY ym";
    $stmt2 = $db->query($q_herb);
    while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        if (isset($chart_data[$row['ym']])) {
            $chart_data[$row['ym']]['herb'] = (float)$row['total'];
        }
    }

    // 4. แยกข้อมูลออกเป็น Array 3 เส้น เพื่อส่งให้ Chart.js ไปวาดกราฟง่ายๆ
    $labels = [];
    $opd_data = [];
    $herb_data = [];

    foreach ($chart_data as $key => $val) {
        $labels[] = $val['label'];
        $opd_data[] = $val['opd'];
        $herb_data[] = $val['herb'];
    }

    echo json_encode([
        "status" => "success",
        "labels" => $labels,
        "opdData" => $opd_data,
        "herbData" => $herb_data
    ]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "DB Error: " . $e->getMessage()]);
}
?>