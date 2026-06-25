/**
 * ไฟล์: assets/js/ncd.js
 * หน้าที่: ดึงข้อมูล KPI โรคไม่ติดต่อ (DM/HT) และอัปเดตหน้าจอ Dashboard
 */

document.addEventListener("DOMContentLoaded", function() {
    // โหลดข้อมูลทันทีที่หน้าเว็บเปิดขึ้นมา
    fetchNCDData();
});

function fetchNCDData() {
    // กำหนด URL ของ API
    const apiUrl = 'api/get_kpi_ncd.php';

    fetch(apiUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log("NCD Data:", data); // แสดงข้อมูลใน Console เพื่อตรวจสอบ

            if (data.status === 'success') {
                // 1. อัปเดตตัวเลขร้อยละ (%)
                updateElement('dmRate', data.dm_rate + ' %');
                updateElement('htRate', data.ht_rate + ' %');

                // 2. อัปเดต Progress Bar (ความยาวของหลอดสี)
                const dmProg = document.getElementById('dmProgress');
                if (dmProg) dmProg.style.width = data.dm_rate + '%';

                const htProg = document.getElementById('htProgress');
                if (htProg) htProg.style.width = data.ht_rate + '%';

                // 3. อัปเดตตัวเลขจำนวนคน (สงสัยป่วย / ยืนยันแล้ว)
                updateElement('dmSuspect', numberFormat(data.summary.dm_suspect));
                updateElement('dmConfirmed', numberFormat(data.summary.dm_confirmed));
                
                updateElement('htSuspect', numberFormat(data.summary.ht_suspect));
                updateElement('htConfirmed', numberFormat(data.summary.ht_confirmed));

                // 4. วาดกราฟแสดงสถิติรายเดือน
                renderNCDChart(data.monthly_data);

            } else {
                console.error("API Error:", data.message);
                showErrorState("ไม่สามารถโหลดข้อมูลได้");
            }
        })
        .catch(error => {
            console.error("Fetch Error:", error);
            showErrorState("เกิดข้อผิดพลาดในการเชื่อมต่อ");
        });
}

/**
 * ฟังก์ชันช่วยสำหรับอัปเดตข้อความใน HTML
 * (ตรวจสอบก่อนว่ามี Element นั้นอยู่จริง ป้องกัน Error)
 */
function updateElement(elementId, value) {
    const el = document.getElementById(elementId);
    if (el) {
        el.innerText = value;
    }
}

/**
 * ฟังก์ชันช่วยจัดรูปแบบตัวเลข (ใส่ลูกน้ำ)
 */
function numberFormat(num) {
    if (!num) return "0";
    return new Intl.NumberFormat().format(num);
}

/**
 * ฟังก์ชันแสดงข้อความ Error บนหน้าจอ
 */
function showErrorState(msg) {
    updateElement('dmRate', 'Error');
    updateElement('htRate', 'Error');
    updateElement('dmSuspect', '-');
    updateElement('dmConfirmed', '-');
    updateElement('htSuspect', '-');
    updateElement('htConfirmed', '-');
}

/**
 * ฟังก์ชันวาดกราฟสถิติรายเดือนด้วย Chart.js
 */
let ncdChartInstance = null; // เก็บ Instance ของกราฟไว้เพื่อทำลายก่อนวาดใหม่

function renderNCDChart(monthlyData) {
    const canvas = document.getElementById('ncdChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    
    // ถ้าเคยวาดกราฟไปแล้ว ให้ทำลายทิ้งก่อน (ป้องกันกราฟซ้อนทับกันเวลา Refresh)
    if (ncdChartInstance) {
        ncdChartInstance.destroy();
    }

    ncdChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            // เดือนในปีงบประมาณ (ต.ค. - ก.ย.)
            labels: ['ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.'],
            datasets: [{
                label: 'จำนวนผู้มารับบริการคัดกรองความเสี่ยง (คน)',
                data: monthlyData,
                backgroundColor: 'rgba(13, 202, 240, 0.5)', // สีฟ้า Info อ่อนๆ
                borderColor: 'rgba(13, 202, 240, 1)',       // สีฟ้า Info เข้ม
                borderWidth: 1,
                borderRadius: 4, // ขอบกราฟมนนิดๆ
                hoverBackgroundColor: 'rgba(13, 202, 240, 0.8)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // ปล่อยให้กราฟขยายตาม DIV แม่
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ` จำนวน: ${numberFormat(context.raw)} คน`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1, // บังคับให้แสดงตัวเลขแกน Y เป็นจำนวนเต็ม
                        font: {
                            family: "'Sarabun', sans-serif"
                        }
                    }
                },
                x: {
                    ticks: {
                        font: {
                            family: "'Sarabun', sans-serif"
                        }
                    }
                }
            }
        }
    });
}