<?php session_start(); ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>กลุ่มวัยเรียน/วัยรุ่น - รพ.ปลวกแดง</title>
    
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
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 bg-white p-3 rounded shadow-sm border-start border-warning border-4">
            <div class="mb-3 mb-md-0">
                <h4 class="mb-1 text-secondary fw-bold">
                    <i class="fa-solid fa-person-skating text-warning me-2"></i> 
                    งานส่งเสริมสุขภาพ กลุ่มวัยเรียน/วัยรุ่น (15-19 ปี)
                </h4>
                <small class="text-muted">ปีงบประมาณ 2569 | ข้อมูลประชากร Type 1 และ 3</small>
            </div>
            
            <div class="d-flex gap-2">
                <a href="api/export_teen_target.php" class="btn btn-sm btn-outline-success shadow-sm" target="_blank" title="โหลดรายชื่อวัยรุ่นที่ยังไม่ได้คัดกรองซึมเศร้า">
                    <i class="fa-solid fa-file-excel me-1"></i> เป้าหมายคัดกรองซึมเศร้า (15-19 ปี)
                </a>
            </div>
        </div>

        <div class="row mb-4">
            
            <div class="col-md-6 mb-3 mb-md-0">
                <div class="card card-kpi shadow-sm border-0 border-top border-danger border-4 h-100">
                    <div class="card-body text-center py-4">
                        <h6 class="text-muted fw-bold small mb-3">อัตราการตั้งครรภ์ในหญิงอายุ 15-19 ปี (ต่อพันประชากร)</h6>
                        <h1 class="fw-bold text-danger display-5" id="pregRate"><i class="fas fa-spinner fa-spin fs-4"></i></h1>
                        
                        <div class="d-flex justify-content-between text-muted small mt-4 px-3 border-top pt-3">
                            <span class="fw-bold text-success">เป้าหมาย < 15 ต่อพัน</span>
                            <span>ตั้งครรภ์: <strong id="pregCount" class="text-danger">0</strong> ราย / หญิงทั้งหมด: <strong id="popFemale" class="text-dark">0</strong> ราย</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card card-kpi shadow-sm border-0 border-top border-success border-4 h-100">
                    <div class="card-body text-center py-4">
                        <h6 class="text-muted fw-bold small mb-3">ร้อยละวัยรุ่นได้รับการคัดกรองซึมเศร้า (1I8)</h6>
                        <h1 class="fw-bold text-success display-5" id="depressRate"><i class="fas fa-spinner fa-spin fs-4"></i></h1>
                        
                        <div class="progress mt-3 mb-2" style="height: 12px; background-color: #e9ecef;">
                            <div id="depressProgress" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%"></div>
                        </div>
                        
                        <div class="d-flex justify-content-between text-muted small mt-2 px-3">
                            <span class="fw-bold text-success">เป้าหมาย > 70%</span>
                            <span>คัดกรองแล้ว: <strong id="depressCount" class="text-success">0</strong> / เป้าหมายรวม: <strong id="popAll" class="text-dark">0</strong> คน</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom-0">
                <h6 class="mb-0 fw-bold text-secondary">
                    <i class="fa-solid fa-chart-area text-warning me-2"></i> สถิติวัยรุ่น (15-19 ปี) เข้ารับบริการรายเดือน
                </h6>
            </div>
            <div class="card-body pt-0">
                <div style="position: relative; height: 320px; width: 100%;">
                    <canvas id="teenChart"></canvas>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/adolescent.js"></script>

</body>
</html>