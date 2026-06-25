<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // กำหนดช่วงปีงบประมาณ 2568-2569
    $curr_start = '2025-10-01'; 
    $curr_end   = '2026-09-30';

    // -----------------------------------------------------------------
    // 🩸 1. เบาหวาน (DM) 
    // หาคนที่มีรหัสสงสัยป่วย (R73.9) และติดตามว่าได้รับรหัสยืนยันป่วย (E10-E14) หรือไม่
    // -----------------------------------------------------------------
    $q_dm = "SELECT 
                COUNT(DISTINCT s.hn) as total_suspect,
                COUNT(DISTINCT c.hn) as confirmed
             FROM (
                -- กลุ่มสงสัยป่วย (Pre-DM)
                SELECT hn, MIN(regdate) as suspect_date
                FROM (
                    SELECT hn, regdate FROM pcu.pdiag WHERE diag LIKE 'R739%' AND regdate BETWEEN :s1 AND :e1
                    UNION ALL
                    SELECT hn, regdate FROM opd.odiag WHERE diag LIKE 'R739%' AND regdate BETWEEN :s2 AND :e2
                ) t1 GROUP BY hn
             ) s
             LEFT JOIN (
                -- กลุ่มยืนยันผล (DM)
                SELECT hn, regdate FROM pcu.pdiag WHERE diag REGEXP '^E1[0-4]'
                UNION ALL
                SELECT hn, regdate FROM opd.odiag WHERE diag REGEXP '^E1[0-4]'
             ) c ON s.hn = c.hn AND c.regdate >= s.suspect_date";
    
    $stmt_dm = $db->prepare($q_dm);
    $stmt_dm->execute(['s1' => $curr_start, 'e1' => $curr_end, 's2' => $curr_start, 'e2' => $curr_end]);
    $row_dm = $stmt_dm->fetch(PDO::FETCH_ASSOC);

    // -----------------------------------------------------------------
    // 🩺 2. ความดัน (HT) 
    // หาคนที่มีรหัสสงสัยป่วย (R03.0) และติดตามว่าได้รับรหัสยืนยันป่วย (I10-I15) หรือไม่
    // -----------------------------------------------------------------
    $q_ht = "SELECT 
                COUNT(DISTINCT s.hn) as total_suspect,
                COUNT(DISTINCT c.hn) as confirmed
             FROM (
                -- กลุ่มสงสัยป่วย (Pre-HT)
                SELECT hn, MIN(regdate) as suspect_date
                FROM (
                    SELECT hn, regdate FROM pcu.pdiag WHERE diag LIKE 'R030%' AND regdate BETWEEN :s3 AND :e3
                    UNION ALL
                    SELECT hn, regdate FROM opd.odiag WHERE diag LIKE 'R030%' AND regdate BETWEEN :s4 AND :e4
                ) t1 GROUP BY hn
             ) s
             LEFT JOIN (
                -- กลุ่มยืนยันผล (HT)
                SELECT hn, regdate FROM pcu.pdiag WHERE diag REGEXP '^I1[0-5]'
                UNION ALL
                SELECT hn, regdate FROM opd.odiag WHERE diag REGEXP '^I1[0-5]'
             ) c ON s.hn = c.hn AND c.regdate >= s.suspect_date";
    
    $stmt_ht = $db->prepare($q_ht);
    $stmt_ht->execute(['s3' => $curr_start, 'e3' => $curr_end, 's4' => $curr_start, 'e4' => $curr_end]);
    $row_ht = $stmt_ht->fetch(PDO::FETCH_ASSOC);

    // -----------------------------------------------------------------
    // 📊 3. กราฟสถิติรายเดือน (นับเฉพาะกลุ่มที่เข้ามารับการคัดกรอง Z13.1 และ Z01.3)
    // -----------------------------------------------------------------
    $monthly_screen = array_fill(0, 12, 0);
    $q_m = "SELECT MONTH(regdate) as m, COUNT(DISTINCT hn) as total 
            FROM (
                SELECT hn, regdate FROM pcu.pdiag WHERE (diag LIKE 'Z131%' OR diag LIKE 'Z013%') AND regdate BETWEEN :s5 AND :e5
                UNION ALL
                SELECT hn, regdate FROM opd.odiag WHERE (diag LIKE 'Z131%' OR diag LIKE 'Z013%') AND regdate BETWEEN :s6 AND :e6
            ) t_screen 
            GROUP BY m";
            
    $stmt_m = $db->prepare($q_m);
    $stmt_m->execute(['s5' => $curr_start, 'e5' => $curr_end, 's6' => $curr_start, 'e6' => $curr_end]);
    
    while($rm = $stmt_m->fetch(PDO::FETCH_ASSOC)) {
        $m = (int)$rm['m'];
        $idx = ($m >= 10) ? ($m - 10) : ($m + 2); // จัดเรียง ต.ค. - ก.ย.
        if($idx >= 0 && $idx < 12) {
            $monthly_screen[$idx] = (int)$rm['total'];
        }
    }

    // -----------------------------------------------------------------
    // 🎯 สรุปผลการคำนวณ
    // -----------------------------------------------------------------
    $dm_rate = ($row_dm['total_suspect'] > 0) ? round(($row_dm['confirmed'] / $row_dm['total_suspect']) * 100, 2) : 0;
    $ht_rate = ($row_ht['total_suspect'] > 0) ? round(($row_ht['confirmed'] / $row_ht['total_suspect']) * 100, 2) : 0;

    echo json_encode([
        "status" => "success",
        "dm_rate" => $dm_rate,
        "ht_rate" => $ht_rate,
        "monthly_data" => $monthly_screen,
        "summary" => [
            "dm_suspect" => (int)$row_dm['total_suspect'],
            "dm_confirmed" => (int)$row_dm['confirmed'],
            "ht_suspect" => (int)$row_ht['total_suspect'],
            "ht_confirmed" => (int)$row_ht['confirmed']
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "SQL Error: " . $e->getMessage()]);
}
?>