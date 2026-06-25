<?php
// api/calculate_kpi.php
// ใช้สำหรับดึงข้อมูลจาก Himpro (HIS DB) มาคำนวณและอัปเดตลงตาราง kpi_data_cache (APP DB)

header("Content-Type: application/json; charset=UTF-8");

require_once '../config/database.php';

$database = new Database();
$app_conn = $database->getAppConnection();
$his_conn = $database->getHisConnection();

if (!$app_conn || !$his_conn) {
    echo json_encode(["status" => "error", "message" => "ไม่สามารถเชื่อมต่อฐานข้อมูลได้"]);
    exit();
}

$b_year = $_GET['year'] ?? '2569';
$month = date('n');
$quarter = ceil($month / 3);

$results = [];

// ดึงรายชื่อ KPI ทั้งหมดที่ใช้งานอยู่
$stmtKPI = $app_conn->prepare("SELECT id, kpi_number FROM kpi_master WHERE status = 'active'");
$stmtKPI->execute();
$kpi_list = $stmtKPI->fetchAll(PDO::FETCH_ASSOC);

// กำหนดวันที่สำหรับการคำนวณ (ปีงบประมาณ)
// สมมติฐาน: เริ่มต้นปีงบประมาณ เช่น 1 ต.ค. ปีก่อนหน้า ถึงปัจจุบัน
$start_date = ((int)$b_year - 544) . '-10-01'; 
$end_date = date('Y-m-d');

