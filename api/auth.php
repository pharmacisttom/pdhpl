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
    $db = $database->getAppConnection();

    try {
        $query = "SELECT id, username, password, full_name, role FROM users WHERE username = :username LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // ตรวจสอบรหัสผ่านแบบเข้ารหัส bcrypt หรืออนุญาตให้ใช้ admin123 ชั่วคราวเพื่อเข้าสู่ระบบครั้งแรก
            if (password_verify($password, $row['password']) || ($username === 'admin' && $password === 'admin123')) {
                // สร้าง Session 
                $_SESSION['kpi_user_id'] = $row['id'];
                $_SESSION['kpi_username'] = $row['username'];
                $_SESSION['kpi_full_name'] = $row['full_name'];
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