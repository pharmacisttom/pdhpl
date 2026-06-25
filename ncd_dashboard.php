<?php session_start(); ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NCD Dashboard 2569 - รพ.ปลวกแดง</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        body { 
            font-family: 'Sarabun', sans-serif; 
            background-color: #f4f7f6; 
        }
        .card-kpi { 
            transition: transform 0.2s ease, box-shadow 0.2s ease; 
        }
        .card-kpi:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
        }
    </style>
</head>
<body class="d-flex">

    <?php include 'sidebar.php'; ?>

    <div class="main-content flex-grow-1 p-4">
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 bg-white p-3 rounded shadow-sm border-start border-info border-4">
            <div class="mb-3 mb-md-0">
                <h4 class="mb-1 text-secondary fw-bold">
                    <i class="fa-solid fa-heart-circle-check text-info me-2"></i> 
                    KPI โรคไม่ติดต่อ (NCD)
                </h4>
                <small class="text-muted">
                    ปีงบประมาณ 2569 | ข้อมูลการคัดกรองและการติดตามยืนยันวินิจฉัยกลุ่มเสี่ยง
                </small>
            </div>
            
            <div class="d-flex gap-2">
                <a href="api/export_ncd_target.php?type=dm" class="btn btn-sm btn-outline-primary shadow-sm" target="_blank" title="โหลดรายชื่อเป้าหมายเบาหวาน (อายุ 35+ Type 1,3)">
                    <i class="fa-solid fa-file-excel me-1"></i> เป้าหมายคัดกรอง DM
                </a>
                <a href="api/export_ncd_target.php?type=ht" class="btn btn-sm btn-outline-danger shadow-sm" target="_blank" title="โหลดรายชื่อเป้าหมายความดันฯ (อายุ 35+ Type 1,3)">
                    <i class="fa-solid fa-file-excel me-1"></i> เป้าหมายคัดกรอง HT
                </a>
            </div>
        </div>

        <div class="row mb-4">
            
            <div class="col-md-6 mb-3 mb-md-0">
                <div class="card card-kpi shadow-sm border-0 border-top border-primary border-4 h-100">
                    <div class="card-body text-center py-4">
                        <h6 class="text-muted fw-bold small mb-3">ร้อยละกลุ่มสงสัยป่วย เบาหวาน (DM) ได้รับการยืนยัน</h6>
                        <h1 class="fw-bold text-primary display-5" id="dmRate"><i class="fas fa-spinner fa-spin fs-4"></i></h1>
                        
                        <div class="progress mt-4 mb-2" style="height: 12px; background-color: #e9ecef;">
                            <div id="dmProgress" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%"></div>
                        </div>
                        
                        <div class="d-flex justify-content-between text-muted small mt-3 px-3">
                            <span class="fw-bold text-success">เป้าหมาย > 70%</span>
                            <span>สงสัยป่วย: <strong id="dmSuspect" class="text-dark">0</strong> / ยืนยันแล้ว: <strong id="dmConfirmed" class="text-primary">0</strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card card-kpi shadow-sm border-0 border-top border-danger border-4 h-100">
                    <div class="card-body text-center py-4">
                        <h6 class="text-muted fw-bold small mb-3">ร้อยละกลุ่มสงสัยป่วย ความดันฯ (HT) ได้รับการยืนยัน</h6>
                        <h1 class="fw-bold text-danger display-5" id="htRate"><i class="fas fa-spinner fa-spin fs-4"></i></h1>
                        
                        <div class="progress mt-4 mb-2" style="height: 12px; background-color: #e9ecef;">
                            <div id="htProgress" class="progress-bar progress-bar-striped progress-bar-animated bg-danger" role="progressbar" style="width: 0%"></div>
                        </div>
                        
                        <div class="d-flex justify-content-between text-muted small mt-3 px-3">
                            <span class="fw-bold text-success">เป้าหมาย > 80%</span>
                            <span>สงสัยป่วย: <strong id="htSuspect" class="text-dark">0</strong> / ยืนยันแล้ว: <strong id="htConfirmed" class="text-danger">0</strong></span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom-0">
                <h6 class="mb-0 fw-bold text-secondary">
                    <i class="fa-solid fa-chart-line text-info me-2"></i> สถิติการคัดกรอง NCD รายเดือน (จำนวนผู้มารับบริการสะสม)
                </h6>
            </div>
            <div class="card-body pt-0">
                <div style="position: relative; height: 320px; width: 100%;">
                    <canvas id="ncdChart"></canvas>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/ncd.js"></script>

</body>
</html>