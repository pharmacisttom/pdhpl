-- สร้างตารางสำหรับเก็บข้อมูลหลักของ KPI ทั้ง 36 ตัว
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `role` enum('admin','executive','head','staff') NOT NULL DEFAULT 'staff',
  `email` varchar(100) DEFAULT NULL,
  `line_token` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- รหัสผ่าน default คือ 'admin123' (bcrypt hash: $2y$10$f/9Bv1v.1hN3ZlT7L3XwQOUgV/d922H/M8XGk9O9/Rk5.5yUa2pwe)
INSERT IGNORE INTO `users` (`username`, `password`, `full_name`, `role`, `is_active`) VALUES
('admin', '$2y$10$f/9Bv1v.1hN3ZlT7L3XwQOUgV/d922H/M8XGk9O9/Rk5.5yUa2pwe', 'ผู้ดูแลระบบ', 'admin', 1);

CREATE TABLE IF NOT EXISTS `kpi_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kpi_number` varchar(10) NOT NULL COMMENT 'รหัสตัวชี้วัด เช่น 1, 2.1, 16.1',
  `kpi_name` varchar(255) NOT NULL COMMENT 'ชื่อตัวชี้วัด',
  `excellence_category` varchar(100) NOT NULL COMMENT 'หมวดหมู่ เช่น PP&P, Service, People',
  `target_value` float NOT NULL DEFAULT '0' COMMENT 'ค่าเป้าหมาย',
  `target_type` enum('percentage','rate','count') NOT NULL DEFAULT 'percentage' COMMENT 'ประเภทเป้าหมาย',
  `target_operator` enum('>','>=','<','<=','=') NOT NULL DEFAULT '>=' COMMENT 'เงื่อนไขผ่านเกณฑ์',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `kpi_number` (`kpi_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- สร้างตารางสำหรับเก็บข้อมูล Cache ที่คำนวณจาก Himpro เพื่อให้ Dashboard โหลดเร็วขึ้น
CREATE TABLE IF NOT EXISTS `kpi_data_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kpi_id` int(11) NOT NULL,
  `b_year` varchar(4) NOT NULL COMMENT 'ปีงบประมาณ เช่น 2569',
  `month` int(2) DEFAULT NULL COMMENT 'เดือนที่ประเมิน (1-12)',
  `quarter` int(1) DEFAULT NULL COMMENT 'ไตรมาส (1-4)',
  `param_a` float NOT NULL DEFAULT '0' COMMENT 'ตัวตั้ง (A)',
  `param_b` float NOT NULL DEFAULT '0' COMMENT 'ตัวหาร (B)',
  `calculated_result` float NOT NULL DEFAULT '0' COMMENT 'ผลลัพธ์ที่คำนวณได้',
  `last_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `kpi_id` (`kpi_id`),
  KEY `b_year` (`b_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- เพิ่มข้อมูลตัวชี้วัดตั้งต้น (ตัวอย่าง 5 ตัวแรก)
INSERT INTO `kpi_master` (`kpi_number`, `kpi_name`, `excellence_category`, `target_value`, `target_type`, `target_operator`) VALUES
('1', 'อัตราส่วนการตายมารดาไทยต่อการเกิดมีชีพแสนคน', 'PP&P Excellence', 15, 'rate', '<='),
('2.1', 'ร้อยละของเด็ก อายุ 0 - 5 ปี มีพัฒนาการสมวัย', 'PP&P Excellence', 88, 'percentage', '>='),
('2.2', 'ร้อยละของเด็ก อายุ 0 – 5 ปี ฟันดีไม่มีผุ', 'PP&P Excellence', 80, 'percentage', '>='),
('11', 'อัตราตายของผู้ป่วยโรคหลอดเลือดสมอง (Stroke: I60-I64)', 'Service Excellence', 7, 'percentage', '<='),
('18', 'อัตราตายผู้ป่วยติดเชื้อในกระแสเลือดแบบรุนแรงชนิด community-acquired', 'Service Excellence', 24, 'percentage', '<=');
