<?php
// ไฟล์: api/export_ncd_target.php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$type = isset($_GET['type']) ? $_GET['type'] : 'dm';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="Target_NCD_' . strtoupper($type) . '_2569.csv"');
echo "\xEF\xBB\xBF"; 

$output = fopen('php://output', 'w');
fputcsv($output, ['ลำดับ', 'HN', 'เลขบัตรประชาชน (CID)', 'ชื่อ', 'นามสกุล', 'อายุ (ปี)', 'Type Area']);

try {
    $curr_start = '2024-10-01'; 
    $curr_end   = '2026-09-30';

    if ($type == 'dm') {
        $diag_sick = "^E1[0-4]"; 
        $diag_screen = "Z131%";  
    } else {
        $diag_sick = "^I1[0-5]"; 
        $diag_screen = "Z013%";  
    }

    // ปรับชื่อคอลัมน์ให้ตรงตามไฟล์ person.sql และตัดการ JOIN ตาราง home ออกชั่วคราวเพื่อลด Error h_code
    $query = "SELECT 
                p.hn, p.id_card as cid, p.fname, p.lname, 
                TIMESTAMPDIFF(YEAR, p.birthdate, '2025-10-01') as age, 
                p.typearea
              FROM pcu.person p
              WHERE p.typearea IN ('1', '3')
              AND p.discharge = '9'
              AND TIMESTAMPDIFF(YEAR, p.birthdate, '2025-10-01') >= 35
              AND p.hn NOT IN (
                  SELECT hn FROM pcu.pdiag WHERE diag REGEXP :diag_sick1
                  UNION
                  SELECT hn FROM opd.odiag WHERE diag REGEXP :diag_sick2
              )
              AND p.hn NOT IN (
                  SELECT hn FROM pcu.pdiag WHERE diag LIKE :diag_screen1 AND regdate BETWEEN :s1 AND :e1
                  UNION
                  SELECT hn FROM opd.odiag WHERE diag LIKE :diag_screen2 AND regdate BETWEEN :s2 AND :e2
              )
              ORDER BY p.fname ASC";

    $stmt = $db->prepare($query);
    $stmt->execute([
        'diag_sick1' => $diag_sick, 'diag_sick2' => $diag_sick,
        'diag_screen1' => $diag_screen, 's1' => $curr_start, 'e1' => $curr_end,
        'diag_screen2' => $diag_screen, 's2' => $curr_start, 'e2' => $curr_end
    ]);

    $i = 1;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [$i++, $row['hn'], "'" . $row['cid'], $row['fname'], $row['lname'], $row['age'], $row['typearea']]);
    }
} catch (PDOException $e) {
    fputcsv($output, ['เกิดข้อผิดพลาด:', $e->getMessage()]);
}
fclose($output);
exit();
?>