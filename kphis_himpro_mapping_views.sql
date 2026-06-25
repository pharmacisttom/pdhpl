-- ==============================================================================
-- KPHIS to HIMPRO Mapping Views
-- สคริปต์สำหรับสร้าง View ในฐานข้อมูล HIMPRO เพื่อจำลองโครงสร้างตารางให้เหมือน HOSxP
-- (เพื่อให้ระบบ KPHIS สามารถอ่านข้อมูลจาก HIMPRO ได้โดยไม่ต้องแก้ Source Code)
-- ==============================================================================

-- 1. ข้อมูลผู้ใช้งานระบบและการ Login (opduser)
-- แมปข้อมูลจากตารางผู้ใช้งานของ Himpro (hosdata.user)
CREATE OR REPLACE VIEW opduser AS 
SELECT 
    userlogin AS loginname, 
    npass AS passweb,       -- เข้ารหัส MD5
    username AS name, 
    NULL AS doctorcode,     -- ใส่ชื่อฟิลด์รหัสแพทย์ถ้ามี
    groupcode AS entryposition 
FROM hosdata.user;


-- 2. ข้อมูลทั่วไปของผู้ป่วย (patient)
-- แมปข้อมูลจากตารางประวัติผู้ป่วย (สมมติว่าเป็น opd.pt หรือ pcu.person)
-- หมายเหตุ: กรุณาแก้ไขชื่อตาราง `opd.pt` เป็นชื่อตารางเก็บข้อมูลประวัติคนไข้ที่แท้จริงของ Himpro
CREATE OR REPLACE VIEW patient AS 
SELECT 
    hn AS hn, 
    cid AS cid, 
    NULL AS pname,    -- คำนำหน้าชื่อ (แก้ไขชื่อฟิลด์ถ้ามี)
    fname AS fname, 
    lname AS lname, 
    brthdate AS birthday, -- วันเกิด (อาจเป็น birth_date)
    sex AS sex, 
    NULL AS bloodgrp  -- กรุ๊ปเลือด
FROM opd.pt;


-- 3. ข้อมูลการเข้ารับบริการผู้ป่วยใน (ipt และ an_stat)
-- แมปข้อมูลผู้ป่วยในจากตาราง ipd.ipd
CREATE OR REPLACE VIEW ipt AS 
SELECT 
    an AS an, 
    hn AS hn, 
    vn AS vn,         -- เลขที่รับบริการ OPD ถ้ามี
    ward AS ward,     -- รหัสตึกที่แอดมิท
    regdate AS regdate, 
    regtime AS regtime, 
    dchdate AS dchdate 
FROM ipd.ipd;

CREATE OR REPLACE VIEW an_stat AS 
SELECT 
    an AS an, 
    hn AS hn, 
    0 AS age_y,       -- คำนวณอายุจากวันเกิด (แก้ไขลอจิกตามที่ Himpro เก็บ)
    0 AS age_m, 
    0 AS age_d 
FROM ipd.ipd;


-- 4. ข้อมูลการรับบริการ OPD และ ER (ovst และ vn_stat)
-- แมปจากตาราง opd.opd
CREATE OR REPLACE VIEW ovst AS 
SELECT 
    vn AS vn,         -- อาจเป็นคอลัมน์ vn หรือลำดับการมารับบริการ
    hn AS hn, 
    regdate AS vstdate, 
    regtime AS vsttime, 
    NULL AS spclty    -- รหัสแผนก (แก้ไขเป็นฟิลด์ที่ถูกต้อง)
FROM opd.opd;

CREATE OR REPLACE VIEW vn_stat AS 
SELECT 
    vn AS vn, 
    hn AS hn, 
    regdate AS vstdate 
FROM opd.opd;


-- 5. ข้อมูลประวัติการแพ้ยา (opd_allergy)
-- แมปข้อมูลประวัติการแพ้ยา 
-- หมายเหตุ: เปลี่ยน `opd.drugallergy` เป็นชื่อตารางแพ้ยาของ Himpro
CREATE OR REPLACE VIEW opd_allergy AS 
SELECT 
    hn AS hn, 
    regdate AS report_date, 
    drugname AS agent,   -- ชื่อยาที่แพ้
    symptom AS symptom, 
    NULL AS allergy_relation_id 
FROM opd.drugallergy;


-- 6. ข้อมูลรายการยาพื้นฐาน (drugitems)
-- แมปรายการยาจากตาราง hos.itemlist
CREATE OR REPLACE VIEW drugitems AS 
SELECT 
    itemcode AS icode, 
    itemname AS name, 
    strength AS strength, 
    unit AS units, 
    NULL AS drugusage 
FROM hos.itemlist
WHERE groupitem IN ('DRUG', 'HERB'); -- กรองเฉพาะหมวดหมู่ยา


-- 7. ข้อมูลตึก แผนก และแพทย์ (ward, spclty, doctor)
-- หมายเหตุ: เปลี่ยนชื่อตารางและฟิลด์ให้ตรงกับตาราง Master ของ Himpro
CREATE OR REPLACE VIEW ward AS 
SELECT 
    wardcode AS ward, 
    wardname AS name 
FROM ipd.ward;

CREATE OR REPLACE VIEW spclty AS 
SELECT 
    depcode AS spclty, 
    depname AS name 
FROM opd.department;

CREATE OR REPLACE VIEW doctor AS 
SELECT 
    doctorcode AS code, 
    doctorname AS name, 
    licenseno AS licenseno 
FROM hos.doctor;

-- ==============================================================================
-- สิ้นสุดสคริปต์
-- ==============================================================================
