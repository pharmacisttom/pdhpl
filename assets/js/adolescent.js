/**
 * ไฟล์: assets/js/adolescent.js
 * หน้าที่: ดึงข้อมูลงานส่งเสริมสุขภาพกลุ่มวัยรุ่น (ตั้งครรภ์ & ซึมเศร้า) มาแสดงบน Dashboard
 */

document.addEventListener("DOMContentLoaded", function() {
    // โหลดข้อมูลทันทีเมื่อเปิดหน้าจอ
    fetchAdolescentData();
});

function fetchAdolescentData() {
    // แสดงสถานะกำลังโหลด (สามารถดูได้ผ่าน Console F12)
    console.log("Fetching Adolescent data...");

    fetch('api/get_kpi_adolescent.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log("Adolescent Data Received:", data);

            if (data.status === 'success') {
                // 1. อัปเดตข้อมูลอัตราการตั้งครรภ์วัยรุ่น (ต่อพันประชากร)
                updateElement('pregRate', data.preg_rate + ' ‰');
                updateElement('pregCount', numberFormat(data.summary.preg_count));
                updateElement('popFemale', numberFormat(data.summary.pop_female));

                // 2. อัปเดตข้อมูลการคัดกรองซึมเศร้า (ร้อยละ)
                updateElement('depressRate', data.depress_rate + ' %');
                updateElement('depressCount', numberFormat(data.summary.depress_screened));
                updateElement('popAll', numberFormat(data.summary.pop_all));
                
                // อัปเดตความยาวหลอด Progress Bar
                const depressProg = document.getElementById('depressProgress');
                if (depressProg) {
                    depressProg.style.width = data.depress_rate + '%';
                    // เปลี่ยนสีหลอดตามผลงาน (ถ้าถึงเป้า 70% ให้เป็นสีเขียว ถ้าไม่ถึงให้เป็นสีส้ม)
                    if(data.depress_rate >= 70) {
                        depressProg.className = "progress-bar progress-bar-striped progress-bar-animated bg-success";
                    } else {
                        depressProg.className = "progress-bar progress-bar-striped progress-bar-animated bg-warning";
                    }
                }

                // 3. วาดกราฟเส้นแสดงสถิติรายเดือน
                renderTeenChart(data.monthly_data);

            } else {
                console.error("Backend Error:", data.message);
                showErrorState();
            }
        })
        .catch(error => {
            console.error("Fetch Error:", error);
            showErrorState();
        });
}

/**
 * ฟังก์ชันช่วย: อัปเดตข้อความใน HTML อย่างปลอดภัย
 */
function updateElement(elementId, value) {
    const el = document.getElementById(elementId);
    if (el) {
        el.innerText = value;
    }
}

/**
 * ฟังก์ชันช่วย: ใส่ลูกน้ำคั่นหลักพัน (เช่น 1,500)
 */
function numberFormat(num) {
    if (!num && num !== 0) return "0";
    return new Intl.NumberFormat('th-TH').format(num);
}

/**
 * ฟังก์ชันช่วย: แสดงข้อความ Error หาก API ล่มหรือข้อมูลไม่มา
 */
function showErrorState() {
    updateElement('pregRate', 'Error');
    updateElement('depressRate', 'Error');
    updateElement('pregCount', '-');
    updateElement('popFemale', '-');
    updateElement('depressCount', '-');
    updateElement('popAll', '-');
}

/**
 * ฟังก์ชันวาดกราฟเส้น (Line Chart) สำหรับวัยรุ่นด้วย Chart.js
 */
let teenChartInstance = null;

function renderTeenChart(monthlyData) {
    const canvas = document.getElementById('teenChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    
    // ทำลายกราฟเดิมทิ้งก่อนวาดใหม่ ป้องกันการซ้อนทับกัน
    if (teenChartInstance) {
        teenChartInstance.destroy();
    }

    teenChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.'],
            datasets: [{
                label: 'จำนวนการรับบริการคัดกรองสุขภาพวัยรุ่น (ครั้ง)',
                data: monthlyData,
                borderColor: '#ffc107', // สีเหลืองทอง (Warning) ให้เข้ากับ Theme หน้าจอ
                backgroundColor: 'rgba(255, 193, 7, 0.2)', // สีพื้นหลังกราฟโปร่งแสง
                borderWidth: 2,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#ffc107',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true, // ระบายสีใต้เส้นกราฟ
                tension: 0.3 // ทำให้เส้นกราฟมีความโค้งมนสวยงาม
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        font: { family: "'Sarabun', sans-serif" }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ` จำนวน: ${numberFormat(context.raw)} ครั้ง`;
                        }
                    },
                    titleFont: { family: "'Sarabun', sans-serif" },
                    bodyFont: { family: "'Sarabun', sans-serif" }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: { family: "'Sarabun', sans-serif" }
                    }
                },
                x: {
                    ticks: {
                        font: { family: "'Sarabun', sans-serif" }
                    }
                }
            }
        }
    });
}