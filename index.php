<?php
session_start();
// ตรวจสอบสิทธิ์การเข้าถึง (ถ้ายังไม่ Login ให้เด้งไปหน้า login.php)
if (!isset($_SESSION['kpi_user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบบริหารจัดการ KPI 2569 - รพ.ปลวกแดง</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { 
            font-family: 'Sarabun', sans-serif; 
            background-color: #f4f6f9; 
            overflow-x: hidden;
        }
        
        /* สไตล์ของ Sidebar */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            transition: all 0.3s;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.85);
            padding: 10px 15px;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            color: #fff !important;
            font-weight: 600;
        }
        .sidebar .nav-link i { width: 25px; text-align: center; }
        
        /* สไตล์ Accordion ใน Sidebar */
        .accordion-button:after { filter: invert(1) brightness(200%); }
        .accordion-button:not(.collapsed) {
            background-color: rgba(255, 255, 255, 0.1) !important;
            color: #fff !important;
            box-shadow: none;
        }
        .accordion-button { padding: 12px 20px; font-weight: 500; }
        .accordion-button:focus { box-shadow: none; border-color: transparent; }
        
        /* สไตล์ Scrollbar สำหรับ Sidebar */
        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.2); border-radius: 10px; }
        
        /* พื้นที่เนื้อหาหลัก */
        .main-content { 
            width: calc(100% - 280px); 
            padding: 20px 30px; 
            height: 100vh;
            overflow-y: auto;
        }
        
        /* สไตล์ Card สรุปข้อมูล */
        .stat-card { border-left: 5px solid; transition: transform 0.2s; border-radius: 10px; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
    </style>
</head>
<body class="d-flex">

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm border-start border-primary border-4">
            <h4 class="mb-0 text-secondary fw-bold"><i class="fa-solid fa-chart-line text-primary me-2"></i> แดชบอร์ดสรุปผลการดำเนินงาน</h4>
            <div>
                <span class="badge bg-success fs-6 px-3 py-2"><i class="fa-regular fa-calendar me-1"></i> ปีงบประมาณ 2569</span>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card stat-card shadow-sm border-0 border-primary h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 text-sm fw-bold">PP&P Excellence</p>
                                <h3 class="text-primary mb-0 fw-bold" id="ppp-total">กำลังโหลด...</h3>
                                <div id="ppp-passed"><small class="text-muted">กำลังโหลด...</small></div>
                            </div>
                            <div class="fs-1 text-primary opacity-25"><i class="fa-solid fa-heart-pulse"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card stat-card shadow-sm border-0 border-success h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 text-sm fw-bold">Service Excellence</p>
                                <h3 class="text-success mb-0 fw-bold" id="service-total">กำลังโหลด...</h3>
                                <div id="service-passed"><small class="text-muted">กำลังโหลด...</small></div>
                            </div>
                            <div class="fs-1 text-success opacity-25"><i class="fa-solid fa-hand-holding-medical"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card stat-card shadow-sm border-0 border-warning h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 text-sm fw-bold">People Excellence</p>
                                <h3 class="text-warning mb-0 fw-bold" id="people-total">กำลังโหลด...</h3>
                                <div id="people-passed"><small class="text-muted">กำลังโหลด...</small></div>
                            </div>
                            <div class="fs-1 text-warning opacity-25"><i class="fa-solid fa-user-doctor"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="card stat-card shadow-sm border-0 border-danger h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 text-sm fw-bold">Governance Excellence</p>
                                <h3 class="text-danger mb-0 fw-bold" id="gov-total">กำลังโหลด...</h3>
                                <div id="gov-passed"><small class="text-muted">กำลังโหลด...</small></div>
                            </div>
                            <div class="fs-1 text-danger opacity-25"><i class="fa-solid fa-scale-balanced"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="card stat-card shadow-sm border-0 border-info h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 text-sm fw-bold">Health Economy</p>
                                <h3 class="text-info mb-0 fw-bold" id="economy-total">กำลังโหลด...</h3>
                                <div id="economy-passed"><small class="text-muted">กำลังโหลด...</small></div>
                            </div>
                            <div class="fs-1 text-info opacity-25"><i class="fa-solid fa-chart-line"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 border-bottom border-light d-flex justify-content-between">
                        <h6 class="mb-0 fw-bold text-secondary"><i class="fa-solid fa-chart-pie text-primary me-2"></i> สัดส่วนการผ่านเกณฑ์ตัวชี้วัด (36 KPI)</h6>
                    </div>
                    <div class="card-body d-flex justify-content-center">
                        <canvas id="kpiStatusChart" style="max-height: 250px;"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 border-bottom border-light">
                        <h6 class="mb-0 fw-bold text-secondary"><i class="fa-solid fa-bell text-warning me-2"></i> สถานะการเชื่อมต่อข้อมูล</h6>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <i class="fa-solid fa-database text-success me-2"></i> ฐานข้อมูล OPD
                                </div>
                                <span class="badge bg-success rounded-pill">เชื่อมต่อปกติ</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <i class="fa-solid fa-database text-success me-2"></i> ฐานข้อมูล HOS
                                </div>
                                <span class="badge bg-success rounded-pill">เชื่อมต่อปกติ</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3 bg-light">
                                <div class="text-muted small">
                                    <i class="fa-solid fa-clock me-1"></i> อัปเดตข้อมูลล่าสุด: วันนี้
                                </div>
                                <button class="btn btn-sm btn-outline-primary" onclick="location.reload();"><i class="fa-solid fa-rotate-right"></i> รีเฟรช</button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <footer class="text-center text-muted small mt-4 mb-2">
            &copy; 2026 ระบบบริหารจัดการตัวชี้วัด (KPI Dashboard) | โรงพยาบาลปลวกแดง
        </footer>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/dashboard.js"></script>
</body>
</html>