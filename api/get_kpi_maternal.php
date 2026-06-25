<?php
// =========================================================
// ไฟล์: /api/get_kpi_maternal.php
// หน้าที่: ประมวลผล KPI แม่และเด็ก และวิเคราะห์ Type Area 1,3
// =========================================================
session_start();
header("Content-Type: application/json; charset=UTF-8");

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // ⚠️ ช่วงเวลาทดสอบ (ปรับเป็นปีงบ 69 เมื่อใช้งานจริง)
    $start_date = '2025-10-01';
    $end_date = '2026-09-30';

    // ==========================================
    // 1. คำนวณตัวชี้วัดหลัก (เฉพาะ Type Area 1, 3)
    // ==========================================

    // --- ตัวหาร: เด็กปฐมวัยในเขต (1,3) ---
    $q_target = "SELECT COUNT(DISTINCT p_code) as total_target 
                 FROM pcu.person 
                 WHERE typearea IN ('1', '3')
                 AND birthdate IS NOT NULL AND birthdate != '0000-00-00'";
    $stmt_target = $db->prepare($q_target);
    $stmt_target->execute();
    $row_target = $stmt_target->fetch(PDO::FETCH_ASSOC);
    $target_children = ($row_target['total_target'] > 0) ? $row_target['total_target'] : 1; 

    // --- KPI 2.1: พัฒนาการสมวัย (1B260) ในเขต ---
    $q_dev = "SELECT COUNT(DISTINCT s.hn) as total_dev 
              FROM pcu.ppsp s
              INNER JOIN pcu.person p ON s.hn = p.p_code
              WHERE s.regdate BETWEEN :start AND :end 
              AND p.typearea IN ('1', '3')
              AND (s.code = '1B260' OR s.code LIKE '1E%')"; 
    $stmt_dev = $db->prepare($q_dev);
    $stmt_dev->execute(['start' => $start_date, 'end' => $end_date]);
    $row_dev = $stmt_dev->fetch(PDO::FETCH_ASSOC);
    $dev_percent = round(($row_dev['total_dev'] / $target_children) * 100, 2);

    // --- ANC < 12 Weeks ในเขต ---
    $q_anc = "SELECT 
                COUNT(DISTINCT a.hn) as total_anc,
                SUM(CASE WHEN DATEDIFF(a.regdate, a.lmp)/7 <= 12 THEN 1 ELSE 0 END) as anc_12w
              FROM pcu.anc a
              INNER JOIN pcu.person p ON a.hn = p.p_code
              WHERE a.regdate BETWEEN :start AND :end AND p.typearea IN ('1', '3')";
    $stmt_anc = $db->prepare($q_anc);
    $stmt_anc->execute(['start' => $start_date, 'end' => $end_date]);
    $row_anc = $stmt_anc->fetch(PDO::FETCH_ASSOC);
    $anc_percent = ($row_anc['total_anc'] > 0) ? round(($row_anc['anc_12w'] / $row_anc['total_anc']) * 100, 2) : 0;

    // --- วัคซีน MMR ในเขต ---
    $q_vac = "SELECT COUNT(DISTINCT v.hn) as total_vac 
              FROM pcu.ptvaccine v
              INNER JOIN pcu.person p ON v.hn = p.p_code
              WHERE v.regdate BETWEEN :start AND :end 
              AND p.typearea IN ('1', '3')
              AND (v.code IN ('061', '062', 'V039', 'V040') OR v.code LIKE '%MMR%')";
    $stmt_vac = $db->prepare($q_vac);
    $stmt_vac->execute(['start' => $start_date, 'end' => $end_date]);
    $row_vac = $stmt_vac->fetch(PDO::FETCH_ASSOC);
    $vaccine_percent = round(($row_vac['total_vac'] / $target_children) * 100, 2);

    // ==========================================
    // 2. วิเคราะห์ Type Area (Workload vs KPI)
    // ==========================================

    // วิเคราะห์ ANC
    $q_anc_ta = "SELECT 
                    SUM(CASE WHEN p.typearea IN ('1', '3') THEN 1 ELSE 0 END) as in_target,
                    SUM(CASE WHEN p.typearea NOT IN ('1', '3') OR p.typearea IS NULL THEN 1 ELSE 0 END) as out_target
                 FROM pcu.anc a
                 LEFT JOIN pcu.person p ON a.hn = p.p_code
                 WHERE a.regdate BETWEEN :start AND :end";
    $stmt_anc_ta = $db->prepare($q_anc_ta);
    $stmt_anc_ta->execute(['start' => $start_date, 'end' => $end_date]);
    $row_anc_ta = $stmt_anc_ta->fetch(PDO::FETCH_ASSOC);

    // วิเคราะห์ DSPM
    $q_dev_ta = "SELECT 
                    SUM(CASE WHEN p.typearea IN ('1', '3') THEN 1 ELSE 0 END) as in_target,
                    SUM(CASE WHEN p.typearea NOT IN ('1', '3') OR p.typearea IS NULL THEN 1 ELSE 0 END) as out_target
                 FROM pcu.ppsp s
                 LEFT JOIN pcu.person p ON s.hn = p.p_code
                 WHERE s.regdate BETWEEN :start AND :end 
                 AND (s.code = '1B260' OR s.code LIKE '1E%')";
    $stmt_dev_ta = $db->prepare($q_dev_ta);
    $stmt_dev_ta->execute(['start' => $start_date, 'end' => $end_date]);
    $row_dev_ta = $stmt_dev_ta->fetch(PDO::FETCH_ASSOC);

    // วิเคราะห์ EPI
    $q_vac_ta = "SELECT 
                    SUM(CASE WHEN p.typearea IN ('1', '3') THEN 1 ELSE 0 END) as in_target,
                    SUM(CASE WHEN p.typearea NOT IN ('1', '3') OR p.typearea IS NULL THEN 1 ELSE 0 END) as out_target
                 FROM pcu.ptvaccine v
                 LEFT JOIN pcu.person p ON v.hn = p.p_code
                 WHERE v.regdate BETWEEN :start AND :end 
                 AND (v.code IN ('061', '062', 'V039', 'V040') OR v.code LIKE '%MMR%')";
    $stmt_vac_ta = $db->prepare($q_vac_ta);
    $stmt_vac_ta->execute(['start' => $start_date, 'end' => $end_date]);
    $row_vac_ta = $stmt_vac_ta->fetch(PDO::FETCH_ASSOC);

    // ==========================================
    // 3. ข้อมูลกราฟรายเดือน (ANC)
    // ==========================================
    $chart_labels = ['ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.'];
    $chart_data = array_fill(0, 12, 0); 
    $q_chart = "SELECT MONTH(regdate) as m, COUNT(DISTINCT hn) as total FROM pcu.anc 
                WHERE regdate BETWEEN :start AND :end GROUP BY m";
    $stmt_chart = $db->prepare($q_chart);
    $stmt_chart->execute(['start' => $start_date, 'end' => $end_date]);
    while ($row = $stmt_chart->fetch(PDO::FETCH_ASSOC)) {
        $m = (int)$row['m'];
        $index = ($m >= 10) ? ($m - 10) : ($m + 2);
        $chart_data[$index] = (int)$row['total'];
    }

    // ==========================================
    // 4. ส่งออกผลลัพธ์ JSON
    // ==========================================
    echo json_encode([
        "status" => "success",
        "anc_percent" => number_format($anc_percent, 1),
        "dev_percent" => number_format($dev_percent, 1),
        "vaccine_percent" => number_format($vaccine_percent, 1),
        "death_count" => 0,
        "chart" => [
            "labels" => $chart_labels,
            "data_anc" => $chart_data
        ],
        "typearea_analysis" => [
            "labels" => ['ฝากครรภ์ (ANC)', 'คัดกรองเด็ก (DSPM)', 'ฉีดวัคซีน (EPI)'],
            "in_target" => [(int)$row_anc_ta['in_target'], (int)$row_dev_ta['in_target'], (int)$row_vac_ta['in_target']],
            "out_target" => [(int)$row_anc_ta['out_target'], (int)$row_dev_ta['out_target'], (int)$row_vac_ta['out_target']]
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "DB Error: " . $e->getMessage()]);
}
?>