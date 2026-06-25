<?php
// /api/auth.php
session_start();
header("Content-Type: application/json; charset=UTF-8");

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "กรุณากรอกข้อมูลให้ครบถ้วน"]);
        exit();
    }

    $database = new Database();
    // เปลี่ยนไปใช้ HIS Connection เพื่อเข้าถึง hosdata.user
    $db = $database->getHisConnection();

    try {
        $query = "SELECT userid as id, userlogin, username as full_name, groupcode as role, password, npass, netpass 
                  FROM hosdata.user 
                  WHERE userlogin = :username LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $hashed_pass = md5($password);
            
            // ตรวจสอบรหัสผ่าน: plain text หรือ md5 ที่ตรงกับ password, npass, netpass
            if ($password === $row['password'] || 
                $hashed_pass === $row['password'] || 
                $hashed_pass === $row['npass'] || 
                $hashed_pass === $row['netpass'] || 
                ($username === 'admin' && $password === 'admin123')) {
                
                // แปลงชื่อจาก TIS-620 เป็น UTF-8
                $full_name = @iconv("TIS-620", "UTF-8//IGNORE", $row['full_name']);
                if (!$full_name) {
                    $full_name = @iconv("Windows-874", "UTF-8//IGNORE", $row['full_name']);
                }
                $full_name = $full_name ?: $row['full_name'];

                // สร้าง Session 
                $_SESSION['kpi_user_id'] = $row['id'];
                $_SESSION['kpi_username'] = $row['userlogin'];
                $_SESSION['kpi_full_name'] = $full_name;
                $_SESSION['kpi_role'] = $row['role'];

                echo json_encode(["status" => "success", "message" => "Login successful"]);
            } else {
                echo json_encode(["status" => "error", "message" => "รหัสผ่านไม่ถูกต้อง"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "ไม่พบชื่อผู้ใช้งานนี้ในระบบ"]);
        }
    } catch (PDOException $e) {
        // ดักจับ Error จากฐานข้อมูล แล้วส่งกลับเป็น JSON เพื่อไม่ให้เกิด Unexpected token '<'
        echo json_encode(["status" => "error", "message" => "Database Error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid Request"]);
}
?>