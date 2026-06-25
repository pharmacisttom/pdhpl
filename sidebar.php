<?php 
// ดึงชื่อไฟล์ปัจจุบัน เพื่อใช้ไฮไลท์เมนู (Active)
$current_page = basename($_SERVER['PHP_SELF']); 
?>
<style>
    .sidebar {
        background: linear-gradient(180deg, #2c3e50 0%, #000000 100%);
        min-height: 100vh;
        color: white;
        transition: all 0.3s;
    }
    .nav-link {
        color: rgba(255,255,255,0.7);
        border-radius: 8px;
        margin: 2px 0;
        transition: 0.2s;
        display: flex;
        align-items: center;
    }
    .nav-link:hover {
        background: rgba(255,255,255,0.1);
        color: #fff;
    }
    .nav-link.active {
        background: #0d6efd !important;
        color: #fff !important;
        font-weight: bold;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
    }
    .accordion-button:not(.collapsed) {
        background-color: rgba(255,255,255,0.05);
        color: #fff;
    }
    .accordion-button::after {
        filter: brightness(0) invert(1);
    }
    .nav-item i {
        width: 25px;
        text-align: center;
    }
</style>

<div class="sidebar d-flex flex-column shadow" style="width: 280px; overflow-y: auto;">
    <div class="text-center py-4 border-bottom border-light border-opacity-25 mb-3">
        <h4 class="fw-bold mb-0"><i class="fa-solid fa-hospital-user text-info"></i> รพ.ปลวกแดง</h4>
        <small class="text-light opacity-50">Smart Hospital Dashboard 2026</small>
    </div>
    
    <div class="accordion accordion-flush flex-grow-1" id="menuKPI">
        
        <div class="nav-item mx-3 mb-2">
            <a href="index.php" class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?> py-3">
                <i class="fa-solid fa-house-chimney-medical me-2"></i> หน้าหลักภาพรวม
            </a>
        </div>

        <div class="accordion-item bg-transparent border-0 mx-2">
            <h2 class="accordion-header">
                <button class="accordion-button <?php echo ($current_page == 'ppp_excellence.php') ? '' : 'collapsed'; ?> bg-transparent text-light" type="button" data-bs-toggle="collapse" data-bs-target="#menuPPP">
                    <i class="fa-solid fa-heart-pulse me-2"></i> PP&P Excellence
                </button>
            </h2>
            <div id="menuPPP" class="accordion-collapse collapse <?php echo ($current_page == 'ppp_excellence.php') ? 'show' : ''; ?>">
                <div class="accordion-body p-0 pb-2">
                    <div class="nav-item ms-4 me-3">
                        <a href="ppp_excellence.php" class="nav-link <?php echo ($current_page == 'ppp_excellence.php') ? 'active' : 'text-light opacity-75'; ?> small py-2">
                            <i class="fa-solid fa-list-check me-2"></i> ตัวชี้วัด PP&P ทั้งหมด
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion-item bg-transparent border-0 mx-2">
            <h2 class="accordion-header">
                <button class="accordion-button <?php echo ($current_page == 'service_excellence.php') ? '' : 'collapsed'; ?> bg-transparent text-light" type="button" data-bs-toggle="collapse" data-bs-target="#menuService">
                    <i class="fa-solid fa-hand-holding-medical me-2"></i> Service Excellence
                </button>
            </h2>
            <div id="menuService" class="accordion-collapse collapse <?php echo ($current_page == 'service_excellence.php') ? 'show' : ''; ?>">
                <div class="accordion-body p-0 pb-2">
                    <div class="nav-item ms-4 me-3">
                        <a href="service_excellence.php" class="nav-link <?php echo ($current_page == 'service_excellence.php') ? 'active' : 'text-light opacity-75'; ?> small py-2">
                            <i class="fa-solid fa-list-check me-2"></i> ตัวชี้วัด Service ทั้งหมด
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion-item bg-transparent border-0 mx-2">
            <h2 class="accordion-header">
                <button class="accordion-button <?php echo ($current_page == 'people_excellence.php') ? '' : 'collapsed'; ?> bg-transparent text-light" type="button" data-bs-toggle="collapse" data-bs-target="#menuPeople">
                    <i class="fa-solid fa-user-doctor me-2"></i> People Excellence
                </button>
            </h2>
            <div id="menuPeople" class="accordion-collapse collapse <?php echo ($current_page == 'people_excellence.php') ? 'show' : ''; ?>">
                <div class="accordion-body p-0 pb-2">
                    <div class="nav-item ms-4 me-3">
                        <a href="people_excellence.php" class="nav-link <?php echo ($current_page == 'people_excellence.php') ? 'active' : 'text-light opacity-75'; ?> small py-2">
                            <i class="fa-solid fa-users me-2"></i> การจัดการกำลังคนด้านสุขภาพ
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion-item bg-transparent border-0 mx-2">
            <h2 class="accordion-header">
                <button class="accordion-button <?php echo ($current_page == 'governance_excellence.php') ? '' : 'collapsed'; ?> bg-transparent text-light" type="button" data-bs-toggle="collapse" data-bs-target="#menuGovernance">
                    <i class="fa-solid fa-scale-balanced me-2"></i> Governance Excellence
                </button>
            </h2>
            <div id="menuGovernance" class="accordion-collapse collapse <?php echo ($current_page == 'governance_excellence.php') ? 'show' : ''; ?>">
                <div class="accordion-body p-0 pb-2">
                    <div class="nav-item ms-4 me-3">
                        <a href="governance_excellence.php" class="nav-link <?php echo ($current_page == 'governance_excellence.php') ? 'active' : 'text-light opacity-75'; ?> small py-2">
                            <i class="fa-solid fa-building-columns me-2"></i> ธรรมาภิบาลและองค์กรคุณภาพ
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion-item bg-transparent border-0 mx-2">
            <h2 class="accordion-header">
                <button class="accordion-button <?php echo ($current_page == 'health_economy_excellence.php') ? '' : 'collapsed'; ?> bg-transparent text-light" type="button" data-bs-toggle="collapse" data-bs-target="#menuEconomy">
                    <i class="fa-solid fa-chart-line me-2"></i> Health Economy
                </button>
            </h2>
            <div id="menuEconomy" class="accordion-collapse collapse <?php echo ($current_page == 'health_economy_excellence.php') ? 'show' : ''; ?>">
                <div class="accordion-body p-0 pb-2">
                    <div class="nav-item ms-4 me-3">
                        <a href="health_economy_excellence.php" class="nav-link <?php echo ($current_page == 'health_economy_excellence.php') ? 'active' : 'text-light opacity-75'; ?> small py-2">
                            <i class="fa-solid fa-leaf me-2"></i> เศรษฐกิจสุขภาพเป็นเลิศ
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php if(isset($_SESSION['kpi_role']) && $_SESSION['kpi_role'] === 'admin'): ?>
        <hr class="text-light opacity-25 mx-4 my-3">
        <div class="nav-item mx-3">
            <a href="#" class="nav-link text-light opacity-75 small py-2"><i class="fa-solid fa-users-gear me-2"></i> จัดการผู้ใช้งานระบบ</a>
        </div>
        <?php endif; ?>

    </div>

    <div class="mt-auto p-3 border-top border-light border-opacity-10">
        <div class="d-flex align-items-center mb-3">
            <div class="flex-shrink-0">
                <div class="bg-white rounded-circle p-1 d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;">
                    <i class="fa-solid fa-user-tie text-primary"></i>
                </div>
            </div>
            <div class="flex-grow-1 ms-3 overflow-hidden">
                <p class="mb-0 text-white text-truncate small fw-bold">
                    <?php echo htmlspecialchars($_SESSION['kpi_full_name'] ?? 'Guest User'); ?>
                </p>
                <small class="text-light opacity-50 small">โรงพยาบาลปลวกแดง</small>
            </div>
        </div>
        <a href="api/logout.php" class="btn btn-outline-danger btn-sm w-100 border-0"><i class="fa-solid fa-power-off me-2"></i> ออกจากระบบ</a>
    </div>
</div>