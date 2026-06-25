# KPHIS Data Dictionary (Mapping Guide)

เนื่องจากระบบ KPHIS ถูกออกแบบมาให้ทำงานร่วมกับโครงสร้างฐานข้อมูลของ **HOSxP** เป็นหลัก หากโรงพยาบาลปลวกแดงต้องการนำ KPHIS ไปทำงานร่วมกับ **HIMPRO** จะต้องมีการสร้าง View หรือ Mapping ตารางในฝั่ง HIMPRO ให้มีชื่อและโครงสร้างตรงกับที่ KPHIS ต้องการเรียกใช้งาน (Query) ครับ

นี่คือ Data Dictionary ส่วนสำคัญของ **HOSxP (ฝั่ง HIS)** ที่ระบบ KPHIS จำเป็นต้องเข้าไปอ่านข้อมูล:

## 1. ข้อมูลผู้ใช้งานระบบและการ Login (opduser)
ใช้สำหรับตรวจสอบสิทธิ์เข้าใช้งานระบบ KPHIS 

| Field Name | Type | Description |
|---|---|---|
| `loginname` | varchar | ชื่อผู้ใช้งาน (Username) |
| `passweb` | varchar | รหัสผ่าน (Password) ที่เข้ารหัสเป็น MD5 |
| `name` | varchar | ชื่อ-นามสกุล ของผู้ใช้งาน |
| `doctorcode` | varchar | รหัสแพทย์/พยาบาล (อ้างอิงกับตาราง doctor) |
| `entryposition` | varchar | ตำแหน่งผู้ใช้งาน |

## 2. ข้อมูลทั่วไปของผู้ป่วย (patient)
ใช้แสดงข้อมูล Demographic ของคนไข้

| Field Name | Type | Description |
|---|---|---|
| `hn` | varchar | รหัสประจำตัวผู้ป่วย (HN) |
| `cid` | varchar | เลขประจำตัวประชาชน 13 หลัก |
| `pname` | varchar | คำนำหน้าชื่อ |
| `fname` | varchar | ชื่อจริง |
| `lname` | varchar | นามสกุล |
| `birthday` | date | วันเกิด |
| `sex` | varchar | เพศ (1=ชาย, 2=หญิง) |
| `bloodgrp` | varchar | กรุ๊ปเลือด |

## 3. ข้อมูลการเข้ารับบริการผู้ป่วยใน (ipt / an_stat)
ใช้สำหรับดึงรายชื่อและข้อมูลผู้ป่วยที่กำลัง Admit (ผู้ป่วยใน)

**ตาราง ipt**
| Field Name | Type | Description |
|---|---|---|
| `an` | varchar | เลขที่ Admit (AN) |
| `hn` | varchar | รหัสประจำตัวผู้ป่วย (HN) |
| `vn` | varchar | เลขที่เข้ารับบริการ OPD (VN) |
| `ward` | varchar | รหัสตึกผู้ป่วยในที่ Admit (อ้างอิงตาราง ward) |
| `regdate` | date | วันที่ Admit |
| `regtime` | time | เวลาที่ Admit |
| `dchdate` | date | วันที่จำหน่าย (Discharge) ถ้ายังไม่จำหน่ายมักจะเป็น null |

**ตาราง an_stat** (สถิติและข้อมูลสรุปของ AN)
| Field Name | Type | Description |
|---|---|---|
| `an` | varchar | เลขที่ Admit (AN) |
| `hn` | varchar | รหัสประจำตัวผู้ป่วย (HN) |
| `age_y` | int | อายุ (ปี) ขณะที่ Admit |
| `age_m` | int | อายุ (เดือน) |
| `age_d` | int | อายุ (วัน) |

## 4. ข้อมูลการรับบริการ OPD และ ER (ovst / vn_stat)
ใช้สำหรับดึงรายชื่อผู้ป่วยที่แผนกฉุกเฉิน (ER) หรือหน้าห้องตรวจต่างๆ

| Field Name | Type | Description |
|---|---|---|
| `vn` | varchar | เลขที่เข้ารับบริการ (VN) |
| `hn` | varchar | รหัสประจำตัวผู้ป่วย (HN) |
| `vstdate` | date | วันที่เข้ารับบริการ |
| `vsttime` | time | เวลาที่เข้ารับบริการ |
| `spclty` | varchar | รหัสแผนก (อ้างอิงตาราง spclty) |

## 5. ข้อมูลประวัติการแพ้ยา (opd_allergy)
ระบบ KPHIS จะดึงไปแสดงหน้าจอเตือนและแสดงประวัติการแพ้ยาเพื่อป้องกัน Medication Error

| Field Name | Type | Description |
|---|---|---|
| `hn` | varchar | รหัสประจำตัวผู้ป่วย (HN) |
| `report_date` | date | วันที่รายงานการแพ้ยา |
| `agent` | varchar | ชื่อยาที่แพ้ |
| `symptom` | varchar | อาการที่แพ้ (เช่น ผื่น, หายใจไม่ออก) |
| `allergy_relation_id` | varchar | ระดับความรุนแรงของการแพ้ |

## 6. ข้อมูลรายการยาพื้นฐาน (drugitems)
ตารางหลักที่ KPHIS จะไป Query รายการยาเพื่อใช้ในการสั่งยาหรือสั่ง Order

| Field Name | Type | Description |
|---|---|---|
| `icode` | varchar | รหัสรายการยา (ตัวเลข) |
| `name` | varchar | ชื่อสามัญ/ชื่อทางการค้าของยา |
| `strength` | varchar | ความแรงของยา |
| `units` | varchar | หน่วยของยา (เช่น เม็ด, ขวด) |
| `drugusage` | varchar | วิธีใช้ยาเริ่มต้น (อ้างอิงตาราง drugusage) |

## 7. ข้อมูลตึก แผนก และแพทย์ (ward, spclty, doctor)

**ตาราง ward** (ตึกผู้ป่วยใน)
| Field Name | Type | Description |
|---|---|---|
| `ward` | varchar | รหัสตึก |
| `name` | varchar | ชื่อตึกผู้ป่วยใน |

**ตาราง spclty** (แผนก)
| Field Name | Type | Description |
|---|---|---|
| `spclty` | varchar | รหัสแผนก |
| `name` | varchar | ชื่อแผนก |

**ตาราง doctor** (แพทย์/ผู้ปฏิบัติงาน)
| Field Name | Type | Description |
|---|---|---|
| `code` | varchar | รหัสแพทย์ (doctorcode) |
| `name` | varchar | ชื่อแพทย์/พยาบาล |
| `licenseno` | varchar | เลขที่ใบประกอบวิชาชีพ (ว.) |

---

> [!TIP]
> **คำแนะนำสำหรับ รพ.ปลวกแดง (HIMPRO):**
> 1. ในฐานข้อมูลของ HIMPRO ให้สร้าง `VIEW` โดยนำชื่อ Field ด้านบนนี้ไป Map กับคอลัมน์ที่มีอยู่จริงใน HIMPRO 
> 2. ตัวอย่างการสร้าง View ใน Database: 
>    `CREATE VIEW opduser AS SELECT UserName as loginname, Password as passweb, FullName as name FROM himpro_user_table;`
> 3. ทำแบบนี้กับทุกตารางที่สำคัญ เพื่อหลอกให้ KPHIS เข้าใจว่ากำลังต่อกับ HOSxP อยู่ครับ
