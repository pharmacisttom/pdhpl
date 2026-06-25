/**
 * ไฟล์: assets/js/communicable.js
 * หน้าที่: ดึงข้อมูลระบาดวิทยาแยกตามกลุ่มโรค และจัดการการแสดงผลบนตาราง 506
 * รองรับ: การแบ่งประเภทโรคชัดเจน และปีงบประมาณ 2569
 */

document.addEventListener("DOMContentLoaded", function() {
    // เรียกฟังก์ชันดึงข้อมูลเมื่อโหลดหน้าจอ
    fetchCommunicableTable();
});

function fetchCommunicableTable() {
    const tableBody = document.getElementById('kpiTableBody');
    
    // ตรวจสอบว่ามี Element นี้ใน HTML หรือไม่ป้องกัน Error
    if (!tableBody) return;

    // ดึงข้อมูลจาก API
    fetch('api/get_kpi_communicable.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            // ✅ แก้ไข Error: ตรวจสอบโครงสร้างข้อมูลก่อนใช้ forEach
            if (data.status === 'success' && data.data && Array.isArray(data.data)) {
                let html = '';

                // 1. วนลูปตามกลุ่มโรค (Group)
                data.data.forEach(groupObj => {
                    
                    // สร้างแถวหัวข้อกลุ่มโรค (Category Row)
                    html += `
                        <tr class="group-header" style="background-color: #f2f2f2; font-weight: bold;">
                            <td colspan="17" class="text-start py-2 ps-3">
                                <i class="fa-solid fa-folder-tree me-2 text-secondary"></i> ${groupObj.group}
                            </td>
                        </tr>
                    `;

                    // 2. ตรวจสอบว่ามีรายการโรคในกลุ่มนี้หรือไม่
                    if (groupObj.items && Array.isArray(groupObj.items)) {
                        groupObj.items.forEach(item => {
                            html += `
                                <tr>
                                    <td class="disease-name sticky-col text-start ps-5">
                                        ${item.name}
                                    </td>
                                    
                                    <td>${numberFormat(item.y67)}</td>
                                    <td>${numberFormat(item.y68)}</td>
                                    
                                    <td class="bg-primary bg-opacity-10 fw-bold text-primary">
                                        ${numberFormat(item.y69)}
                                    </td>
                                    
                                    <td class="bg-warning bg-opacity-10 fw-bold">
                                        ${item.median}
                                    </td>

                                    ${item.monthly.map(count => {
                                        let cellClass = count > 0 ? 'fw-bold text-dark' : 'text-muted';
                                        return `<td class="${cellClass}">${numberFormat(count)}</td>`;
                                    }).join('')}
                                </tr>
                            `;
                        });
                    }
                });

                // นำ HTML ไปใส่ในตาราง
                tableBody.innerHTML = html;

            } else {
                // กรณี API ส่งข้อมูลมาไม่ถูกต้อง
                tableBody.innerHTML = `<tr><td colspan="17" class="text-danger py-5">ข้อมูลจากระบบไม่ถูกต้อง: ${data.message || 'Unknown Error'}</td></tr>`;
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            tableBody.innerHTML = `<tr><td colspan="17" class="text-danger py-5">เกิดข้อผิดพลาดในการเชื่อมต่อ: ${error.message}</td></tr>`;
        });
}

/**
 * ฟังก์ชันช่วยจัดการรูปแบบตัวเลข
 */
function numberFormat(num) {
    if (num === 0 || num === "0" || num === null) {
        return '<span class="opacity-25">0</span>';
    }
    return new Intl.NumberFormat().format(num);
}