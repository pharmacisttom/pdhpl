// =========================================================
// ไฟล์: /assets/js/dashboard.js
// หน้าที่: จัดการฝั่ง Client-side (ดึง API อัปเดตตัวเลข และวาดกราฟ)
// =========================================================

document.addEventListener("DOMContentLoaded", function() {
    // 1. เรียกใช้งานฟังก์ชันดึงข้อมูลเมื่อหน้าเว็บโหลดเสร็จ
    fetchDashboardSummary();
    
    // 2. เรียกใช้งานฟังก์ชันดึงข้อมูลเพื่อวาดกราฟ 
    fetchChartData();
});

// ---------------------------------------------------------
// ฟังก์ชันที่ 1: ดึงข้อมูลสรุป 4+1 Excellence
// ---------------------------------------------------------
function fetchDashboardSummary() {
    fetch('api/get_dashboard_summary.php')
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                const summary = data.data;
                
                // อัปเดต UI 
                updateExcellenceCard('ppp', summary['PP&P Excellence']);
                updateExcellenceCard('service', summary['Service Excellence']);
                updateExcellenceCard('people', summary['People Excellence']);
                updateExcellenceCard('gov', summary['Governance Excellence']);
                updateExcellenceCard('economy', summary['Health Economy Excellence']);
                
                // วาดกราฟโดนัทตรงกลาง (ถ้ามีข้อมูล)
                // renderKpiStatusChart(summary);
            } else {
                console.error("API Error: ", data.message);
            }
        })
        .catch(error => {
            console.error("Fetch error: ", error);
        });
}

function updateExcellenceCard(idPrefix, data) {
    const totalEl = document.getElementById(idPrefix + '-total');
    const passedEl = document.getElementById(idPrefix + '-passed');
    
    if (totalEl && data) {
        totalEl.innerText = data.total + " ตัวชี้วัด";
        
        let html = '';
        if (data.total > 0) {
            let percent = (data.passed / data.total) * 100;
            if (percent >= 80) {
                html = `<small class="text-success"><i class="fa-solid fa-arrow-up"></i> ผ่านเกณฑ์ ${data.passed} ตัวชี้วัด</small>`;
            } else if (percent > 0) {
                html = `<small class="text-warning"><i class="fa-solid fa-minus"></i> ผ่านเกณฑ์ ${data.passed} ตัวชี้วัด</small>`;
            } else {
                html = `<small class="text-danger"><i class="fa-solid fa-arrow-down"></i> ไม่ผ่านเกณฑ์เลย</small>`;
            }
        } else {
            html = `<small class="text-muted">ยังไม่มีข้อมูล</small>`;
        }
        
        if(passedEl) passedEl.innerHTML = html;
    }
}

// ---------------------------------------------------------
// ฟังก์ชันที่ 3: ดึงข้อมูลกราฟจาก API
// ---------------------------------------------------------
function fetchChartData() {
    fetch('api/get_chart_data.php')
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                // โยนข้อมูลที่ได้ไปให้ฟังก์ชันวาดกราฟ
                renderMainChart(data.labels, data.opdData, data.herbData);
            } else {
                console.error("Chart API Error: ", data.message);
            }
        })
        .catch(error => console.error("Fetch error (Chart): ", error));
}

// ---------------------------------------------------------
// ฟังก์ชันที่ 4: วาดกราฟแสดงแนวโน้ม (Chart.js)
// ---------------------------------------------------------
let mainChartInstance = null; // เก็บตัวแปรกราฟไว้ เผื่อมีการอัปเดตข้อมูล (Destroy & Re-render)

function renderMainChart(labelsData, opdDataArray, herbDataArray) {
    const ctx = document.getElementById('mainChart').getContext('2d');
    
    // ถ้าเคยวาดกราฟไว้แล้ว ให้ทำลายของเก่าทิ้งก่อนวาดใหม่ (ป้องกันกราฟซ้อนกันเวลา Refresh ข้อมูล)
    if(mainChartInstance !== null) {
        mainChartInstance.destroy();
    }

    mainChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labelsData, // ใช้ Labels จากฐานข้อมูล (เช่น ต.ค. 68, พ.ย. 68)
            datasets: [
                {
                    label: 'ผู้รับบริการ OPD (คน)',
                    data: opdDataArray, // ข้อมูลคนไข้จริง
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    yAxisID: 'y' // ผูกกับแกน Y ซ้าย
                },
                {
                    label: 'มูลค่ายาสมุนไพร (บาท)',
                    data: herbDataArray, // ข้อมูลเงินจริง
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    tension: 0.4,
                    yAxisID: 'y1' // ผูกกับแกน Y ขวา
                }
            ]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) { label += ': '; }
                            if (context.parsed.y !== null) {
                                // ใส่ลูกน้ำให้ตัวเลขใน Tooltip
                                label += new Intl.NumberFormat('th-TH').format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: { display: true, text: 'จำนวนคน' }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: { display: true, text: 'จำนวนเงิน (บาท)' },
                    grid: { drawOnChartArea: false } // ป้องกันเส้น Grid ของ 2 แกนทับกันจนมั่ว
                }
            }
        }
    });
}