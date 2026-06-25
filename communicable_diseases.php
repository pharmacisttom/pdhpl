<?php session_start(); ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานโรคติดต่อเฝ้าระวัง - รพ.ปลวกแดง</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f4f7f6; }
        .table-kpi { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); background: white; }
        .table-kpi thead th {
            background-color: #f8f9fa;
            vertical-align: middle;
            text-align: center;
            font-size: 0.8rem;
            border: 1px solid #dee2e6;
            color: #495057;
            white-space: nowrap;
        }
        .table-kpi tbody td {
            font-size: 0.85rem;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #eee;
        }
        .group-header {
            background-color: #e9ecef !important;
            color: #2c3e50;
            font-weight: 700;
            text-align: left !important;
        }
        .disease-name {
            text-align: left !important;
            padding-left: 2rem !important;
            min-width: 250px;
        }
        .sticky-col {
            position: sticky;
            left: 0;
            background-color: white;
            z-index: 1;
            border-right: 2px solid #dee2e6 !important;
        }
        .bg-current-year { background-color: rgba(13, 110, 253, 0.05); }
        .text-current { color: #0d6efd; font-weight: 700; }
        @media print {
            .sidebar, .btn-print { display: none !important; }
            .main-content { margin: 0; padding: 0; width: 100%; }
        }
    </style>
</head>
<body class="d-flex">

    <?php include 'sidebar.php'; ?>

    <div class="main-content flex-grow-1 p-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm border-start border-danger border-4">
            <div>
                <h4 class="mb-1 text-secondary fw-bold">
                    <i class="fa-solid fa-file-medical text-danger me-2"></i> 
                    รายงานจำนวนผู้ป่วยโรคติดต่อเฝ้าระวัง (ระบาดวิทยา 506)
                </h4>
                <small class="text-muted">
                    ข้อมูลเปรียบเทียบรายปี (2567-2569) และรายเดือนปีงบประมาณปัจจุบัน (รวม OPD & IPD)
                </small>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-sm btn-outline-secondary btn-print shadow-sm">
                    <i class="fa-solid fa-print me-1"></i> พิมพ์รายงาน
                </button>
                <a href="api/get_kpi_communicable.php" target="_blank" class="btn btn-sm btn-outline-primary btn-print shadow-sm">
                    <i class="fa-solid fa-code me-1"></i> JSON API
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-kpi mb-0">
                        <thead>
                            <tr>
                                <th rowspan="2" class="sticky-col">กลุ่มโรค / รายการเฝ้าระวัง</th>
                                <th colspan="3" class="bg-light">จำนวนผู้ป่วย (ปีงบประมาณ)</th>
                                <th rowspan="2" class="bg-warning bg-opacity-10">Median<br>(67-68)</th>
                                <th colspan="12" class="bg-primary bg-opacity-10">จำนวนผู้ป่วยรายเดือน (ปีงบประมาณปัจจุบัน 2569)</th>
                            </tr>
                            <tr>
                                <th>2567</th>
                                <th>2568</th>
                                <th class="bg-primary text-white">2569</th>
                                <th>ต.ค.</th><th>พ.ย.</th><th>ธ.ค.</th><th>ม.ค.</th><th>ก.พ.</th><th>มี.ค.</th>
                                <th>เม.ย.</th><th>พ.ค.</th><th>มิ.ย.</th><th>ก.ค.</th><th>ส.ค.</th><th>ก.ย.</th>
                            </tr>
                        </thead>
                        <tbody id="kpiTableBody">
                            <tr>
                                <td colspan="17" class="py-5 text-center">
                                    <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                                    กำลังประมวลผลข้อมูลระบาดวิทยาจากฐานข้อมูล JHCIS...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-0 py-3">
                <small class="text-muted">
                    <i class="fa-solid fa-circle-info me-1"></i> 
                    หมายเหตุ: ข้อมูลปี 2569 นับตั้งแต่วันที่ 1 ต.ค. 2568 ถึงปัจจุบัน | Median คำนวณจากค่าเฉลี่ยย้อนหลัง 2 ปี
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/communicable.js"></script>
</body>
</html>