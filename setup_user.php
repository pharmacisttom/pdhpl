<?php
// setup_user.php
require_once 'config/database.php';

$database = new Database();
$db = $database->getAppConnection();

// ข้อมูล User กลางที่ต้องการ
$username = "admin";
$password = "admin123";
$full_name = "ผู้ดูแลระบบ";
$role = "admin";

// 1. เข้ารหัสผ่านด้วย bcrypt
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // 2. ตรวจสอบก่อนว่ามี User นี้ในระบบหรือยัง
    $check_query = "SELECT id FROM users WHERE username = :username";
    $stmt_check = $db->prepare($check_query);
    $stmt_check->bindParam(":username", $username);
    $stmt_check->execute();

    if ($stmt_check->rowCount() > 0) {
        // อัปเดตรหัสผ่านใหม่เผื่อมีปัญหา
        $update_query = "UPDATE users SET password = :password WHERE username = :username";
        $stmt_update = $db->prepare($update_query);
        $stmt_update->bindParam(":password", $hashed_password);
        $stmt_update->bindParam(":username", $username);
        $stmt_update->execute();
        
        echo "<h3 style='color: orange;'>มีผู้ใช้งาน '{$username}' ในระบบแล้ว ทำการรีเซ็ตรหัสผ่านให้ใหม่เรียบร้อยครับ</h3>";
        echo "<p><strong>Username:</strong> {$username}</p>";
        echo "<p><strong>Password:</strong> {$password}</p>";
        echo "<p><a href='login.php'>คลิกที่นี่เพื่อเข้าสู่ระบบ</a></p>";
    } else {
        // 3. เพิ่ม User ลงในฐานข้อมูล
        $insert_query = "INSERT INTO users (username, password, full_name, role) VALUES (:username, :password, :full_name, :role)";
        $stmt_insert = $db->prepare($insert_query);
        
        $stmt_insert->bindParam(":username", $username);
        $stmt_insert->bindParam(":password", $hashed_password);
        $stmt_insert->bindParam(":full_name", $full_name);
        $stmt_insert->bindParam(":role", $role);
        
        if ($stmt_insert->execute()) {
            echo "<h3 style='color: green;'>✅ สร้างผู้ใช้งานสำเร็จ!</h3>";
            echo "<p><strong>Username:</strong> {$username}</p>";
            echo "<p><strong>Password:</strong> {$password} <em>(ถูกเข้ารหัสในฐานข้อมูลแล้ว)</em></p>";
            echo "<p>ตอนนี้คุณสามารถไปที่หน้า <a href='login.php'>เข้าสู่ระบบ</a> ได้เลยครับ</p>";
        }
    }
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ เกิดข้อผิดพลาด: " . $e->getMessage() . "</h3>";
}
?>