foreach ($kpi_list as $kpi) {
    $kpi_id = $kpi['id'];
    $kpi_num = $kpi['kpi_number'];

    // ค่าเริ่มต้น
    $status = "pending";
    $message = "ยังไม่สามารถ match ข้อมูลได้";
    $param_a = 0;
    $param_b = 0;
    $calculated_result = 0;

    try {
        if ($kpi_num === '1') {
            // KPI 1: อัตราส่วนการตายมารดาไทยต่อการเกิดมีชีพแสนคน
            $stmtA = $his_conn->prepare("
                SELECT COUNT(*) as maternal_deaths 
                FROM ipt_diag id
                JOIN ipt i ON id.an = i.an
                JOIN pt p ON i.hn = p.hn
                WHERE id.icd10 BETWEEN 'O00' AND 'O99' 
                AND i.dchtype = '04'
                AND p.nationality = '99'
            ");
            $stmtA->execute();
            $param_a = $stmtA->fetchColumn() ?? 0;

            $stmtB = $his_conn->prepare("
                SELECT COUNT(*) as live_births 
                FROM ipt_labour 
                WHERE result = '1'
            ");
            $stmtB->execute();
            $param_b = $stmtB->fetchColumn() ?? 1;

            $calculated_result = ($param_b > 0) ? ($param_a / $param_b) * 100000 : 0;
            $status = "success";
            $message = "คำนวณสำเร็จ";

        } elseif ($kpi_num === '11') {
            // KPI 11: อัตราตายของผู้ป่วยโรคหลอดเลือดสมอง (Stroke: I60-I64)
            $stmtA = $his_conn->prepare("
                SELECT COUNT(DISTINCT i.an) as stroke_deaths 
                FROM ipd.ipd i
                JOIN ipd.idiag idg ON i.an = idg.an
                WHERE idg.icd10 REGEXP '^(I60|I61|I62|I63|I64)'
                AND i.dsc_status IN ('8', '9') 
            ");
            $stmtA->execute();
            $param_a = $stmtA->fetchColumn() ?? 0;

            $stmtB = $his_conn->prepare("
                SELECT COUNT(DISTINCT i.an) as stroke_total 
                FROM ipd.ipd i
                JOIN ipd.idiag idg ON i.an = idg.an
                WHERE idg.icd10 REGEXP '^(I60|I61|I62|I63|I64)'
            ");
            $stmtB->execute();
            $param_b = $stmtB->fetchColumn() ?? 1;

            $calculated_result = ($param_b > 0) ? ($param_a / $param_b) * 100 : 0;
            $status = "success";
            $message = "คำนวณสำเร็จ";

        } elseif ($kpi_num === '15') {
            // KPI 15: มูลค่าการใช้ยาสมุนไพรสิทธิ UC
            $stmtA = $his_conn->prepare("
                SELECT SUM(d.price) as herb_uc_value
                FROM opd.drug_order_opd d
                JOIN opd.opd o ON d.hn = o.hn AND d.regdate = o.regdate
                JOIN hos.insclasses ins ON o.ptclass = ins.code
                WHERE o.regdate BETWEEN :start_date AND :end_date
                AND d.namedrug IN ('ยาฟ้าทะลายโจร', 'ยาขมิ้นชัน', 'ยาเถาวัลย์เปรียง', 'ยาหอมนวโกฐ') 
                AND (
                    ins.Name LIKE '%บัตรทอง%' OR 
                    ins.Name LIKE '%30 บาท%' OR 
                    ins.Name LIKE '%สปสช%' OR 
                    ins.Name LIKE '%uc%' OR 
                    ins.Name LIKE '%ในเขต%' OR 
                    ins.Name LIKE '%นอกเขต%'
                )
            ");
            $stmtA->execute([':start_date' => $start_date, ':end_date' => $end_date]);
            $param_a = $stmtA->fetchColumn() ?? 0;
            $param_b = 1; 
            $calculated_result = $param_a;
            $status = "success";
            $message = "คำนวณสำเร็จ";

        } elseif ($kpi_num === '18') {
            // KPI 18: อัตราตายผู้ป่วยติดเชื้อในกระแสเลือด (Sepsis)
            $stmtA = $his_conn->prepare("
                SELECT COUNT(DISTINCT i.an) as sepsis_deaths 
                FROM ipd.ipd i
                JOIN ipd.idiag idg ON i.an = idg.an
                JOIN pt.ptdead d ON i.hn = d.hn 
                WHERE idg.icd10 LIKE 'A41%'
            ");
            $stmtA->execute();
            $param_a = $stmtA->fetchColumn() ?? 0;

            $stmtB = $his_conn->prepare("
                SELECT COUNT(DISTINCT i.an) as sepsis_total 
                FROM ipd.ipd i
                JOIN ipd.idiag idg ON i.an = idg.an
                WHERE idg.icd10 LIKE 'A41%'
            ");
            $stmtB->execute();
            $param_b = $stmtB->fetchColumn() ?? 1;

            $calculated_result = ($param_b > 0) ? ($param_a / $param_b) * 100 : 0;
            $status = "success";
            $message = "คำนวณสำเร็จ";
        }

        // หากคำนวณสำเร็จ ให้บันทึกลง Cache
        if ($status === "success" || $status === "pending") {
            // บันทึกลงตาราง kpi_data_cache
            $stmtInsert = $app_conn->prepare("
                INSERT INTO kpi_data_cache (kpi_id, b_year, month, quarter, param_a, param_b, calculated_result) 
                VALUES (:kpi_id, :b_year, :month, :quarter, :param_a, :param_b, :calculated_result)
                ON DUPLICATE KEY UPDATE 
                    param_a = VALUES(param_a), param_b = VALUES(param_b), 
                    calculated_result = VALUES(calculated_result), last_updated = CURRENT_TIMESTAMP
            ");
            $stmtInsert->execute([
                ':kpi_id' => $kpi_id,
                ':b_year' => $b_year,
                ':month' => $month,
                ':quarter' => $quarter,
                ':param_a' => $param_a,
                ':param_b' => $param_b,
                ':calculated_result' => $calculated_result
            ]);
        }

        $results['kpi_' . $kpi_num] = [
            "status" => $status,
            "message" => $message,
            "a" => $param_a,
            "b" => $param_b,
            "result" => $calculated_result
        ];

    } catch (Exception $e) {
        $results['kpi_' . $kpi_num] = ["status" => "error", "message" => $e->getMessage()];
    }
}

echo json_encode([
    "status" => "success",
    "message" => "KPI Calculation completed.",
    "data" => $results
]);
?>
