<?php
// import_all_kpis.php
require_once __DIR__ . '/config/database.php';

$database = new Database();
$app_conn = $database->getAppConnection();

if (!$app_conn) {
    die("ไม่สามารถเชื่อมต่อฐานข้อมูล KPI ได้");
}

$kpis = [
    ['1', 'อัตราส่วนการตายมารดาไทยต่อการเกิดมีชีพแสนคน', 'PP&P Excellence', 15, 'rate', '<='],
    ['2.1', 'ร้อยละของเด็ก อายุ 0 - 5 ปี มีพัฒนาการสมวัย', 'PP&P Excellence', 88, 'percentage', '>='],
    ['2.2', 'ร้อยละของเด็ก อายุ 0 – 5 ปี ฟันดีไม่มีผุ (Cavity Free)', 'PP&P Excellence', 80, 'percentage', '>='],
    ['3', 'ร้อยละของชุมชนมีการดำเนินการจัดการสุขภาพที่เหมาะสมกับประชาชน', 'PP&P Excellence', 90, 'percentage', '>='],
    ['4', 'อัตราความรอบรู้ด้านสุขภาพของประชาชนไทย อายุ 15 ปี ขึ้นไป', 'PP&P Excellence', 82, 'percentage', '>='],
    ['5', 'ระดับความรอบรู้สุขภาพของประชาชนเรื่องโรคอุบัติใหม่และอุบัติซ้ำเพิ่มขึ้น', 'PP&P Excellence', 5, 'percentage', '>='],
    ['6.1', 'ร้อยละการตรวจติดตามยืนยันวินิจฉัยกลุ่มสงสัยป่วยโรคเบาหวาน', 'PP&P Excellence', 70, 'percentage', '>='],
    ['6.2', 'ร้อยละการตรวจติดตามยืนยันวินิจฉัยกลุ่มสงสัยป่วยโรคความดันโลหิตสูง', 'PP&P Excellence', 80, 'percentage', '>='],
    ['7', 'อัตราการเสียชีวิตและบาดเจ็บจากอุบัติเหตุทางถนนในกลุ่มเด็กและเยาวชนลดลง', 'PP&P Excellence', 3, 'percentage', '>='],
    ['8', 'ร้อยละความครอบคลุมของวัคซีนป้องกันหัด-คางทูม-หัดเยอรมัน เข็มที่ 2 (MMR2)', 'PP&P Excellence', 95, 'percentage', '>='],
    ['9.1', 'ร้อยละของโรงพยาบาลที่พัฒนาอนามัยสิ่งแวดล้อมได้ตามเกณฑ์ GREEN & CLEAN', 'PP&P Excellence', 95, 'percentage', '>='],
    ['9.2', 'ร้อยละของโรงพยาบาลที่พัฒนาอนามัยสิ่งแวดล้อมได้ตามเกณฑ์ (ระดับท้าทาย)', 'PP&P Excellence', 30, 'percentage', '>='],
    ['10', 'จำนวนหน่วยบริการปฐมภูมิที่ผ่านเกณฑ์คุณภาพมาตรฐาน', 'Service Excellence', 4500, 'count', '>='],
    ['11', 'อัตราตายของผู้ป่วยโรคหลอดเลือดสมอง (Stroke: I60-I64)', 'Service Excellence', 7, 'percentage', '<='],
    ['12', 'อัตราความสำเร็จการรักษาผู้ป่วยวัณโรคปอดรายใหม่', 'Service Excellence', 88, 'percentage', '>='],
    ['13', 'อัตราตายทารกแรกเกิดอายุน้อยกว่าหรือเท่ากับ 28 วัน', 'Service Excellence', 3.6, 'rate', '<='],
    ['14', 'ร้อยละของประชาชนที่มารับบริการปฐมภูมิได้รับการรักษาด้วยแพทย์แผนไทย', 'Service Excellence', 50, 'percentage', '>='],
    ['15', 'มูลค่าการใช้ยาสมุนไพรในสิทธิ UC', 'Service Excellence', 2000, 'count', '>='],
    ['16.1', 'อัตราการฆ่าตัวตายสำเร็จ', 'Service Excellence', 7.8, 'rate', '<='],
    ['16.2', 'ร้อยละของผู้พยายามฆ่าตัวตายเข้าถึงบริการที่มีประสิทธิภาพ', 'Service Excellence', 70, 'percentage', '>='],
    ['17.1', 'ร้อยละของผู้ป่วยจิตเวชยาเสพติดก่อความรุนแรงได้รับการดูแลต่อเนื่อง', 'Service Excellence', 40, 'percentage', '>='],
    ['17.2', 'ร้อยละการเข้าถึงบริการของผู้ป่วยโรคจิตเวชยาเสพติด', 'Service Excellence', 40, 'percentage', '>='],
    ['18', 'อัตราตายผู้ป่วยติดเชื้อในกระแสเลือดแบบรุนแรงชนิด community-acquired', 'Service Excellence', 24, 'percentage', '<='],
    ['19', 'อัตราตายของผู้ป่วยโรคกล้ามเนื้อหัวใจตายเฉียบพลันชนิด STEMI', 'Service Excellence', 9, 'percentage', '<='],
    ['20', 'ร้อยละผู้ป่วยไตเรื้อรัง stage 5 รายใหม่ ที่ลดลงจากปีงบประมาณก่อนหน้า', 'Service Excellence', 10, 'percentage', '>='],
    ['21', 'อัตราส่วนของผู้บริจาคอวัยวะสมองตายต่อจำนวนผู้ป่วยเสียชีวิต', 'Service Excellence', 10, 'percentage', '>='],
    ['22.1', 'ร้อยละของผู้ป่วยมะเร็งได้รับการรักษาด้วยการผ่าตัดภายใน 4 สัปดาห์', 'Service Excellence', 70, 'percentage', '>='],
    ['22.2', 'ร้อยละของผู้ป่วยมะเร็งได้รับการรักษาด้วยเคมีบำบัดภายใน 6 สัปดาห์', 'Service Excellence', 70, 'percentage', '>='],
    ['22.3', 'ร้อยละของผู้ป่วยมะเร็งได้รับการรักษาด้วยรังสีรักษาภายใน 6 สัปดาห์', 'Service Excellence', 60, 'percentage', '>='],
    ['23', 'ร้อยละของผู้ป่วยยาเสพติดเข้าสู่กระบวนการบำบัดรักษา', 'Service Excellence', 70, 'percentage', '>='],
    ['24', 'ร้อยละของผู้ป่วยวิกฤต เข้าถึงบริการการแพทย์ฉุกเฉิน', 'Service Excellence', 29, 'percentage', '>='],
    ['25', 'ร้อยละผู้ป่วยในพระบรมราชานุเคราะห์ ได้รับการดูแลอย่างมีคุณภาพ', 'Service Excellence', 90, 'percentage', '>='],
    ['26', 'สัดส่วนการกระจายแพทย์ในโรงพยาบาลชุมชนสังกัด สป.สธ.', 'People Excellence', 70, 'percentage', '>='],
    ['27', 'ร้อยละของหน่วยงานที่ผ่านเกณฑ์มาตรฐานความมั่นคงปลอดภัยไซเบอร์', 'Governance Excellence', 100, 'percentage', '>='],
    ['28.1', 'ร้อยละของหน่วยงานในสังกัด สธ. ผ่านเกณฑ์การประเมิน ITA', 'Governance Excellence', 94, 'percentage', '>='],
    ['28.2', 'ร้อยละของส่วนราชการผ่านเกณฑ์ประเมินระบบการควบคุมภายใน', 'Governance Excellence', 85, 'percentage', '>='],
    ['29.1', 'ร้อยละของ รพศ. รพท. มีคุณภาพผ่านการรับรองตามมาตรฐาน', 'Governance Excellence', 100, 'percentage', '>='],
    ['29.2', 'ร้อยละของ รพช. มีคุณภาพผ่านการรับรองตามมาตรฐาน', 'Governance Excellence', 92, 'percentage', '>='],
    ['30.1', 'ร้อยละของโรงพยาบาลผ่านเกณฑ์การตรวจทางห้องปฏิบัติการอย่างสมเหตุผล', 'Governance Excellence', 50, 'percentage', '>='],
    ['30.2', 'ร้อยละของผู้ป่วยเบาหวาน ได้รับการตรวจ HbA1c ซ้ำภายใน 90 วัน ไม่เกิน', 'Governance Excellence', 5, 'percentage', '<='],
    ['31', 'ความแตกต่างการใช้สิทธิเมื่อใช้บริการผู้ป่วยในของผู้มีสิทธิ UHC', 'Health Economy Excellence', 1.5, 'percentage', '<='],
    ['32', 'ประชาชนสามารถเข้าถึงสิทธิในระบบหลักประกันสุขภาพถ้วนหน้า', 'Health Economy Excellence', 99.55, 'percentage', '>='],
    ['33', 'ร้อยละของหน่วยบริการที่ผ่านเกณฑ์ประเมิน TPS', 'Health Economy Excellence', 55, 'percentage', '>='],
    ['34', 'อัตราการเพิ่มขึ้นของจำนวนสถานประกอบการท่องเที่ยวเชิงสุขภาพ', 'Health Economy Excellence', 20, 'percentage', '>='],
    ['35', 'ร้อยละที่เพิ่มของกลุ่มอุตสาหกรรมการแพทย์และการท่องเที่ยวเชิงสุขภาพ', 'Health Economy Excellence', 10, 'percentage', '>='],
    ['36.1', 'ร้อยละผลิตภัณฑ์สุขภาพชุมชนได้รับการอนุญาต', 'Health Economy Excellence', 10, 'percentage', '>=']
];

foreach ($kpis as $k) {
    $stmt = $app_conn->prepare("INSERT INTO kpi_master (kpi_number, kpi_name, excellence_category, target_value, target_type, target_operator) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE kpi_name=VALUES(kpi_name), target_value=VALUES(target_value), target_type=VALUES(target_type), target_operator=VALUES(target_operator), excellence_category=VALUES(excellence_category)");
    $stmt->execute($k);
}

echo "นำเข้าข้อมูล KPI ครบทั้งหมดสำเร็จแล้ว!";
