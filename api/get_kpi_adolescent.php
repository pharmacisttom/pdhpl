<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // กำหนดช่วงปีงบประมาณ และวันที่ใช้คำนวณอายุ (ปรับเปลี่ยนได้ตามต้องการ)
    $curr_start = '2024-10-01'; 
    $curr_end   = '2026-09-30';
    $calc_date  = '2025-10-01'; 

    // -----------------------------------------------------------------
    // 🤰 1. อัตราการตั้งครรภ์ในวัยรุ่นหญิง (15-19 ปี)
    // -----------------------------------------------------------------
    // 1.1 ฐานประชากรหญิง
    $q_pop_f = "SELECT COUNT(DISTINCT p.hn) FROM pcu.person p
                WHERE p.sex = '2' AND p.typearea IN ('1', '3') 
                AND p.discharge = '9'
                AND TIMESTAMPDIFF(YEAR, p.birthdate, :cdate) BETWEEN 15 AND 19";
    $stmt_pop_f = $db->prepare($q_pop_f);
    $stmt_pop_f->execute(['cdate' => $calc_date]);
    $pop_female_15_19 = (int)$stmt_pop_f->fetchColumn();

    // 1.2 วัยรุ่นหญิงที่ตั้งครรภ์ (ดึงจากรหัสโรค O, Z34, Z35)
    $q_preg = "SELECT COUNT(DISTINCT p.hn)
               FROM pcu.person p
               INNER JOIN (
                   SELECT hn FROM pcu.pdiag WHERE diag REGEXP '^(O|Z34|Z35)' AND regdate BETWEEN :s1 AND :e1
                   UNION ALL
                   SELECT hn FROM opd.odiag WHERE diag REGEXP '^(O|Z34|Z35)' AND regdate BETWEEN :s2 AND :e2
               ) dx ON p.hn = dx.hn
               WHERE p.sex = '2' AND p.typearea IN ('1', '3') AND p.discharge = '9'
               AND TIMESTAMPDIFF(YEAR, p.birthdate, :cdate) BETWEEN 15 AND 19";
    $stmt_preg = $db->prepare($q_preg);
    $stmt_preg->execute(['s1'=>$curr_start, 'e1'=>$curr_end, 's2'=>$curr_start, 'e2'=>$curr_end, 'cdate'=>$calc_date]);
    $preg_count = (int)$stmt_preg->fetchColumn();

    // -----------------------------------------------------------------
    // 🧠 2. การคัดกรองซึมเศร้าวัยรุ่น (15-19 ปี)
    // -----------------------------------------------------------------
    // 2.1 ฐานประชากรรวมวัยรุ่น (ชาย-หญิง)
    $q_pop_all = "SELECT COUNT(DISTINCT p.hn) FROM pcu.person p
                  WHERE p.typearea IN ('1', '3') AND p.discharge = '9'
                  AND TIMESTAMPDIFF(YEAR, p.birthdate, :cdate) BETWEEN 15 AND 19";
    $stmt_pop_all = $db->prepare($q_pop_all);
    $stmt_pop_all->execute(['cdate' => $calc_date]);
    $pop_all_15_19 = (int)$stmt_pop_all->fetchColumn();

    // 2.2 วัยรุ่นที่ได้รับการคัดกรองซึมเศร้า (ดึงจากตาราง ppsp อย่างเดียว)
    $q_depress = "SELECT COUNT(DISTINCT p.hn)
                  FROM pcu.person p
                  INNER JOIN (
                      SELECT hn, regdate FROM pcu.ppsp WHERE code LIKE '1I8%' AND regdate BETWEEN :s1 AND :e1
                  ) screen ON p.hn = screen.hn
                  WHERE p.typearea IN ('1', '3') AND p.discharge = '9'
                  AND TIMESTAMPDIFF(YEAR, p.birthdate, :cdate) BETWEEN 15 AND 19";
    $stmt_depress = $db->prepare($q_depress);
    $stmt_depress->execute(['s1'=>$curr_start, 'e1'=>$curr_end, 'cdate'=>$calc_date]);
    $depress_screened = (int)$stmt_depress->fetchColumn();

    // -----------------------------------------------------------------
    // 📊 กราฟสถิติรายเดือน (ซึมเศร้า)
    // -----------------------------------------------------------------
    $monthly_visits = array_fill(0, 12, 0);
    $q_m = "SELECT MONTH(screen.regdate) as m, COUNT(DISTINCT p.hn) as total 
            FROM pcu.person p
            INNER JOIN (
                SELECT hn, regdate FROM pcu.ppsp WHERE code LIKE '1I8%' AND regdate BETWEEN :s1 AND :e1
            ) screen ON p.hn = screen.hn
            WHERE TIMESTAMPDIFF(YEAR, p.birthdate, :cdate) BETWEEN 15 AND 19
            GROUP BY m";
    $stmt_m = $db->prepare($q_m);
    $stmt_m->execute(['s1'=>$curr_start, 'e1'=>$curr_end, 'cdate'=>$calc_date]);
    
    while($rm = $stmt_m->fetch(PDO::FETCH_ASSOC)) {
        $m = (int)$rm['m'];
        // จัดเรียงเดือนตามปีงบประมาณ (ต.ค. = index 0)
        $idx = ($m >= 10) ? ($m - 10) : ($m + 2);
        if($idx >= 0 && $idx < 12) $monthly_visits[$idx] = (int)$rm['total'];
    }

    // -----------------------------------------------------------------
    // 🎯 สรุปผล
    // -----------------------------------------------------------------
    $preg_rate = ($pop_female_15_19 > 0) ? round(($preg_count / $pop_female_15_19) * 1000, 2) : 0;
    $depress_rate = ($pop_all_15_19 > 0) ? round(($depress_screened / $pop_all_15_19) * 100, 2) : 0;

    echo json_encode([
        "status" => "success",
        "preg_rate" => $preg_rate,
        "depress_rate" => $depress_rate,
        "monthly_data" => $monthly_visits,
        "summary" => [
            "preg_count" => $preg_count,
            "pop_female" => $pop_female_15_19,
            "depress_screened" => $depress_screened,
            "pop_all" => $pop_all_15_19
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "SQL Error: " . $e->getMessage()]);
}
?>