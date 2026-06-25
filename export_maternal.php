<?php
session_start();
require_once 'config/database.php';

// 1. ตั้งค่า Header สำหรับ Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Maternal_Workload_PDPA_" . date("Ymd_Hi") . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// 2. ส่งค่า BOM เพื่อให้ Excel อ่านภาษาไทย UTF-8 ได้
echo "\xEF\xBB\xBF";

$database = new Database();
$db = $database->getConnection();
$kpi_type = isset($_GET['kpi']) ? $_GET['kpi'] : '';

$start_date = '2015-10-01';
$end_date = '2026-09-30';

echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
echo '<head><meta http-equiv="Content-type" content="text/html;charset=utf-8" /></head>';
echo '<body>';
echo '<table border="1">';

try {
    if ($kpi_type === 'anc') {
        echo '<tr><th colspan="6" style="background-color:#e83e8c; color:white;">รายงานภาระงาน ฝากครรภ์รายใหม่ (ANC) - [PDPA COMPLIANT]</th></tr>';
        echo '<tr style="background-color:#f8f9fa;">
                <th>HN (รหัสบริการ)</th><th>วันที่รับบริการ</th>
                <th>อายุครรภ์ (สัปดาห์)</th><th>Type Area</th><th>สถานะ KPI</th><th>ผลประเมิน < 12 Wk</th>
              </tr>';

        $q = "SELECT a.hn, p.typearea, a.regdate, a.lmp,
                     FLOOR(DATEDIFF(a.regdate, a.lmp)/7) as ga_weeks
              FROM pcu.anc a
              LEFT JOIN pcu.person p ON a.hn = p.p_code
              WHERE a.regdate BETWEEN :start AND :end AND a.lmp != '0000-00-00'
              ORDER BY a.regdate DESC";
              
        $stmt = $db->prepare($q);
        $stmt->execute(['start' => $start_date, 'end' => $end_date]);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $typearea = $row['typearea'] ?? '-';
            $kpi_status = in_array($typearea, ['1', '3']) ? 'นับผลงาน (ในเขต)' : 'ภาระงาน (นอกเขต)';
            $pass_12w = ($row['ga_weeks'] <= 12) ? 'ผ่านเกณฑ์' : 'ไม่ผ่าน';

            echo "<tr>
                    <td style='mso-number-format:\"\@\";'>{$row['hn']}</td>
                    <td>{$row['regdate']}</td>
                    <td>{$row['ga_weeks']}</td>
                    <td>{$typearea}</td>
                    <td>{$kpi_status}</td>
                    <td>{$pass_12w}</td>
                  </tr>";
        }

    } elseif ($kpi_type === 'dev') {
        echo '<tr><th colspan="5" style="background-color:#0dcaf0; color:white;">รายงานภาระงาน คัดกรองพัฒนาการเด็ก - [PDPA COMPLIANT]</th></tr>';
        echo '<tr style="background-color:#f8f9fa;">
                <th>HN (รหัสบริการ)</th><th>วันที่คัดกรอง</th><th>รหัสบริการ</th><th>Type Area</th><th>สถานะ KPI</th>
              </tr>';

        $q = "SELECT s.hn, p.typearea, s.regdate, s.code
              FROM pcu.ppsp s
              LEFT JOIN pcu.person p ON s.hn = p.p_code
              WHERE s.regdate BETWEEN :start AND :end 
              AND (s.code = '1B260' OR s.code LIKE '1E%' OR s.name LIKE '%พัฒนาการ%')
              ORDER BY s.regdate DESC";
              
        $stmt = $db->prepare($q);
        $stmt->execute(['start' => $start_date, 'end' => $end_date]);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $typearea = $row['typearea'] ?? '-';
            $kpi_status = in_array($typearea, ['1', '3']) ? 'นับผลงาน (ในเขต)' : 'ภาระงาน (นอกเขต)';

            echo "<tr>
                    <td style='mso-number-format:\"\@\";'>{$row['hn']}</td>
                    <td>{$row['regdate']}</td>
                    <td>{$row['code']}</td>
                    <td>{$typearea}</td>
                    <td>{$kpi_status}</td>
                  </tr>";
        }

    } elseif ($kpi_type === 'vac') {
        echo '<tr><th colspan="5" style="background-color:#198754; color:white;">รายงานภาระงาน การรับวัคซีน (EPI) - [PDPA COMPLIANT]</th></tr>';
        echo '<tr style="background-color:#f8f9fa;">
                <th>HN (รหัสบริการ)</th><th>วันที่ฉีดวัคซีน</th><th>รหัสวัคซีน</th><th>Type Area</th><th>สถานะ KPI</th>
              </tr>';

        $q = "SELECT v.hn, p.typearea, v.regdate, v.code
              FROM pcu.ptvaccine v
              LEFT JOIN pcu.person p ON v.hn = p.p_code
              WHERE v.regdate BETWEEN :start AND :end 
              AND (v.code IN ('061', '062', '063', 'V039', 'V040', 'V041') OR v.code LIKE '%MMR%')
              ORDER BY v.regdate DESC";
              
        $stmt = $db->prepare($q);
        $stmt->execute(['start' => $start_date, 'end' => $end_date]);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $typearea = $row['typearea'] ?? '-';
            $kpi_status = in_array($typearea, ['1', '3']) ? 'นับผลงาน (ในเขต)' : 'ภาระงาน (นอกเขต)';

            echo "<tr>
                    <td style='mso-number-format:\"\@\";'>{$row['hn']}</td>
                    <td>{$row['regdate']}</td>
                    <td>{$row['code']}</td>
                    <td>{$typearea}</td>
                    <td>{$kpi_status}</td>
                  </tr>";
        }
    }
} catch (PDOException $e) {
    echo "<tr><td colspan='5'>Error: " . $e->getMessage() . "</td></tr>";
}

echo '</table></body></html>';