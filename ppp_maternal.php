<?php
session_start();
// ตรวจสอบสิทธิ์ (เปิดใช้งานเมื่อทำระบบ Login เสร็จ)
/*
if (!isset($_SESSION['kpi_user_id'])) {
    header("Location: login.php");
    exit();
}
*/
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KPI แม่และเด็ก - รพ.ปลวกแดง</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css"> 
</head>
<body class="d-flex">

    <?php include 'sidebar.php'; ?> 

    <div class="main-content flex-grow-1 p-4 bg-light" style="min-height: 100vh;">
        
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm border-start border-pink border-4" style="border-left-color: #d63384 !important;">
            <div>
                <h4 class="mb-1 text-secondary fw-bold"><i class="fa-solid fa-baby text-danger me-2"></i> ตัวชี้วัดกลุ่มแม่และเด็ก (MCH)</h4>
                <small class="text-muted">ข้อมูลเปรียบเทียบภาระงานและเป้าหมาย KPI (Type Area 1, 3)</small>
            </div>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-sm btn-success dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-file-excel me-1"></i> ส่งออกภาระงาน (Excel)
                    </button>
                    <ul class="dropdown-menu shadow-sm">
                        <li><a class="dropdown-item" href="export_maternal.php?kpi=anc"><i class="fa-solid fa-person-pregnant text-pink me-2"></i> ภาระงาน ANC</a></li>
                        <li><a class="dropdown-item" href="export_maternal.php?kpi=dev"><i class="fa-solid fa-child-reaching text-info me-2"></i> ภาระงาน DSPM</a></li>
                        <li><a class="dropdown-item" href="export_maternal.php?kpi=vac"><i class="fa-solid fa-syringe text-success me-2"></i> ภาระงาน EPI</a></li>
                    </ul>
                </div>
                <a href="index.php" class="btn btn-sm btn-outline-secondary shadow-sm"><i class="fa-solid fa-arrow-left"></i> กลับ</a>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stat-card shadow-sm border-0 h-100" style="border-left: 4px solid #e83e8c;">
                    <div class="card-body">
                        <p class="text-muted mb-1 small fw-bold">ฝากครรภ์ < 12 สัปดาห์ (ในเขต)</p>
                        <h3 class="mb-0 fw-bold" style="color: #e83e8c;" id="anc12Weeks">... %</h3>
                        <small class="text-success">เป้าหมาย > 75%</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card shadow-sm border-0 h-100" style="border-left: 4px solid #0dcaf0;">
                    <div class="card-body">
                        <p class="text-muted mb-1 small fw-bold">เด็กพัฒนาการสมวัย (ในเขต)</p>
                        <h3 class="text-info mb-0 fw-bold" id="childDev">... %</h3>
                        <small class="text-success">เป้าหมาย > 85%</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card shadow-sm border-0 h-100" style="border-left: 4px solid #198754;">
                    <div class="card-body">
                        <p class="text-muted mb-1 small fw-bold">รับวัคซีน MMR ตามเกณฑ์ (ในเขต)</p>
                        <h3 class="text-success mb-0 fw-bold" id="vaccineCov">... %</h3>
                        <small class="text-success">เป้าหมาย > 90%</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card shadow-sm border-0 h-100" style="border-left: 4px solid #dc3545;">
                    <div class="card-body">
                        <p class="text-muted mb-1 small fw-bold">อัตราส่วนมารดาตาย</p>
                        <h3 class="text-danger mb-0 fw-bold" id="maternalDeath">0 ราย</h3>
                        <small class="text-muted">เป้าหมาย 0 ราย</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-7">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 border-bottom border-light">
                        <h6 class="mb-0 fw-bold text-secondary"><i class="fa-solid fa-layer-group text-primary me-2"></i> วิเคราะห์ภาระงาน (ในเขต vs นอกเขต)</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="typeAreaChart" height="250"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 border-bottom border-light">
                        <h6 class="mb-0 fw-bold text-secondary"><i class="fa-solid fa-chart-pie text-info me-2"></i> สัดส่วนผลงานพัฒนาการเด็ก (DSPM)</h6>
                    </div>
                    <div class="card-body d-flex justify-content-center align-items-center">
                        <canvas id="devChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 border-bottom border-light">
                        <h6 class="mb-0 fw-bold text-secondary"><i class="fa-solid fa-chart-line text-danger me-2"></i> แนวโน้มหญิงตั้งครรภ์รายใหม่รายเดือน (รวมทุกกลุ่ม)</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="maternalChart" height="80"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/maternal.js"></script> 
</body>
</html>