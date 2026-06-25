<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // ปีงบประมาณ 2569 (ปัจจุบัน)
    $curr_start = '2025-10-01';
    $curr_end   = '2026-09-30';

    // จัดกลุ่มโรคตามรูปภาพที่คุณส่งมา
    $groups = [
        "1. โรคทางเดินอาหารและน้ำ" => [
            ['name' => '1.1 อุจจาระร่วง (Diarrhea)', 'code' => 'A09'],
            ['name' => '1.2 อาหารเป็นพิษ (Food Poisoning)', 'code' => 'A05'],
            ['name' => '1.3 บิด (Dysentery)', 'code' => 'A06']
        ],
        "2. โรคทางเดินหายใจ" => [
            ['name' => '2.1 ไข้หวัดใหญ่ (Flu)', 'code' => 'J10|J11'],
            ['name' => '2.2 ปอดอักเสบ (Pneumonia)', 'code' => 'J18'],
            ['name' => '2.3 วัณโรค (TB)', 'code' => 'A15|A16|A17|A18|A19']
        ],
        "3. โรคติดต่อพยากรณ์/นำโดยแมลง" => [
            ['name' => '3.1 ไข้เลือดออก (Dengue)', 'code' => 'A90|A91'],
            ['name' => '3.2 ชิคุนกุนยา (Chikungunya)', 'code' => 'A920'],
            ['name' => '3.3 มาลาเรีย (Malaria)', 'code' => 'B50|B51|B52|B53|B54']
        ],
        "4. โรคอื่นๆ และอุบัติใหม่" => [
            ['name' => '4.1 มือ เท้า ปาก (HFMD)', 'code' => 'B084'],
            ['name' => '4.2 ตาแดง (Conjunctivitis)', 'code' => 'B30|H10'],
            ['name' => '4.3 โรคติดเชื้อไวรัสโคโรนา 2019', 'code' => 'U071']
        ]
    ];

    $final_output = []; // ตัวแปรนี้จะกลายเป็น data.data ในฝั่ง JS

    foreach ($groups as $groupName => $diseases) {
        $items = [];
        foreach ($diseases as $ds) {
            // ดึงข้อมูลรายปี 67, 68, 69
            $q_yearly = "SELECT 
                            SUM(CASE WHEN regdate BETWEEN '2023-10-01' AND '2024-09-30' THEN 1 ELSE 0 END) as y67,
                            SUM(CASE WHEN regdate BETWEEN '2024-10-01' AND '2025-09-30' THEN 1 ELSE 0 END) as y68,
                            SUM(CASE WHEN regdate BETWEEN '2025-10-01' AND '2026-09-30' THEN 1 ELSE 0 END) as y69
                         FROM (
                            SELECT regdate FROM opd.odiag WHERE diag REGEXP '^{$ds['code']}'
                            UNION ALL
                            SELECT i.regdate FROM ipd.idiag id INNER JOIN ipd.ipd i ON id.an = i.an WHERE id.diag REGEXP '^{$ds['code']}'
                         ) as t";
            
            $stmt_y = $db->prepare($q_yearly);
            $stmt_y->execute();
            $row_y = $stmt_y->fetch(PDO::FETCH_ASSOC);

            // ดึงข้อมูลรายเดือน ปี 69
            $monthly = array_fill(1, 12, 0);
            $q_m = "SELECT MONTH(regdate) as m, COUNT(*) as total FROM (
                        SELECT regdate FROM opd.odiag WHERE regdate BETWEEN :s AND :e AND diag REGEXP '^{$ds['code']}'
                        UNION ALL
                        SELECT i.regdate FROM ipd.idiag id INNER JOIN ipd.ipd i ON id.an = i.an WHERE i.regdate BETWEEN :s AND :e AND id.diag REGEXP '^{$ds['code']}'
                    ) as tm GROUP BY m";
            $stmt_m = $db->prepare($q_m);
            $stmt_m->execute(['s' => $curr_start, 'e' => $curr_end]);
            while($rm = $stmt_m->fetch(PDO::FETCH_ASSOC)) { $monthly[(int)$rm['m']] = (int)$rm['total']; }

            $months_order = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];
            $sorted_m = [];
            foreach($months_order as $m) { $sorted_m[] = $monthly[$m]; }

            $items[] = [
                'name' => $ds['name'],
                'y67' => (int)$row_y['y67'],
                'y68' => (int)$row_y['y68'],
                'y69' => (int)$row_y['y69'],
                'median' => round(((int)$row_y['y67'] + (int)$row_y['y68'])/2, 1),
                'monthly' => $sorted_m
            ];
        }
        $final_output[] = [
            'group' => $groupName,
            'items' => $items
        ];
    }

    echo json_encode(["status" => "success", "data" => $final_output]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}