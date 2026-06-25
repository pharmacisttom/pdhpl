/**
 * ไฟล์: assets/js/maternal.js
 * หน้าที่: ดึงข้อมูลจาก API และวาดกราฟ Dashboard แม่และเด็ก
 * รวมถึงการประมวลผลกราฟ Stacked Bar แยกตาม Type Area
 */

document.addEventListener("DOMContentLoaded", function() {
    // เรียกฟังก์ชันดึงข้อมูลเมื่อโหลดหน้าเว็บ
    fetchMaternalData();
});

function fetchMaternalData() {
    // ดึงข้อมูลจาก API ที่เราสร้างไว้
    fetch('api/get_kpi_maternal.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // 1. อัปเดตตัวเลขในการ์ด Summary (แสดงผลงานเฉพาะ Type 1,3)
                document.getElementById('anc12Weeks').innerText = data.anc_percent + ' %';
                document.getElementById('childDev').innerText = data.dev_percent + ' %';
                document.getElementById('vaccineCov').innerText = data.vaccine_percent + ' %';
                document.getElementById('maternalDeath').innerText = data.death_count + ' ราย';

                // 2. วาดกราฟแนวโน้มรายเดือน (Line/Bar Chart)
                renderMonthlyChart(data.chart.labels, data.chart.data_anc);

                // 3. วาดกราฟสัดส่วนพัฒนาการเด็ก (Doughnut Chart)
                renderDevDoughnut(parseFloat(data.dev_percent));

                // 4. วาดกราฟวิเคราะห์ภาระงานแยกตาม Type Area (Stacked Bar Chart) **ตัวใหม่**
                renderTypeAreaStackedChart(data.typearea_analysis);

            } else {
                console.error("Backend Error:", data.message);
            }
        })
        .catch(error => {
            console.error("Fetch Error:", error);
        });
}

/**
 * กราฟที่ 1: แนวโน้มหญิงตั้งครรภ์รายเดือน
 */
function renderMonthlyChart(labels, dataValues) {
    const ctx = document.getElementById('maternalChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'จำนวนหญิงตั้งครรภ์รายใหม่ (รวมทุกกลุ่ม)',
                data: dataValues,
                fill: true,
                backgroundColor: 'rgba(214, 51, 132, 0.1)',
                borderColor: '#d63384',
                tension: 0.3,
                pointBackgroundColor: '#d63384'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
}

/**
 * กราฟที่ 2: สัดส่วนพัฒนาการเด็ก (Doughnut)
 */
function renderDevDoughnut(percent) {
    const ctx = document.getElementById('devChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['สมวัย (ในเขต)', 'อื่นๆ'],
            datasets: [{
                data: [percent, 100 - percent],
                backgroundColor: ['#0dcaf0', '#f1f1f1'],
                borderWidth: 0
            }]
        },
        options: {
            cutout: '80%',
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}

/**
 * กราฟที่ 3: วิเคราะห์ภาระงาน Stacked Bar (หัวใจสำคัญของการแยกผลงาน)
 */
function renderTypeAreaStackedChart(analysis) {
    const ctx = document.getElementById('typeAreaChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: analysis.labels,
            datasets: [
                {
                    label: 'ผลงานเป้าหมาย (Type 1, 3)',
                    data: analysis.in_target,
                    backgroundColor: '#198754', // สีเขียว
                    borderRadius: 4
                },
                {
                    label: 'ภาระงานนอกเขต (Type อื่นๆ)',
                    data: analysis.out_target,
                    backgroundColor: '#adb5bd', // สีเทา
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { stacked: true },
                y: { 
                    stacked: true, 
                    beginAtZero: true,
                    title: { display: true, text: 'จำนวนราย' }
                }
            },
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        // แสดงผลรวมเมื่อเอาเมาส์ไปชี้ที่แท่งกราฟ
                        footer: (items) => {
                            let total = 0;
                            items.forEach(item => total += item.parsed.y);
                            return 'รวมภาระงานทั้งหมด: ' + total + ' ราย';
                        }
                    }
                }
            }
        }
    });
}