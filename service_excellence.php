<?php
session_start();
if (!isset($_SESSION['kpi_user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'config/database.php';
$database = new Database();
$app_conn = $database->getAppConnection();

$category_name = 'Service Excellence';

$stmt = $app_conn->prepare("
    SELECT m.*, c.calculated_result, c.last_updated
    FROM kpi_master m
    LEFT JOIN kpi_data_cache c ON m.id = c.kpi_id AND c.b_year = '2569'
    WHERE m.excellence_category = :cat
    ORDER BY CAST(m.kpi_number AS DECIMAL), m.kpi_number ASC
");
$stmt->execute([':cat' => $category_name]);
$kpis = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Excellence - ระบบบริหารจัดการ KPI 2569</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f4f6f9; overflow-x: hidden; }
        .main-content { width: calc(100% - 280px); padding: 20px 30px; height: 100vh; overflow-y: auto; }
        .kpi-card { border-left: 4px solid #198754; transition: all 0.2s; }
        .kpi-card:hover { transform: translateY(-2px); box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1) !important; }
    </style>
</head>
<body class="d-flex">

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm border-start border-success border-4">
            <h4 class="mb-0 text-secondary fw-bold"><i class="fa-solid fa-hand-holding-medical text-success me-2"></i> Service Excellence (ด้านบริการเป็นเลิศ)</h4>
            <div><span class="badge bg-success fs-6 px-3 py-2">ปีงบประมาณ 2569</span></div>
        </div>

        <div class="row" id="kpi-list">
            <?php foreach ($kpis as $kpi): ?>
            <div class="col-md-12 mb-3">
                <div class="card kpi-card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="text-success fw-bold mb-1">ตัวชี้วัดที่ <?php echo htmlspecialchars($kpi['kpi_number'] . '. ' . $kpi['kpi_name']); ?></h5>
                                <p class="text-muted small mb-0">เป้าหมาย: <?php echo htmlspecialchars($kpi['target_operator'] . ' ' . $kpi['target_value'] . ' ' . ($kpi['target_type'] == 'percentage' ? '%' : '')); ?></p>
                            </div>
                            <div class="text-end">
                                <?php if ($kpi['calculated_result'] !== null): ?>
                                    <h3 class="text-secondary fw-bold mb-0"><?php echo number_format($kpi['calculated_result'], 2); ?><?php echo ($kpi['target_type'] == 'percentage' ? '%' : ''); ?></h3>
                                    <small class="text-muted" style="font-size: 0.7rem;">อัปเดต: <?php echo date('d/m/Y H:i', strtotime($kpi['last_updated'])); ?></small>
                                <?php else: ?>
                                    <h5 class="text-warning fw-bold mb-0">รอผลประมวล</h5>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
